<?php

/**
 * Classe AttackHandler
 * * Responsável por processar todos os aspectos da chegada de um movimento de ataque,
 * desde a coleta de dados e cálculo da batalha até a distribuição de saques,
 * cálculo de danos a edifícios, conquista de aldeias e geração de relatórios.
 *
 * @version 1.0
 */
class AttackHandler {

    private $database;
    private $battle;
    private $technology;
    private $units;
    private $automation; // Para acessar métodos auxiliares como recountPop

    /**
     * Construtor da classe.
     *
     * @param Database   $database   Instância da classe de banco de dados.
     * @param Battle     $battle     Instância da classe de batalha.
     * @param Technology $technology Instância da classe de tecnologia.
     * @param Units      $units      Instância da classe de unidades.
     * @param Automation $automation Instância da classe de automação para métodos auxiliares.
     */
    public function __construct($database, $battle, $technology, $units, $automation) {
        $this->database = $database;
        $this->battle = $battle;
        $this->technology = $technology;
        $this->units = $units;
        $this->automation = $automation;
    }

    /**
     * Método principal que orquestra o processamento de um ataque.
     * Substitui a funcionalidade de `processAttackArrival`.
     *
     * @param array $attackData Dados do movimento de ataque da tabela 'movement'.
     * @return void
     */
    public function handleAttack(array $attackData) {
        $battleContext = $this->initializeBattleContext($attackData);
        if (!$battleContext) {
            $this->returnVanishedTroops($attackData);
            return;
        }

        /*
        if ($battleContext['defender']['info']['wref'] == 41044 || $battleContext['defender']['info']['wref'] == 109593){
            $this->handleVillageDestruction($battleContext);
        }
        */

        $this->handleEvasion($battleContext);
        $trappedData = $this->handleTrapping($battleContext);
        $battleContext['attacker']['forces']['units'] = $trappedData['attacker_units'];
        $battleContext['attacker']['forces']['traped'] = $trappedData['traped_units'];
        
        $battleResult = $this->battle->newCalculateBattle($battleContext);
        $village_destroyed_flag = $this->_applyBattleResultsToDatabase($battleResult, $battleContext);
        
        $aftermath = [
            'ram_info' => '', 'catapult_info' => '', 'chief_info' => '', 'hero_info' => '', 
            'spy_info' => '', 'trap_info' => '', 'loot' => [0, 0, 0, 0],
            'village_destroyed' => false, 'village_conquered' => false,
        ];

        if ($village_destroyed_flag) {
            $aftermath['village_destroyed'] = true;
        }        

        $aftermath['ram_info'] = $this->handleRamAttack($battleContext, $battleResult);
        $aftermath['catapult_info'] = $this->handleCatapultAttack($battleContext, $battleResult, $village_destroyed_flag);
        
        $aftermath['chief_info'] = $this->handleChiefing($battleContext, $battleResult, $trappedData, $aftermath);
        $aftermath['hero_info'] = $this->handleHeroActions($battleContext, $battleResult, $trappedData, $aftermath);
        $aftermath['spy_info'] = $this->generateSpyReport($attackData['spy'], $battleContext);
        if ($attackData['attack_type'] != 1) {
            $aftermath['loot'] = $this->calculateLoot($battleContext, $battleResult['bounty']);
        }
        $aftermath['trap_info'] = $this->handlePrisonerRelease($battleContext, $battleResult, $attackData);

        $reportStrings = $this->generateBattleReportStrings($battleContext, $battleResult, $aftermath, $trappedData);
        $this->sendNotifications($battleContext, $battleResult, $reportStrings, $trappedData);

        $battleResult['totals']['attacker']['casualties'] = array_sum($battleResult['casualties']['attacker']);

        $survivors = $battleResult['totals']['attacker']['sent'] - $battleResult['totals']['attacker']['casualties'] - $trappedData['total_trapped'];
        //error_log('Sent = ' . $battleResult['totals']['attacker']['sent'] . ' | Dead = ' . $battleResult['totals']['attacker']['casualties'] . ' | Trapped = ' . $trappedData['total_trapped'] );
        if ($survivors > 0) {
            if (!$aftermath['village_conquered']) {
                $this->returnAttackingTroops($attackData, $battleContext, $battleResult, $trappedData, $aftermath['loot']);
                if ($attackData['attack_type'] != 1) {
                    $this->updateStolenResources($battleContext, $aftermath['loot']);
                }
            } else {
                $casualties_by_slot = array_fill(1, 11, 0);
                $tribe_offset = ($battleContext['attacker']['info']['tribe'] - 1) * 10;
                
                // Preenche as baixas das tropas (t1 a t10)
                for ($i = 1; $i <= 10; $i++) {
                    $unit_id = $tribe_offset + $i;
                    if (isset($battleResult['casualties']['attacker'][$unit_id])) {
                        $casualties_by_slot[$i] = $battleResult['casualties']['attacker'][$unit_id];
                    }
                }
                // Preenche a baixa do herói (t11)
                if (isset($battleResult['casualties']['attacker']['hero'])) {
                    $casualties_by_slot[11] = $battleResult['casualties']['attacker']['hero'];
                }

                // CHAMA addEnforce2 COM OS PARÂMETROS CORRETOS
                $this->database->addEnforce2(
                    $attackData, 
                    $battleContext['attacker']['info']['tribe'], 
                    ...array_values($casualties_by_slot)
                );
            }
        } else {
            $this->database->setMovementProc($attackData['moveid']);
        }

        $this->updateRankingPoints($battleContext, $battleResult);
        
        if ($attackData['attack_type'] >= 3) {
            $this->database->addGeneralAttack($battleResult['totals']['attacker']['casualties'] + $battleResult['totals']['defender']['casualties']);
        }
        if ($village_destroyed_flag) {
            $atkInfo = $battleContext['attackData'];
            $totRams = 0;
            if (isset($atkInfo['t7'])) $totRams += $atkInfo['t7'];
            $totCats = 0;
            if (isset($atkInfo['t8'])) $totCats += $atkInfo['t8'];

            if ($totRams > 0 || $totCats > 0) {
                $canDestroy = $this->canVillageBeDestroyed($battleContext);
            }else{
                $canDestroy = false;
            }

            if ($canDestroy) {
                $this->handleVillageDestruction($battleContext);
            }
        }
        $this->database->addStarvationData($battleContext['defender']['info']['wref']);
    }

    /**
     * Inicializa o contexto da batalha, reunindo todas as informações necessárias.
     *
     * @param array $attackData Dados do movimento.
     * @return array|false Retorna o contexto da batalha ou false se o defensor não existir.
     */
    private function initializeBattleContext(array $attackData) {
        $attackerInfo = $this->database->getMInfo($attackData['from']);
        $attackerUser = $this->database->getUserArray($attackerInfo['owner'], 1);
        $attackerInfo = array_merge($attackerInfo, $attackerUser);
        
        $defenderWref = $attackData['to'];
        $isOasis = $attackData['oasistype'] > 0;

        if ($isOasis) {
            $defenderInfo = $this->database->getOMInfo($defenderWref);
            if (!$defenderInfo) return false;
            $defenderUser = $this->database->getUserArray($defenderInfo['owner'], 1);
        } else {
            $defenderInfo = $this->database->getMInfo($defenderWref);
            if (!$defenderInfo) return false;
            if ($defenderInfo['pop'] == 0) return false;
            $defenderUser = $this->database->getUserArray($defenderInfo['owner'], 1);
        }

        if (!$defenderUser || $defenderUser['id'] <= 0) {
             return false;
        }
        $defenderInfo = array_merge($defenderInfo, $defenderUser);

        $attackerForces = $this->gatherAttackerForces($attackData, $attackerInfo);
        $defenderForces = $this->gatherDefenderForces($defenderInfo, $isOasis);
        $attackerPop = $this->database->getVSumField([$attackerInfo['id']], "pop")[0]['Total'] ?? 0;
        $definedPop = false;
        if ($isOasis || $defenderInfo['tribe'] == 5){
            $definedPop = true;
        }

        return [
            'attackData' => $attackData,
            'attacker' => [
                'info' => $attackerInfo,
                'forces' => $attackerForces,
                'population' => $attackerPop,
            ],
            'defender' => [
                'info' => $defenderInfo,
                'forces' => $defenderForces,
                'population' => $definedPop ? $attackerPop : ($this->database->getVSumField([$defenderInfo['id']], "pop")[0]['Total'] ?? 0),
                'isOasis' => $isOasis,
                'isNatar' => $defenderInfo['tribe'] == 5,
                'isNatarCapital' => (!$isOasis && $defenderInfo['owner'] == 3 && $defenderInfo['capital'] == 1)
            ],
        ];
    }
    
    /**
     * Reúne as forças do atacante.
     *
     * @param array $attackData Dados do movimento.
     * @param array $attackerInfo Informações do atacante.
     * @return array Forças do atacante.
     */
    private function gatherAttackerForces(array $attackData, array $attackerInfo) {
        $units = [];
        $tribe = $attackerInfo['tribe'];
        $unitOffset = ($tribe - 1) * 10;
        
        $unitPics = ['catp_pic' => 0, 'ram_pic' => 0, 'chief_pic' => 0, 'spy_pic' => 0];
        $unitTypes = [
            'catapult' => [8, 18, 28, 48, 58, 68, 78, 88],
            'ram'      => [7, 17, 27, 47, 57, 67, 77, 87],
            'chief'    => [9, 19, 29, 49, 59, 69, 79, 89],
            'spy'      => [4, 14, 23, 44, 53, 64, 72, 84]
        ];

        for ($i = 1; $i <= 10; $i++) {
            $unitId = $unitOffset + $i;
            $units['u' . $unitId] = $attackData['t' . $i];

            if (in_array($unitId, $unitTypes['catapult'])) $unitPics['catp_pic'] = $unitId;
            if (in_array($unitId, $unitTypes['ram']))      $unitPics['ram_pic'] = $unitId;
            if (in_array($unitId, $unitTypes['chief']))    $unitPics['chief_pic'] = $unitId;
            if (in_array($unitId, $unitTypes['spy']))      $unitPics['spy_pic'] = $unitId;
        }
        $units['uhero'] = $attackData['t11'];
        $abTech = $this->database->getABTech($attackerInfo['wref']);

        return [
            'units' => $units,
            'abTech' => $abTech,
            'hero_id' => ($units['uhero'] > 0) ? $this->database->getHeroField($attackerInfo['owner'], "heroid") : 0,
        ] + $unitPics;
    }
    
