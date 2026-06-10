<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Project:       TravianZ                                                    ##
##  Version:       22.06.2015                    			                   ##
##  Filename       Automation.php                                              ##
##  Developed by:  Mr.php , Advocaite , brainiacX , yi12345 , Shadow , ronix   ##
##  Fixed by:      Shadow - STARVATION , HERO FIXED COMPL.  		           ##
##  Fixed by:      InCube - double troops				                       ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2018. All rights reserved.                ##
##  URLs:          http://travian.shadowss.ro                		           ##
##  Source code:   https://github.com/Shadowss/TravianZ		                   ##
##                                                                             ##
#################################################################################

// make sure we only run the automation script once and wait until it's done,
// so concurrent AJAX calls from many different users won't overload the server
include_once("Database.php");
include_once("Data/buidata.php");
include_once("Data/unitdata.php");
include_once("Data/hero_full.php");
include_once("Units.php");
include_once("Battle.php");
include_once("Technology.php");
include_once("Ranking.php");
include_once("Generator.php");
include_once("Multisort.php");
include_once("Building.php");
include_once("Artifacts.php");
include_once("AttackHandler.php");

class Automation {

    /**
     * @var object The artifacts class, used to create Natars, artifacts and obtaining info about them
     */
    
    private $artifacts;
	private $lock_path_prefix;
	
	private $group_village_units_methods = [
        "completeMovementsSequentially", "buildComplete", "demolitionComplete",
        "marketComplete", "researchComplete", "updateHero",
        "trainingComplete", "MasterBuilder", "sendSettlersComplete", "corrigirSlotsComProblema"
    ];

    private $group_accounts_alliances_methods = [
        "procNewClimbers", "ClearUser", "ClearInactive",
        "celebrationComplete", "culturePoints", "rebuildStatCaches",
        "updateGeneralAttack", "checkInvitedPlayes", "CheckBan", "loyaltyRegeneration"
    ];

    private $group_world_maintenance_methods = [
        "pruneResource", "pruneOResource", "clearDeleting", "starvationNew",
        "delTradeRoute", "TradeRoute", "regenerateOasisTroops", "archiveAndPrune",
        "woundedDecay"
    ];

    private $group_game_events_methods = [
        "checkWWAttacks", // startNatarAttack é chamado por buildComplete
        "spawnNatars", "spawnWWVillages", "spawnWWBuildingPlans", "activateArtifacts",
        "artefactOfTheFool", "startFakeAttack", "updateStoreNew", "medalsNew"
    ];

    
    public function __construct() {
    	
        //Classes initialization
        $this->artifacts = new Artifacts();
		
		$this->lock_path_prefix = dirname(__FILE__) . "/Prevention/";
    	
        $automation_groups = [
            [
                'name'      => 'Village_Units',
                'methods'   => $this->group_village_units_methods,
                'lockFile'  => 'automation_village_units.lock',
                'cooldown'  => 10,      // Segundos (10 seg)
                'maxExecTime' => 120    // Segundos (2 min) - Tempo máximo que um lock é considerado válido
            ],
            [
                'name'      => 'Accounts_Alliances',
                'methods'   => $this->group_accounts_alliances_methods,
                'lockFile'  => 'automation_accounts_alliances.lock',
                'cooldown'  => 30,     // Segundos (30)
                'maxExecTime' => 120   // Segundos (2 min)
            ],
            [
                'name'      => 'World_Maintenance',
                'methods'   => $this->group_world_maintenance_methods,
                'lockFile'  => 'automation_world_maintenance.lock',
				'cooldown'  => 300,     // Segundos (5 minutos)
                'maxExecTime' => 450    // Segundos (7.5 minutos)
            ],
            [
                'name'      => 'Game_Events',
                'methods'   => $this->group_game_events_methods,
                'lockFile'  => 'automation_game_events.lock',
				'cooldown'  => 900,    // Segundos (15 minutos)
                'maxExecTime' => 450    // Segundos (7.5 minutos)
            ]
        ];

		// Tenta executar cada grupo
        foreach ($automation_groups as $group) {
            $this->tryExecuteGroup(
                $group['methods'],
                $group['lockFile'],
                $group['cooldown'],
                $group['maxExecTime'],
                $group['name'] // Adicionado para logging/debug
            );
        }

    }
	
	public function tryExecuteGroup($methodsArray, $lockFileName, $groupCooldown, $maxExecTime, $groupName = 'UnknownGroup') {
		global $database;
        $lockFilePath = $this->lock_path_prefix . $lockFileName;
        $currentTime = time();
        $currentPid = getmypid();

        $fileHandle = @fopen($lockFilePath, 'c+');
        if (!$fileHandle) {
            error_log("AutomationT3.6: [$groupName] Could not open/create lock file: " . $lockFilePath);
            return;
        }

        // 1. TENTA OBTER O LOCK PRIMEIRO
        if (flock($fileHandle, LOCK_EX | LOCK_NB)) {
            // --- SUCESSO AO OBTER O LOCK ---
            // Agora que o lock é nosso, podemos ler e escrever com segurança.
            
            $fileContent = '';
            rewind($fileHandle);
            clearstatcache(true, $lockFilePath);
            $fileSize = filesize($lockFilePath);
            if ($fileSize > 0) {
                $fileContent = fread($fileHandle, $fileSize);
            }

            $lastRunStartTimestampFromFile = 0;
            if (!empty($fileContent)) {
                $parts = explode(' ', trim($fileContent));
                if (count($parts) >= 1 && is_numeric($parts[0])) {
                    $lastRunStartTimestampFromFile = intval($parts[0]);
                }
            }

            // 2. VERIFICA O COOLDOWN APÓS OBTER O LOCK
            if ($lastRunStartTimestampFromFile > 0 && ($currentTime - $lastRunStartTimestampFromFile < $groupCooldown)) {
                $coolDownRem = $groupCooldown - ($currentTime - $lastRunStartTimestampFromFile);
                error_log("AutomationT3.6: [$groupName] Cooldown active ($coolDownRem s remaining). Releasing lock and deferring execution.");
                
                flock($fileHandle, LOCK_UN); // Libera o lock imediatamente
                fclose($fileHandle);
                return;
            }

            // 3. SE PASSOU DO COOLDOWN, ATUALIZA O ARQUIVO DE LOCK E EXECUTA
            ftruncate($fileHandle, 0);
            rewind($fileHandle);
            fwrite($fileHandle, $currentTime . ' ' . $currentPid);
            fflush($fileHandle);

            error_log("AutomationT3.6: [$groupName] Lock acquired by PID $currentPid. Starting execution.");

            try {
                foreach ($methodsArray as $method) {
                    if (method_exists($this, $method)) {
                        call_user_func(array($this, $method));
                    } else {
                        error_log("AutomationT3.6: [$groupName] Method $method does not exist in Automation class.");
                    }
                }
            } catch (Exception $e) {
                $database->log_with_context("AutomationT3.6: [$groupName] Exception during execution by PID $currentPid: " . $e->getMessage() . $e->getTraceAsString());
            } finally {
                // 4. GARANTE A LIBERAÇÃO DO LOCK NO FINAL
                flock($fileHandle, LOCK_UN);
            }

        } else {
            // --- FALHA AO OBTER O LOCK ---
            // Outro processo está com o lock. Vamos ler o arquivo para descobrir quem é.
            // Esta parte permanece a mesma, mas a chance de cair no último 'else' (arquivo não informativo) é agora mínima.
            $lockingProcessContent = '';
            rewind($fileHandle);
            clearstatcache(true, $lockFilePath);
            $currentFileSize = filesize($lockFilePath);
            if ($currentFileSize > 0) {
                $lockingProcessContent = fread($fileHandle, $currentFileSize);
            }

            $lockingTimestamp = 0;
            $lockingPid = 0;
            if (!empty($lockingProcessContent)) {
                $parts = explode(' ', trim($lockingProcessContent));
                if (count($parts) >= 1 && is_numeric($parts[0])) {
                    $lockingTimestamp = intval($parts[0]);
                }
                if (count($parts) >= 2 && is_numeric($parts[1])) {
                    $lockingPid = intval($parts[1]);
                }
            }

            if ($lockingTimestamp > 0) {
                // Se temos um timestamp, o arquivo é informativo. Verificamos se é obsoleto ou apenas ocupado.
                $age = $currentTime - $lockingTimestamp;
                if ($age < $maxExecTime) {
                    error_log("AutomationT3.6: [$groupName] Execution deferred. Lock held by PID $lockingPid, which started $age s ago (maxExecTime: $maxExecTime s).");
                } else {
                    error_log("AutomationT3.6: [$groupName] Execution blocked. Lock appears STALE (held by PID $lockingPid for $age s). However, the OS lock could not be acquired. Manual intervention may be needed if PID $lockingPid is defunct.");
                }
            } else {
                // Se o arquivo está vazio ou malformado, este é o sintoma da condição de corrida.
                // Ação: Tratar como "ocupado" e simplesmente adiar a execução.
                error_log("AutomationT3.6: [$groupName] Execution deferred. Another process holds the OS lock and is likely in the process of writing to the lock file.");
            }
        }
        
        fclose($fileHandle);
    }

	/**
	 * Corrige os slots na tabela s1_fdata com problemas de tipo ou nível.
	 * Adaptada para usar um objeto de banco de dados com um método query().
	 *
	 * @param object $database A instância do seu objeto de conexão com o banco de dados.
	 * @return int O número de linhas de aldeias que foram afetadas pela operação.
	 * Retorna -1 em caso de erro na query.
	 */
	function corrigirSlotsComProblema(){
		global $database;

		$setClauses = [];
		$whereClauses = [];

		// Esta parte é idêntica: constrói a query SQL dinamicamente.
		for ($i = 19; $i <= 40; $i++) {
			$setClauses[] = "f{$i} = CASE 
									WHEN f{$i}t = 0 AND f{$i} > 0 THEN 0 
									WHEN f{$i} > 20 THEN 20 
									ELSE f{$i} 
								END";
			$whereClauses[] = "(f{$i}t = 0 AND f{$i} > 0)";
			$whereClauses[] = "(f{$i} > 20)";
		}

		$sqlSet = implode(', ', $setClauses);
		$sqlWhere = implode(' OR ', $whereClauses);
		$sql = "UPDATE " . TB_PREFIX . "fdata SET {$sqlSet} WHERE {$sqlWhere}";

		$resultado = mysqli_query($database->dblink, $sql);
		$totSlots = mysqli_affected_rows($database->dblink);

		if (!$resultado || $totSlots == 0) {
			return;
		}

		error_log("AutomationT3: [CorrigirSlots] Slots com problema: $totSlots.");
	}

    private function completeMovementsSequentially() {
		global $database, $battle, $technology, $units;
	
		$events = [];
        $time = time();
        $hasMovements = false;
		
		// Busca Ataques/Raids (type 0)
		$ataques = $database->getFinishedMovements(0);
		foreach ($ataques as $ataque) {
			$events[] = [
				'endtime' => (int)$ataque['endtime'],
				'priority' => 3, // Prioridade 3 (menor) para ataques
				'type' => 'Attack',
				'data' => $ataque 
			];
		}
		
		// Busca Reforços (type 1)
		$reforcos = $database->getFinishedMovements(1);
		foreach ($reforcos as $reforco) {
			$events[] = [
				'endtime' => (int)$reforco['endtime'],
				'priority' => 1, // Prioridade 1 (máxima) para reforços
				'type' => 'Reinforcement',
				'data' => $reforco
			];
		}
		
		// Busca Retornos (type 2)
		$retornos = $database->getFinishedMovements(2);
		foreach ($retornos as $retorno) {
			$events[] = [
				'endtime' => (int)$retorno['endtime'],
				'priority' => 2, // Prioridade 2 para retornos
				'type' => 'Return',
				'data' => $retorno
			];
		}
		
		$settler_returns = $database->getFinishedMovements(3);
		foreach ($settler_returns as $settler_return) {
			$events[] = [
				'endtime' => (int)$settler_return['endtime'],
				'priority' => 2, // Mesma prioridade de um retorno normal
				'type' => 'Return_Settlers', // Um tipo específico para ele
				'data' => $settler_return
			];
		}

        if (!empty($events)) {
            $hasMovements = true;
        } else {
            // 2. Se não há nada finalizado, verificamos se existe ALGO no futuro, validamos apenas com ataques a caminho, visto que uns poucos retornos ainda pode significar falha no bot
            $hasMovements = $database->hasFutureAttacks();
        }

        // 3. Informa o BD que está iniciando o processamento
        $database->updateAutomationTime($time, $hasMovements);
		
		if (empty($events)) {
			return; // Nada a fazer neste ciclo
		}
		
		$vilIDsAT = [];
		$vilIDsREF = [];
		$enforce_tos = [];
        $enforce_froms = [];
		$vilIDsRET = [];
		
		foreach ($events as $event) {
			// Coleta os IDs de origem e destino de todos os eventos
			if ($event['type'] == 'Attack') {
				$vilIDsAT[$event['data']['from']] = true;
                $vilIDsAT[$event['data']['to']] = true;
			}
			
			if ($event['type'] == 'Reinforcement') {
				$vilIDsREF[$event['data']['from']] = true;
				$vilIDsREF[$event['data']['to']] = true;
				$enforce_tos[$event['data']['to']] = true;
				$enforce_froms[$event['data']['from']] = true;
			}
			
			if ($event['type'] == 'Return') {
				$vilIDsRET[$event['data']['from']] = true;
                $vilIDsRET[$event['data']['to']] = true;
			}
			
		}
		
		if (!empty($vilIDsAT)){
			$vilIDsAT = array_keys($vilIDsAT);
            $database->getProfileVillages($vilIDsAT, 5);
            $database->getUnit($vilIDsAT);
            $database->getEnforceVillage($vilIDsAT, 0);
            $database->getMovement(34, $vilIDsAT, 1);
            $database->getABTech($vilIDsAT);
		}
		
		if (!empty($vilIDsREF)){
			$vilIDsREF = array_keys($vilIDsREF);
            $database->getProfileVillages($vilIDsREF, 5);
            $database->getUnit($vilIDsREF);
            $database->getEnforce(array_keys($enforce_tos), array_keys($enforce_froms));
            $database->getVillageByWorldID($vilIDsREF);		
		}
		
		if (!empty($vilIDsRET)){
			$vilIDsRET = array_keys($vilIDsRET);
			$database->getProfileVillages($vilIDsRET, 5);
            $database->getOasisEnforce($vilIDsRET, 0);
            $database->getOasisEnforce($vilIDsRET, 1);
		}
		
		// Ordena primeiro por 'endtime' (cronológico), e depois por 'priority' para desempate
		usort($events, function($a, $b) {
			if ($a['endtime'] == $b['endtime']) {
				return $a['priority'] <=> $b['priority']; // Prioridade menor (1) vem primeiro
			}
			return $a['endtime'] <=> $b['endtime']; // Ordena pelo tempo de conclusão
		});
	
	
		foreach ($events as $event) {

			// Delega para o método de tratamento correto com base no tipo
			switch ($event['type']) {
				case 'Attack':
					$this->processAttackArrival($event['data']);
					break;
				case 'Reinforcement':
					$this->processReinforcementArrival($event['data']);
					break;
				case 'Return':
					$this->processTroopReturn($event['data']);
					break;
				case 'Return_Settlers':
					$this->processSettlerReturn($event['data']);
					break;
			}
		}
		
		$this->pruneResource();
		
	}

	
	
	public function procResType($ref, $mode = 0) {
        //Capital or only 1 village left = cannot be destroyed
        return addslashes(empty($build = Building::procResType($ref)) && !$mode ? "Village can't be" : $build);
    }

    public function recountPop($vid, $use_cache = true){
        global $database;
        
        $vid = (int) $vid;
        $fdata = $database->getResourceLevel($vid, $use_cache);
        $popTot = 0;

        for ($i = 1; $i <= 40; $i++) {
            $lvl = $fdata["f".$i];
            $building = $fdata["f".$i."t"];
			if($building) $popTot += $this->buildingPOP($building, $lvl);
        }

		if ($fdata["f99t"] == 40){
            $lvl = $fdata["f99"];
            $building = $fdata["f99t"];
			if($building) $popTot += $this->buildingPOP($building, $lvl);
        }
        
        Building::recountCP($database, $vid);
		$q = "UPDATE ".TB_PREFIX."vdata set pop = $popTot where wref = $vid";
		mysqli_query($database->dblink, $q);
		$owner = $database->getVillageField($vid, "owner");
		$this->procClimbers($owner);

        return $popTot;
    }

    function buildingPOP($f, $lvl){
        $name = "bid".$f;
        global $$name;
        
        $popT = 0;
        $dataarray = $$name;

        for ($i = 0; $i <= $lvl; $i++) {
            $popT += ((isset($dataarray[$i]) && isset($dataarray[$i]['pop'])) ? $dataarray[$i]['pop'] : 0);
        }
        return $popT;
    }