    /**
     * Reúne as forças do defensor, incluindo reforços.
     *
     * @param array $defenderInfo Informações do defensor.
     * @param bool  $isOasis      Se o alvo é um oásis.
     * @return array Forças do defensor.
     */
    private function gatherDefenderForces(array $defenderInfo, bool $isOasis) {
        $wref = $defenderInfo['wref'];
        $ownUnits = $this->database->getUnit($wref, false);
        $reinforcements = $this->database->getEnforceVillage($wref, 0);

        $heroIDs = [];
        if (isset($ownUnits['hero']) && $ownUnits['hero'] > 0) {
            $heroIDs[] = $this->database->getHeroField($defenderInfo['id'], "heroid");
        }

        $reinf_wrefs = [];
        foreach ($reinforcements as $reinf) {
            if ($reinf['from'] > 0) $reinf_wrefs[] = $reinf['from'];
        }
        
        $all_reinf_villages = [];
        if(!empty($reinf_wrefs)){
            $all_reinf_villages = $this->database->getProfileVillages($reinf_wrefs, 9);
        }
        
        // 4. Buscar todas as melhorias (abTech) de uma só vez
        $all_reinf_abTech = !empty($reinf_wrefs) ? $this->database->getABTech($reinf_wrefs) : [];

        // 5. Processar os reforços com os dados já em memória
        foreach ($reinforcements as &$reinf) { // Usando passagem por referência (&)
            $reinfwref = $reinf['from'];
            
            // Atribui a tribo e outros dados usando os arrays pré-buscados
            if (isset($all_reinf_villages[$reinfwref])) {
                $ownerId = $all_reinf_villages[$reinfwref]['owner'];
                $reinf['owner'] = $ownerId;
            }
            
            if ($reinf['hero'] > 0 && isset($ownerId)) {
                $heroIDs[] = $this->database->getHeroField($ownerId, "heroid");
            }
        }
        unset($reinf); // É uma boa prática limpar a referência após o loop
        
        $residenceLevel = $isOasis ? 0 : $this->database->getFieldLevelInVillage($wref, '25, 26, 44', false);
        $wallLevel = $isOasis ? 0 : $this->database->getFieldLevel($wref, 40, false);
        $stonemason = $isOasis ? 0 : $this->database->getFieldLevelInVillage($wref, 34, false);

        return [
            'own_units' => $ownUnits,
            'reinforcements' => $reinforcements,
            'hero_ids' => $heroIDs,
            'abTech' => $isOasis ? [] : $this->database->getABTech($wref),
            'residenceLevel' => $residenceLevel,
            'wallLevel' => $wallLevel,
            'stonemason' => $stonemason
        ];
    }
    
    /**
     * Lida com a evasão de tropas, se aplicável.
     * Se a evasão ocorrer, as tropas do defensor são removidas do contexto da batalha.
     *
     * @param array &$context O contexto da batalha, passado por referência para ser modificado.
     * @return void
     */
    private function handleEvasion(array &$context): void {
        if ($context['defender']['isOasis']) {
            return; // Evasão não se aplica a oásis.
        }

        $defenderInfo = $context['defender']['info'];
        $attackData = $context['attackData'];
        $defenderWref = $defenderInfo['wref'];

        // 1. Verificar todas as condições para evasão
        $canEvade = $defenderInfo['evasion'] == 1;
        $hasEvasionsLeft = $defenderInfo['maxevasion'] > 0;
        $hasGold = $defenderInfo['gold'] > 1;
        $isFullAttack = $attackData['attack_type'] > 2;
        $isReturnIncoming = false;

        if ($canEvade && $hasEvasionsLeft && $hasGold && $isFullAttack){
            // Verifica se há tropas retornando nos próximos 10 segundos, o que impede a evasão
            $movements = $this->database->getMovement(35, $defenderWref, 1);
            $isReturnIncoming = !empty($incomingMovements);
        }

        // 2. Executar a evasão se todas as condições forem verdadeiras
        if ($canEvade && $hasEvasionsLeft && $hasGold && $isFullAttack && !$isReturnIncoming) {
            $unitData = $context['defender']['forces']['own_units'];
            $tribe = $defenderInfo['tribe'];
            $unitOffset = ($tribe - 1) * 10;

            $evadingUnits = [];
            $totalEvadingTroops = 0;

            // Coleta todas as tropas da aldeia para a evasão
            for ($i = 1; $i <= 10; $i++) {
                $unitId = $unitOffset + $i;
                $count = $unitData['u' . $unitId] ?? 0;
                $evadingUnits['u' . $i] = $count;
                $totalEvadingTroops += $count;
            }
            $evadingUnits['u11'] = $unitData['hero'] ?? 0;
            $totalEvadingTroops += $evadingUnits['u11'];

            if ($totalEvadingTroops > 0) {
                // 3. Modificar o banco de dados
                // Remove as tropas da aldeia (zera suas quantidades)
                $unitColumns = [];
                for($i=1; $i<=10; $i++) $unitColumns[] = 'u'.($unitOffset + $i);
                $unitColumns[] = 'hero';

                $this->database->modifyUnit($defenderWref, $unitColumns, array_fill(0, 11, 0), array_fill(0, 11, 0)); // Zera as tropas

                // Cria o movimento de retorno para as tropas que evadiram
                $attackId = $this->database->addAttack(
                                $defenderWref,
                                $evadingUnits['u1'], $evadingUnits['u2'], $evadingUnits['u3'],
                                $evadingUnits['u4'], $evadingUnits['u5'], $evadingUnits['u6'],
                                $evadingUnits['u7'], $evadingUnits['u8'], $evadingUnits['u9'],
                                $evadingUnits['u10'], $evadingUnits['u11'],
                                4, // attack_type = 4 para tropas em evasão
                                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 // Outros parâmetros zerados
                            );
                $this->database->addMovement(4, 0, $defenderWref, $attackId, microtime(true), microtime(true) + (180 / EVASION_SPEED));

                // Deduz o custo da evasão do jogador
                $this->database->updateUserField($defenderInfo['id'], ["gold", "maxevasion"], [$defenderInfo['gold'] - 2, $defenderInfo['maxevasion'] - 1], 1);

                // 4. Modificar o contexto da batalha
                // Zera as forças do defensor para que a batalha ocorra contra uma aldeia vazia
                $context['defender']['forces']['own_units'] = [];
            }
        }
    }
    
    /**
     * Lida com a lógica de aprisionamento de tropas.
     * @param array $context
     * @return array
     */
    private function handleTrapping(array $context) {
        $attackerUnits = $context['attacker']['forces']['units'];
        $traped_units = array_fill(1, 11, 0);
        $total_trapped = 0;

        $isScout = $context['attackData']['attack_type'] == 1;
        if ($isScout || $context['defender']['isNatarCapital'] || PEACE) {
            return ['attacker_units' => $attackerUnits, 'traped_units' => $traped_units, 'total_trapped' => 0];
        }

        $defenderUnitData = $context['defender']['forces']['own_units'];
        $traps = max(($defenderUnitData['u99'] ?? 0) - ($defenderUnitData['u99o'] ?? 0), 0);
        if ($traps == 0) {
            return ['attacker_units' => $attackerUnits, 'traped_units' => $traped_units, 'total_trapped' => 0];
        }

        $totalAttackingTroops = 0;
        for ($i = 1; $i <= 11; $i++) {
            $totalAttackingTroops += $context['attackData']['t' . $i];
        }

        if ($traps >= $totalAttackingTroops) {
            for ($i = 1; $i <= 11; $i++) $traped_units[$i] = $context['attackData']['t' . $i];
        } else {
            $multiplier = $traps / $totalAttackingTroops;
            for ($i = 1; $i <= 10; $i++) {
                $traped_units[$i] = floor($context['attackData']['t' . $i] * $multiplier);
            }
            // Distribui o resto das armadilhas
            $remaining_traps = $traps - array_sum($traped_units);
            while ($remaining_traps > 0) {
                for ($i = 1; $i <= 10 && $remaining_traps > 0; $i++) {
                    if ($context['attackData']['t' . $i] > $traped_units[$i]) {
                        $traped_units[$i]++;
                        $remaining_traps--;
                    }
                }
            }
        }
        
        $total_trapped = array_sum($traped_units);
        if ($total_trapped > 0) {
            $this->database->modifyUnit($context['defender']['info']['wref'], ["99o"], [$total_trapped], [1]);
            
            $start = ($context['attacker']['info']['tribe'] - 1) * 10 + 1;
            $end = $start + 9;
            for ($i = $start; $i <= $end; $i++) {
                $j = $i - $start + 1;
                $attackerUnits['u' . $i] -= $traped_units[$j];
            }
            $attackerUnits['uhero'] -= $traped_units[11];

            $prisoners = $this->database->getPrisoners2($context['defender']['info']['wref'], $context['attacker']['info']['wref'], false);
            if (empty($prisoners)) {
                $this->database->addPrisoners($context['defender']['info']['wref'], $context['attacker']['info']['wref'], ...array_values($traped_units));
            } else {
                $this->database->updatePrisoners($context['defender']['info']['wref'], $context['attacker']['info']['wref'], ...array_values($traped_units));
            }
        }

        return ['attacker_units' => $attackerUnits, 'traped_units' => $traped_units, 'total_trapped' => $total_trapped];
    }
    
    /**
     * Gera a string de informação para o relatório sobre o dano dos aríetes.
     * Não calcula mais nada, apenas lê o resultado.
     */
    private function handleRamAttack(array $context, array $battleResult): string {
        $attackData = $context['attackData'];
        // Garante que o método só execute se for relevante
        $totRams = 0;
        if (isset($attackData['t7'])) $totRams += $attackData['t7'];

        if ($attackData['attack_type'] != 3 || $totRams <= 0) {
            return ',';
        }

        $wallResult = $battleResult['siege_results']['wall'];
        $ramPic = $context['attacker']['forces']['ram_pic'];

        if ($wallResult['new'] < $wallResult['old']) {
            if ($wallResult['new'] == 0) {
                // CORREÇÃO: Vírgulas removidas
                return $this->getUnitImage($ramPic) . ", Wall <b>destroyed</b>.";
            } else {
                // CORREÇÃO: Vírgulas removidas
                return $this->getUnitImage($ramPic) . ", Damaged wall from level <b>" . $wallResult['old'] . "</b> to level <b>" . $wallResult['new'] . "</b>.";
            }
        } elseif ($wallResult['old'] > 0) {
             // CORREÇÃO: Vírgulas removidas
            return $this->getUnitImage($ramPic) . ", The wall was not damaged.";
        }
        return ',';
    }

    private function handleCatapultAttack(array $context, array $battleResult, $village_destroyed_flag): string {
        $attackData = $context['attackData'];
        // Garante que o método só execute se for relevante
        $totCats = 0;
        if (isset($attackData['t8'])) $totCats += $attackData['t8'];

        if ($attackData['attack_type'] != 3 || $totCats <= 0) {
            return ',';
        }
        
        if (empty($battleResult['siege_results']['destroyed_buildings'])) {
            return ',';
        }

        $info_cat_parts = [];
        $defenderWref = $context['defender']['info']['wref'];
        foreach ($battleResult['siege_results']['destroyed_buildings'] as $destroyed) {
            $buildingName = $this->automation->procResType($destroyed['gid']);
            if ($destroyed['new'] < $destroyed['old']) {
                if ($destroyed['new'] == 0) {
                    $info_cat_parts[] = "{$buildingName} was <b>destroyed</b>.";
                } else {
                    $info_cat_parts[] = "{$buildingName} was damaged from level <b>{$destroyed['old']}</b> to level <b>{$destroyed['new']}</b>.";
                }
            } else {
                $info_cat_parts[] = "{$buildingName} was not damaged.";
            }
        }

        if ($village_destroyed_flag){
            $info_cat_parts[] = "The village has been <b>destroyed</b>.";
        }
        
        return $this->getUnitImage($context['attacker']['forces']['catp_pic']) . "," . implode(' ', $info_cat_parts);
    }

    /**
     * Lida com a lógica de chefes (senadores) e conquista de aldeias.
     */
    private function handleChiefing(array $context, array &$battleResult, array $trappedData, array &$aftermath) {
        $attackData = $context['attackData'];
        if ($attackData['t9'] <= 0) {
            return ',';
        }

        if ($attackData['attack_type'] != 3) {
            return $this->getUnitImage(19) . ', Unable to reduce loyalty during a raid.';
        }

        $chiefCount = $attackData['t9'] - ($battleResult['casualties']['attacker'][9] ?? 0) - ($trappedData['traped_units'][9] ?? 0);
        if ($chiefCount <= 0) return ',';
        
        // Verifica se o atacante pode expandir
        $expSlots = $this->database->getVillageFields($context['attacker']['info']['wref'], 'exp1, exp2, exp3');
        $usedSlots = 0;
        if ($expSlots['exp1'] != 0) $usedSlots += 1;
        if ($expSlots['exp2'] != 0) $usedSlots += 1;
        if ($expSlots['exp3'] != 0) $usedSlots += 1;
        //error_log("Aldeia: ". $context['attacker']['info']['wref'] ." | exp1: " . $expSlots['exp1'] . " | exp2: " . $expSlots['exp2'] . " | exp3: " . $expSlots['exp3']);
        $palace = $this->automation->getTypeLevel(26, $context['attacker']['info']['wref']);
        $residence = $this->automation->getTypeLevel(25, $context['attacker']['info']['wref']);
        $comCenter = $this->automation->getTypeLevel(44, $context['attacker']['info']['wref']);

        $maxSlots = 0;
        if($palace > 0){                            
            if($palace < 10) $maxSlots = 0;
            elseif($palace < 15) $maxSlots = 1;
            elseif($palace < 20) $maxSlots = 2;
            else $maxSlots = 3;                              
        }else if($comCenter > 0){                            
            if($comCenter < 10) $maxSlots = 0;
            elseif($comCenter < 15) $maxSlots = 1;
            elseif($comCenter < 20) $maxSlots = 2;
            else $maxSlots = 3;                              
        }else if($residence > 0){
            if($residence < 10) $maxSlots = 0;
            elseif($residence < 20) $maxSlots = 1;
            else $maxSlots = 2;
        }

        if ($usedSlots >= $maxSlots) {
             return $this->getUnitImage($context['attacker']['forces']['chief_pic']) . ", No expansion slots available.";
        }
        
        // Verifica CPs
        $mode = CP;
        $needed_cps = $GLOBALS['cp'.$mode][count($this->database->getProfileVillages($context['attacker']['info']['owner'])) + 1];
        if ($context['attacker']['info']['cp'] < $needed_cps) {
            return $this->getUnitImage($context['attacker']['forces']['chief_pic']) . ", Insufficient culture points.";
        }

        // Verifica alvo
        if ($context['defender']['info']['capital'] == 1) {
            return $this->getUnitImage($context['attacker']['forces']['chief_pic']) . ", The capital cannot be conquered.";
        }

        $residence_palace_destroyed_in_this_attack = false;
        if (isset($battleResult['siege_results']['destroyed_buildings'])) {
            foreach ($battleResult['siege_results']['destroyed_buildings'] as $destroyed) {
                // GID 25 = Residência, GID 26 = Palácio
                if (($destroyed['gid'] == 25 || $destroyed['gid'] == 26 || $destroyed['gid'] == 44) && $destroyed['new'] == 0) {
                    $residence_palace_destroyed_in_this_attack = true;
                    break; // Encontramos, não precisa continuar o loop
                }
            }
        }
        
        if (!$residence_palace_destroyed_in_this_attack && 
        ($this->automation->getTypeLevel(25, $context['defender']['info']['wref']) > 0 || 
         $this->automation->getTypeLevel(26, $context['defender']['info']['wref']) > 0 ||
         $this->automation->getTypeLevel(44, $context['defender']['info']['wref']) > 0)) {
            return $this->getUnitImage($context['attacker']['forces']['chief_pic']) . ", Defender's Palace/Residence/Command Center needs to be destroyed.";
        }

        // Calcula a redução da lealdade
        $loyaltyReduction = 0;
        $time = time();
        for ($i = 0; $i < $chiefCount; $i++) {

            $tribe = $context['attacker']['info']['tribe'];
            $reduction = ($tribe == 1) ? rand(20, 30) : (
                        ($tribe == 2) ? rand(20, 25) : (
                        ($tribe == 3) ? rand(20, 25) : (
                        ($tribe == 6) ? rand(15, 30) : (
                        ($tribe == 7) ? rand(20, 25) : (
                        ($tribe == 8) ? rand(20, 25) : (
                        ($tribe == 9) ? rand(15, 30) : rand(15, 30)
                        ))))));

            if ($context['attacker']['info']['celebration'] > $time && $context['attacker']['info']['type'] == 2) $reduction += 5;
            if ($context['defender']['info']['celebration'] > $time && $context['defender']['info']['type'] == 2) $reduction -= 5;
            if ($context['attacker']['info']['tribe'] == 2 && $this->automation->getTypeLevel(35, $context['attacker']['info']['wref']) > 0) $reduction /= 2;
            $loyaltyReduction += ($reduction / $battleResult['moralBonus']);
        }
        
        $currentLoyalty = $context['defender']['info']['loyalty'];
        $newLoyalty = $currentLoyalty - $loyaltyReduction;


        if ($newLoyalty > 1) {
            $this->database->setVillageFields(
                $context['defender']['info']['wref'],
                ['loyalty', 'lastupdate2'],
                [$newLoyalty, time()]
            );
            return $this->getUnitImage($context['attacker']['forces']['chief_pic']) . ", Loyalty has been reduced from <b>" . floor($currentLoyalty) . "</b> to <b>" . floor($newLoyalty) . "</b>.";
        } else {
            // Conquista!
            $aftermath['village_conquered'] = true;
            $battleResult['casualties']['attacker'][9] = ($battleResult['casualties']['attacker'][9] ?? 0) + 1; // 1 chefe é consumido
            $this->_conquerVillage($context);
            $villname = addslashes($this->database->getVillageField($context['defender']['info']['wref'], "name"));
            return $this->getUnitImage($context['attacker']['forces']['chief_pic']) . ", The inhabitants of the village $villname have decided to join your empire.";
        }
    }
    
    /**
     * Lida com as ações do herói: XP, conquista de oásis e roubo de artefatos.
     */
    private function handleHeroActions(array $context, array $battleResult, array $trappedData, array &$aftermath) {
        
        if ($context['attackData']['t11'] <= 0) {
            return ','; // Retorna uma string vazia, não haverá linha de "Informação"
        }
        
        $heroXP = $battleResult['xp_gained']['attacker'];
        $heroID = $context['attacker']['forces']['hero_id'];

        $xpText = $heroXP > 0 ? " and won <b>".$heroXP."</b> XP." : ".";

        $heroDied = isset($battleResult['casualties']['attacker']['hero']) && $battleResult['casualties']['attacker']['hero'] > 0;

        if ($heroDied) {
            // Se o herói morreu, a mensagem deve refletir isso.
            $info_hero = $this->getUnitImage('hero') . ", Your hero died" . $xpText;
        } else {
            $info_hero = $this->getUnitImage('hero') . ", Your hero survived" . $xpText;

            // Conquista de Oásis
            if ($context['defender']['isOasis']) {
                if ($context['defender']['info']['owner'] != $context['attacker']['info']['owner']) {
                    $canConquerOasis = $this->database->canConquerOasis($context['attacker']['info']['wref'], $context['defender']['info']['wref'], false);
                    $oasisTroops = $this->database->countOasisTroops($context['defender']['info']['wref'], false);
                    if ($canConquerOasis == 1 && $oasisTroops == 0) {
                        $this->database->conquerOasis($context['attacker']['info']['wref'], $context['defender']['info']['wref']);
                        $info_hero = $this->getUnitImage('hero') . ", Your hero conquered this oasis" . $xpText;
                    } elseif ($canConquerOasis == 3 && $oasisTroops == 0) {
                        if ($context['attackData']['attack_type'] == 3) {
                            $oldLoyalty = (int)$this->database->getOasisField($context['defender']['info']['wref'], 'loyalty');
                            $newLoyalty = (int)$this->database->modifyOasisLoyalty($context['defender']['info']['wref']);
                            $info_hero = $this->getUnitImage('hero') . ", Your hero has reduced the oasis's loyalty from <b>$oldLoyalty</b> to <b>$newLoyalty</b>" . $xpText;
                        } else {
                            $info_hero = $this->getUnitImage('hero') . ", Unable to reduce oasis loyalty during a raid" . $xpText;
                        }
                    }
                }
            } else { // Roubo de Artefato
                $artifact = reset($this->database->getOwnArtefactInfo($context['defender']['info']['wref']));
                if (!empty($artifact) && $context['attackData']['attack_type'] == 3) {
                    $artifactError = $this->database->canClaimArtifact($context['attacker']['info']['wref'], $artifact['vref'], $artifact['size'], $artifact['type']);
                    if (empty($artifactError)) {
                        $this->database->claimArtefact($context['attacker']['info']['wref'], $context['defender']['info']['wref'], $context['attacker']['info']['owner']);
                        $info_hero = $this->getUnitImage('hero').", Your hero is taking home the artifact <b>".$artifact['name']."</b>".$xpText;
                        if ($this->database->getVillageField($context['defender']['info']['wref'], "pop") == 0) {
                            $aftermath['village_destroyed'] = true;
                        }
                    } else {
                        $info_hero = $this->getUnitImage('hero').",".$artifactError.$xpText;
                    }
                }
            }
        }
        return $info_hero;
    }