    private function loyaltyRegeneration() {
    	global $database;
        
        $array = [];
        $array = $database->getProfileVillages(0, 6);
        if(!empty($array)) {
            foreach($array as $loyalty) {
                if (($t25_level = $this->getTypeLevel(25, $loyalty['wref'])) >= 1) {
                    $value = $t25_level;
                }elseif(($t26_level = $this->getTypeLevel(26, $loyalty['wref'])) >= 1){
                    $value = $t26_level;
                }elseif(($t44_level = $this->getTypeLevel(44, $loyalty['wref'])) >= 1){
                    $value = $t44_level;
                }
                else $value = 0;
                
                if($value > 0){
                    $newloyalty = min(100, $loyalty['loyalty'] + $value * (time() - $loyalty['lastupdate2']) / 3600);
                    $q = "UPDATE ".TB_PREFIX."vdata SET loyalty = $newloyalty, lastupdate2=".time()." WHERE wref = '".$loyalty['wref']."'";
                    $database->query($q);
                }            
            }
        }
        
        $array = [];
        $q = "SELECT conqured, loyalty, lastupdated, wref FROM ".TB_PREFIX."odata WHERE loyalty < 100";
        $array = $database->query_return($q);
        if(!empty($array)) {
            foreach($array as $loyalty) {
                $value = $this->getTypeLevel(37, $loyalty['conqured']);   
                
                if($value > 0){
                    $newloyalty = min(100, $loyalty['loyalty'] + $value * (time() - $loyalty['lastupdated']) / 3600);
                    $q = "UPDATE ".TB_PREFIX."odata SET loyalty = $newloyalty, lastupdated=".time()." WHERE wref = '".$loyalty['wref']."'";
                    $database->query($q);
                }              
            }
        }
    }

    

    public function getTypeLevel($tid, $vid) {
        global $database;
        
        $keyholder = [];

        $resourcearray = $database->getResourceLevel($vid);
        foreach(array_keys($resourcearray, $tid) as $key) {
            if(strpos($key,'t')) {
                $key = preg_replace("/[^0-9]/", '', $key);
                array_push($keyholder, $key);
            }
        }
        
        $element = count($keyholder);
        if($element >= 2) {
            if($tid <= 4) {
                $temparray = [];
                for($i = 0; $i <= $element - 1; $i++) {
                    array_push($temparray,$resourcearray['f'.$keyholder[$i]]);
                }
                foreach ($temparray as $key => $val) {
                    if ($val == max($temparray)) $target = $key;                        
                }
            }
            else {
                $target = 0;
                for($i = 1; $i <= $element - 1; $i++) {
                    if($resourcearray['f'.$keyholder[$i]] > $resourcearray['f'.$keyholder[$target]]) {
                        $target = $i;
                    }
                }
            }
        }
        else if($element == 1) $target = 0;
        else return 0;

        if(!empty($keyholder[$target])) return $resourcearray['f'.$keyholder[$target]];
        else return 0;
    }

    private function clearDeleting() {
    	global $database;
        
        $needDelete = $database->getNeedDelete();
        if(count($needDelete) > 0) {
        	
        	//Remove the time limit, otherwise deleting players with 80 or more villages couldn't be deleted in one run
        	@set_time_limit(0);
        	
            foreach($needDelete as $need) {
                $need['uid'] = (int) $need['uid'];
                
                //Get the villages which have to be deleted
                $needVillages = $database->getVillagesID($need['uid']);
                
                //Delete all villages
                $database->DelVillage($needVillages);

                for($i = 0;$i < 20; $i++){
                    $q = "SELECT id FROM ".TB_PREFIX."users where friend".$i." = ".$need['uid']." or friend".$i."wait = ".$need['uid']."";
                    $array = $database->query_return($q);
                    foreach($array as $friend){
                        $database->deleteFriend($friend['id'],"friend".$i);
                        $database->deleteFriend($friend['id'],"friend".$i."wait");
                    }
                }

                $database->updateUserField($need['uid'], 'alliance', 0, 1);

                if($database->isAllianceOwner($need['uid'])){
                    $alliance = $database->getUserAllianceID($need['uid']);
                    $newowner = $database->getAllMember2($alliance);
                    $newleader = $newowner['id'];
                    $q = "UPDATE " . TB_PREFIX . "alidata set leader = ".(int) $newleader." where id = ".(int) $alliance."";
                    $database->query($q);
                    $database->updateAlliPermissions($newleader, $alliance, "Leader", 1, 1, 1, 1, 1, 1, 1);
                    Automation::updateMax($newleader);
                }

                if (isset($alliance)) $database->deleteAlliance($alliance);
                
                $q = "DELETE FROM ".TB_PREFIX."hero where uid = ".$need['uid'];
                $database->query($q);

                $q = "DELETE FROM ".TB_PREFIX."mdata where target = ".$need['uid']." or owner = ".$need['uid'];
                $database->query($q);

                $q = "DELETE FROM ".TB_PREFIX."ndata where uid = ".$need['uid'];
                $database->query($q);

                $q = "DELETE FROM ".TB_PREFIX."users where id = ".$need['uid'];
                $database->query($q);

                $q = "DELETE FROM ".TB_PREFIX."deleting where uid = ".$need['uid'];
                $database->query($q);
            }
        }
    }

    private function ClearUser() {
        global $database;
        
        if(AUTO_DEL_INACTIVE) {
            $time = time() - UN_ACT_TIME;

            $q = "INSERT INTO ".TB_PREFIX."deleting SELECT id, UNIX_TIMESTAMP() FROM ".TB_PREFIX."users WHERE timestamp < $time AND tribe IN(1, 2, 3, 6, 7, 8, 9)";
            $database->query($q);
        }
    }

    private function ClearInactive() {
        global $database;
        
        if(TRACK_USR) {
            $timeout = time()-USER_TIMEOUT * 60;
            $q = "DELETE FROM ".TB_PREFIX."active WHERE timestamp < $timeout";
            $database->query($q);
        }
    }
    