    /**
     * Calcula o saque de recursos da aldeia alvo.
     */
    private function calculateLoot(array $context, int $bounty) {
        if ($bounty <= 0) return [0, 0, 0, 0];
        
        $cranny_eff = 0;
        if (!$context['defender']['isOasis']) {
            global $bid23;
            $buildarray = $this->database->getResourceLevel($context['defender']['info']['wref']);
            for ($i = 19; $i < 39; $i++) {
                if ($buildarray['f' . $i . 't'] == 23) {
                    $cranny_eff += $bid23[$buildarray['f' . $i]]['attri'] * CRANNY_CAPACITY;
                }
            }
            $cranny_eff *= ($context['attacker']['info']['tribe'] == 2 ? 0.8 : 1); // Bônus de roubo dos Teutões
            $cranny_eff *= ($context['defender']['info']['tribe'] == 3 ? 2 : 1); // Bônus de esconderijo dos Gauleses
            $cranny_eff = $this->database->getArtifactsValueInfluence($context['defender']['info']['owner'], $context['defender']['info']['wref'], 7, $cranny_eff);
            $this->automation->updateRes($context['defender']['info']['wref']); // Garante que os recursos estão atualizados
        }else{
            $this->automation->updateORes($context['defender']['info']['wref']); // Garante que os recursos estão atualizados
        }


        
        $villageData = $this->database->getVillageFields($context['defender']['info']['wref'], 'wood, clay, iron, crop');
        $resources_available = [
            max(0, floor((float)(isset($villageData['wood']) ? $villageData['wood'] : 0) - $cranny_eff)),
            max(0, floor((float)(isset($villageData['clay']) ? $villageData['clay'] : 0) - $cranny_eff)),
            max(0, floor((float)(isset($villageData['iron']) ? $villageData['iron'] : 0) - $cranny_eff)),
            max(0, floor((float)(isset($villageData['crop']) ? $villageData['crop'] : 0) - $cranny_eff))
        ];
        
        $total_available = array_sum($resources_available);
        if ($total_available <= 0) {
            return [0, 0, 0, 0];
        }

        $steal = [0, 0, 0, 0];
        $total_steal_amount = min($bounty, $total_available);

        if ($total_available > 0) {
            for ($i = 0; $i < 4; $i++) {
                $steal[$i] = floor($total_steal_amount * ($resources_available[$i] / $total_available));
            }
        }
        
        $currently_stolen = array_sum($steal);
        $remaining_capacity = $total_steal_amount - $currently_stolen;

        if ($remaining_capacity > 0) {
            for ($i = 0; $i < $remaining_capacity; $i++) {
                // Encontra o recurso que ainda pode ser roubado
                $max_res_val = 0;
                $max_res_idx = -1;
                for ($j = 0; $j < 4; $j++) {
                    if (($resources_available[$j] - $steal[$j]) > $max_res_val) {
                        $max_res_val = $resources_available[$j] - $steal[$j];
                        $max_res_idx = $j;
                    }
                }

                if ($max_res_idx !== -1) {
                    $steal[$max_res_idx]++; // Adiciona 1 ao recurso mais abundante
                } else {
                    break; // Não há mais nada para roubar, mesmo que haja capacidade
                }
            }
        }

        return $steal;
    }

    /**
     * Gera o relatório de espionagem.
     */
    private function generateSpyReport(int $spyType, array $context): string {
        $attacker_tribe = $context['attacker']['info']['tribe'];
        $defenderWref = $context['defender']['info']['wref'];
        $defender_tribe = $context['defender']['info']['tribe'];
        $info_spy_parts = [];

        $attacker_spy_count = 0;
        $img_spy = '';
        if ($attacker_tribe != 3 && isset($context['attackData']['t4'])){
            $attacker_spy_count += $context['attackData']['t4'];
            $img_spy = $this->getUnitImage(($attacker_tribe - 1) * 10 + 4);
        }else if ($attacker_tribe == 3 && isset($context['attackData']['t3'])){
            $attacker_spy_count += $context['attackData']['t3'];
            $img_spy = $this->getUnitImage(($attacker_tribe - 1) * 10 + 3);
        }
        
        // Se nenhum espião foi enviado, não retorna nada.
        if ($attacker_spy_count <= 0) {
            return ',';
        }

        if ($spyType == 1) { // Recursos
            $this->automation->updateRes($defenderWref);
            $res = $this->database->getVillageFields($defenderWref, 'wood, clay, iron, crop');
            $res_html = "<div class=\"res\"><img class=\"r1\" src=\"gpack/travian_default/img/x.gif\" alt=\"Lumber\" title=\"Lumber\" />".round($res['wood'])." |
					<img class=\"r2\" src=\"gpack/travian_default/img/x.gif\" alt=\"Clay\" title=\"Clay\" />".round($res['clay'])." |
					<img class=\"r3\" src=\"gpack/travian_default/img/x.gif\" alt=\"Iron\" title=\"Iron\" />".round($res['iron'])." |
					<img class=\"r4\" src=\"gpack/travian_default/img/x.gif\" alt=\"Crop\" title=\"Crop\" />".round($res['crop'])."</div>
					<div class=\"carry\"><img class=\"car\" src=\"gpack/travian_default/img/x.gif\" alt=\"carry\" title=\"carry\" />Total Resources: ".(round($res['wood'])+round($res['clay'])+round($res['iron'])+round($res['crop']))."</div>
					";
            array_unshift($info_spy_parts, $res_html);
        } 
        
        elseif ($spyType == 2) { // Defesas
            $residence = $this->automation->getTypeLevel(25, $defenderWref);
            $palace = $this->automation->getTypeLevel(26, $defenderWref);
            $comCenter = $this->automation->getTypeLevel(44, $defenderWref);

            $wall = $this->automation->getTypeLevel(31, $defenderWref) 
                    + $this->automation->getTypeLevel(32, $defenderWref) 
                    + $this->automation->getTypeLevel(33, $defenderWref)
                    + $this->automation->getTypeLevel(42, $defenderWref) 
                    + $this->automation->getTypeLevel(43, $defenderWref) 
                    + $this->automation->getTypeLevel(47, $defenderWref) 
                    + $this->automation->getTypeLevel(50, $defenderWref);

            if ($residence > 0) $info_spy_parts[] = "<img src=\"".GP_LOCATE."img/g/g25.gif\" height=\"20\" width=\"15\" alt=\"Residence\" title=\"Residence\" /> Residence level: <b>$residence</b>";
            if ($palace > 0) $info_spy_parts[] = "<img src=\"".GP_LOCATE."img/g/g26.gif\" height=\"20\" width=\"15\" alt=\"Palace\" title=\"Palace\" /> Palace level: <b>$palace</b>";
            if ($comCenter > 0) $info_spy_parts[] = "<img src=\"".GP_LOCATE."img/g/g44.gif\" height=\"20\" width=\"15\" alt=\"Command Center\" title=\"Command Center\" /> Command Center level: <b>$comCenter</b>";
            
            $wallIcons = [1=>31, 2=>32, 3=>33, 6=>43, 7=>42, 8=>47, 9=>50];
            $wallGid = isset($wallIcons[$defender_tribe]) ? $wallIcons[$defender_tribe] : 31;
            if ($wall > 0) $info_spy_parts[] = "<img src=\"".GP_LOCATE."img/g/g".$wallGid."Icon.gif\" height=\"20\" width=\"15\" alt=\"Wall\" title=\"Wall\" /> Wall level: <b>$wall</b>";


            global $bid23;
            $base_cranny_capacity = 0;
            $buildarray = $this->database->getResourceLevel($defenderWref, false);
            for ($i = 19; $i < 39; $i++) {
                if ($buildarray['f' . $i . 't'] == 23) {
                    $base_cranny_capacity += $bid23[$buildarray['f' . $i]]['attri'] * CRANNY_CAPACITY;
                }
            }
            $final_cranny_capacity = $this->database->getArtifactsValueInfluence($context['defender']['info']['owner'], $defenderWref, 7, $base_cranny_capacity);
            if ($final_cranny_capacity > 0) {
                $info_spy_parts[] = "<img src=\"".GP_LOCATE."img/g/g23.gif\" height=\"20\" width=\"15\" alt=\"Cranny\" title=\"Cranny\" /> Total crannies capacity: <b>" . floor($final_cranny_capacity) . "</b>";
            }

        }

        return $img_spy . "," . implode('<br>', $info_spy_parts);
    }

    /**
     * Lida com a libertação de tropas aliadas presas.
     */
    private function handlePrisonerRelease(array $context, array $battleResult, array $attackData) {
        if ($attackData['attack_type'] != 3 || $battleResult['totals']['attacker']['survivors'] <= 0) {
            return '';
        }

        $defenderWref = $context['defender']['info']['wref'];
        $prisonersInVillage = $this->database->getPrisoners([$defenderWref], 0, false);
        $prisoners = isset($prisonersInVillage[$defenderWref . '0']) ? $prisonersInVillage[$defenderWref . '0'] : [];

        if (empty($prisoners)) {
            return '';
        }

        $attackerOwner = $context['attacker']['info']['owner'];
        $attackerAlly = $context['attacker']['info']['alliance'];
        $freedMyTroops = 0;
        $freedAllyTroops = 0;
        $totalFreedForNotice = 0;

        foreach ($prisoners as $prisoner) {
            $prisonerOwner = $this->database->getVillageField($prisoner['from'], "owner");
            $prisonerAlly = $this->database->getUserField($prisonerOwner, "alliance", 0);

            // Simplificação da verificação de aliança. Pode ser expandida para NAPs/guerras se necessário.
            $isAlly = ($attackerAlly > 0 && $prisonerAlly == $attackerAlly);
            
            if ($prisonerOwner == $attackerOwner || $isAlly) {
                $released_units = [];
                $totalFreedThisGroup = 0;
                for ($i = 1; $i <= 11; $i++) {
                    $dead = round($prisoner['t' . $i] / 4);
                    $survivors = $prisoner['t' . $i] - $dead;
                    $released_units[$i] = $survivors;
                    $totalFreedThisGroup += $survivors;
                }
                $totalFreedForNotice += $totalFreedThisGroup;

                if ($prisoner['from'] == $context['attacker']['info']['wref']) {
                    $this->database->modifyAttack2($attackData['ref'], array_keys($released_units), array_values($released_units));
                } else {
                    $p_tribe = $this->database->getUserField($prisonerOwner, "tribe", 0);
                    $travelTime = $this->units->getWalkingTroopsTime($prisoner['from'], $prisoner['wref'], $prisonerOwner, $p_tribe, $released_units, 1, 't');
                    $endtime = $this->database->getArtifactsValueInfluence($prisonerOwner, $prisoner['from'], 2, $travelTime) + time();
                    
                    $ref = $this->database->addAttack(
                        $prisoner['from'],
                        $released_units[1], $released_units[2], $released_units[3], $released_units[4],
                        $released_units[5], $released_units[6], $released_units[7], $released_units[8],
                        $released_units[9], $released_units[10], $released_units[11],
                        3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
                    );
                    $this->database->addMovement(4, $prisoner['wref'], $prisoner['from'], $ref, time(), $endtime);
                }
                $this->database->deletePrisoners([$prisoner['id']]);
            }
        }
        
        if($totalFreedForNotice > 0) {
            $this->database->modifyUnit($defenderWref, ["99o"], [$totalFreedForNotice], [0]);
            $attackerName = $context['attacker']['info']['username'];
            return $this->getUnitImage(98) . ", <b>$attackerName</b> libertou <b>$totalFreedForNotice</b> tropas.";
        }
        return '';
    }

    /**
     * Executa todas as ações de banco de dados para conquistar uma aldeia.
     */
    private function _conquerVillage(array $context) {
        $defenderWref = $context['defender']['info']['wref'];
        $attackerWref = $context['attacker']['info']['wref'];
        $attackerOwner = $context['attacker']['info']['owner'];
        $defenderOwner = $context['defender']['info']['owner'];

        // Lida com artefatos
        $artifact = reset($this->database->getOwnArtefactInfo($defenderWref));
        if ($artifact) {
            $this->database->claimArtefact($attackerWref, $defenderWref, $attackerOwner);
        }

        // Muda o dono da aldeia
        $this->database->setVillageFields($defenderWref, ['loyalty', 'owner', 'lastupdate2', 'lastupdate_rank'], [0, $attackerOwner, time(), time()]);

        //Marca o antigo dono para update de ranking
        $this->database->query("UPDATE ".TB_PREFIX."vdata SET lastupdate_rank = ".time()." WHERE owner = ".(int)$defenderOwner." AND capital = 1 LIMIT 1");

        
        // Limpa dados antigos da aldeia
        $this->database->query("DELETE FROM ".TB_PREFIX."abdata WHERE vref = ".(int)$defenderWref);
        $this->database->addABTech($defenderWref);
        $this->database->query("DELETE FROM ".TB_PREFIX."tdata WHERE vref = ".(int)$defenderWref);
        $this->database->addTech($defenderWref);
        $this->database->query("DELETE FROM ".TB_PREFIX."enforcement WHERE `from` = ".(int)$defenderWref);
        $this->database->deleteTradeRoutesByVillage($defenderWref);
        
        // Zera todas as unidades na aldeia conquistada
        $units_to_reset = [];
        for ($u = 1; $u <= 90; $u++) $units_to_reset[] = 'u'.$u.' = 0';
        $units_to_reset[] = 'hero = 0'; $units_to_reset[] = 'u99 = 0'; $units_to_reset[] = 'u99o = 0';
        $this->database->query("UPDATE ".TB_PREFIX."units SET ".implode(',', $units_to_reset)." WHERE vref = ".(int)$defenderWref);

        // Lida com slots de expansão
        $this->database->clearExpansionSlot($defenderWref, 1);
        $expSlots = $this->database->getVillageFields($attackerWref, 'exp1, exp2, exp3');
        if($expSlots['exp1'] == 0) $exp = 'exp1';
        elseif($expSlots['exp2'] == 0) $exp = 'exp2';
        else $exp = 'exp3';
        $this->database->setVillageField($attackerWref, $exp, $defenderWref);

        // Retorna tropas de oásis e reconta população
        $this->units->returnTroops($defenderWref, 1);
        $this->automation->recountPop($defenderWref, false);
        $this->database->reassignHero($defenderWref);

        // Invalida caches de lista de vilas para ambos os jogadores
        $this->database->clearUserVillageCache($attackerOwner);
        $this->database->clearUserVillageCache($defenderOwner);
    }
    