    private function pruneOResource() {
        global $database;
        
        if(!ALLOW_BURST) {
            $database->query("UPDATE
                      ".TB_PREFIX."odata
                  SET
                      wood = IF(wood < 0, 0, wood),
                      clay = IF(clay < 0, 0, clay),
                      iron = IF(iron < 0, 0, iron),
                      crop = IF(crop < 0, 0, crop),
                      maxstore = IF(maxstore < ".STORAGE_BASE.", ".STORAGE_BASE.", maxstore),
                      maxcrop = IF(maxcrop < ".STORAGE_BASE.", ".STORAGE_BASE.", maxcrop)
                  WHERE
                      maxstore < ".STORAGE_BASE." OR
                      maxcrop < ".STORAGE_BASE." OR
                      wood < 0 OR
                      clay < 0 OR
                      iron < 0 OR
                      crop < 0");
        }
    }

    /**
	 * Garante a consistência dos recursos das aldeias de forma otimizada.
	 * Esta versão unificada substitui lógicas antigas e ineficientes.
	 */
	private function pruneResource() {
		global $database;
		
		if (!defined('ALLOW_BURST') || !ALLOW_BURST) {
			// --- ETAPA 1: Corrige valores negativos ---
			// Cada consulta é específica, rápida e pode ser indexada.
			$database->query("UPDATE ".TB_PREFIX."vdata SET wood = 0 WHERE wood < 0");
			$database->query("UPDATE ".TB_PREFIX."vdata SET clay = 0 WHERE clay < 0");
			$database->query("UPDATE ".TB_PREFIX."vdata SET iron = 0 WHERE iron < 0");
			$database->query("UPDATE ".TB_PREFIX."vdata SET crop = 0 WHERE crop < 0");

			// --- ETAPA 2: Garante a capacidade mínima de armazenamento ---
			if (defined('STORAGE_BASE')) {
				$database->query("UPDATE ".TB_PREFIX."vdata SET maxstore = ".STORAGE_BASE." WHERE maxstore < ".STORAGE_BASE);
				$database->query("UPDATE ".TB_PREFIX."vdata SET maxcrop = ".STORAGE_BASE." WHERE maxcrop < ".STORAGE_BASE);
			}
			
			// --- ETAPA 3: Limita o estouro de capacidade (overflow) ---
			$database->query("UPDATE ".TB_PREFIX."vdata SET wood = maxstore WHERE wood > maxstore");
			$database->query("UPDATE ".TB_PREFIX."vdata SET clay = maxstore WHERE clay > maxstore");
			$database->query("UPDATE ".TB_PREFIX."vdata SET iron = maxstore WHERE iron > maxstore");
			$database->query("UPDATE ".TB_PREFIX."vdata SET crop = maxcrop WHERE crop > maxcrop");
		}
	}

    private function culturePoints() {
        global $database;

        $database->updateVSumField('cp');
    }

    private function buildComplete() {
        global $database, $technology, $bid18, $bid10, $bid11, $bid38, $bid39;

        $time = time();
        $villagesAffected = [];
        $loopconUpdates = [];
        $dbIdsToDelete = [];
		$liveCapacities = [];

        // get all pending builds that should be complete by now
        $res = $database->query_return(
            "SELECT
                id, wid, field, level, type, timestamp
             FROM
                ".TB_PREFIX."bdata
            WHERE
                timestamp < $time and master = 0"
        );

        // preload village data
        $vilIDs = [];
        foreach($res as $indi) {
            $vilIDs[$indi['wid']] = true;
        }
        $vilIDs = array_keys($vilIDs);
        $database->getProfileVillages($vilIDs, 5);
        $database->getEnforceVillage($vilIDs, 0);

        // complete buildings
        foreach($res as $indi) {
            $villageData = $database->getVillageFields($indi['wid'],'owner, maxcrop, maxstore, starv, pop');
            $villageOwner = $villageData['owner'];
            $villagesAffected[] = (int) $indi['wid'];
            $fieldsToSet = [];
            
            $q = "UPDATE ".TB_PREFIX."fdata SET f".$indi['field']." = ".$indi['level'].", f".$indi['field']."t = ".$indi['type']." WHERE vref = ".(int) $indi['wid'];

            if($database->query($q)) {
                $level = $indi['level'];  // this will be the level we brought the building to now

                // TODO: magic numbers into constants (for building types below)

                // update capacity if we updated a warehouse or a granary
                if (in_array($indi['type'], [10, 11, 38, 39])) {
                    $fieldDbName = (in_array($indi['type'], [10, 38]) ? 'maxstore' : 'maxcrop');
					
					if (!isset($liveCapacities[$indi['wid']][$fieldDbName])) {
						$liveCapacities[$indi['wid']][$fieldDbName] = $villageData[$fieldDbName];
					}
				
                    $currentMax = $liveCapacities[$indi['wid']][$fieldDbName];

                    if ($level != 1) {
						$currentMax -= ${'bid'.$indi['type']}[$level - 1]['attri'] * STORAGE_MULTIPLIER;
					}
					$newMax = $currentMax + ${'bid'.$indi['type']}[$level]['attri'] * STORAGE_MULTIPLIER;

					// 3. Atualizamos nosso valor "ao vivo" com a nova capacidade
					$liveCapacities[$indi['wid']][$fieldDbName] = $newMax;
					
					// 4. Preparamos para salvar no banco de dados
					$fieldsToSet[$fieldDbName] = $newMax;
                }

                // if we updated Embassy, update maximum members that the alliance can take
                if($indi['type'] == 18) Automation::updateMax($villageOwner);

                // by SlimShady95 aka Manuel Mannhardt < manuel_mannhardt@web.de >
                if ($indi['type'] == 40 && ($indi['level'] % 5 == 0 || $indi['level'] > 95) && $indi['level'] != 100) {
                    $this->startNatarAttack($indi['level'], $indi['wid'], $indi['timestamp']);
                }

                //now can't be more than one winner if ww to level 100 is build by 2 users or more on same time
                if ($indi['type'] == 40 && $indi['level'] == 100) {
                    mysqli_query($database->dblink,"TRUNCATE ".TB_PREFIX."bdata");
                }

                // TODO: find out what exactly these conditions are for
                // no special military conditioning for Teutons and Gauls
                if ($database->getUserField($villageOwner, "tribe", 0) != 1) $loopconUpdates[$indi['wid']] = '';                 
                else
                {
                    // special condition for Roman military buildings
                    if ($indi['field'] > 18) $loopconUpdates[$indi['wid']] = ' AND field > 18';                    
                    else $loopconUpdates[$indi['wid']] = ' AND field < 19';                                      
                }

                // Update ww last finish upgrade
                if ($indi['type'] == 40) {
                    $qW = "UPDATE ".TB_PREFIX."fdata set ww_lastupdate = ".time()." where vref = ".(int) $indi['wid'];
                    $database->query($qW);
                }

                $dbIdsToDelete[] = (int) $indi['id'];
            }

            //Update starvation data
            $database->addStarvationData($indi['wid']);

            // update the requested fields, all at once
            if (!empty($fieldsToSet)) {
				$database->setVillageFields($indi['wid'], array_keys($fieldsToSet), array_values($fieldsToSet));
			}
        }

        // update statistical data for affected villages
        foreach ($villagesAffected as $affected_id) $this->recountPop($affected_id, false);

        // update data that can be done in one swoop instead of using multiple update queries
        // no special checks for Romans
        foreach ($loopconUpdates as $villageId => $updateCondition) {
            $database->query(
                "UPDATE
                    ".TB_PREFIX."bdata
                 SET
                    loopcon = 0
                 WHERE
                    loopcon = 1 AND
                    master = 0 AND
                    wid = ".$villageId.$updateCondition);
        }

        // delete all processed entries
        if (count($dbIdsToDelete)) {
            $database->query( "DELETE FROM " . TB_PREFIX . "bdata WHERE id IN(" . implode( ',', $dbIdsToDelete ) . ")" );
        }
    }    

    private function getPop($tid, $level) {
        $name = "bid".$tid;
        global $$name;
        
        $dataarray = $$name;
        $pop = $dataarray[($level + 1)]['pop'];
        $cp = $dataarray[($level + 1)]['cp'];
        return [$pop, $cp];
    }

    private function delTradeRoute() {
        global $database;
     
        $database->delTradeRoute();
    }

    private function TradeRoute() {
        global $database;
        $time = time();
        $q = "SELECT `from`, wood, clay, iron, crop, wid, deliveries, id FROM ".TB_PREFIX."route where timestamp < $time";
        $dataarray = $database->query_return($q);

        $vilIDs = [];
        foreach($dataarray as $data) {
            $vilIDs[$data['to']] = true;
            $vilIDs[$data['from']] = true;
        }
        $vilIDs = array_keys($vilIDs);
        $database->getVillageByWorldID($vilIDs);

        foreach($dataarray as $data) {
            $targettribe = $database->getUserField($database->getVillageField($data['from'], "owner"), "tribe", 0);
			$this->sendResource2($data['wood'], $data['clay'], $data['iron'], $data['crop'], $data['from'], $data['wid'], $targettribe, $data['deliveries']);
			$database->editTradeRoute($data['id'], "timestamp", 86400, 1);
        }
    }

    private function marketComplete() {
        global $database, $units;

        $time = microtime(true);
        $q = "SELECT s.wood, s.clay, s.iron, s.crop, `to`, `from`, endtime, merchant, send, moveid FROM ".TB_PREFIX."movement m, ".TB_PREFIX."send s WHERE m.ref = s.id AND m.proc = 0 AND sort_type = 0 AND endtime < $time";
        $dataarray = $database->query_return($q);

        foreach($dataarray as $data) {
            $userData_from = $database->getUserFields($database->getVillageField($data['from'], "owner"), "alliance, tribe", 0);
            $userData_to = $database->getUserFields($database->getVillageField($data['to'], "owner"), "alliance, tribe", 0);

            if($data['wood'] >= $data['clay'] && $data['wood'] >= $data['iron'] && $data['wood'] >= $data['crop']) $sort_type = 10;
            elseif($data['clay'] >= $data['wood'] && $data['clay'] >= $data['iron'] && $data['clay'] >= $data['crop']) $sort_type = 11;
            elseif($data['iron'] >= $data['wood'] && $data['iron'] >= $data['clay'] && $data['iron'] >= $data['crop']) $sort_type = 12;
            elseif($data['crop'] >= $data['wood'] && $data['crop'] >= $data['clay'] && $data['crop'] >= $data['iron']) $sort_type = 13;

            $to = $database->getMInfo($data['to']);
            $from = $database->getMInfo($data['from']);

            $ownally = $userData_from['alliance'];
            $targetally = $userData_to['alliance'];

            $database->addNotice($to['owner'],$to['wref'],$targetally,$sort_type,''.addslashes($from['name']).' send resources to '.addslashes($to['name']).'',''.$from['owner'].','.$from['wref'].','.$data['wood'].','.$data['clay'].','.$data['iron'].','.$data['crop'].'',$data['endtime']);
            if($from['owner'] != $to['owner']) {
                $database->addNotice($from['owner'],$to['wref'],$ownally,$sort_type,''.addslashes($from['name']).' send resources to '.addslashes($to['name']).'',''.$from['owner'].','.$from['wref'].','.$data['wood'].','.$data['clay'].','.$data['iron'].','.$data['crop'].'',$data['endtime']);
            }
            $database->modifyResource($data['to'],$data['wood'],$data['clay'],$data['iron'],$data['crop'],1);
            $targettribe = $userData_to["tribe"];
            $endtime = $units->getWalkingTroopsTime($data['from'], $data['to'], 0, 0, [$targettribe], 0) + $data['endtime'];
            $database->addMovement(2, $data['to'], $data['from'], $data['merchant'], time(), $endtime, $data['send'], $data['wood'], $data['clay'], $data['iron'], $data['crop']);
            $database->setMovementProc($data['moveid']);
        }

        $q1 = "SELECT send, moveid, `to`, wood, clay, iron, crop, `from` FROM ".TB_PREFIX."movement WHERE proc = 0 and sort_type = 2 and endtime < $time";
        $dataarray1 = $database->query_return($q1);

        $vilIDs = [];
        foreach($dataarray1 as $data1) {
            $vilIDs[$data1['to']] = true;
            $vilIDs[$data1['from']] = true;
        }
        $vilIDs = array_keys($vilIDs);
        $database->getVillageByWorldID($vilIDs);

        foreach($dataarray1 as $data1) {
            $database->setMovementProc($data1['moveid']);
            if($data1['send'] > 1){
                $targettribe1 = $database->getUserFields($database->getVillageField($data1['to'],"owner"),"alliance, tribe",0)['tribe'];
                $send = $data1['send']-1;
                $this->sendResource2($data1['wood'],$data1['clay'],$data1['iron'],$data1['crop'],$data1['to'],$data1['from'],$targettribe1,$send);
            }
        }
    }

    private function sendResource2($wtrans, $ctrans, $itrans, $crtrans, $from, $to, $tribe, $send) {
        global $bid17, $bid28, $database, $units;

        $availableWood = $database->getWoodAvailable($from);
        $availableClay = $database->getClayAvailable($from);
        $availableIron = $database->getIronAvailable($from);
        $availableCrop = $database->getCropAvailable($from);
        
        if($availableWood + $availableClay + $availableIron + $availableCrop > 0)
        {
            if($availableWood < $wtrans) $wtrans = $availableWood;
            if($availableClay < $ctrans) $ctrans = $availableClay;
            if($availableIron < $itrans) $itrans = $availableIron;
            if($availableCrop < $crtrans) $crtrans = $availableCrop;          
            
            $merchant2 = ($this->getTypeLevel(17, $from) > 0)? $this->getTypeLevel(17, $from) : 0;
            $used2 = $database->totalMerchantUsed($from, false);
            $merchantAvail2 = $merchant2 - $used2;
            $maxcarry2 = ($tribe == 1)? 500 : (($tribe == 2)? 1000 : (($tribe == 3)? 750 : (($tribe == 6)? 500 : (($tribe == 7)? 750 : (($tribe == 8)? 500 : (($tribe == 9)? 750 : 500))))));
            $maxcarry2 *= TRADER_CAPACITY;
            
            if($this->getTypeLevel(28, $from) != 0) {
                $maxcarry2 *= $bid28[$this->getTypeLevel(28, $from)]['attri'] / 100;
            }
            
            $resource = [$wtrans, $ctrans, $itrans, $crtrans];
            $reqMerc = ceil((array_sum($resource) - 0.1) / $maxcarry2);
            
            if($merchantAvail2 > 0 && $reqMerc <= $merchantAvail2) {                
                if($database->getVillageState($to)) {
                    $timetaken = $units->getWalkingTroopsTime($from, $to, 0, 0, [$tribe], 0);
                    $res = $resource[0] + $resource[1] + $resource[2] + $resource[3];
                    if($res > 0){
                        $reference = $database->sendResource($resource[0], $resource[1], $resource[2], $resource[3], $reqMerc, 0);
                        $database->modifyResource($from, $resource[0], $resource[1], $resource[2], $resource[3], 0);
                        $database->addMovement(0, $from, $to, $reference, microtime(true), microtime(true) + $timetaken, $send);
                    }
                }           
            }
        }
    }

	private function processAttackArrival($data) {
		global $database, $battle, $technology, $units; // Dependências globais necessárias

		// Instancia o handler, passando as dependências necessárias.
		// A própria classe Automation é passada como o último parâmetro para 
		// acessar métodos auxiliares como recountPop.
		$attackHandler = new AttackHandler($database, $battle, $technology, $units, $this);

		// Delega toda a lógica para a nova classe
		$attackHandler->handleAttack($data);
	}

	private function processReinforcementArrival($data){
		global $bid23, $database, $technology, $battle;
	
		$isoasis = $database->isVillageOases($data['to']);
		$AttackArrivalTime = $data['endtime'];

		if($isoasis == 0){
			$to = $database->getMInfo($data['to']);
			$toF = $database->getVillage($data['to']);
			$DefenderID = $to['owner'];
			$targettribe = $database->getUserField($DefenderID, "tribe", 0);
			$conqureby = 0;
		}else{
			$to = $database->getOMInfo($data['to']);
			$toF = $database->getOasisV($data['to']);
			$DefenderID = $to['owner'];
			$targettribe = $database->getUserField($DefenderID, "tribe", 0);
			$conqureby = $toF['conqured'];
		}

		if($data['from'] == 0){
			$DefenderID = $database->getVillageField($data['to'], "owner");
			$database->addEnforce(['from' => $data['from'], 'to' => $data['to'], 't1' => 0, 't2' => 0, 't3' => 0, 't4' => 0, 't5' => 0, 't6' => 0, 't7' => 0, 't8' => 0, 't9' => 0, 't10' => 0, 't11' => 0]);
			$reinf = $database->getEnforce($data['to'], $data['from']);
			$database->modifyEnforce($reinf['id'], 31, 1, 1);
			$data_fail = '0,0,4,1,0,0,0,0,0,0,0,0,0,0';
			$database->addNotice($to['owner'], $to['wref'], (isset($targetally) ? $targetally : 0), 8, 'village of the elders reinforcement ' . addslashes($to['name']), $data_fail, $AttackArrivalTime);
			$database->setMovementProc($data['moveid']);
		}else{
			//set base things
			$from = $database->getMInfo($data['from']);
			$fromF = $database->getVillage($data['from']);
			$AttackerID = $from['owner'];
			$owntribe = $database->getUserField($AttackerID,"tribe",0);
		   
			$HeroTransfer = $troopsPresent = 0;              
			for($i = 1;$i <= 10; $i++) {
				if($data['t'.$i] > 0) {
					$troopsPresent = 1;
					break;
				}
			}
			
			//check if the hero is present and we're not sending him to an occupied oasis
			//only add hero if we're sending him alone
			if($data['t11'] > 0 && !$isoasis && !$troopsPresent) {
				//check if we're sending a hero between own villages
				if($AttackerID == $DefenderID) {             
					//check if there's a Mansion at target village
					if($this->getTypeLevel(37, $data['to']) > 0){
						//don't reinforce, addunit instead
						$database->modifyUnit($data['to'], ["hero"], [1], [1]);
						$heroid = $database->getHeroField($DefenderID, 'heroid');
						$database->modifyHero("wref", $data['to'], $heroid);
						$HeroTransfer = 1;
					}
				}
			}                   

			if($data['t11'] > 0 || $troopsPresent) {
				$temphero = $data['t11'];
				if ($HeroTransfer) $data['t11'] = 0;
				//check if there is defence from town in to town
				$check = $database->getEnforce($data['to'], $data['from']);
				if (!isset($check['id'])) $database->addEnforce($data);
				else
				{
					//yes
					$start = ($owntribe - 1) * 10 + 1;
					$end = ($owntribe * 10);
					
					//add unit.
					$t_units = '';
					for($i = $start, $j = 1; $i <= $end; $i++, $j++)
					{
						$t_units .= "u".$i." = u".$i." + ".$data['t'.$j].(($j > 9) ? '' : ', ');
					}
					
					$q = "UPDATE ".TB_PREFIX."enforcement set $t_units where id =".(int) $check['id'];
					$database->query($q);
					$database->modifyEnforce($check['id'], 'hero', $data['t11'], 1);
				}
				$data['t11'] = $temphero;
			}
			//send rapport
			$unitssend_att = ''.$data['t1'].','.$data['t2'].','.$data['t3'].','.$data['t4'].','.$data['t5'].','.$data['t6'].','.$data['t7'].','.$data['t8'].','.$data['t9'].','.$data['t10'].','.$data['t11'].'';
			$data_fail = ''.$from['wref'].','.$from['owner'].','.$owntribe.','.$unitssend_att.'';


			if($isoasis == 0) $to_name = $to['name'];    
			else $to_name = "Oasis ".$database->getVillageField($to['conqured'],"name");                 
			
			$database->addNotice($from['owner'],$from['wref'],(isset($ownally) ? $ownally : 0),8,''.addslashes($from['name']).' reinforcement '.addslashes($to_name).'',$data_fail,(isset($AttackArrivalTime) ? $AttackArrivalTime : time()));
			if($from['owner'] != $to['owner']) {
				$database->addNotice($to['owner'],$to['wref'],(isset($targetally) ? $targetally : 0),8,''.addslashes($from['name']).' reinforcement '.addslashes($to_name).'',$data_fail,(isset($AttackArrivalTime) ? $AttackArrivalTime : time()));
			}
			//update status
			$database->setMovementProc($data['moveid']);
		}

		//Update starvation data
		$database->addStarvationData($data['to']);

		//check empty reinforcement in rally point
		$e_units = '';
		for ($i = 1; $i <= 90; $i++) $e_units.= 'u'.$i.'= 0 AND ';
		
		$e_units.= 'hero = 0';
		$q = "DELETE FROM ".TB_PREFIX."enforcement WHERE ".$e_units." AND (vref=".(int) $data['to']." OR `from`=".(int) $data['to'].")";
		$database->query($q);
	}

	private function processTroopReturn($data) {
		global $database, $technology;
	
		$tribe = $database->getUserField($database->getVillageField($data['to'], "owner"), "tribe", 0);
		$u = $tribe == 1 ? "" : $tribe - 1;
		$database->modifyUnit(
				$data['to'],
				[$u."1",      $u."2",      $u."3",      $u."4",      $u."5",      $u."6",      $u."7",      $u."8",      $u."9", $tribe."0", "hero"],
				[$data['t1'], $data['t2'], $data['t3'], $data['t4'], $data['t5'], $data['t6'], $data['t7'], $data['t8'], $data['t9'], $data['t10'], $data['t11']],
				[1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1]
				);
		
		//If there's at least 1 resource, add it to the village
		if($data['wood'] + $data['clay'] + $data['iron'] + $data['crop'] > 0){
			$database->modifyResource($data['to'], $data['wood'], $data['clay'], $data['iron'], $data['crop'], 1);
		}
		
		$database->setMovementProc($data['moveid']);
		
		//Update starvation data
		$database->addStarvationData($data['to']);
		
	}
	
	private function processSettlerReturn($data) {
		global $database;
		$tribe = $database->getUserField($database->getVillageField($data['to'], "owner"), "tribe", 0);
		$database->modifyUnit($data['to'], [$tribe."0"], [3], [1]);
		
		//If a settling is canceled, add 750 for each resource type
		$database->modifyResource($data['to'], 750, 750, 750, 750, 1);
		$database->setMovementProc($data['moveid']);
	}

    private function sendSettlersComplete() {
        global $database;

        $time = microtime(true);
        $q = "SELECT `to`, `from`, moveid, starttime, ref FROM ".TB_PREFIX."movement where proc = 0 and sort_type = 5 and endtime < $time";

        $dataarray = $database->query_return($q);
        $movementProcIDs = [];
        $fieldIDs = [];
        $addUnitsWrefs = [];
        $addTechWrefs = [];
        $addABTechWrefs = [];
        $time = microtime(true);
        $types = [];
        $froms = [];
        $tos = [];
        $refs = [];
        $times = [];
        $endtimes = [];

        // preload village data
        $vilIDs = [];
        foreach($dataarray as $data) {
            $vilIDs[$data['from']] = true;
            $vilIDs[$data['to']] = true;
        }
        $vilIDs = array_keys($vilIDs);
        $database->getProfileVillages($vilIDs, 5);
        $database->getVillageByWorldID($vilIDs);

        foreach($dataarray as $data) {
            $ownerID = $database->getUserField($database->getVillageField($data['from'], "owner"), "id", 0);
			$to = $database->getMInfo($data['from']);
			$user = addslashes($database->getUserField($to['owner'], 'username', 0));
			$taken = $database->getVillageState($data['to']);
			if($taken != 1){
				$fieldIDs[] = $data['to'];
				$database->addVillage($data['to'], $to['owner'], $user, '0');
				$database->addResourceFields($data['to'], $database->getVillageType($data['to']));
                $addUnitsWrefs[] = $data['to'];
                $addTechWrefs[] = $data['to'];
                $addABTechWrefs[] = $data['to'];
                $movementProcIDs[] = $data['moveid'];
                
				$exp1 = $database->getVillageField($data['from'], 'exp1');
				$exp2 = $database->getVillageField($data['from'], 'exp2');
				$exp3 = $database->getVillageField($data['from'], 'exp3');
				
				if($exp1 == 0){
					$exp = 'exp1';
					$value = $data['to'];
				}elseif($exp2 == 0){
					$exp = 'exp2';
					$value = $data['to'];
				}else{
					$exp = 'exp3';
					$value = $data['to'];
				}
				
				$database->setVillageField($data['from'], $exp, $value);
            }else{
                // here must come movement from returning settlers
                $types[] = 4;
                $froms[] = $data['to'];
                $tos[] = $data['from'];
                $refs[] = $data['ref'];
                $times[] = $time;
                $endtimes[] = $time + ($time - $data['starttime']);
                $movementProcIDs[] = $data['moveid'];
            }
        }

        $database->addMovement($types, $froms, $tos, $refs, $times, $endtimes);
        $database->setMovementProc(implode(', ', $movementProcIDs));
        $database->setFieldTaken($fieldIDs);
        $database->addUnits($addUnitsWrefs);
        $database->addTech($addTechWrefs);
        $database->addABTech($addABTechWrefs);

    }
    


    private function researchComplete() {
        global $database;

        $time = time();
        $deleteIDs = [];
        $tdata = [];
        $abdata = [];

        $q = "SELECT tech, vref, id FROM ".TB_PREFIX."research where timestamp < $time";
        $dataarray = $database->query_return($q);

        foreach($dataarray as $data) {
            $sort_type = substr($data['tech'],0,1);
            switch($sort_type) {
                case "t":
                    if (!isset($tdata[$data['vref']])) $tdata[$data['vref']] = [];
                    $tdata[$data['vref']][] = $data['tech'].' = 1';
                    break;
                case "a":
                case "b":
                    if (!isset($abdata[$data['vref']])) $abdata[$data['vref']] = [];
                    $abdata[$data['vref']][] = $data['tech']." = ".$data['tech']." + 1";
                    break;
            }
            $deleteIDs[] = (int) $data['id'];
        }

        // execute queries with consolidated research data
        if (count($tdata)) {
            foreach ( $tdata as $vid => $preparedData ) {
                $q = "UPDATE ".TB_PREFIX."tdata SET ".implode(', ', $preparedData)." WHERE vref = ".$vid;
                $database->query($q);
            }
        }

        if (count($abdata)) {
            foreach ( $abdata as $vid => $preparedData ) {
                $q = "UPDATE ".TB_PREFIX."abdata SET ".implode(', ', $preparedData)." WHERE vref = ".$vid;
                $database->query($q);
            }
        }

        if (count($deleteIDs)) {
            $q = "DELETE FROM " . TB_PREFIX . "research where id IN(" . implode( ', ', $deleteIDs ) . ")";
            $database->query( $q );
        }
    }

    public function updateORes($bountywid) {
    	global $database;
    	
    	$oasisInfoArray = $database->getOasisV($bountywid);
    	$timepast = time() - $oasisInfoArray['lastupdated'];
    	$nwood = (OASIS_WOOD_PRODUCTION / 3600) * $timepast;
    	$nclay = (OASIS_CLAY_PRODUCTION / 3600) * $timepast;
    	$niron = (OASIS_IRON_PRODUCTION / 3600) * $timepast;
    	$ncrop = (OASIS_CROP_PRODUCTION / 3600) * $timepast;
    	$database->modifyOasisResource($bountywid, $nwood, $nclay, $niron, $ncrop, 1);
    	$database->updateOasis($bountywid);
    }
    
    public function updateRes($bountywid) {
    	global $database, $technology;
    	
    	//Get village infos
    	$villageInfoArray = $database->getVillage($bountywid);
    	
    	//Get building and resource fields array
    	$resArray = $database->getResourceLevel($bountywid, false);
    	
    	//Get oasis array
    	$oasisArray = $database->getOasis($bountywid);
    	
    	//Get an array with the numbers of the oasis
    	$numberOfOasis = $this->bountysortOasis($oasisArray);
    	
    	//Set the village population (if WW Villages, it's halved)
    	$villagePopulation = !$villageInfoArray['natar'] ? $villageInfoArray['pop'] : round($villageInfoArray['pop'] / 2);
    	
    	//Get the upkeep of the village
    	$upkeep = $technology->getUpkeep($this->getAllUnits($bountywid), 0, $bountywid);   	
 
    	//Calculate the produced resources
    	$timepast = time() - $villageInfoArray['lastupdate'];
    	$nwood = ($this->bountyGetWoodProd($resArray, $numberOfOasis) / 3600) * $timepast;
    	$nclay = ($this->bountyGetClayProd($resArray, $numberOfOasis) / 3600) * $timepast;
    	$niron = ($this->bountyGetIronProd($resArray, $numberOfOasis) / 3600) * $timepast;
    	$ncrop = (($this->bountyGetCropProd($resArray, $numberOfOasis) - $villagePopulation - $upkeep) / 3600) * $timepast;
    	$database->modifyResource($bountywid, $nwood, $nclay, $niron, $ncrop, 1);
    	$database->updateVillage($bountywid);
    }

    private function bountysortOasis($oasisArray) {
        $crop = $clay = $wood = $iron = 0;
        foreach ($oasisArray as $oasis) {
            switch($oasis['type']) {
                case 1:
                case 2:
                    $wood++;
                    break;
                case 3:
                    $wood++;
                    $crop++;
                    break;
                case 4:
                case 5:
                    $clay++;
                    break;
                case 6:
                    $clay++;
                    $crop++;
                    break;
                case 7:
                case 8:
                    $iron++;
                    break;
                case 9:
                    $iron++;
                    $crop++;
                    break;
                case 10:
                case 11:
                    $crop++;
                    break;
                case 12:
                    $crop += 2;
                    break;
            }
        }
        return [$wood, $clay, $iron, $crop];
    }

    function getAllUnits($base, $use_cache = true) {
        global $database;

        $ownunit = $database->getUnit($base, $use_cache);
		$enforcementarray = $database->getEnforceVillage($base, 0);
		
		if(count($enforcementarray) > 0){
			foreach($enforcementarray as $enforce){
				for($i = 1; $i <= 90; $i++){
					$ownunit['u'.$i] += $enforce['u'.$i];
				}
			}
		}
		
		$enforceoasis = $database->getOasisEnforce($base, 0, $use_cache);
		if(count($enforceoasis) > 0){
			foreach($enforceoasis as $enforce){
				for($i = 1; $i <= 90; $i++){
					$ownunit['u'.$i] += $enforce['u'.$i];
				}
			}
		}
		
		$enforceoasis1 = $database->getOasisEnforce($base, 1, $use_cache);
		if(count($enforceoasis1) > 0){
			foreach($enforceoasis1 as $enforce){
				for($i = 1; $i <= 90; $i++){
					$ownunit['u'.$i] += $enforce['u'.$i];
				}
			}
		}
		
		$movement = $database->getVillageMovement($base);
		if(!empty($movement)){
			for($i = 1; $i <= 90; $i++){
				if(!isset($ownunit['u' . $i])){
					$ownunit['u'.$i] = 0;
				}
				
				$ownunit['u'.$i] += (isset($movement['u'.$i]) ? $movement['u'.$i] : 0);
			}
		}
		
		$prisoners = $database->getPrisoners($base, 1);
		if(!empty($prisoners)){
			foreach($prisoners as $prisoner){
				$owner = $database->getVillageField($base, "owner");
				$ownertribe = $database->getUserField($owner, "tribe", 0);
				$start = ($ownertribe - 1) * 10 + 1;
				$end = ($ownertribe * 10);
				for($i = $start; $i <= $end; $i++){
					$j = $i - $start + 1;
					$ownunit['u'.$i] += $prisoner['t'.$j];
				}
				$ownunit['hero'] += $prisoner['t11'];
			}
		}
		return $ownunit;
    }
    
    private function bountyGetWoodProd($resArray, $oasisNumber) {
        global $bid1, $bid5;
        
        $wood = $sawmill = 0;
        $woodholder = [];
        for($i = 1; $i <= 38; $i++) {
        	if($resArray['f'.$i.'t'] == 1) array_push($woodholder,'f'.$i);
            if($resArray['f'.$i.'t'] == 5) $sawmill = $resArray['f'.$i];
        }
        
        for($i = 0; $i <= count($woodholder) - 1; $i++) $wood += $bid1[$resArray[$woodholder[$i]]]['prod'];
        
        if($sawmill >= 1) $wood += $wood / 100 * $bid5[$sawmill]['attri'];
        if($oasisNumber[0] > 0) $wood += $wood * 0.25 * $oasisNumber[0];

        return round($wood * SPEED);
    }
    
    private function bountyGetClayProd($resArray, $oasisNumber) {
        global $bid2, $bid6;
        
        $clay = $brick = 0;
        $clayholder = [];
        for($i = 1; $i <= 38; $i++) {
        	if($resArray['f'.$i.'t'] == 2) array_push($clayholder, 'f'.$i);
        	if($resArray['f'.$i.'t'] == 6) $brick = $resArray['f'.$i];
        }
        
        for($i = 0; $i <= count($clayholder) - 1; $i++) $clay += $bid2[$resArray[$clayholder[$i]]]['prod'];
        
        if($brick >= 1) $clay += $clay / 100 * $bid6[$brick]['attri'];
        if($oasisNumber[1] > 0) $clay += $clay * 0.25 * $oasisNumber[1];

        return round($clay * SPEED);
    }

    private function bountyGetIronProd($resArray, $oasisNumber) {
        global $bid3, $bid7;
        
        $iron = $foundry = 0;
        $ironholder = [];
        for($i = 1; $i <= 38; $i++) {
        	if($resArray['f'.$i.'t'] == 3) array_push($ironholder, 'f'.$i);               
        	if($resArray['f'.$i.'t'] == 7) $foundry = $resArray['f'.$i];
        }
        
        for($i = 0; $i <= count($ironholder) - 1; $i++) $iron += $bid3[$resArray[$ironholder[$i]]]['prod'];
        
        if($foundry >= 1) $iron += $iron / 100 * $bid7[$foundry]['attri'];
        if($oasisNumber[2] > 0) $iron += $iron * 0.25 * $oasisNumber[2];

        return round($iron * SPEED);
    }

    private function bountyGetCropProd($resArray, $oasisNumber) {
        global $bid4, $bid8, $bid9, $database;
        
        $crop = $grainmill = $bakery = 0;
        $cropholder = [];
        for($i = 1; $i <= 38;$i++) {
        	if($resArray['f'.$i.'t'] == 4) array_push($cropholder, 'f'.$i);
        	if($resArray['f'.$i.'t'] == 8) $grainmill = $resArray['f'.$i];
        	if($resArray['f'.$i.'t'] == 9) $bakery = $resArray['f'.$i];
        }
        for($i = 0; $i <= count($cropholder) - 1; $i++) $crop += $bid4[$resArray[$cropholder[$i]]]['prod'];
        
        if($grainmill >= 1) $crop += $crop / 100 * (isset($bid8[$grainmill]['attri']) ? $bid8[$grainmill]['attri'] : 0);
        if($bakery >= 1) $crop += $crop / 100 * (isset($bid9[$bakery]['attri']) ? $bid9[$bakery]['attri'] : 0);                
        if($oasisNumber[3] > 0) $crop += $crop * 0.25 * $oasisNumber[3];       
        
        if(!empty($resArray['vref']) && is_numeric($resArray['vref'])){
        	$who = $database->getVillageField($resArray['vref'], "owner");
            $croptrue = $database->getUserField($who, "b4", 0);
            if($croptrue > time()) $crop *= 1.25;
        }
        
        return round($crop * SPEED);
    }

    private function trainingComplete() {
        global $database, $technology;

        $time = time();
        $trainlist = $database->getTrainingList();
        if(count($trainlist) > 0){
            // preload village data
            $vilIDs = [];
            foreach($trainlist as $train){
                $vilIDs[$train['vref']] = true;
            }
            $vilIDs = array_keys($vilIDs);
            $database->getProfileVillages($vilIDs, 5);
            $database->cacheResourceLevels($vilIDs);
            $database->getUnit($vilIDs);
            $database->getEnforceVillage($vilIDs, 0 );
            $database->getMovement(3, $vilIDs, 0);
            $database->getMovement(4, $vilIDs, 1);
            $database->getMovement(5, $vilIDs, 0);
            $database->getOasisEnforce($vilIDs, 0);
            $database->getOasisEnforce($vilIDs, 1);
            $database->getPrisoners($vilIDs, 1);

            // calculate training updates
            foreach($trainlist as $train){
                $timepast = $train['timestamp2'] - $time;
                $pop = $train['pop'];
                $valuesUpdated = false;
                if($timepast <= 0 && $train['amt'] > 0) {
                    $valuesUpdated = true;
                    if($train['eachtime'] > 0){                       
                        $timepast2 = $time - $train['timestamp2'];
                        $trained = 1;
                        while($timepast2 >= $train['eachtime']){
                            $timepast2 -= $train['eachtime'];
                            $trained += 1;
                        }
                        
                        if($trained > $train['amt']) $trained = $train['amt'];
                    }
                    else $trained = $train['amt'];               
                    
                    if($train['unit'] > 2000) {
                        $realUnit = $train['unit'] - 2000;
                        $database->modifyUnit($train['vref'], [$realUnit], [$trained], [1]);
                    }
                    elseif($train['unit'] > 1000 && $train['unit'] != 99){
                        $database->modifyUnit($train['vref'], [$train['unit'] - 1000], [$trained], [1]);
                    }
                    else $database->modifyUnit($train['vref'], [$train['unit']], [$trained], [1]);
                
                    $database->updateTraining($train['id'], $trained, $trained * $train['eachtime']);
                    
                    if($train['amt'] - $trained <= 0) $database->trainUnit($train['id'], 0, 0, 0, 0, 1);
                }

                if ($valuesUpdated) call_user_func(get_class($database).'::clearUnitsCache');
             
                //Update starvation data
                $database->addStarvationData($train['vref']);
            }
        }
    }

    private function getsort_typeLevel($tid, $resarray) {
        $keyholder = [];

        foreach(array_keys($resarray, $tid) as $key) {
            if(strpos($key, 't')) {
                $key = preg_replace("/[^0-9]/", '', $key);
                array_push($keyholder, $key);
            }
        }

        $element = count($keyholder);
        if($element >= 2) {
            if($tid <= 4) {
                $temparray = [];

                for($i = 0; $i <= $element - 1; $i++) {
                    array_push($temparray, $resarray['f'.$keyholder[$i]]);
                }

                foreach ($temparray as $key => $val) {
                    if ($val == max($temparray)) $target = $key;                   
                }
            }
        }
        else if($element == 1) $target = 0;
        else return 0;

        if(!empty($keyholder[$target])) return $resarray['f'.$keyholder[$target]];
        else return 0;
    }

    private function celebrationComplete() {
        global $database;

        $varray = $database->getCel();
        foreach($varray as $vil){
            $id = $vil['wref'];
            $type = $vil['type'];
            $user = $vil['owner'];
            $cp = ($type == 1) ? 500 : 2000;           
            $database->clearCel($id);
            $database->setCelCp($user, $cp);
        }
    }

    private function demolitionComplete() {
        global $database;

        $varray = $database->getDemolition();
        foreach($varray as $vil) {
            if ($vil['timetofinish'] <= time()) {
                $type = $database->getFieldType($vil['vref'],$vil['buildnumber']);
                $level = $database->getFieldLevel($vil['vref'],$vil['buildnumber']);

                if ($level < 0) $level = 0;

                $buildarray = $GLOBALS["bid".$type];

                if ($type == 10 || $type == 38) {
					$prev_level_attri = ($level - 1 > 0) ? $buildarray[$level - 1]['attri'] : 0;
					$capacidade_a_remover = $buildarray[$level]['attri'] - $prev_level_attri;

                    $database->query("
                        UPDATE ".TB_PREFIX."vdata
                            SET
                                `maxstore` = IF(`maxstore` - ".$capacidade_a_remover." <= ".STORAGE_BASE.", ".STORAGE_BASE.", `maxstore` - ". $capacidade_a_remover.")
                            WHERE
                                wref=".(int) $vil['vref']);
                }

                if ($type == 11 || $type == 39) {
					$prev_level_attri = ($level - 1 > 0) ? $buildarray[$level - 1]['attri'] : 0;
					$capacidade_a_remover = $buildarray[$level]['attri'] - $prev_level_attri;

                    $database->query("
                        UPDATE ".TB_PREFIX."vdata
                            SET
                                `maxcrop` = IF(`maxcrop` - ".$capacidade_a_remover." <= ".STORAGE_BASE.", ".STORAGE_BASE.", `maxcrop` - ". $capacidade_a_remover.")
                            WHERE
                                wref=".(int) $vil['vref']);
                }

                if ($level == 1) $clear = ", f".$vil['buildnumber']."t=0";
				else $clear = "";

                if ($database->getVillageField($vil['vref'], 'natar') == 1 && $type == 40) $clear = ""; //fix by ronix - fixed by iopietro

                $q = "UPDATE ".TB_PREFIX."fdata SET f".$vil['buildnumber']."=".(($level - 1 >= 0) ? $level - 1 : 0).$clear." WHERE vref=".(int) $vil['vref'];
                $database->query($q);

                $pop = $this->getPop($type, $level - 1);
                $database->modifyPop($vil['vref'], $pop[0], 1);
                $this->procClimbers($database->getVillageField($vil['vref'], 'owner'));
                $database->delDemolition($vil['vref'], true);

                if ($type == 18) Automation::updateMax($database->getVillageField($vil['vref'], 'owner'));
            }
        }

    }

    private function updateHero() {
        global $database, $hero_levels;
        
        $harray = $database->getHero();
        if(!empty($harray)){
            // first of all, prepare all unit data at once for these heroes
            $heroVillageIDs = [];
            foreach($harray as $hdata) {
                $heroVillageIDs[] = $hdata['wref'];
            }

            // load data for those prepared IDs
            $unitData = $database->getUnit($heroVillageIDs);

            // now do the math
            $lastUpdateIDs = [];
            $timeNow = time();
            foreach($harray as $hdata){
                $columns = [];
                $columnValues = [];
                $modes = [];
                $lastUpdateTime = $timeNow;
                $newHealth = -1;

                if((time()-$hdata['lastupdate']) >= 1){
                    if($hdata['health'] < 100 and $hdata['health'] > 0){
                        if(SPEED <= 10) $speed = SPEED;                           
                        else if(SPEED <= 100) $speed = ceil(SPEED / 10);                           
                        else $speed = ceil(SPEED / 100);

                        $reg = $hdata['health'] + $hdata['regeneration'] * 5 * $speed / 86400 * (time() - $hdata['lastupdate']);

                        if($reg <= 100) $newHealth = $reg;                        
                        else $newHealth = 100;                      
                    }
                }

                $herolevel = $hdata['level'];
                $newLevel = - 1;
                $scorePoints = false;
                for ($i = $herolevel + 1; $i < 100; $i++){
                    if($hdata['experience'] >= $hero_levels[$i]){
                        $newLevel = $i;
                        if ($i < 100) $scorePoints = true;
                    }
                }

                // upgrade hero to a new level, if needed
                if ($newLevel > -1) {
                    $columns[] = 'level';
                    $columnValues[] = $newLevel;
                    $modes[] = null;
                }

                // add as many points as needed, if we're below level 100
                if ($scorePoints) {
                    $columns[] = 'points';
                    $columnValues[] = (5 * ($newLevel - $herolevel));
                    $modes[] = 1;
                }

                $villunits = $unitData[$hdata['wref']];
                if($hdata['trainingtime'] < time() && $hdata['inrevive'] == 1){
                    mysqli_query($database->dblink,"UPDATE " . TB_PREFIX . "units SET hero = 1 WHERE vref = ".(int) $hdata['wref']."");

                    $columns[] = 'dead';
                    $columnValues[] = 0;
                    $modes[] = null;

                    $columns[] = 'inrevive';
                    $columnValues[] = 0;
                    $modes[] = null;

                    $columns[] = 'inrevive';
                    $columnValues[] = 0;
                    $modes[] = null;

                    $newHealth = 100;
                    $lastUpdateTime = (int) $hdata['trainingtime'];
                }

                if($hdata['trainingtime'] < time() && $hdata['intraining'] == 1){
                    mysqli_query($database->dblink,"UPDATE " . TB_PREFIX . "units SET hero = 1 WHERE vref = ".(int) $hdata['wref']);

                    $columns[] = 'dead';
                    $columnValues[] = 0;
                    $modes[] = null;

                    $columns[] = 'intraining';
                    $columnValues[] = 0;
                    $modes[] = null;

                    $lastUpdateTime = (int) $hdata['trainingtime'];
                }

                // update health, if needed
                if ($newHealth > -1) {
                    $columns[] = 'health';
                    $columnValues[] = $newHealth;
                    $modes[] = null;
                }

                if ($lastUpdateTime != $timeNow) {
                    // last update timestamp
                    $columns[]      = 'lastupdate';
                    $columnValues[] = $lastUpdateTime;
                    $modes[]        = null;
                } else {
                    // leave same last update values for multiple heroes to the end
                    $lastUpdateIDs[] = $hdata['heroid'];
                }

                if (count($columns)) $database->modifyHero($columns, $columnValues, $hdata['heroid'], $modes);
            }

            if (count($lastUpdateIDs)) {
                mysqli_query($database->dblink,"UPDATE " . TB_PREFIX . "hero SET lastupdate = $timeNow WHERE heroid IN(".implode(', ', $lastUpdateIDs).")");
            }
        }
    }



    private function checkInvitedPlayes() {
        global $database;
        
        $q = "SELECT id, invited FROM ".TB_PREFIX."users WHERE invited > 0";
        $array = $database->query_return($q);

        // preload villages data
        $userIDs = [];
        foreach($array as $user) {
            $userIDs[] = $user['id'];
        }
        $database->getProfileVillages($userIDs);

        // continue...
        foreach($array as $user) {
            $numusers = mysqli_fetch_array(mysqli_query($database->dblink,"SELECT Count(*) as Total FROM ".TB_PREFIX."users WHERE id = ".(int) $user['invited']), MYSQLI_ASSOC);
            if($numusers['Total'] > 0){
                $varray = count($database->getProfileVillages($user['id']));
                if($varray > 1){
                    $usergold = $database->getUserField($user['invited'],"gold",0);
                    $gold = $usergold+50;
                    $database->updateUserField($user['invited'],"gold",$gold,1);
                    $database->updateUserField($user['id'],"invited",0,1);
                }
            }
        }
    }

    private function updateGeneralAttack() {
        global $database;

        mysqli_query($database->dblink, "
            UPDATE ".TB_PREFIX."general
                SET
                    shown = 0
                WHERE
                    shown = 1 AND
                    `time` < (UNIX_TIMESTAMP() - (86400 * 8))");
    }

    private function MasterBuilder() {
        global $database;
        
        $q = "SELECT id, wid, type, level, field, timestamp FROM ".TB_PREFIX."bdata WHERE master = 1";
        $array = $database->query_return($q);

        foreach($array as $master) {      
            $owner = $database->getVillageField($master['wid'], 'owner');
            $tribe = $database->getUserField($owner, 'tribe', 0);
            $villwood = $database->getVillageField($master['wid'], 'wood');
            $villclay = $database->getVillageField($master['wid'], 'clay');
            $villiron = $database->getVillageField($master['wid'], 'iron');
            $villcrop = $database->getVillageField($master['wid'], 'crop');
            $type = $master['type'];
            $level = $master['level'];
            $buildarray = $GLOBALS["bid".$type];
            $buildwood = $buildarray[$level]['wood'];
            $buildclay = $buildarray[$level]['clay'];
            $buildiron = $buildarray[$level]['iron'];
            $buildcrop = $buildarray[$level]['crop'];
            $ww = count($database->getBuildingByType($master['wid'], 40));

            if($tribe == 1){
                if($master['field'] < 19){
                    $bdata = $database->getDorf1Building($master['wid']);
                    $bdataTotal = count($bdata);
                    $bbdata = count($database->getDorf2Building($master['wid']));
                }else{
                    $bdata = $database->getDorf2Building($master['wid']);
                    $bdataTotal = count($bdata);
                    $bbdata = count($database->getDorf1Building($master['wid']));
                }
            }else{
                $bdata = array_merge($database->getDorf1Building($master['wid']), $database->getDorf2Building($master['wid']));
                $bdataTotal = $bbdata = count($bdata);          
            }

            if($database->getUserField($owner, 'plus', 0) > time() || $ww > 0){
                if($bbdata < 2) $inbuild = 2;                
                else $inbuild = 1;
            }
            else $inbuild = 1;

            $usergold = $database->getUserField($owner, 'gold', 0);

            if($bdataTotal < $inbuild && $buildwood <= $villwood && $buildclay <= $villclay && $buildiron <= $villiron && $buildcrop <= $villcrop && $usergold > 0){
                $time = $master['timestamp'] + time();

                if(!empty($bdata)){
                    foreach($bdata as $masterLoop) $time += ($masterLoop['timestamp'] - time());
                }

                if($bdataTotal == 0) $database->updateBuildingWithMaster($master['id'], $time, 0);                  
                else $database->updateBuildingWithMaster($master['id'], $time, 1);             

                $database->updateUserField($owner, 'gold', --$usergold, 1);
                $database->modifyResource($master['wid'], $buildwood, $buildclay, $buildiron, $buildcrop, 0);
            }
        }
    }


    
	private function rebuildStatCaches() {
		global $ranking;
		$ranking->rebuildUserStats();
		$ranking->rebuildVillageRanks();
	}

	private function procNewClimbers() {
		global $database, $ranking;

		// Garante que user_stats está populado antes de carregar o ranking completo
		$ranking->rebuildUserStats();

		// --- ETAPA 1: PREPARAÇÃO - Ler e processar o ranking completo UMA ÚNICA VEZ.
		$ranking->procRankArray(0, 999999, true);
		$climbers = $ranking->getRank();

		// Guarda de segurança: se não há jogadores, não há o que fazer.
		if (count($climbers) <= 2) { // <= 2 por causa do "pad" no índice 0
			return;
		}

		// --- ETAPA 2: ANÁLISE E SINCRONIZAÇÃO ---
		// Prepara as variáveis para o cálculo de pontos.
		$q_week = "SELECT week FROM ".TB_PREFIX."medal ORDER BY week DESC LIMIT 1";
		$result_week = mysqli_query($database->dblink, $q_week);
		$week = mysqli_num_rows($result_week) > 0 ? mysqli_fetch_assoc($result_week)['week'] + 1 : 1;
		$totalPlayers = count($climbers) - 1;

		// Itera sobre o ranking ATUAL e COMPLETO que está em memória.
		foreach ($climbers as $currentRank => $playerData) {
			// Pula o elemento "pad" no início do array.
			if ($currentRank == 0 || !isset($playerData['userid'])) {
				continue;
			}

			$uid = $playerData['userid'];
			// Pega o rank antigo que estava SALVO no banco de dados.
			$savedOldRank = $playerData['oldrank'];

			// Se for um jogador novo, seu rank antigo efetivo é sua posição atual para o cálculo.
			if ($savedOldRank == 0) {
				$savedOldRank = $currentRank;
			}
			
			// A principal verificação: o rank salvo no banco é diferente do rank real de agora?
			if ($currentRank != $savedOldRank) {
				// Sim, a posição do jogador mudou. Precisamos recalcular seus pontos de escalador.
				if ($week > 1) {
					// Lógica diferencial para semanas > 1: calcula a MUDANÇA nos pontos.
					$points_change = $savedOldRank - $currentRank; // Positivo se subiu, negativo se desceu.
					
					if ($points_change > 0) {
						$database->addclimberrankpop($uid, $points_change);
					} else {
						$database->removeclimberrankpop($uid, abs($points_change));
					}

				} else {
					// Lógica absoluta para a semana 1: define os pontos totais com base na posição.
					$totalpoints = $totalPlayers - $currentRank;
					$database->setclimberrankpop($uid, $totalpoints);
				}
			}

			// --- ETAPA 3: ATUALIZAÇÃO ---
			// Garante que o 'oldrank' no banco de dados reflita a posição ATUAL do jogador.
			// Isso "quita a dívida" e garante que na próxima vez que a função rodar, o estado estará correto.
			if ($playerData['oldrank'] != $currentRank) {
				$database->updateoldrank($uid, $currentRank);
			}
		}
	}

    private function procClimbers($uid) {
        global $database, $ranking;
        
        $ranking->rebuildUserStats();
        $ranking->procRankArray(0, 999999, true);
        $climbers = $ranking->getRank();
		
        if(count($ranking->getRank()) > 0){
            $q = "SELECT week FROM ".TB_PREFIX."medal order by week DESC LIMIT 0, 1";
            $result = mysqli_query($database->dblink,$q);
            if(mysqli_num_rows($result)) {
                $row = mysqli_fetch_assoc($result);
                $week = $row['week'] + 1;
            }
            else $week = 1;

            $myrank = $ranking->getUserRank($uid);
			
            if(isset($climbers[$myrank]['oldrank']) && $climbers[$myrank]['oldrank'] > $myrank){
                for($i = $myrank + 1; $i <= $climbers[$myrank]['oldrank']; $i++) {
				
                    if(isset($climbers[$i]['oldrank'])){
                        $oldrank = $ranking->getUserRank($climbers[$i]['userid']);
                        if($week > 1){
                            $totalpoints = $oldrank - $climbers[$i]['oldrank'];
                            $database->removeclimberrankpop($climbers[$i]['userid'], $totalpoints);
                            $database->updateoldrank($climbers[$i]['userid'], $oldrank);
                        }else{
                            $totalpoints = count($ranking->getRank()) - $oldrank;
                            $database->setclimberrankpop($climbers[$i]['userid'], $totalpoints);
                            $database->updateoldrank($climbers[$i]['userid'], $oldrank);
                        }
                    }              
                }
                if(isset($climbers[$myrank]['oldrank'])){
                    if($week > 1){
                        $totalpoints = $climbers[$myrank]['oldrank'] - $myrank;
                        $database->addclimberrankpop($climbers[$myrank]['userid'], $totalpoints);
                        $database->updateoldrank($climbers[$myrank]['userid'], $myrank);
                    }else{
                        $totalpoints = count($ranking->getRank()) - $myrank;
                        $database->setclimberrankpop($climbers[$myrank]['userid'], $totalpoints);
                        $database->updateoldrank($climbers[$myrank]['userid'], $myrank);
                    }
                }        
            }else if(isset($climbers[$myrank]['oldrank']) && $climbers[$myrank]['oldrank'] < $myrank){
                for($i = $climbers[$myrank]['oldrank']; $i < $myrank; $i++) {
                    if(isset($climbers[$i]['oldrank'])){
                        $oldrank = $ranking->getUserRank($climbers[$i]['userid']);
                        if($week > 1){
                            $totalpoints = $climbers[$i]['oldrank'] - $oldrank;
                            $database->addclimberrankpop($climbers[$i]['userid'], $totalpoints);
                            $database->updateoldrank($climbers[$i]['userid'], $oldrank);
                        }else{
                            $totalpoints = count($ranking->getRank()) - $oldrank;
                            $database->setclimberrankpop($climbers[$i]['userid'], $totalpoints);
                            $database->updateoldrank($climbers[$i]['userid'], $oldrank);
                        }
                    }          
                }
                if(isset($climbers[$myrank-1]['oldrank'])){
                    if($week > 1){
                        $totalpoints = $myrank - $climbers[$myrank-1]['oldrank'];
                        $database->removeclimberrankpop($climbers[$myrank-1]['userid'], $totalpoints);
                        $database->updateoldrank($climbers[$myrank-1]['userid'], $myrank);
                    }else{
                        $totalpoints = count($ranking->getRank()) - $myrank;
                        $database->setclimberrankpop($climbers[$myrank-1]['userid'], $totalpoints);
                        $database->updateoldrank($climbers[$myrank-1]['userid'], $myrank);
                    }
                }               
            }
        }
		
        $ranking->procARankArray();
        $aid = $database->getUserField($uid,"alliance",0);
        if(count($ranking->getRank()) > 0 && $aid != 0){
            $ally = $database->getAlliance($aid);
            $memberlist = $database->getAllMember($ally['id']);
            $oldrank = 0;

            $memberIDs = [];
            foreach($memberlist as $member) {
                $memberIDs[] = $member['id'];
            }
            $data = $database->getVSumField($memberIDs,"pop");

            if (count($data)) {
                foreach ($data as $row) {
                    $oldrank += $row['Total'];
                }
            }

            if($ally['oldrank'] != $oldrank){
                if($ally['oldrank'] < $oldrank) {
                    $totalpoints = $oldrank - $ally['oldrank'];
                    $database->addclimberrankpopAlly($ally['id'], $totalpoints);
                    $database->updateoldrankAlly($ally['id'], $oldrank);
                } else
                    if($ally['oldrank'] > $oldrank) {
                        $totalpoints = $ally['oldrank'] - $oldrank;
                        $database->removeclimberrankpopAlly($ally['id'], $totalpoints);
                        $database->updateoldrankAlly($ally['id'], $oldrank);
                    }
            }
        }
    }

    private function checkBan() {
        global $database;

        mysqli_query($database->dblink, "
            UPDATE ".TB_PREFIX."banlist as b
                JOIN ".TB_PREFIX."users as u ON b.uid = u.id
                    SET
                        b.active = 0,
                        u.access = 2
                    WHERE
                        b.active = 1 AND
                        b.`end` < UNIX_TIMESTAMP() AND
                        b.`end` > 0");
    }

    private function regenerateOasisTroops() {
        global $database;
        
        $timeFinal = time() - NATURE_REGTIME;
        $q = "SELECT wref FROM " . TB_PREFIX . "odata where conqured = 0 and lastupdated2 < $timeFinal";
        $array = $database->query_return($q);
        if (count($array)) {
            $ids = [];
            foreach($array as $oasis) $ids[] = $oasis['wref'];
            $database->regenerateOasisUnits($ids, true);
        }
    }

    public static function updateMax($leader) {
        global $bid18, $database;
        
        $q = mysqli_fetch_array(mysqli_query($database->dblink,"SELECT Count(*) as Total FROM " . TB_PREFIX . "alidata where leader = ". (int) $leader), MYSQLI_ASSOC);
        if ($q['Total'] > 0) {
            $villages = $database->getVillagesID2($leader);
            $max = 0;

            // cache resource levels
            $vilIDs = [];
            foreach($villages as $village){
                $vilIDs[$village['wref']] = true;
            }
            $database->cacheResourceLevels(array_keys($vilIDs));

            foreach($villages as $village){
                $field = $database->getResourceLevel($village['wref'], false);
                for($i = 19; $i <= 40; $i++){
                    if($field['f'.$i.'t'] == 18){
                        $level = $field['f'.$i];
                        $attri = $bid18[$level]['attri'];
                    }
                }
                if($attri > $max){
                    $max = $attri;
                }
            }
            $q = "UPDATE ".TB_PREFIX."alidata set max = ".(int) $max." where leader = ".(int) $leader;
            $database->query($q);
        }
    }



    


    public function startFakeAttack() {
        global $database, $units;
        $db = $database->dblink;

        // --- CONFIGURAÇÃO ---
        $maxGlobalFakes = 50; // Limite máximo de ataques fakes por ciclo
        $totalFakesCreated = 0;
        $activeThreshold = time() - 86400; // Ativos nas últimas 24h

        // 1. Selecionar jogadores ativos
        $qUsers = "SELECT id FROM " . TB_PREFIX . "users WHERE timestamp > $activeThreshold AND id > 5 AND access < 8";
        $resUsers = mysqli_query($db, $qUsers);
        
        $activeUsers = [];
        while ($row = mysqli_fetch_assoc($resUsers)) { $activeUsers[] = $row; }

        // 2. Aleatorizar a lista de usuários para que o rodízio seja justo
        shuffle($activeUsers);

        foreach ($activeUsers as $user) {
            if ($totalFakesCreated >= $maxGlobalFakes) break;

            $userId = (int)$user['id'];
            
            // 2. Buscar todas as vilas deste usuário
            $qVilas = "SELECT v.wref, w.x, w.y FROM " . TB_PREFIX . "vdata v 
                    JOIN " . TB_PREFIX . "wdata w ON w.id = v.wref 
                    WHERE v.owner = $userId";
            $resVilas = mysqli_query($db, $qVilas);
            
            $vilasDoJogador = [];
            while($v = mysqli_fetch_assoc($resVilas)) { $vilasDoJogador[] = $v; }
            
            if (empty($vilasDoJogador)) continue;

            // 3. Definir QUEM será o atacante (baseado em uma vila aleatória do alvo)
            $vilaSorteadaParaBusca = $vilasDoJogador[array_rand($vilasDoJogador)];
            $attackerData = $this->findNearestAttacker(
                $vilaSorteadaParaBusca['x'], 
                $vilaSorteadaParaBusca['y'], 
                $userId
            );

            if (!$attackerData) continue;

            $attackerWref = $attackerData['wref'];
            $attackerOwnerId = $attackerData['owner'];
            
            // Pegamos a tribo para o cálculo de tempo
            $uInfo = $database->getUserArray($attackerOwnerId, 1);
            $tribe = $uInfo['tribe'];

            // 4. Processar CADA vila do jogador alvo
            foreach ($vilasDoJogador as $vilaAlvo) {
                
                if ($totalFakesCreated >= $maxGlobalFakes) break;

                // 3% de chance para cada vila receber um ataque
                if (rand(1, 100) <= 1) {
                    
                    $possibleTroops = [1, 2, 3, 4, 5, 6, 7, 8];
                
                    if ($tribe == 1 || $tribe == 2) {
                        $possibleTroops = [1, 2, 3, 5, 6, 7, 8]; // Remove T4
                    } elseif ($tribe == 3) {
                        $possibleTroops = [1, 2, 4, 5, 6, 7, 8]; // Remove T3
                    }

                    // Sorteia uma das unidades permitidas
                    $troopNum = $possibleTroops[array_rand($possibleTroops)];
                    $fakeUnits = array_fill(1, 11, 0);
                    $fakeUnits['t'.$troopNum] = 1;

                    // Calcular distância e tempo
                    $travelTime = $units->getWalkingTroopsTime(
                        $attackerWref, 
                        $vilaAlvo['wref'], 
                        $attackerOwnerId, 
                        $tribe, 
                        $fakeUnits, 
                        1, 
                        't'
                    );

                    $duration = $database->getArtifactsValueInfluence($attackerOwnerId, $attackerWref, 2, $travelTime);
                    $endtime = time() + $duration;

                    $ctp1 = ($troopNum == 8) ? 99 : 0;
                    $ctp2 = ($troopNum == 8) ? 99 : 0;

                    // 5. Lançar o Ataque (Tipo 3 = Ataque Normal)
                    $ref = $database->addAttack(
                        $attackerWref,
                        ($troopNum==1?1:0), ($troopNum==2?1:0), ($troopNum==3?1:0), 
                        ($troopNum==4?1:0), ($troopNum==5?1:0), ($troopNum==6?1:0), 
                        ($troopNum==7?1:0), ($troopNum==8?1:0), 0, 0, 0, 
                        3, $ctp1, $ctp2, 0
                    );

                    // Adicionar o movimento no mapa/lista de ataques
                    $database->addMovement(3, $attackerWref, $vilaAlvo['wref'], $ref, time(), $endtime);

                    $totalFakesCreated++;
                }
            }
        }
        return $totalFakesCreated;
    }

    private function findNearestAttacker($targetX, $targetY, $targetOwner) {
        global $database;
        $db = $database->dblink;

        $inactiveThreshold = time() - 86400;
        
        for ($radius = 7; $radius <= 100; $radius += 10) {
            $minX = $targetX - $radius; $maxX = $targetX + $radius;
            $minY = $targetY - $radius; $maxY = $targetY + $radius;

            // Query otimizada: Já busca o dono da vila mais próxima
            $q = "SELECT v.owner FROM " . TB_PREFIX . "vdata v 
                JOIN " . TB_PREFIX . "wdata w ON w.id = v.wref 
                JOIN " . TB_PREFIX . "users u ON u.id = v.owner
                WHERE w.x BETWEEN $minX AND $maxX AND w.y BETWEEN $minY AND $maxY 
                AND v.owner != $targetOwner 
                AND v.owner > 5 
                AND u.timestamp < $inactiveThreshold
                ORDER BY (POW(w.x - $targetX, 2) + POW(w.y - $targetY, 2)) ASC LIMIT 1";
            
            $res = mysqli_query($db, $q);
            if ($row = mysqli_fetch_assoc($res)) {
                // Agora retornamos o array completo da CAPITAL do atacante
                $qCap = "SELECT wref, owner FROM " . TB_PREFIX . "vdata WHERE owner = {$row['owner']} AND capital = 1 LIMIT 1";
                $resCap = mysqli_query($db, $qCap);
                return mysqli_fetch_assoc($resCap); // Retorna ['wref' => X, 'owner' => Y]
            }
        }
        return null;
    }

    /**
     * Arquiva e apaga dados antigos de tabelas de alto volume para manter a performance.
     * Tabelas-alvo: s1_movement, s1_attacks, s1_ndata (relatórios), s1_mdata (mensagens).
     * O processo é feito em lotes para evitar sobrecarga e travamento do banco de dados.
     */
    private function archiveAndPrune() {
        global $database;
        $archive_older_than_days = 45;
        $batch_limit = 1000; // Apaga em lotes de 1000 registros para ser rápido e seguro
        $cutoff_timestamp = time() - ($archive_older_than_days * 86400);

        error_log("AutomationT3.6: [ArchiveAndPrune] Iniciando processo de arquivamento para dados mais antigos que " . date('Y-m-d H:i:s', $cutoff_timestamp));

        // --- Tabela 1: s1_movement e s1_attacks (são relacionadas) ---
        // Primeiro, identificamos os movimentos antigos. A chave é o `endtime`.
        $movements_to_archive_sql = "SELECT moveid, ref FROM " . TB_PREFIX . "movement WHERE endtime < " . (int)$cutoff_timestamp . " AND proc = 1 LIMIT " . (int)$batch_limit;
        $movements_to_archive = $database->query_return($movements_to_archive_sql);

        if (!empty($movements_to_archive)) {
            $movement_ids = [];
            $attack_ref_ids = [];
            foreach ($movements_to_archive as $mov) {
                $movement_ids[] = (int)$mov['moveid'];
                if ($mov['ref'] > 0) {
                    $attack_ref_ids[] = (int)$mov['ref'];
                }
            }
            $movement_id_list = implode(',', $movement_ids);

			$pre_delete_mov_sql = "DELETE FROM " . TB_PREFIX . "movement_archive WHERE moveid IN (" . $movement_id_list . ")";
			$database->query($pre_delete_mov_sql);

            // 1.1: Copia os movimentos para a tabela de arquivo
            $copy_mov_sql = "INSERT INTO " . TB_PREFIX . "movement_archive SELECT * FROM " . TB_PREFIX . "movement WHERE moveid IN (" . $movement_id_list . ")";
            $database->query($copy_mov_sql);
            
            // 1.2: Copia os ataques associados para a tabela de arquivo (se houver algum)
            if (!empty($attack_ref_ids)) {
                $attack_ref_id_list = implode(',', array_unique($attack_ref_ids));

				$pre_delete_atk_sql = "DELETE FROM " . TB_PREFIX . "attacks_archive WHERE id IN (" . $attack_ref_id_list . ")";
				$database->query($pre_delete_atk_sql);

                $copy_att_sql = "INSERT INTO " . TB_PREFIX . "attacks_archive SELECT * FROM " . TB_PREFIX . "attacks WHERE id IN (" . $attack_ref_id_list . ")";
                $database->query($copy_att_sql);

                // 1.3: Apaga os ataques da tabela principal
                $delete_att_sql = "DELETE FROM " . TB_PREFIX . "attacks WHERE id IN (" . $attack_ref_id_list . ")";
                $database->query($delete_att_sql);
            }
            
            // 1.4: Apaga os movimentos da tabela principal
            $delete_mov_sql = "DELETE FROM " . TB_PREFIX . "movement WHERE moveid IN (" . $movement_id_list . ")";
            $database->query($delete_mov_sql);

            error_log("AutomationT3.6: [ArchiveAndPrune] Arquivados e apagados " . count($movement_ids) . " registros de s1_movement e s1_attacks.");
        }

        // --- Tabela 2: s1_ndata (Relatórios) ---
        $reports_to_archive_sql = "SELECT id FROM " . TB_PREFIX . "ndata WHERE time < " . (int)$cutoff_timestamp . " LIMIT " . (int)$batch_limit;
        $reports_to_archive = $database->query_return($reports_to_archive_sql);

        if (!empty($reports_to_archive)) {
            $report_ids = [];
            foreach($reports_to_archive as $report) {
                $report_ids[] = (int)$report['id'];
            }
            $report_id_list = implode(',', $report_ids);

			$pre_delete_ndt_sql = "DELETE FROM " . TB_PREFIX . "ndata_archive WHERE id IN (" . $report_id_list . ")";
			$database->query($pre_delete_ndt_sql);

            // 2.1: Copia os relatórios para a tabela de arquivo
            $copy_reports_sql = "INSERT INTO " . TB_PREFIX . "ndata_archive SELECT * FROM " . TB_PREFIX . "ndata WHERE id IN (" . $report_id_list . ")";
            $database->query($copy_reports_sql);

            // 2.2: Apaga os relatórios da tabela principal
            $delete_reports_sql = "DELETE FROM " . TB_PREFIX . "ndata WHERE id IN (" . $report_id_list . ")";
            $database->query($delete_reports_sql);

            error_log("AutomationT3.6: [ArchiveAndPrune] Arquivados e apagados " . count($report_ids) . " relatórios antigos (s1_ndata).");
        }

        // --- Tabela 3: s1_mdata (Mensagens) ---
        $messages_to_archive_sql = "SELECT id FROM " . TB_PREFIX . "mdata WHERE time < " . (int)$cutoff_timestamp . " LIMIT " . (int)$batch_limit;
        $messages_to_archive = $database->query_return($messages_to_archive_sql);

        if (!empty($messages_to_archive)) {
            $message_ids = [];
            foreach($messages_to_archive as $msg) {
                $message_ids[] = (int)$msg['id'];
            }
            $message_id_list = implode(',', $message_ids);

			$pre_delete_mdt_sql = "DELETE FROM " . TB_PREFIX . "mdata_archive WHERE id IN (" . $message_id_list . ")";
			$database->query($pre_delete_mdt_sql);

            // 3.1: Copia as mensagens para a tabela de arquivo
            $copy_messages_sql = "INSERT INTO " . TB_PREFIX . "mdata_archive SELECT * FROM " . TB_PREFIX . "mdata WHERE id IN (" . $message_id_list . ")";
            $database->query($copy_messages_sql);

            // 3.2: Apaga as mensagens da tabela principal
            $delete_messages_sql = "DELETE FROM " . TB_PREFIX . "mdata WHERE id IN (" . $message_id_list . ")";
            $database->query($delete_messages_sql);

            error_log("AutomationT3.6: [ArchiveAndPrune] Arquivadas e apagadas " . count($message_ids) . " mensagens antigas (s1_mdata).");
        }
    }

    private function spawnNatars(){
    	global $database;
    	
    	//Check if Natars account is already created and if the time
    	//is come and we have to create Natars and spawn their artifacts
    	if($database->areArtifactsSpawned() || strtotime(START_DATE) + (NATARS_SPAWN_TIME * 86400) > $database->getGameTime()) return;
    	
    	//Create the Natars account and his capital
    	$this->artifacts->createNatars();
    	
    	//Write the system message
    	$database->displaySystemMessage(ARTEFACT);
    }
    
    private function spawnWWVillages(){
    	global $database;
    	
    	//Check if Natars account has already been created, if WW villages have already been spawned
    	//and if it's the time to spawn them or not
    	if(!$database->areArtifactsSpawned() || $database->areWWVillagesSpawned() || strtotime(START_DATE) + (NATARS_WW_SPAWN_TIME * 86400) > $database->getGameTime()) return;
    	
    	//Create WW villages
    	$this->artifacts->createWWVillages();
	    
	    //Write the system message
    	$database->displaySystemMessage(WWVILLAGEMSG);
    }
    
    private function spawnWWBuildingPlans(){
    	global $database;
    	
    	//Check if Natars account is already spawned, if WW building plans have already been spawned
    	//and if it's the time to spawn them or not
    	if(!$database->areArtifactsSpawned() || $database->areArtifactsSpawned(true) || strtotime(START_DATE) + (NATARS_WW_BUILDING_PLAN_SPAWN_TIME * 86400) > $database->getGameTime()) return;
    	
    	//Create WW building plans
    	$this->artifacts->createWWBuildingPlans();
    	
    	//Set the system message to contain the infos of the WW building plans
    	$database->displaySystemMessage(PLAN_INFO);
    }
    
    private function activateArtifacts() {
        global $database;
        
        //Check if there's at least one artifact, if not, return
        if(!$database->areArtifactsSpawned()) return;
        
        //Activate the artifacts that need to be activated
        $this->artifacts->activateArtifacts();
    }

    private function artefactOfTheFool() {
        global $database;
        
        $time = time();
        $q = "SELECT id, size FROM " . TB_PREFIX . "artefacts where type = 8 AND active = 1 AND del = 0 AND lastupdate <= ".($time - (86400 / (SPEED == 2 ? 1.5 : (SPEED == 3 ? 2 : SPEED))));
        $array = $database->query_return($q);
        if ($array) {
            foreach($array as $artefact) {
                $kind = rand(1, 7);
                
                while($kind == 6) $kind = rand(1, 7);
                    
                if($artefact['size'] != 3) $bad_effect = rand(0, 1);
                else $bad_effect = 0;

                switch($kind) {
                    case 1:
                        $effect = rand(1, 5);
                        break;
                    case 2:
                        $effect = rand(1, 3);
                        break;
                    case 3:
                        $effect = rand(3, 10);
                        break;
                    case 4:
                    case 5:
                        $effect = rand(2, 4);
                        break;
                    case 7:
                        $effect = rand(1, 6);
                        break;
                }
                mysqli_query($database->dblink,"UPDATE ".TB_PREFIX."artefacts SET kind = ". (int) $kind. ", bad_effect = $bad_effect, effect2 = $effect, lastupdate = $time WHERE id = ".(int) $artefact['id']);
            }
        }
    }

    private function startNatarAttack($level, $vid, $time) {
        global $database;

        // bad, but should work :D
        // I took the data from my first ww (first .org world)
        // TODO: get the algo from the real travian with the 100 biggest offs

        $troops = [5 => [[3412, 2814, 4156, 3553, 9, 0], [35, 0, 77, 33, 17, 10]],
                   10 => [[4314, 3688, 5265, 4621, 13, 0], [65, 0, 175, 77, 28, 17]],
                   15 => [[4645, 4267, 5659, 5272, 15, 0], [99, 0, 305, 134, 40, 25]],
                   20 => [[6207, 5881, 7625, 7225, 22, 0], [144, 0, 456, 201, 56, 36]],
                   25 => [[6004, 5977, 7400, 7277, 23, 0], [152, 0, 499, 220, 58, 37]],
                   30 => [[7073, 7181, 8730, 8713, 27, 0], [183, 0, 607, 268, 69, 45]],          
                   35 => [[7090, 7320, 8762, 8856, 28, 0], [186, 0, 620, 278, 70, 45]],           
                   40 => [[7852, 6967, 9606, 8667, 25, 0], [146, 0, 431, 190, 60, 37]],           
                   45 => [[8480, 8883, 10490, 10719, 35, 0], [223, 0, 750, 331, 83, 54]],          
                   50 => [[8522, 9038, 10551, 10883, 35, 0], [224, 0, 757, 335, 83, 54]],            
                   55 => [[8931, 8690, 10992, 10624, 32, 0], [219, 0, 707, 312, 84, 54]],           
                   60 => [[12138, 13013, 15040, 15642, 51, 0], [318, 0, 1079, 477, 118, 76]],            
                   65 => [[13397, 14619, 16622, 17521, 58, 0], [345, 0, 1182, 522, 127, 83]],           
                   70 => [[16323, 17665, 20240, 21201, 70, 0], [424, 0, 1447, 640, 157, 102]],          
                   75 => [[20739, 22796, 25746, 27288, 91, 0], [529, 0, 1816, 803, 194, 127]],           
                   80 => [[21857, 24180, 27147, 28914, 97, 0], [551, 0, 1898, 839, 202, 132]],          
                   85 => [[22476, 25007, 27928, 29876, 100, 0], [560, 0, 1933, 855, 205, 134]],           
                   90 => [[31345, 35053, 38963, 41843, 141, 0], [771, 0, 2668, 1180, 281, 184]],           
                   95 => [[31720, 35635, 39443, 42506, 144, 0], [771, 0, 2671, 1181, 281, 184]],          
                   96 => [[32885, 37007, 40897, 44130, 150, 0], [795, 0, 2757, 1219, 289, 190]],         
                   97 => [[32940, 37099, 40968, 44235, 150, 0], [794, 0, 2755, 1219, 289, 190]],       
                   98 => [[33521, 37691, 41686, 44953, 152, 0], [812, 0, 2816, 1246, 296, 194]],           
                   99 => [[36251, 40861, 45089, 48714, 165, 0], [872, 0, 3025, 1338, 317, 208]]];

        // select the troops^^
        if (isset($troops[$level])) $units = $troops[$level];          
        else return false;

        // get the capital village from the natars
        $query = mysqli_query($database->dblink,'SELECT `wref` FROM `' . TB_PREFIX . 'vdata` WHERE `owner` = 3 and `capital` = 1 LIMIT 1') or die(mysqli_error($database->dblink));
        $row = mysqli_fetch_assoc($query);

        // start the attacks
        $endtime = $time + round(86400 / INCREASE_SPEED);

        // -.-
        $vid = (int) $vid;
        mysqli_query($database->dblink,'INSERT INTO `' . TB_PREFIX . 'ww_attacks` (`vid`, `attack_time`) VALUES (' . $vid . ', ' . $endtime . ')');
        mysqli_query($database->dblink,'INSERT INTO `' . TB_PREFIX . 'ww_attacks` (`vid`, `attack_time`) VALUES (' . $vid . ', ' . ($endtime + 1) . ')');

        // wave 1
        $ref = $database->addAttack($row['wref'], 0, $units[0][0], $units[0][1], 0, $units[0][2], $units[0][3], $units[0][4], $units[0][5], 0, 0, 0, 3, 0, 0, 0, 0, 20, 20, 0, 20, 20, 20, 20);
        $database->addMovement(3, $row['wref'], $vid, $ref, $time, $endtime);

        // wave 2
        $ref2 = $database->addAttack($row['wref'], 0, $units[1][0], $units[1][1], 0, $units[1][2], $units[1][3], $units[1][4], $units[1][5], 0, 0, 0, 3, 40, 0, 0, 0, 20, 20, 0, 20, 20, 20, 20, ['vid' => $vid, 'endtime' => ($endtime + 1)]);
        $database->addMovement(3, $row['wref'], $vid, $ref2, $time, $endtime + 1);
    }

    private function checkWWAttacks() {
        global $database;
        
        $query = mysqli_query($database->dblink,'SELECT vid, attack_time FROM `' . TB_PREFIX . 'ww_attacks` WHERE `attack_time` <= ' . time());
        while ($row = mysqli_fetch_assoc($query))
        {
            // delete the attack
            $query3 = mysqli_query($database->dblink,'DELETE FROM `' . TB_PREFIX . 'ww_attacks` WHERE `vid` = ' . (int) $row['vid'] . ' AND `attack_time` = ' . (int) $row['attack_time']);
        }
    }

    private function updateStoreNew() {
        global $database, $bid10, $bid38, $bid11, $bid39;

        // FASE 1: Filtro Inteligente
        // Só selecionamos vilas onde houve alguma alteração (lastupdate) 
        // após o último cálculo de armazenamento (updateStorage).
        $sql = "SELECT f.vref, f.f19, f.f19t, f.f20, f.f20t, f.f21, f.f21t, f.f22, f.f22t, f.f23, f.f23t, 
                    f.f24, f.f24t, f.f25, f.f25t, f.f26, f.f26t, f.f27, f.f27t, f.f28, f.f28t, f.f29, f.f29t, 
                    f.f30, f.f30t, f.f31, f.f31t, f.f32, f.f32t, f.f33, f.f33t, f.f34, f.f34t, f.f35, f.f35t, 
                    f.f36, f.f36t, f.f37, f.f37t, f.f38, f.f38t 
                FROM " . TB_PREFIX . "fdata f
                JOIN " . TB_PREFIX . "vdata v ON f.vref = v.wref
                WHERE v.lastupdate > v.updateStorage";

        $result = $database->query($sql);
        if (!$result || mysqli_num_rows($result) == 0) return;

        $time = time();
        $updates_to_make = [];

        // FASE 2: Cálculo em Memória
        while ($row = mysqli_fetch_assoc($result)) {
            $ress = 0;
            $crop = 0;

            for ($i = 19; $i <= 38; ++$i) {
                $type = $row['f' . $i . 't'];
                $lvl = $row['f' . $i];

                if ($lvl == 0) continue;

                switch($type) {
                    case 10: $ress += ($bid10[$lvl]['attri'] ?? 0) * STORAGE_MULTIPLIER; break;
                    case 38: $ress += ($bid38[$lvl]['attri'] ?? 0) * STORAGE_MULTIPLIER; break;
                    case 11: $crop += ($bid11[$lvl]['attri'] ?? 0) * STORAGE_MULTIPLIER; break;
                    case 39: $crop += ($bid39[$lvl]['attri'] ?? 0) * STORAGE_MULTIPLIER; break;
                }
            }

            $updates_to_make[$row['vref']] = [
                'maxstore' => max($ress, STORAGE_BASE),
                'maxcrop' => max($crop, STORAGE_BASE)
            ];
        }
        mysqli_free_result($result);

        // FASE 3: Update em Lote
        if (!empty($updates_to_make)) {
            $database->dblink->autocommit(FALSE);
            $chunks = array_chunk($updates_to_make, 500, true);

            foreach ($chunks as $chunk) {
                $whens_store = "";
                $whens_crop = "";
                $ids = implode(',', array_keys($chunk));

                foreach ($chunk as $wref => $val) {
                    $whens_store .= "WHEN $wref THEN " . (int)$val['maxstore'] . " ";
                    $whens_crop  .= "WHEN $wref THEN " . (int)$val['maxcrop'] . " ";
                }

                $up_query = "UPDATE " . TB_PREFIX . "vdata SET 
                            maxstore = CASE wref $whens_store ELSE maxstore END,
                            maxcrop = CASE wref $whens_crop ELSE maxcrop END,
                            updateStorage = $time
                            WHERE wref IN ($ids)";
                
                $database->query($up_query);
            }
            $database->dblink->commit();
            $database->dblink->autocommit(TRUE);
        }
    }

    function medalsNew(){
        global $database;
        $db = $database->dblink;

        // 1. Verificação de tempo (Mantive sua lógica de tempo original)
        $q = "SELECT lastgavemedal FROM ".TB_PREFIX."config";
        $result = mysqli_query($db, $q);
        if(!$result) return;
        $row = mysqli_fetch_assoc($result);

        if($row['lastgavemedal'] == 0){
            $stime = strtotime(START_DATE) - strtotime(date('d.m.Y')) + strtotime(START_TIME);
            if($stime < time()){
                $setDays = round(MEDALINTERVAL / 86400);
                $newtime = $setDays < 7 ? strtotime(($setDays + 1).'day midnight') : strtotime('next monday');
                mysqli_query($db, "UPDATE ".TB_PREFIX."config SET lastgavemedal = ".(int)$newtime);
            }
            return;
        }

        // Só prossegue se for hora de dar medalhas
        if($row['lastgavemedal'] > time() || MEDALINTERVAL <= 0) return;

        mysqli_begin_transaction($db);

        try {
            // Determinar semanas atuais
            $resW = mysqli_query($db, "SELECT MAX(week) as wk FROM ".TB_PREFIX."medal");
            $week = (mysqli_fetch_assoc($resW)['wk'] ?? 0) + 1;
            
            $resAW = mysqli_query($db, "SELECT MAX(week) as wk FROM ".TB_PREFIX."allimedal");
            $allyweek = (mysqli_fetch_assoc($resAW)['wk'] ?? 0) + 1;

            mysqli_query($db, "CREATE TEMPORARY TABLE temp_pop AS 
                           SELECT owner as userid, SUM(pop) as current_pop 
                           FROM ".TB_PREFIX."vdata GROUP BY owner");

            // --- MEDALHAS DE JOGADORES ---
            $userCats = [
                1 => ['col' => 'u.ap', 'img' => 't2_'], // Ataque
                2 => ['col' => 'u.dp', 'img' => 't3_'], // Defesa
                3 => ['col' => '(tp.current_pop - u.oldpop)', 'img' => 't1_'], // Climber (Ponto 3 corrigido)
                10 => ['col' => 'u.clp', 'img' => 't6_'], // Rank Climber
                4 => ['col' => 'u.RR', 'img' => 't4_']   // Robbers
            ];

            foreach ($userCats as $catId => $data) {
                mysqli_query($db, "SET @rank := 0");
                $q = "INSERT INTO ".TB_PREFIX."medal (userid, categorie, plaats, week, points, img)
                    SELECT final.id, $catId, (@rank:=@rank+1), $week, final.pts, CONCAT('{$data['img']}', (@rank))
                    FROM (
                        SELECT u.id, {$data['col']} as pts
                        FROM ".TB_PREFIX."users u
                        LEFT JOIN temp_pop tp ON u.id = tp.userid
                        WHERE u.id > 5 AND u.access < 8 AND {$data['col']} > 0
                        ORDER BY {$data['col']} DESC, u.id DESC 
                        LIMIT 10
                    ) as final";
                mysqli_query($db, $q);
            }

            // --- 1. REPEAT MEDALS: TOP 3 (ACUMULATIVO) ---
            $repeatRules = [
                ['base' => 1, 'new' => 6,  'imgs' => ['t120_1', 't121_1', 't122_1']], // Atk
                ['base' => 2, 'new' => 7,  'imgs' => ['t140_1', 't141_1', 't142_1']], // Def
                ['base' => 3, 'new' => 8,  'imgs' => ['t100_1', 't101_1', 't102_1']], // Pop
                ['base' => 10,'new' => 11, 'imgs' => ['t200_1', 't201_1', 't202_1']], // Rank
                ['base' => 4, 'new' => 9,  'imgs' => ['t160_1', 't161_1', 't162_1']]  // Robber
            ];

            foreach ($repeatRules as $r) {
                $q = "INSERT INTO ".TB_PREFIX."medal (userid, categorie, plaats, week, points, img)
                    SELECT m.userid, {$r['new']}, 0, $week, 
                    CASE WHEN COUNT(m.id) = 3 THEN 'Three' WHEN COUNT(m.id) = 5 THEN 'Five' ELSE 'Ten' END,
                    CASE WHEN COUNT(m.id) = 3 THEN '{$r['imgs'][0]}' WHEN COUNT(m.id) = 5 THEN '{$r['imgs'][1]}' ELSE '{$r['imgs'][2]}' END
                    FROM ".TB_PREFIX."medal m
                    WHERE m.categorie = {$r['base']} AND m.plaats <= 3
                    GROUP BY m.userid
                    HAVING COUNT(m.id) IN (3, 5, 10)
                    AND m.userid IN (SELECT userid FROM ".TB_PREFIX."medal WHERE week = $week AND categorie = {$r['base']} AND plaats <= 3)";
                
                mysqli_query($db, $q);
            }

            // --- 2. STREAK MEDALS: TOP 10 (CONSECUTIVO) ---
            $streakRules = [
                ['base' => 1, 'new' => 12, 'imgs' => ['t130_1', 't131_1', 't132_1']], // Atk
                ['base' => 2, 'new' => 13, 'imgs' => ['t150_1', 't151_1', 't152_1']], // Def
                ['base' => 3, 'new' => 14, 'imgs' => ['t110_1', 't111_1', 't112_1']], // Pop
                ['base' => 10,'new' => 16, 'imgs' => ['t210_1', 't211_1', 't212_1']], // Rank
                ['base' => 4, 'new' => 15, 'imgs' => ['t170_1', 't171_1', 't172_1']]  // Robber
            ];

            foreach ($streakRules as $s) {
                $q = "INSERT INTO ".TB_PREFIX."medal (userid, categorie, plaats, week, points, img)
                    SELECT res.userid, res.cat_new, 0, res.wk, res.medal_text, res.medal_img
                    FROM (
                        SELECT cur.userid, 
                                {$s['new']} as cat_new, 
                                $week as wk,
                                CASE 
                                    WHEN (SELECT COUNT(*) FROM ".TB_PREFIX."medal WHERE userid = cur.userid AND categorie = {$s['base']} AND week > $week - 10) = 10 THEN '{$s['imgs'][2]}'
                                    WHEN (SELECT COUNT(*) FROM ".TB_PREFIX."medal WHERE userid = cur.userid AND categorie = {$s['base']} AND week > $week - 5) = 5 THEN '{$s['imgs'][1]}'
                                    WHEN (SELECT COUNT(*) FROM ".TB_PREFIX."medal WHERE userid = cur.userid AND categorie = {$s['base']} AND week > $week - 3) = 3 THEN '{$s['imgs'][0]}'
                                    ELSE NULL
                                END as medal_img,
                                CASE 
                                    WHEN (SELECT COUNT(*) FROM ".TB_PREFIX."medal WHERE userid = cur.userid AND categorie = {$s['base']} AND week > $week - 10) = 10 THEN 'ten times '
                                    WHEN (SELECT COUNT(*) FROM ".TB_PREFIX."medal WHERE userid = cur.userid AND categorie = {$s['base']} AND week > $week - 5) = 5 THEN 'five times '
                                    WHEN (SELECT COUNT(*) FROM ".TB_PREFIX."medal WHERE userid = cur.userid AND categorie = {$s['base']} AND week > $week - 3) = 3 THEN 'three times '
                                    ELSE NULL
                                END as medal_text
                        FROM ".TB_PREFIX."medal cur
                        WHERE cur.week = $week AND cur.categorie = {$s['base']} AND cur.plaats <= 10
                    ) res
                    WHERE res.medal_img IS NOT NULL";
                
                mysqli_query($db, $q);
            }

            // --- 3. ATK + DEF COMBO (CONSECUTIVO) ---
            // Regra: Top 10 em ambos na mesma semana, 1, 2 ou 3 vezes seguidas
            $qAtkDef = "INSERT INTO ".TB_PREFIX."medal (userid, categorie, plaats, week, points, img)
                SELECT u.id, 5, 0, $week, 
                -- Texto baseado na quantidade que ele JÁ TINHA (Histórico)
                CASE 
                    WHEN (SELECT COUNT(*) FROM ".TB_PREFIX."medal old WHERE old.userid = u.id AND old.categorie = 5) = 1 THEN 'twice '
                    WHEN (SELECT COUNT(*) FROM ".TB_PREFIX."medal old WHERE old.userid = u.id AND old.categorie = 5) >= 2 THEN 'three times '
                    ELSE '' 
                END as medal_text,
                -- Imagem t220, t221 ou t222
                CONCAT('t22', 
                    LEAST((SELECT COUNT(*) FROM ".TB_PREFIX."medal old WHERE old.userid = u.id AND old.categorie = 5), 2), 
                '_1') as medal_img
                FROM ".TB_PREFIX."users u
                WHERE u.id IN (
                    SELECT id FROM (SELECT id FROM ".TB_PREFIX."users WHERE ap > 0 ORDER BY ap DESC, id DESC LIMIT 10) as t1
                )
                AND u.id IN (
                    SELECT id FROM (SELECT id FROM ".TB_PREFIX."users WHERE dp > 0 ORDER BY dp DESC, id DESC LIMIT 10) as t2
                )
                AND (SELECT COUNT(*) FROM ".TB_PREFIX."medal old WHERE old.userid = u.id AND old.categorie = 5) <= 2";

            mysqli_query($db, $qAtkDef);


            // --- MEDALHAS DE ALIANÇAS ---
            $allyCats = [
                1 => ['col' => 'ap', 'img' => 'a2_'],
                2 => ['col' => 'dp', 'img' => 'a3_'],
                3 => ['col' => 'clp', 'img' => 'a1_'],
                4 => ['col' => 'RR', 'img' => 'a4_']
            ];

            foreach ($allyCats as $catId => $data) {
                mysqli_query($db, "SET @rank := 0");
                $q = "INSERT INTO ".TB_PREFIX."allimedal (allyid, categorie, plaats, week, points, img)
                  SELECT final.id, $catId, (@rank:=@rank+1), $allyweek, final.pts, CONCAT('{$data['img']}', (@rank))
                  FROM (
                      SELECT id, {$data['col']} as pts
                      FROM ".TB_PREFIX."alidata
                      WHERE {$data['col']} > 0
                      ORDER BY {$data['col']} DESC, id DESC 
                      LIMIT 10
                  ) as final";
                mysqli_query($db, $q);
            }

            // --- MEDALHA DE BÔNUS ALIANÇA (TOP 10 ATK + DEF SIMULTÂNEOS) ---
            // Mesma lógica: Verifica quem está no Top 10 de ataque E defesa na semana atual
            $qAllyCombo = "INSERT INTO ".TB_PREFIX."allimedal (allyid, categorie, plaats, week, points, img)
                SELECT a.id, 5, 0, $allyweek, 
                CASE 
                    WHEN (SELECT COUNT(*) FROM ".TB_PREFIX."allimedal old WHERE old.allyid = a.id AND old.categorie = 5) = 1 THEN 'twice '
                    WHEN (SELECT COUNT(*) FROM ".TB_PREFIX."allimedal old WHERE old.allyid = a.id AND old.categorie = 5) >= 2 THEN 'three times '
                    ELSE '' 
                END,
                -- O LEAST garante que se o count for 3, 4 ou 50, ele use a imagem 't222_1' (a última disponível)
                CONCAT('t22', LEAST((SELECT COUNT(*) FROM ".TB_PREFIX."allimedal old WHERE old.allyid = a.id AND old.categorie = 5), 2), '_1')
                FROM ".TB_PREFIX."alidata a
                WHERE a.id IN (SELECT id FROM (SELECT id FROM ".TB_PREFIX."alidata WHERE ap > 0 ORDER BY ap DESC LIMIT 10) as t1)
                AND a.id IN (SELECT id FROM (SELECT id FROM ".TB_PREFIX."alidata WHERE dp > 0 ORDER BY dp DESC LIMIT 10) as t2)
                -- Trava de segurança: só ganha se ainda não tiver as 3 cores de fita (ou remova se quiser repetir a fita 3 pra sempre)
                AND (SELECT COUNT(*) FROM ".TB_PREFIX."allimedal old WHERE old.allyid = a.id AND old.categorie = 5) <= 2";

            mysqli_query($db, $qAllyCombo);

            // --- PONTO 3: FINALIZAÇÃO E RESET ---     
            $this->injectMedalsToProfile($week, $allyweek);

            // Atualiza lastgavemedal somando o intervalo ao tempo que deveria ter ocorrido (evita atrasos acumulados)
            $nextTime = $row['lastgavemedal'] + MEDALINTERVAL;
            mysqli_query($db, "UPDATE ".TB_PREFIX."config SET lastgavemedal = $nextTime");

            // Reset de pontos e "foto" da população (oldpop) para a próxima semana
            mysqli_query($db, "UPDATE ".TB_PREFIX."users u 
                               LEFT JOIN temp_pop tp ON u.id = tp.userid
                               SET u.oldpop = IFNULL(tp.current_pop, 0),
                                   u.ap=0, u.dp=0, u.clp=0, u.RR=0 
                               WHERE u.id > 5");
            mysqli_query($db, "UPDATE ".TB_PREFIX."alidata SET ap=0, dp=0, clp=0, RR=0");
            mysqli_query($db, "DROP TEMPORARY TABLE IF EXISTS temp_pop");

            mysqli_commit($db);

        } catch (Exception $e) {
            error_log("AutomationT3.6: [medalsNew] Error: " . $e->getMessage());
            mysqli_rollback($db);
        }
    
    }

    public function injectMedalsToProfile($week, $allyWeek) {
        global $database;
        $db = $database->dblink;

        $enabled = true; 
        if (!$enabled) return;

        // 1. PROCESSAR JOGADORES
        // Selecionamos medalhas da semana. 
        // Filtro: (Categorias de bônus > 4) OU (Categorias normais com lugar <= 3)
        $qUser = "SELECT id, userid, categorie, plaats FROM ".TB_PREFIX."medal 
                WHERE week = ".(int)$week." 
                AND (categorie > 4 OR (categorie IN (1,2,3,4,10) AND plaats <= 3))";
        
        $resUser = mysqli_query($db, $qUser);

        if ($resUser) {
            while ($m = mysqli_fetch_assoc($resUser)) {
                $bbCode = " [#".$m['id']."] "; 
                
                // Destino: Bônus (Cat > 4) vai para desc1 | Top 1-3 (Cat 1-10) vai para desc2
                // Note: Categoria 10 é Rank Climber, tratamos como ranking normal
                $column = (in_array($m['categorie'], [1, 2, 3, 4, 10])) ? "desc2" : "desc1";

                $updateQ = "UPDATE ".TB_PREFIX."users 
                        SET $column = CONCAT(TRIM(COALESCE($column, '')), ' $bbCode') 
                        WHERE id = ".(int)$m['userid'];
                mysqli_query($db, $updateQ);
            }
        }

        // 2. PROCESSAR ALIANÇAS
        // Filtro similar: Categoria 5 (Bonus) OU (Categorias 1-4 com lugar <= 3)
        $qAlli = "SELECT id, allyid, categorie, plaats FROM ".TB_PREFIX."allimedal 
                WHERE week = ".(int)$allyWeek."
                AND (categorie = 5 OR (categorie IN (1,2,3,4) AND plaats <= 3))";
        
        $resAlli = mysqli_query($db, $qAlli);

        if ($resAlli) {
            while ($am = mysqli_fetch_assoc($resAlli)) {
                $bbCode = " [#".$am['id']."] ";

                // Categoria 5 -> notice | Top 1-3 -> desc
                $column = ($am['categorie'] == 5) ? "notice" : "desc";

                $updateAQ = "UPDATE ".TB_PREFIX."alidata 
                        SET ".TB_PREFIX."alidata." . $column . " = CONCAT(TRIM(COALESCE(".TB_PREFIX."alidata." . $column . ", '')), ' $bbCode') 
                        WHERE id = ".(int)$am['allyid'];
                mysqli_query($db, $updateAQ);
            }
        }
    }

    function woundedDecay(){
        global $database;
        $database->woundedDecay();
    }

    /**
     * starvationNew: Processa apenas aldeias sinalizadas com fome.
     * Foca em eficiência cirúrgica: identifica, calcula e carimba o tempo.
     */
    private function starvationNew() {
        global $database;
        if (defined('PEACE') && PEACE) return;

        if (!$database->needRunStarvation()) {
            return; 
        }

        $winnerSql = mysqli_query($database->dblink,"SELECT vref FROM ".TB_PREFIX."fdata WHERE f99 = '100' and f99t = '40'");
		$winner = mysqli_num_rows($winnerSql);
		if($winner > 0) return;

        // 1. Filtro Otimizado: Só pegamos vilas onde a fome foi SINALIZADA (starv > 0)
        // ou onde o starvupdate já expirou o tempo de carência (ex: 6 horas).
        $intervaloRecalculo = 3600 * 6; // 6 horas
        $time = time();
        
        $sql = "SELECT v.wref, v.owner, v.pop, v.starv AS old_starv, v.starvupdate, u.b4, u.tribe 
                FROM ".TB_PREFIX."vdata v FORCE INDEX (idx_owner_wref)
                JOIN ".TB_PREFIX."users u ON v.owner = u.id 
                WHERE v.owner > 5 
                AND (v.starv > 0 AND (v.starvupdate = 0 OR v.starvupdate <= ($time - $intervaloRecalculo)))
                ORDER BY v.wref ASC";

        $villageResult = $database->query($sql);
        if (mysqli_num_rows($villageResult) == 0) return;

        $starvation_updates = [];

        $relevant_wrefs = [];
        $relevant_owner = [];
        $villages = [];
        while ($row = mysqli_fetch_assoc($villageResult)) {
            $relevant_wrefs[] = $row['wref'];
            $relevant_owner[] = $row['owner'];
            $villages[] = $row;
        }
        $wref_list = implode(',', $relevant_wrefs);

        $unitsResult = $database->query("SELECT * FROM ".TB_PREFIX."units WHERE vref IN ($wref_list) ORDER BY vref ASC");
		$reinforcementsResult = $database->query("SELECT * FROM ".TB_PREFIX."enforcement WHERE vref IN ($wref_list) ORDER BY vref ASC");
		$prisonersResult = $database->query("SELECT * FROM ".TB_PREFIX."prisoners WHERE wref IN ($wref_list) ORDER BY wref ASC");
		$outgoingResult = $database->query("SELECT m.from AS home_village_wref, m.ref, m.moveid, a.* FROM ".TB_PREFIX."movement m JOIN ".TB_PREFIX."attacks a ON m.ref = a.id WHERE m.proc = 0 AND m.sort_type = 3 AND m.from IN ($wref_list) ORDER BY home_village_wref ASC");
		$returningResult = $database->query("SELECT m.to AS home_village_wref, m.ref, m.moveid, a.* FROM ".TB_PREFIX."movement m JOIN ".TB_PREFIX."attacks a ON m.ref = a.id WHERE m.proc = 0 AND m.sort_type = 4 AND m.to IN ($wref_list) ORDER BY home_village_wref ASC");
		$settlersResult = $database->query("SELECT m.from AS home_village_wref, m.ref as id, m.moveid FROM ".TB_PREFIX."movement m WHERE m.proc = 0 AND m.sort_type = 5 AND m.from IN ($wref_list) ORDER BY home_village_wref ASC");

        // Mapas de referência rápidos que são pequenos o suficiente.
		$tribeOfOwner = [];
        $owner_list = implode(',', array_unique($relevant_owner));

		$result_tribe_map = $database->query("SELECT id, tribe FROM ".TB_PREFIX."users WHERE id IN ($owner_list)");
		while($row = mysqli_fetch_assoc($result_tribe_map)) { $tribeOfOwner[$row['id']] = $row['tribe']; }
		mysqli_free_result($result_tribe_map);

		$result_owner_map = $database->query_return("SELECT wref, owner FROM ".TB_PREFIX."vdata WHERE owner > 5 and wref IN ($wref_list)");
		$ownerOfVillage = array_column($result_owner_map, 'owner', 'wref');


        $currentUnit = mysqli_fetch_assoc($unitsResult);
		$currentReinforcement = mysqli_fetch_assoc($reinforcementsResult);
		$currentPrisoner = mysqli_fetch_assoc($prisonersResult);
		$currentOutgoing = mysqli_fetch_assoc($outgoingResult);
		$currentReturning = mysqli_fetch_assoc($returningResult);
		$currentSettler = mysqli_fetch_assoc($settlersResult);

        foreach ($villages as $village) {
            $wref = $village['wref'];
            $fome_valor_absoluto = $village['old_starv']; 

            $village_troops = [
                'units' => [], 
                'enforcements' => [], 
                'prisoners' => [], 
                'movements' => []
            ];

            // a) Tropas em casa
            while ($currentUnit && $currentUnit['vref'] < $wref) { $currentUnit = mysqli_fetch_assoc($unitsResult); }
            while ($currentUnit && $currentUnit['vref'] == $wref) {
                $currentUnit['true_owner_id'] = $village['owner'];
                $village_troops['units'][] = $currentUnit;
                $currentUnit = mysqli_fetch_assoc($unitsResult);
            }

            // b) Reforços NA aldeia
            while ($currentReinforcement && $currentReinforcement['vref'] < $wref) { $currentReinforcement = mysqli_fetch_assoc($reinforcementsResult); }
            while ($currentReinforcement && $currentReinforcement['vref'] == $wref) {
                $owner_of_troops = $ownerOfVillage[$currentReinforcement['from']] ?? 0;
                $currentReinforcement['true_owner_id'] = $owner_of_troops;
                $village_troops['enforcements'][] = $currentReinforcement;
                $currentReinforcement = mysqli_fetch_assoc($reinforcementsResult);
            }

            // c) Prisioneiros NA aldeia
            while ($currentPrisoner && $currentPrisoner['wref'] < $wref) { $currentPrisoner = mysqli_fetch_assoc($prisonersResult); }
            while ($currentPrisoner && $currentPrisoner['wref'] == $wref) {
                $owner_of_troops = $ownerOfVillage[$currentPrisoner['from']] ?? 0;
                $currentPrisoner['true_owner_id'] = $owner_of_troops;
                $village_troops['prisoners'][] = $currentPrisoner;
                $currentPrisoner = mysqli_fetch_assoc($prisonersResult);
            }

            while ($currentOutgoing && $currentOutgoing['home_village_wref'] < $wref) { $currentOutgoing = mysqli_fetch_assoc($outgoingResult); }
			while ($currentOutgoing && $currentOutgoing['home_village_wref'] == $wref) {
				$currentOutgoing['true_owner_id'] = $village['owner'];
				$village_troops['movements'][] = $currentOutgoing;
				$currentOutgoing = mysqli_fetch_assoc($outgoingResult);
			}

			while ($currentReturning && $currentReturning['home_village_wref'] < $wref) { $currentReturning = mysqli_fetch_assoc($returningResult); }
			while ($currentReturning && $currentReturning['home_village_wref'] == $wref) {
				$currentReturning['true_owner_id'] = $village['owner'];
				$village_troops['movements'][] = $currentReturning;
				$currentReturning = mysqli_fetch_assoc($returningResult);
			}

			while ($currentSettler && $currentSettler['home_village_wref'] < $wref) { $currentSettler = mysqli_fetch_assoc($settlersResult); }
			while ($currentSettler && $currentSettler['home_village_wref'] == $wref) {
				$currentSettler['true_owner_id'] = $village['owner'];
				$village_troops['movements'][] = $currentSettler;
				$currentSettler = mysqli_fetch_assoc($settlersResult);
			}

            // Executa a limpeza (essa função precisa ser adaptada para usar o valor pronto)
            $this->execute_starvation_for_villageNew($village, $fome_valor_absoluto, $village_troops, $tribeOfOwner);

            // Atualiza o carimbo de tempo
            $starvation_updates[$wref] = [
                'starv' => $fome_valor_absoluto, // Mantém o valor do déficit
                'starvupdate' => $time          // Atualiza a "última foto" para agora
            ];
        }

        if (!empty($starvation_updates)) {
			// Inicia a transação para segurança e performance
			$database->dblink->autocommit(FALSE); 
			
			// Divide o array em pedaços (chunks) de 500 para evitar queries gigantescas
			$chunks = array_chunk($starvation_updates, 500, true); 
			
			foreach ($chunks as $chunk) {
				$whens_starv = "";
				$whens_starvupdate = "";
				$wref_ids = [];

				foreach ($chunk as $wref => $values) {
					$wref_ids[] = $wref;
					$whens_starv .= "WHEN " . (int)$wref . " THEN " . (int)$values['starv'] . " ";
					$whens_starvupdate .= "WHEN " . (int)$wref . " THEN " . (int)$values['starvupdate'] . " ";
				}

				$id_list = implode(', ', $wref_ids);

				// Constrói a query de UPDATE usando CASE WHEN
				$update_query = "UPDATE `" . TB_PREFIX . "vdata` SET " .
								"`starv` = CASE `wref` " . $whens_starv . "ELSE `starv` END, " .
								"`starvupdate` = CASE `wref` " . $whens_starvupdate . "ELSE `starvupdate` END " .
								"WHERE `wref` IN (" . $id_list . ")";
				
				$database->query($update_query);
			}

			$database->dblink->commit(); // Confirma todas as atualizações
			$database->dblink->autocommit(TRUE); // Restaura o modo autocommit
		}

		// Libera a memória de todos os ponteiros de resultado do banco de dados.
		mysqli_free_result($villageResult);
		mysqli_free_result($unitsResult);
		mysqli_free_result($reinforcementsResult);
		mysqli_free_result($prisonersResult);
		mysqli_free_result($outgoingResult);
		mysqli_free_result($returningResult);
		mysqli_free_result($settlersResult);
    }

    /**
	 * Contém a lógica de matar tropas para UMA ÚNICA aldeia faminta.
	 * Este método é chamado pela função principal 'starvation' apenas quando necessário.
	 *
	 * @param array $starv_data         Os dados da aldeia faminta (da nossa lista principal).
	 * @param int   $starv_absolute     O consumo final de cereal já calculado (+).
	 * @param array $village_troops     Array com os grupos de tropas da aldeia, já coletados e padronizados.
	 * @param array $tribeOfOwner       Mapa de referência [owner_id] => tribe_id.
	 */
	private function execute_starvation_for_villageNew($starv_data, $starv_absolute, $village_troops, $tribeOfOwner) {
		global $database;

		$wref = $starv_data['wref'];
		$time = time();
		$starvingTroops = [];
		$type = -1; // -1: nenhum grupo de tropas; 0: reforço; 1: prisioneiro; 2: tropa própria; 3: movimento.


		// Acha o primeiro grupo de tropas a morrer, na ordem de prioridade correta.
		if (!empty($village_troops['enforcements'])) { $starvingTroops = reset($village_troops['enforcements']); $type = 0; }
		else if (!empty($village_troops['prisoners'])) { $starvingTroops = reset($village_troops['prisoners']); $type = 1; }
		else if (!empty($village_troops['units'])) { $starvingTroops = $village_troops['units'][0]; $type = 2; }
		else if (!empty($village_troops['movements'])) { $starvingTroops = reset($village_troops['movements']); $type = 3; }
		
		// Se, por algum motivo, não houver tropas para matar (ex: só consumo da população), encerra.
		if ($type === -1 || empty($starvingTroops)) return;

		$lastUpdate = ($starv_data['starvupdate'] > 0) ? $starv_data['starvupdate'] : ($time - 1);
        $timedif = $time - $lastUpdate;

		$starvsec = ($starv_absolute / 3600);
        $difcrop = ($timedif * $starvsec);

        $oldcrop = $database->getVillageField($wref, 'crop');

        if ($oldcrop > 0) {
            $consumable_crop = min($oldcrop, $difcrop);
            $difcrop -= $consumable_crop;
            // Atualiza o cereal na vila (reduzindo o que foi consumido pela fome)
            $database->modifyResource($wref, 0, 0, 0, ($consumable_crop * -1), 0);
        }

		if ($difcrop > 0) {

            $killedUnits = [];
            $owner_of_troops = $starvingTroops['true_owner_id'];
            $tribe = $tribeOfOwner[$owner_of_troops];
            
            $is_t_column = in_array($type, [1, 3]); // Movimentos e Prisioneiros usam colunas 'tX'
            $prefix = $is_t_column ? 't' : 'u';
            $hero_column = $is_t_column ? 't11' : 'hero';
            $start_index = ($tribe - 1) * 10 + 1;
            
            // Loop para matar tropas até o déficit de cereal ser coberto
            while ($difcrop > 0) {
                $maxcount = 0;
                $maxtype_global_id = 0;
                
                for ($i = $start_index; $i < $start_index + 10; $i++) {
                    // Para tropas 'tX', o índice é 1-10, então precisamos converter
                    $key_to_check = $is_t_column ? $prefix . ($i - $start_index + 1) : $prefix . $i;

                    if (($starvingTroops[$key_to_check] ?? 0) > $maxcount) {
                        $maxcount = $starvingTroops[$key_to_check];
                        $maxtype_global_id = $i;
                    }
                }

                if ($maxcount > 0) {
                    $local_index = $maxtype_global_id - $start_index + 1;
                    $key_to_decrement = $is_t_column ? $prefix . $local_index : $prefix . $maxtype_global_id;

                    $starvingTroops[$key_to_decrement]--;
                    $killedUnits[$local_index] = ($killedUnits[$local_index] ?? 0) + 1;
                    $difcrop -= $GLOBALS['u'.$maxtype_global_id]['pop'];
                } else {
                    if (isset($starvingTroops[$hero_column]) && $starvingTroops[$hero_column] > 0) {
                        $hero_info = $database->getHero($owner_of_troops)[0];
                        if($hero_info) {
                            $database->modifyHero("dead", 1, $hero_info['heroid']);
                            $database->modifyHero("health", 0, $hero_info['heroid']);
                            $killedUnits[11] = ($killedUnits[11] ?? 0) + 1;
                            $starvingTroops[$hero_column] = 0;
                        }
                    }
                    break;
                }
            }

            // Se alguma tropa foi morta, atualiza o banco de dados
            if (!empty($killedUnits)) {
                $id_to_modify = ($type == 2) ? $wref : $starvingTroops['id'];
                $moveid_to_modify = $starvingTroops['moveid'] ?? 0;
                
                switch ($type) {
                    case 0: $database->modifyEnforce($id_to_modify, array_keys($killedUnits), array_values($killedUnits), 0); break;
                    case 1: 
                        $database->modifyPrisoners($id_to_modify, array_keys($killedUnits), array_values($killedUnits), 0);
                        $database->modifyUnit($wref, ["99o"], [array_sum($killedUnits)], [0]);
                        break;
                    case 2:
                        $keys_to_update = [];
                        foreach (array_keys($killedUnits) as $idx) { $keys_to_update[] = ($idx == 11) ? 'hero' : 'u'.($start_index + $idx - 1); }
                        $database->modifyUnit($id_to_modify, $keys_to_update, array_values($killedUnits), array_fill(0, count($killedUnits), 0));
                        break;
                    case 3: 
                        // O 'id' aqui refere-se ao ID da tabela 'attacks'.
                        $database->modifyAttack2($id_to_modify, array_keys($killedUnits), array_values($killedUnits), 0);
                        break;
                }
            }
        }
	}
}
$automation = new Automation;

// remove automation lock file
//@unlink( AUTOMATION_LOCK_FILE_NAME );
?>