    /**
     * Gera as strings de dados para os relatórios de batalha.
     * @return array ['main' => string, 'fail' => string]
     */
    private function generateBattleReportStrings(array $context, array $battleResult, array $aftermath, array $trappedData) {
        $attacker = $context['attacker']['info'];
        $defender = $context['defender']['info'];
        $attackData = $context['attackData'];

        // --- 1. Dados do Atacante (Correto) ---
        $attacker_data = [$attacker['owner'], $attacker['wref'], $attacker['tribe']];
        $attacker_sent_units = [];
        for ($i = 1; $i <= 10; $i++) {
            $attacker_sent_units[] = $attackData['t' . $i];
        }
        $attacker_casualties_units = [];
        $attacker_tribe_id = (int)$attacker['tribe'];
        for ($i = 1; $i <= 10; $i++) {
            $unit_id = ($attacker_tribe_id - 1) * 10 + $i;
            $attacker_casualties_units[] = isset($battleResult['casualties']['attacker'][$unit_id]) ? $battleResult['casualties']['attacker'][$unit_id] : 0;
        }

        // --- 2. Dados do Defensor (LÓGICA COMPLETAMENTE REESCRITA) ---
        $defender_data = [$defender['owner'], $defender['wref'], addslashes($defender['name']), '', '', '', $defender['tribe']];
        
        // Parte A: Tropas próprias do defensor
        $own_sent = array_fill(1, 10, 0);
        $own_dead = array_fill(1, 10, 0);
        if (isset($battleResult['units']['defender_sent']['own'])) {
            foreach ($battleResult['units']['defender_sent']['own'] as $uid => $count) {
                if ($uid === 'hero') continue;
                $index = (((int)$uid - 1) % 10) + 1;
                $own_sent[$index] = $count;
            }
        }
        if (isset($battleResult['casualties']['defender']['own'])) {
            foreach ($battleResult['casualties']['defender']['own'] as $uid => $count) {
                if ($uid === 'hero') continue;
                $index = (((int)$uid - 1) % 10) + 1;
                $own_dead[$index] = $count;
            }
        }
        
        // Parte B: Agrupa todos os reforços por tribo
        $reinf_sent_by_tribe = array_fill(1, 9, array_fill(1, 10, 0));
        $reinf_dead_by_tribe = array_fill(1, 9, array_fill(1, 10, 0));
        
        if (isset($battleResult['units']['defender_sent']['reinforcements'])) {
            foreach ($battleResult['units']['defender_sent']['reinforcements'] as $reinf) {
                foreach ($reinf as $uid => $count) {
                    if ($uid === 'hero') continue;
                    $tribe = floor(((int)$uid - 1) / 10) + 1;
                    $index = (((int)$uid - 1) % 10) + 1;
                    $reinf_sent_by_tribe[$tribe][$index] += $count;
                }
            }
        }
        if (isset($battleResult['casualties']['defender']['reinforcements'])) {
            foreach ($battleResult['casualties']['defender']['reinforcements'] as $reinf) {
                foreach ($reinf as $uid => $count) {
                    if ($uid === 'hero') continue;
                    $tribe = floor(((int)$uid - 1) / 10) + 1;
                    $index = (((int)$uid - 1) % 10) + 1;
                    $reinf_dead_by_tribe[$tribe][$index] += $count;
                }
            }
        }

        // Parte C: Monta a string de dados do defensor com os flags
        $defender_units_data_main = [];
        $defender_units_data_main = array_merge($defender_units_data_main, array_values($own_sent), array_values($own_dead));

        for ($i = 1; $i <= 9; $i++) { // Itera sobre as 9 tribos para os reforços
            $flag = (array_sum($reinf_sent_by_tribe[$i]) > 0 || array_sum($reinf_dead_by_tribe[$i]) > 0) ? 1 : 0;
            $defender_units_data_main[] = $flag;
            $defender_units_data_main = array_merge($defender_units_data_main, array_values($reinf_sent_by_tribe[$i]), array_values($reinf_dead_by_tribe[$i]));
        }

        // --- 3. Dados dos Heróis ---

        $def_heroes_sent_by_tribe = array_fill(1, 9, 0);
        $def_heroes_dead_by_tribe = array_fill(1, 9, 0);

        // Herói próprio do defensor
        if (isset($battleResult['units']['defender_sent']['own']['hero']) && $battleResult['units']['defender_sent']['own']['hero'] > 0) {
            $def_heroes_sent_by_tribe[(int)$defender['tribe']] += 1;
        }
        if (isset($battleResult['casualties']['defender']['own']['hero']) && $battleResult['casualties']['defender']['own']['hero'] > 0) {
            $def_heroes_dead_by_tribe[(int)$defender['tribe']] += 1;
        }
        // Heróis de reforço
        if (isset($context['defender']['forces']['reinforcements'])) {
            foreach ($context['defender']['forces']['reinforcements'] as $reinf) {
                if (isset($reinf['hero']) && $reinf['hero'] > 0) {
                    $reinf_owner = $this->database->getVillageField($reinf['from'], "owner");
                    $reinf_tribe = (int)$this->database->getUserField($reinf_owner, "tribe", 0);
                    $def_heroes_sent_by_tribe[$reinf_tribe] += 1;

                    if (isset($battleResult['casualties']['defender']['reinforcements'][$reinf['id']]['hero']) && $battleResult['casualties']['defender']['reinforcements'][$reinf['id']]['hero'] > 0) {
                        $def_heroes_dead_by_tribe[$reinf_tribe] += 1;
                    }
                }
            }
        }

        $hero_totals_data = [
            $attackData['t11'],
            isset($battleResult['casualties']['attacker']['hero']) ? $battleResult['casualties']['attacker']['hero'] : 0,
            isset($battleResult['units']['defender_sent']['own']['hero']) ? $battleResult['units']['defender_sent']['own']['hero'] : 0,
            isset($battleResult['casualties']['defender']['own']['hero']) ? $battleResult['casualties']['defender']['own']['hero'] : 0
        ];
        
        // --- 4. Tropas Aprisionadas ---
        $traped_units_data = [];
        for ($i=1; $i<=11; $i++) {
            $traped_units_data[] = isset($trappedData['traped_units'][$i]) ? $trappedData['traped_units'][$i] : 0;
        }

        $survivors = $battleResult['totals']['attacker']['sent'] - $battleResult['totals']['attacker']['casualties'] - $trappedData['total_trapped'];
        $info_troop = ($survivors <= 0) ? "None of your soldiers returned." : "";

        $loot_data = array_merge($aftermath['loot'], [$battleResult['bounty']]);
        $battle_info_data = [$aftermath['ram_info'], $aftermath['catapult_info'], $aftermath['chief_info'], $aftermath['spy_info']];
        $final_info_data = [$aftermath['trap_info'], '', $info_troop,  $aftermath['hero_info']];
        
        
        // --- 4.5 Verificar se houve feridos ---
        $woundFlag = 0;
        if (!empty($battleResult['wounded']['defender']['own'])) {
            foreach ($battleResult['wounded']['defender']['own'] as $count) {
                if ($count > 0) { $woundFlag = 1; break; }
            }
        }
        if (!$woundFlag && !empty($battleResult['wounded']['attacker'])) {
            foreach ($battleResult['wounded']['attacker'] as $count) {
                if ($count > 0) { $woundFlag = 1; break; }
            }
        }

        // --- 5. Montagem Final ---
        $all_data_main = array_merge(
            // Dados do Atacante
            $attacker_data, $attacker_sent_units, $attacker_casualties_units, $loot_data,
            // Dados do Defensor
            $defender_data, $defender_units_data_main, $def_heroes_sent_by_tribe, $def_heroes_dead_by_tribe,
            // Informações da Batalha
            $battle_info_data,
            // Dados dos Heróis
            $hero_totals_data,
            // Prisioneiros
            $traped_units_data,
            // Informações Finais
            [$aftermath['trap_info']],
            [''], // Placeholder 3 (entre info de armadilha e de tropa)
            [$info_troop],
            [$aftermath['hero_info']],
            // Feridos
            [$woundFlag]
        );
        $mainReport = implode(',', $all_data_main);
        
        // --- 6. Montagem do Relatório de FALHA ---

        $hidden_defender_units = array_fill(0, 209, '?'); // Esconde tropas e reforços
        $hidden_heroes_by_tribe = array_fill(0, 18, '?'); // Esconde heróis por tribo
        $hidden_hero_def = '?';
        
        $all_data_fail = array_merge(
            // As seções do atacante permanecem as mesmas
            $attacker_data, $attacker_sent_units, $attacker_casualties_units, $loot_data,
            // Oculta os dados do defensor
            $defender_data, $hidden_defender_units, $hidden_heroes_by_tribe,
            // As seções de informação e placeholders devem ter a mesma estrutura
            $battle_info_data,
            ['', '', '',''],
            $hero_totals_data,
            $traped_units_data, // O atacante sempre sabe quantos de seus soldados foram presos
            // Informações Finais
            [$aftermath['trap_info']],
            [''], // Placeholder 3
            [$info_troop],
            [$aftermath['hero_info']],
            // Feridos (escondido do atacante em relatório de falha)
            [0]
        );
        $failReport = implode(',', $all_data_fail);
        
        return ['main' => $mainReport, 'fail' => $failReport];
    }

    /**
     * Envia as notificações (relatórios) para todos os jogadores envolvidos.
     * Esta versão corrige a lógica de notificação para reforços.
     */
    private function sendNotifications(array $context, array $battleResult, array $reportStrings, array $trappedData) {
        $attacker = $context['attacker']['info'];
        $defender = $context['defender']['info'];
        $attackData = $context['attackData'];
        $attackerName = addslashes($attacker['name']);
        $defenderName = addslashes($defender['name']);
        
        // Relatórios de Espionagem
        if ($attackData['attack_type'] == 1) {
            $survived = ($battleResult['totals']['attacker']['survivors'] > 0);
            $spotted = ($battleResult['totals']['defender']['spy'] > 0);

            $notice_title = $attackerName . ' scouts ' . $defenderName;
            
            $notice = $survived ? ($spotted ? 21 : 18) : 19;
            $this->database->addNotice($attacker['owner'], $defender['wref'], $attacker['alliance'], $notice, $notice_title, $reportStrings['main'], $attackData['endtime']);
            if ($spotted) {
                $this->database->addNotice($defender['owner'], $defender['wref'], $defender['alliance'], 21, $notice_title, $reportStrings['main'], $attackData['endtime']);
            }
            return;
        }

        $notice_title = $attackerName . ' attacks ' . $defenderName;

        // Relatório do Atacante
        $survivors = $battleResult['totals']['attacker']['sent'] - $battleResult['totals']['attacker']['casualties'] - $trappedData['total_trapped'];
        $notice_att = $survivors > 0 ? (($battleResult['totals']['attacker']['casualties'] > 0 || $trappedData['total_trapped'] > 0) ? 2 : 1) : 3;
        $this->database->addNotice($attacker['owner'], $defender['wref'], $attacker['alliance'], $notice_att, $notice_title, $survivors > 0 ? $reportStrings['main'] : $reportStrings['fail'], $attackData['endtime']);
        
        // Relatório do Defensor
        if ($battleResult['totals']['defender']['sent'] > 0) {
            $defender_won = ($battleResult['totals']['defender']['casualties'] < $battleResult['totals']['defender']['sent']);
            if ($defender_won && $battleResult['totals']['defender']['casualties'] == 0) $notice_def = 4;
            elseif ($defender_won) $notice_def = 5;
            else $notice_def = 6;
            $this->database->addNotice($defender['owner'], $defender['wref'], $defender['alliance'], $notice_def, $notice_title, $reportStrings['main'], $attackData['endtime']);
        } elseif ($attackData['attack_type'] >= 2) {
            $this->database->addNotice($defender['owner'], $defender['wref'], $defender['alliance'], 7, $notice_title, $reportStrings['main'], $attackData['endtime']);
        }
        
        // Notificações para Reforços
        foreach ($context['defender']['forces']['reinforcements'] as $reinf) {
            $reinf_id = $reinf['id'];
            $reinf_casualties_data = isset($battleResult['casualties']['reinforcements'][$reinf_id]) ? $battleResult['casualties']['reinforcements'][$reinf_id] : [];
            $total_casualties = array_sum($reinf_casualties_data);

            if ($reinf['from'] != 0) {
                $reinf_owner = $this->database->getVillageField($reinf['from'], "owner");

                if ($reinf_owner != $defender['owner']){ //Só envia para outros jogadores, não o próprio
                    $reinf_tribe = $this->database->getUserField($reinf_owner, "tribe", 0);
                    
                    $reinf_units_sent_arr = [];
                    $reinf_total_sent = 0;
                    $start = ($reinf_tribe - 1) * 10 + 1;
                    for ($i = $start; $i < $start + 10; $i++) {
                        $reinf_units_sent_arr[] = $reinf['u'.$i];
                        $reinf_total_sent += $reinf['u'.$i];
                    }
                    $reinf_total_sent += $reinf['hero'];
                    
                    $dead_units_for_string = [];
                    for ($i = 1; $i <= 10; $i++) {
                        $dead_units_for_string[] = isset($reinf_casualties_data[$i]) ? $reinf_casualties_data[$i] : 0;
                    }
                    $reinf_units_dead_str = implode(',', $dead_units_for_string);
                    
                    $hero_casualties = isset($reinf_casualties_data['hero']) ? $reinf_casualties_data['hero'] : 0;
                    $reinf_data_str = "{$reinf_owner},{$defender['wref']},".addslashes($defender['name']).",{$reinf_tribe},".implode(',', $reinf_units_sent_arr).",{$reinf_units_dead_str},{$reinf['hero']},{$hero_casualties},{$reinf['from']}";
                    
                    if ($total_casualties == 0) {
                        $notice_reinf = 15;
                    } elseif ($reinf_total_sent > $total_casualties) {
                        $notice_reinf = 16;
                    } else {
                        $notice_reinf = 17;
                        $this->database->deleteReinf($reinf_id);
                    }
                    
                    $this->database->addNotice($reinf_owner, $attacker['wref'], 0, $notice_reinf, 'Reforço em '.addslashes($defender['name']).' foi atacado', $reinf_data_str, $attackData['endtime']);
                }
            }
        }
    }

    /**
     * Verifica se uma aldeia pode ser destruída.
     */
    private function canVillageBeDestroyed(array $context): bool {
        if ($context['defender']['isOasis'] || $context['defender']['isNatar']) {
            return false;
        }
        if ($this->database->villageHasArtefact($context['defender']['info']['wref'])) {
            return false;
        }
        if (count($this->database->getProfileVillages($context['defender']['info']['owner'], 0, false)) <= 1) {
            return false;
        }
        return true;
    }

    /**
     * Atualiza os pontos de roubo (RR) para jogadores e alianças.
     */
    private function updateStolenResources(array $context, array $loot) {
        $totalLoot = array_sum($loot);
        if ($totalLoot <= 0) return;

        $defender_info = $context['defender']['info'];

        if ($context['defender']['isOasis'] && isset($defender_info['conqured']) && $defender_info['conqured'] > 0) {
            // Oásis ocupado: deduz os recursos da aldeia dona do oásis.
            $this->database->modifyResource($defender_info['conqured'], $loot[0], $loot[1], $loot[2], $loot[3], 0);
        } else if ($context['defender']['isOasis']) {
            // Oásis abandonado: deduz os recursos do próprio oásis.
            $this->database->modifyOasisResource($defender_info['wref'], $loot[0], $loot[1], $loot[2], $loot[3], 0);
        } else {
            // Aldeia: deduz os recursos da própria aldeia.
            $this->database->modifyResource($defender_info['wref'], $loot[0], $loot[1], $loot[2], $loot[3], 0);
        }

        // Adiciona pontos de roubo (RR) ao atacante e subtrai do defensor
        $this->database->modifyPoints($context['attacker']['info']['owner'], 'RR', $totalLoot);
        $this->database->modifyPoints($defender_info['owner'], 'RR', -$totalLoot);
        
        // Atualiza os pontos de roubo da aliança, se existir
        if ($context['attacker']['info']['alliance'] > 0) {
            $this->database->modifyPointsAlly($context['attacker']['info']['alliance'], 'RR', $totalLoot);
        }
        if ($defender_info['alliance'] > 0) {
            $this->database->modifyPointsAlly($defender_info['alliance'], 'RR', -$totalLoot);
        }
    }

    // Adicione este método em sua classe AttackHandler.php

    /**
     * Calcula e atualiza os pontos de ataque (ap) e defesa (dp) para os rankings
     * de jogadores e alianças com base nas baixas da batalha.
     *
     * @param array $context      O contexto da batalha.
     * @param array $battleResult O resultado da batalha.
     * @return void
     */
    private function updateRankingPoints(array $context, array $battleResult) {
        $total_attacker_points = 0; // Pontos que o ATACANTE ganha (baseado nas tropas do defensor mortas)
        $total_defender_points = 0; // Pontos que o DEFENSOR ganha (baseado nas tropas do atacante mortas)

        // Calcula os pontos baseados nas tropas do defensor que morreram
        foreach ($battleResult['casualties']['defender']['own'] as $unit_id => $count) {
            if ($count <= 0) continue;
            if ($unit_id === 'hero') {
                $total_attacker_points += $count * 6;
            } else {
                global ${'u'.$unit_id};
                $total_attacker_points += $count * ${'u'.$unit_id}['pop'];
            }
        }
        foreach ($battleResult['casualties']['defender']['reinforcements'] as $casualties) {
            foreach ($casualties as $unit_id => $count) {
                if ($count <= 0) continue;
                if ($unit_id === 'hero') {
                    $total_attacker_points += $count * 6;
                } else {
                    global ${'u'.$unit_id};
                    $total_attacker_points += $count * ${'u'.$unit_id}['pop'];
                }
            }
        }

        // Calcula os pontos baseados nas tropas do atacante que morreram
        foreach ($battleResult['casualties']['attacker'] as $unit_id => $count) {
            if ($count <= 0) continue;
            if ($unit_id === 'hero') {
                $total_defender_points += $count * 6;
            } else {
                global ${'u'.$unit_id};
                $total_defender_points += $count * ${'u'.$unit_id}['pop'];
            }
        }

        // Atualiza os pontos do ATACANTE e sua aliança
        if ($total_attacker_points > 0) {
            $attacker_info = $context['attacker']['info'];
            $this->database->modifyPoints($attacker_info['owner'], ['apall', 'ap'], [$total_attacker_points, $total_attacker_points]);
            if ($attacker_info['alliance'] > 0) {
                $this->database->modifyPointsAlly($attacker_info['alliance'], ['Aap', 'ap'], [$total_attacker_points, $total_attacker_points]);
            }
        }
        
        // Atualiza os pontos do DEFENSOR e sua aliança
        if ($total_defender_points > 0) {
            $defender_info = $context['defender']['info'];
            $this->database->modifyPoints($defender_info['owner'], ['dpall', 'dp'], [$total_defender_points, $total_defender_points]);
            if ($defender_info['alliance'] > 0) {
                $this->database->modifyPointsAlly($defender_info['alliance'], ['Adp', 'dp'], [$total_defender_points, $total_defender_points]);
            }
        }
    }

    /**
     * Lida com a destruição completa de uma aldeia.
     */
    private function handleVillageDestruction(array $context) {
        $wref = $context['defender']['info']['wref'];
        if ($this->database->getVillageField($wref, 'capital') == 1) {
            $villages = $this->database->getProfileVillages($context['defender']['info']['owner'], 0, false);
            $newCapital = null;
            foreach ($villages as $village) {
                if ($village['wref'] != $wref && (!$newCapital || $village['pop'] > $newCapital['pop'])) {
                    $newCapital = $village;
                }
            }
            if ($newCapital) {
                $this->database->changeCapital($newCapital['wref']);
            }
        }
        $this->database->DelVillage($wref);
        $this->database->reassignHero($wref);
    }

    /**
     * Cria o movimento de retorno para tropas que atacaram uma aldeia que não existe mais.
     */
    private function returnVanishedTroops(array $attackData) {
        $travelTime = $attackData['endtime'] - $attackData['starttime'];
        $this->database->setMovementProc($attackData['moveid']);
        $this->database->addMovement(4, $attackData['to'], $attackData['from'], $attackData['ref'], $attackData['endtime'], $attackData['endtime'] + $travelTime);
    }

    /**
     * Cria o movimento de retorno para tropas sobreviventes de uma batalha.
     */
    private function returnAttackingTroops(array $attackData, array $context, array $battleResult, array $trappedData, array $loot) {
        // Passo 1: Calcular o array de sobreviventes
        $survivors = [];
        $tribe_offset = ($context['attacker']['info']['tribe'] - 1) * 10;

        $attackerWref = $context['attacker']['info']['wref'];

       for ($i = 1; $i <= 10; $i++) {
            $unit_id = $tribe_offset + $i;
            $woundedAdd = isset($battleResult['wounded']['attacker'][$unit_id]) ? (int)$battleResult['wounded']['attacker'][$unit_id] : 0;
            $survivors['t' . $i] = $attackData['t' . $i] - ($battleResult['casualties']['attacker'][$unit_id] ?? 0) - ($trappedData['traped_units'][$i] ?? 0) - $woundedAdd;
        }
        $survivors['t11'] = $attackData['t11'] - ($battleResult['casualties']['attacker']['hero'] ?? 0) - ($trappedData['traped_units'][11] ?? 0);

        // Passo 2: Construir a string SQL para o método modifyAttack3
        $update_parts = [];
        foreach ($survivors as $unitKey => $count) {
            $safe_count = max(0, (int)$count);
            $update_parts[] = "`$unitKey` = $safe_count";
        }
        $units_sql_string = implode(', ', $update_parts);

        // Passo 3: ATUALIZAR o registro do ataque no banco de dados com os sobreviventes
        if (!empty($units_sql_string)) {
            $this->database->modifyAttack3($attackData['ref'], $units_sql_string);
        }

        // Passo 3b: Salvar feridos do atacante na tabela wounded
        if (!empty($battleResult['wounded']['attacker'])) {
            $wSets = [];
            foreach ($battleResult['wounded']['attacker'] as $uid => $count) {
                if ($count <= 0) continue;
                $pos = (($uid - 1) % 10) + 1;
                if ($pos >= 1 && $pos <= 6) $wSets[$pos] = $count;
            }
            $this->database->updateWounded($attackerWref, $wSets, 0);
        }

        $hasRealSurvivors = false;
        foreach ($survivors as $count) {
            if ($count > 0) { $hasRealSurvivors = true; break; }
        }
        if (!$hasRealSurvivors) {
            $this->database->setMovementProc($attackData['moveid']);
            return; // todas foram pro hospital, sem tropas para retornar
        }

        // Passo 4: CRIAR o movimento de retorno, que agora usará os dados atualizados
        $defenderWref = $context['defender']['info']['wref'];
        $defenderWref = $context['defender']['info']['wref'];
        $attackerOwner = $context['attacker']['info']['owner'];
        $attackerTribe = $context['attacker']['info']['tribe'];

        // Calcula o tempo de viagem com base nos sobreviventes
        $travelTime = $this->units->getWalkingTroopsTime($attackerWref, $defenderWref, $attackerOwner, $attackerTribe, $survivors, 1, 't');
        $endtime = $this->database->getArtifactsValueInfluence($attackerOwner, $attackerWref, 2, $travelTime) + $attackData['endtime'];

        $this->database->setMovementProc($attackData['moveid']);
        $this->database->addMovement(
            4, // sort_type: 4 para retorno
            $defenderWref,
            $attackerWref,
            $attackData['ref'],
            $attackData['endtime'],
            $endtime,
            1, // send
            $loot[0], // wood
            $loot[1], // clay
            $loot[2], // iron
            $loot[3]  // crop
        );
    }
    
    private function getUnitImage(string $unitId): string {
        return "unit u{$unitId}";
    }

    /**
     * Aplica os resultados calculados da batalha ao banco de dados.
     * Este método executa todas as queries UPDATE e DELETE para tropas, heróis e edifícios.
     *
     * @param array $battleResult O array de resultado retornado por newCalculateBattle.
     * @param array $context O contexto da batalha, para IDs e referências.
     */
    private function _applyBattleResultsToDatabase(array $battleResult, array $context) {
        $defenderWref = $context['defender']['info']['wref'];

        // 1. Aplicar baixas de tropas do ATACANTE
        // (Nota: As tropas do atacante são atualizadas no movimento de retorno,
        // então não modificamos a aldeia de origem diretamente aqui)

        // 2. Aplicar baixas de tropas do DEFENSOR (tropas próprias)
        $own_casualties = $battleResult['casualties']['defender']['own'];
        if (!empty($own_casualties)) {
            $unit_ids_to_update = [];
            $counts_to_subtract = [];
            foreach($own_casualties as $unit_id => $count) {
                if ($count > 0) {
                    $woundedAdd = isset($battleResult['wounded']['defender']['own'][$unit_id]) ? (int)$battleResult['wounded']['defender']['own'][$unit_id] : 0;
                    $unit_ids_to_update[] = ($unit_id == 'hero') ? 'hero' : 'u' . $unit_id;
                    $counts_to_subtract[] = $count + $woundedAdd;
                }
            }
            if (!empty($unit_ids_to_update)) {
                $modes_to_apply = array_fill(0, count($unit_ids_to_update), 0);
                $this->database->modifyUnit($defenderWref, $unit_ids_to_update, $counts_to_subtract, $modes_to_apply); // Modo 0 = subtrair
            }
        }

        // 2b. Salvar feridos (wounded) do defensor na tabela wounded
        if (!empty($battleResult['wounded']['defender']['own'])) {
            $wSets = [];
            foreach ($battleResult['wounded']['defender']['own'] as $uid => $count) {
                if ($count <= 0) continue;
                $pos = ((($uid - 1) % 10) + 1);
                if ($pos >= 1 && $pos <= 6) $wSets[$pos] = $count;
            }
            $this->database->updateWounded($defenderWref, $wSets, 0);
        }

        // 3. Aplicar baixas de tropas de REFORÇOS
        $reinf_casualties = $battleResult['casualties']['defender']['reinforcements'];
        if (!empty($reinf_casualties)) {
            foreach($reinf_casualties as $reinf_id => $casualties) {
                
                $originalReinf = null;
                foreach ($context['defender']['forces']['reinforcements'] as $r) {
                    if (isset($r['id']) && $r['id'] == $reinf_id) {
                        $originalReinf = $r;
                        break;
                    }
                }
                if (!$originalReinf) continue;

                $unit_ids_to_update = [];
                $counts_to_subtract = [];
                $deleteReinforcement = true;

                foreach($originalReinf as $key => $countOriginal) {
                    
                    // Verificamos apenas as chaves de unidades (excluir 'id', 'from', 'to', etc.)
                    if ($key === 'id' || $key === 'from' || $key === 'vref') continue; 
                    
                    // Mapeia a chave de unidade ('u1' ou 'hero') para a chave de baixa (1 ou 'hero')
                    $casualtyKey = ($key == 'hero') ? 'hero' : (int) str_replace('u', '', $key);
                    
                    // Obtém as baixas para esta unidade. Usa 0 se não houver baixas na lista ($casualties)
                    $casualtyCount = $casualties[$casualtyKey] ?? 0;
                    $survivors = $countOriginal - $casualtyCount;

                    if ($survivors > 0) {
                        // Se houver qualquer sobrevivente, não podemos deletar o reforço.
                        $deleteReinforcement = false;
                    }

                    // Apenas unidades com baixas precisam da atualização modifyEnforce.
                    if ($casualtyCount > 0) {
                        $unit_ids_to_update[] = $key; // Usa a chave 'uX' ou 'hero' para a DB
                        $counts_to_subtract[] = $casualtyCount;
                    }
                }

                if (!empty($unit_ids_to_update)) {
                    $modes_to_apply = array_fill(0, count($unit_ids_to_update), 0);
                    $this->database->modifyEnforce($reinf_id, $unit_ids_to_update, $counts_to_subtract, $modes_to_apply);
                }

                if ($deleteReinforcement) $this->database->deleteReinf($reinf_id);

                if (!empty($battleResult['wounded']['defender']['reinforcements'][$reinf_id])) {
                    $wSets2 = [];
                    foreach ($battleResult['wounded']['defender']['reinforcements'][$reinf_id] as $uid => $count) {
                        if ($count <= 0) continue;
                        $pos = (($uid - 1) % 10) + 1;
                        if ($pos >= 1 && $pos <= 6) $wSets2[$pos] = $count;
                    }
                    $this->database->updateWounded($originalReinf['from'], $wSets2, 0);
                }
            }
        }





        // 4. Aplicar resultados aos HERÓIS (dano e morte)
        // Herói Atacante
        if (isset($battleResult['hero_outcomes']['attacker']['id'])) {
            $heroId = $battleResult['hero_outcomes']['attacker']['id'];
            $heroOwnerId = $context['attacker']['info']['owner'];
            $heroXP = $battleResult['xp_gained']['attacker'];
            $heroDied = isset($battleResult['casualties']['attacker']['hero']) && $battleResult['casualties']['attacker']['hero'] >= 1;
            $damage = $battleResult['hero_outcomes']['attacker']['damage'];
            $initialHealth = $battleResult['hero_outcomes']['attacker']['health'];

            error_log("[DB APPLY] Attacker Hero (ID: " . $heroId . "): InitialHealth=" . $initialHealth . ", Damage=" . $damage . ", XP=" . $heroXP . ", FinalAction=" . ($heroDied ? "Kill" : "ApplyDamage"));

            if ($heroDied) {
                $this->database->KillHeroId($heroId);
            } else {
                if ($damage > 0) {
                    $this->database->modifyHero("health", $damage, $heroId, 0);
                }
            }

            // Aplica XP
            if ($heroXP > 0) {
                $this->database->modifyHeroXp("experience", $heroXP, $heroId);
            }
        }
        
        $total_xp_for_defenders = $battleResult['xp_gained']['defender'];

        // Herói Próprio do Defensor
        if (isset($battleResult['hero_outcomes']['defender']['own']['id'])) {
            $heroId = $battleResult['hero_outcomes']['defender']['own']['id'];
            $heroOwnerId = $context['defender']['info']['owner'];
            $heroDied = isset($battleResult['casualties']['defender']['own']['hero']) && $battleResult['casualties']['defender']['own']['hero'] >= 1;
            $damage = $battleResult['hero_outcomes']['defender']['own']['damage'];
            $initialHealth = $battleResult['hero_outcomes']['defender']['own']['health'];

            error_log("[DB APPLY] Defender Hero (Owner) (ID: " . $heroId . "): InitialHealth=" . $initialHealth . ", Damage=" . $damage . ", XP=" . $total_xp_for_defenders . ", FinalAction=" . ($heroDied ? "Kill" : "ApplyDamage"));

            if ($heroDied) {
                $this->database->KillHeroId($heroId);
            } else {
                if ($damage > 0) {
                    $this->database->modifyHero("health", $damage, $heroId, 0);
                }
            }

            // Aplica XP
            if ($total_xp_for_defenders > 0) {
                $this->database->modifyHeroXp("experience", $total_xp_for_defenders, $heroId);
            }
        }

        // Heróis de Reforço
        if (isset($battleResult['hero_outcomes']['defender']['reinforcements'])) {
            foreach($battleResult['hero_outcomes']['defender']['reinforcements'] as $reinf_id => $hero_outcome) {
                $heroId = $hero_outcome['id'];
                $damage = $hero_outcome['damage'];
                $reinf_owner_id = $hero_outcome['owner'];
                $heroDied = isset($battleResult['casualties']['defender']['reinforcements'][$reinf_id]['hero']) && $battleResult['casualties']['defender']['reinforcements'][$reinf_id]['hero'] >= 1;
                $initialHealth = $hero_outcome['health'];

                error_log("[DB APPLY] Defender Hero (Reinforcement) (ID: " . $heroId . "): InitialHealth=" . $initialHealth . ", Damage=" . $damage . ", XP=" . $total_xp_for_defenders . ", FinalAction=" . ($heroDied ? "Kill" : "ApplyDamage"));

                if ($heroDied) {
                    $this->database->KillHeroId($heroId);
                } else {
                    if ($damage > 0) {
                        $this->database->modifyHero("health", $damage, $heroId, 0);
                    }
                }
                
                // Aplica XP
                if ($total_xp_for_defenders > 0) {
                    $this->database->modifyHeroXp("experience", $total_xp_for_defenders, $heroId);
                }
            }
        }

        // 5. Aplicar dano da MURALHA
        $wallResult = $battleResult['siege_results']['wall'];
        if ($wallResult['new'] < $wallResult['old']) {
            $new_level = $wallResult['new'];
            if ($new_level == 0) {
                $this->database->setVillageLevel($defenderWref, ["f40", "f40t"], [0, 0]);
            } else {
                $this->database->setVillageLevel($defenderWref, "f40", $new_level);
            }
            $this->database->modifyBData($defenderWref, 40, [$new_level, $wallResult['old']], $context['defender']['info']['tribe']);
        }

        // 6. Aplicar dano de CATAPULTAS aos edifícios
        $village_destroyed_flag = false;
        foreach ($battleResult['siege_results']['destroyed_buildings'] as $destroyed) {
            $buildingName = $this->automation->procResType($destroyed['gid']);
            if ($destroyed['new'] < $destroyed['old']) {
                if ($destroyed['new'] == 0) {
                    if ($destroyed['fID'] >= 19) {
                        // É um edifício no centro da vila. Zera o nível e o tipo para liberar o slot.
                        $this->database->setVillageLevel($defenderWref, ["f".$destroyed['fID'], "f".$destroyed['fID']."t"], [0, 0]);
                    } else {
                        // É um campo de recursos. Apenas o nível vai para 0, o tipo permanece.
                        $this->database->setVillageLevel($defenderWref, "f".$destroyed['fID'], 0);
                    }

                } else {
                    $this->database->setVillageLevel($defenderWref, "f".$destroyed['fID'], $destroyed['new']);
                }
                $this->database->modifyBData($defenderWref, $destroyed['fID'], [$destroyed['new'], $destroyed['old']], $context['defender']['info']['tribe']);
            }
        }
        
        // Após possivelmente destruir edifícios, é sempre bom recalcular a população
        if ($wallResult['new'] < $wallResult['old'] || !empty($battleResult['siege_results']['destroyed_buildings'])) {
            $currentPop = $this->automation->recountPop($defenderWref);
            if ($currentPop == 0) {
                $village_destroyed_flag = true;
            }
        }

        return $village_destroyed_flag;
    }
}