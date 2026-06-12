<?php

use App\Utils\Math;

trait DBMovement {

    function setMovementProc($moveid) {
        if (!Math::isInt($moveid)) {
            list($moveid) = $this->escape_input($moveid);
        }

        if(empty($moveid)) return;

        // rather than re-selecting data and updating cache here, let's just
        // flush the cache and let it re-cach itself as neccessary
        self::$marketMovementCache = [];

		$q = "UPDATE " . TB_PREFIX . "movement set proc = 1 where moveid IN($moveid)";
		return mysqli_query($this->dblink,$q);
	}

	function getFinishedMovements($type){ // 0 Ataques 1 Reforços 2 Retornos
		$time = time();
		switch($type) {
			case 0: //ATAQUES
					$q = "
				SELECT
					`from`, `to`, endtime, starttime, ref, ctar1, ctar2, spy, moveid, attack_type,
					t1, t2, t3, t4, t5, t6, t7, t8, t9, t10, t11, (SELECT oasistype FROM ".TB_PREFIX."wdata WHERE id = `to`) as oasistype
				FROM
					".TB_PREFIX."movement,
					".TB_PREFIX."attacks
				WHERE
					".TB_PREFIX."movement.ref = ".TB_PREFIX."attacks.id
					AND
					".TB_PREFIX."movement.proc = 0
					AND
					".TB_PREFIX."movement.sort_type = 3
					AND
					".TB_PREFIX."attacks.attack_type != 2
					AND
					endtime < $time
				ORDER BY endtime ASC";
				break;
					
			case 1: //REFORÇOS
					$q = "
				SELECT
					`to`, `from`, moveid, endtime,
					t1, t2, t3, t4, t5, t6, t7, t8, t9, t10, t11
				FROM
					".TB_PREFIX."movement,
					".TB_PREFIX."attacks 
				WHERE
					".TB_PREFIX."movement.ref = ".TB_PREFIX."attacks.id
					AND
					".TB_PREFIX."movement.proc = 0
					AND
					".TB_PREFIX."movement.sort_type = 3
					AND
					".TB_PREFIX."attacks.attack_type = 2
					AND
					endtime < $time";
				break;
			
			case 2: //RETORNOS
					$q = "
				SELECT
					`to`, `from`, moveid, starttime, endtime, wood, clay, iron, crop,
					t1, t2, t3, t4, t5, t6, t7, t8, t9, t10, t11
				FROM
					".TB_PREFIX."movement,
					".TB_PREFIX."attacks
				WHERE
					".TB_PREFIX."movement.ref = ".TB_PREFIX."attacks.id
					AND
					".TB_PREFIX."movement.proc = 0
					AND
					".TB_PREFIX."movement.sort_type = 4
					AND
					endtime < $time";
				break;
			case 3: //SETTLERS CANCELADOS
					$q = "
				SELECT 
					`to`, moveid 
				FROM 
					".TB_PREFIX."movement
				WHERE
					ref = 0 
					AND 
					proc = '0'
					AND 
					sort_type = '4'
					AND 
					endtime < $time";
				break;
		}
	
		$result = $this->mysqli_fetch_all(mysqli_query($this->dblink,$q));
		return $result;
	}

	function getAllFinishedMovements() {
		$time = time();

		$q = "SELECT
				m.from, m.to, m.endtime, m.starttime, m.ref, m.moveid,
				m.wood, m.clay, m.iron, m.crop,
				a.ctar1, a.ctar2, a.spy, a.attack_type,
				a.t1, a.t2, a.t3, a.t4, a.t5, a.t6, a.t7, a.t8, a.t9, a.t10, a.t11,
				(SELECT oasistype FROM ".TB_PREFIX."wdata WHERE id = m.to) as oasistype,
				CASE
					WHEN m.sort_type = 3 AND a.attack_type = 2 THEN 1
					WHEN m.sort_type = 3 AND a.attack_type != 2 THEN 0
					WHEN m.sort_type = 4 THEN 2
				END as movement_type
			FROM ".TB_PREFIX."movement m
			JOIN ".TB_PREFIX."attacks a ON m.ref = a.id
			WHERE m.proc = 0
			  AND m.sort_type IN (3, 4)
			  AND m.endtime < $time
			ORDER BY m.endtime ASC,
				CASE
					WHEN m.sort_type = 3 AND a.attack_type = 2 THEN 1
					WHEN m.sort_type = 4 THEN 2
					WHEN m.sort_type = 3 AND a.attack_type != 2 THEN 3
				END ASC";

		$result = $this->query_return($q);

		$q2 = "SELECT `to`, moveid
			   FROM ".TB_PREFIX."movement
			   WHERE ref = 0 AND proc = 0 AND sort_type = 4 AND endtime < $time";

		$settlers = $this->query_return($q2);

		return ['movements' => $result, 'settlers' => $settlers];
	}

    public function hasFutureAttacks() {
        $time = time();
        //Verifica quantos ataques atualmente existem no servidor partindo de vilas ativas na última hora

        $q = "SELECT COUNT(moveid) 
            FROM    ".TB_PREFIX."movement,
					".TB_PREFIX."attacks
                    JOIN ".TB_PREFIX."vdata ON ".TB_PREFIX."vdata.wref = ".TB_PREFIX."attacks.vref
				WHERE
					".TB_PREFIX."movement.ref = ".TB_PREFIX."attacks.id
					AND
					".TB_PREFIX."movement.proc = 0
					AND
					".TB_PREFIX."movement.sort_type = 3
					AND
					".TB_PREFIX."attacks.attack_type != 2
                    AND
					vdata.lastupdate > (" . $time . " - 3600)
					AND
					endtime > " . $time . "
                    LIMIT 1";
        $result = mysqli_query($this->dblink, $q);
        $row = mysqli_fetch_row($result);
        return ($row[0] > 0);
    }

	function getMovement($type, $village, $mode, $use_cache = true) {
        $array_passed = is_array($village);

        if (!$array_passed) {
            $village = [(int) $village];
        } else {
            foreach ($village as $index => $villageValue) {
                $village[$index] = (int) $villageValue;
            }
        }

        if (!count($village)) {
            return [];
        }

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && !$array_passed && isset(self::$marketMovementCache[$type.$village[0].$mode]) && is_array(self::$marketMovementCache[$type.$village[0].$mode]) && !count(self::$marketMovementCache[$type.$village[0].$mode])) {
            return self::$marketMovementCache[$type.$village[0].$mode];
        } else if ($use_cache && $array_passed) {
            // check what we can return from cache
            $newIDs = [];
            foreach ($village as $key) {
                if (!isset(self::$marketMovementCache[$type.$key.$mode])) {
                    $newIDs [] = $key;
                }
            }

            // everything's cached, just return the cache
            if (!count($newIDs)) {
                return self::$marketMovementCache;
            } else {
                // update remaining IDs to select and cache
                $village = $newIDs;
            }
        } else if ($use_cache && !$array_passed && ($cachedValue = self::returnCachedContent(self::$marketMovementCache, $type.$village[0].$mode)) && !is_null($cachedValue)) {
            // special case when we have empty arrays cached for this cache only
            return ($array_passed ? self::$marketMovementCache: $cachedValue);
        }

		$time = time();
		if(!$mode) {
			$where = "from";
		} else {
			$where = "to";
		}
		switch($type) {
			case 0:
				$q = "SELECT * FROM " . TB_PREFIX . "movement, " . TB_PREFIX . "send where " . TB_PREFIX . "movement.`" . $where . "` IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "send.id and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 0 ORDER BY endtime ASC";
				break;
			case 1:
				$q = "SELECT * FROM " . TB_PREFIX . "movement, " . TB_PREFIX . "send where " . TB_PREFIX . "movement.`" . $where . "` IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "send.id and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 6 ORDER BY endtime ASC";
				break;
			case 2:
				$q = "SELECT * FROM " . TB_PREFIX . "movement where " . TB_PREFIX . "movement.`" . $where . "` IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 2 ORDER BY endtime ASC";
				break;
			case 3:
				$q = "SELECT * FROM " . TB_PREFIX . "movement, " . TB_PREFIX . "attacks where " . TB_PREFIX . "movement.`" . $where . "` IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "attacks.id and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 3 ORDER BY endtime ASC";
				break;
			case 4:
				$q = "SELECT * FROM " . TB_PREFIX . "movement, " . TB_PREFIX . "attacks where " . TB_PREFIX . "movement.`" . $where . "` IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "attacks.id and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 4 ORDER BY endtime ASC";
				break;
			case 5:
				$q = "SELECT * FROM " . TB_PREFIX . "movement where " . TB_PREFIX . "movement.`" . $where . "` IN(".implode(', ', $village).") and sort_type = 5 and proc = 0 ORDER BY endtime ASC";
				break;
			case 6:
				$q = "SELECT * FROM " . TB_PREFIX . "movement," . TB_PREFIX . "odata, " . TB_PREFIX . "attacks where " . TB_PREFIX . "odata.wref IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.to IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "attacks.id and " . TB_PREFIX . "attacks.attack_type != 1 and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 3 ORDER BY endtime ASC";
				//$q = "SELECT * FROM " . TB_PREFIX . "movement," . TB_PREFIX . "odata, " . TB_PREFIX . "attacks where " . TB_PREFIX . "odata.conqured IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.to = " . TB_PREFIX . "odata.wref and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "attacks.id and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 3 ORDER BY endtime ASC";
				break;
			case 7:
				$q = "SELECT * FROM " . TB_PREFIX . "movement where " . TB_PREFIX . "movement.`" . $where . "` IN(".implode(', ', $village).") and sort_type = 4 and ref = 0 and proc = 0 ORDER BY endtime ASC";
				break;
			case 8:
				$q = "SELECT * FROM " . TB_PREFIX . "movement, " . TB_PREFIX . "attacks where " . TB_PREFIX . "movement.`" . $where . "` IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "attacks.id and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 3 and " . TB_PREFIX . "attacks.attack_type = 1 ORDER BY endtime ASC";
				break;
			case 34:
				$q = "SELECT * FROM " . TB_PREFIX . "movement, " . TB_PREFIX . "attacks where " . TB_PREFIX . "movement.`" . $where . "` IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "attacks.id and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 3 or " . TB_PREFIX . "movement.`" . $where . "` IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "attacks.id and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 4 ORDER BY endtime ASC";
				break;
            case 35:
                // Query otimizada para a verificação de evasão.
                // Retorna 1 se encontrar um movimento de retorno chegando em breve, senão não retorna nada.
                $q = "SELECT 1 FROM " . TB_PREFIX . "movement, " . TB_PREFIX . "attacks 
                    WHERE " . TB_PREFIX . "movement.`" . $where . "` IN(".implode(', ', $village).") 
                    and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "attacks.id 
                    and " . TB_PREFIX . "movement.proc = 0 
                    and " . TB_PREFIX . "movement.sort_type = 4
                    AND endtime <= " . (time() + 10) . " 
                    LIMIT 1";
                break;
			default:
				return [];
		}

		$result = $this->mysqli_fetch_all(mysqli_query($this->dblink,$q));

        // return a single value
        if (!$array_passed) {
            self::$marketMovementCache[$type.$village[0].$mode] = $result;
        } else {
            if ($result && count($result)) {
                foreach ( $result as $record ) {
                    self::$marketMovementCache[ $type . $record[ $where ] . $mode ][] = $record;
                }
            }

            // check for any missing IDs and fill them in with blanks,
            // since no movements were found for these villages
            foreach ($village as $key) {
                if (!isset(self::$marketMovementCache[$type.$key.$mode])) {
                    self::$marketMovementCache[$type.$key.$mode] = [];
                }
            }
        }

        return ($array_passed ? self::$marketMovementCache : self::$marketMovementCache[$type.$village[0].$mode]);
	}
	
	function getUnitonRoute($village, $use_cache = true){
		$array_passed = is_array($village);
		if (!$array_passed) {
            $village = [(int) $village];
        } else {
            foreach ($village as $index => $villageValue) {
                $village[$index] = (int) $villageValue;
            }
        }
		
		$q = "SELECT SUM(t1) as t1, SUM(t2) as t2, SUM(t3) as t3, SUM(t4) as t4, SUM(t5) as t5, SUM(t6) as t6  , SUM(t7) as t7  , SUM(t8) as t8  , SUM(t9) as t9  , SUM(t10) as t10  , SUM(t11) as t11  
		FROM " . TB_PREFIX . "movement, " . TB_PREFIX . "attacks 
		where (" . TB_PREFIX . "movement.from IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "attacks.id and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 3)
		OR (" . TB_PREFIX . "movement.to IN(".implode(', ', $village).") and " . TB_PREFIX . "movement.ref = " . TB_PREFIX . "attacks.id and " . TB_PREFIX . "movement.proc = 0 and " . TB_PREFIX . "movement.sort_type = 4)";
			
        $result = mysqli_query($this->dblink,$q);
        $dbarray = mysqli_fetch_array($result);
        return $dbarray;
		
	}
	
	function getUnitonVacation($village, $tribe, $use_cache = true) {
        // 1. Sanitiza e formata a entrada $village
        $array_passed = is_array($village);
        if (!$array_passed) {
            $village_ids = [(int) $village];
        } else {
            $village_ids = [];
            foreach ($village as $villageValue) {
                $village_ids[] = (int) $villageValue;
            }
        }

        // Filtra IDs inválidos (<= 0) e duplicados
        $village_ids = array_filter(array_unique($village_ids), function($id) { return $id > 0; });

        // Define um array vazio padrão para retorno em caso de erro ou nenhuma vila
        $default_return = []; // Será preenchido com as chaves corretas abaixo

        if (empty($village_ids)) {
            // Preenche o default_return com zeros antes de retornar
            $tribe = (int) $tribe;
            switch ($tribe) {
                case 1: $keys = array_merge(array_map(fn($i) => 'u'.$i, range(1, 10)), ['hero']); break;
                case 2: $keys = array_merge(array_map(fn($i) => 'u'.$i, range(11, 20)), ['hero']); break;
                case 3: $keys = array_merge(array_map(fn($i) => 'u'.$i, range(21, 30)), ['hero']); break;
                case 4: $keys = array_merge(array_map(fn($i) => 'u'.$i, range(31, 40)), ['hero']); break;
                case 5: $keys = array_merge(array_map(fn($i) => 'u'.$i, range(41, 50)), ['hero']); break;
                case 6: $keys = array_merge(array_map(fn($i) => 'u'.$i, range(51, 60)), ['hero']); break;
                case 7: $keys = array_merge(array_map(fn($i) => 'u'.$i, range(61, 70)), ['hero']); break;
                case 8: $keys = array_merge(array_map(fn($i) => 'u'.$i, range(71, 80)), ['hero']); break;
                case 9: $keys = array_merge(array_map(fn($i) => 'u'.$i, range(81, 90)), ['hero']); break;
                // Adicione outras tribos se necessário (4, 5...)
                default: $keys = []; // Ou talvez todas as u1-u50 + hero se preferir
            }
            foreach ($keys as $key) { $default_return[$key] = 0; }
            return $default_return;
        }


        // 2. Determina o intervalo de colunas uX e constrói a cláusula SELECT
        $selectFields = [];
        $tribe = (int) $tribe; // Garante que tribe seja um inteiro
        $startUnit = 0;
        $endUnit = 0;
        $expectedKeys = []; // Para garantir o retorno correto

        switch ($tribe) {
            case 1: // Romanos
                $startUnit = 1; $endUnit = 10;
                break;
            case 2: // Teutões
                $startUnit = 11; $endUnit = 20;
                break;
            case 3: // Gauleses
                $startUnit = 21; $endUnit = 30;
                break;
            case 4: // Ex: Natureza
                $startUnit = 31; $endUnit = 40;
                break;
            case 5: // Ex: Natar
                $startUnit = 41; $endUnit = 50;
                break;
            case 6: // Ex: Huns
                $startUnit = 51; $endUnit = 60;
                break;
            case 7: // Ex: Egyptians
                $startUnit = 61; $endUnit = 70;
                break;
            case 8: // Ex: Spartans
                $startUnit = 71; $endUnit = 80;
                break;
            case 9: // Ex: Vikings
                $startUnit = 81; $endUnit = 90;
                break;
            default:
                // Tribo inválida ou não suportada
                error_log("getUnitonVacation: Tribo inválida ou não mapeada: " . $tribe);
                // Define keys vazias, resultando em retorno vazio preenchido
                $keys = [];
                foreach ($keys as $key) { $default_return[$key] = 0; }
                return $default_return;
        }

        // Constrói a lista de campos a serem selecionados (SUM(uX) AS uX)
        if ($startUnit > 0 && $endUnit >= $startUnit) {
            for ($i = $startUnit; $i <= $endUnit; $i++) {
                $selectFields[] = "SUM(u{$i}) AS u{$i}";
                $expectedKeys[] = "u{$i}"; // Adiciona à lista de chaves esperadas
            }
            // Adiciona o herói - assumindo que existe uma coluna 'hero' separada
            // Se o herói for uma das colunas uX (ex: u11 para teutão), ajuste a lógica.
            $selectFields[] = "SUM(hero) AS hero";
            $expectedKeys[] = "hero"; // Adiciona 'hero' às chaves esperadas
        } else {
            // Segurança extra, caso a lógica do switch falhe
            error_log("getUnitonVacation: Falha ao determinar start/end unit para tribo " . $tribe);
            foreach ($expectedKeys as $key) { $default_return[$key] = 0; } // Usa as keys definidas no switch, se houver
            return $default_return;
        }

        // Preenche a estrutura de retorno padrão com zeros para as chaves esperadas
        foreach ($expectedKeys as $key) {
            $default_return[$key] = 0;
        }

        // Junta os campos para a cláusula SELECT
        $selectClause = implode(', ', $selectFields);

        // 3. Monta a consulta SQL final
        // A cláusula IN é segura aqui porque $village_ids contém apenas inteiros
        $q = "SELECT " . $selectClause . "
            FROM " . TB_PREFIX . "enforcement AS trEnf
            WHERE trEnf.from IN (" . implode(', ', $village_ids) . ")";

        // 4. Executa a consulta e retorna os resultados
        $result = mysqli_query($this->dblink, $q);

        if ($result) {
            $dbarray = mysqli_fetch_assoc($result); // Use fetch_assoc para obter chaves como 'u1', 'u11', 'hero', etc.

            // Se a consulta retornou uma linha (mesmo que com NULLs), mescla com o padrão
            if ($dbarray) {
                foreach ($expectedKeys as $key) {
                    // Garante que o valor seja inteiro, tratando NULL do SUM como 0
                    $default_return[$key] = isset($dbarray[$key]) ? (int)$dbarray[$key] : 0;
                }
            }
            // Se não retornou linha (nenhum reforço encontrado), $default_return já contém zeros.

            return $default_return; // Retorna array associativo com chaves corretas (uX, hero) e valores zero se não encontrados

        } else {
            // Erro na consulta
            error_log("Erro na consulta SQL em getUnitonVacation para tribo {$tribe}: " . mysqli_error($this->dblink));
            // Retorna a estrutura padrão com zeros em caso de erro
            return $default_return;
        }
    }

	function addA2b($ckey, $timestamp, $to, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type) {
	    list($ckey, $timestamp, $to, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type) = $this->escape_input($ckey, (int) $timestamp, (int) $to, (int) $t1, (int) $t2, (int) $t3, (int) $t4, (int) $t5, (int) $t6, (int) $t7, (int) $t8, (int) $t9, (int) $t10, (int) $t11, (int) $type);

		$q = "INSERT INTO " . TB_PREFIX . "a2b (ckey,time_check,to_vid,u1,u2,u3,u4,u5,u6,u7,u8,u9,u10,u11,type) VALUES ('$ckey', '$timestamp', '$to', '$t1', '$t2', '$t3', '$t4', '$t5', '$t6', '$t7', '$t8', '$t9', '$t10', '$t11', '$type')";
		mysqli_query($this->dblink,$q);
		return mysqli_insert_id($this->dblink);
	}

    function remA2b($id) {
        $id = (int) $id;

        $q = "DELETE FROM " . TB_PREFIX . "a2b WHERE id = $id";
        return mysqli_query($this->dblink,$q);
    }

	// no need to cache this method
	function getA2b($ckey) {
        list($ckey) = $this->escape_input($ckey);

		$q = "SELECT * from " . TB_PREFIX . "a2b where ckey = '" . $ckey . "'";
		$result = mysqli_query($this->dblink,$q);
		if($result) return mysqli_fetch_assoc($result);
        else return false;
	}

	function addMovement($type, $from, $to, $ref, $time, $endtime, $send = 1, $wood = 0, $clay = 0, $iron = 0, $crop = 0, $ref2 = 0) {
        // always prepare for multiple inserts at once
        if (!is_array($type)) {
            $type = [$type];
            $from = [$from];
            $to = [$to];
            $ref = [$ref];
            $time = [$time];
            $endtime = [$endtime];
            $send = [$send];
            $wood = [$wood];
            $clay = [$clay];
            $iron = [$iron];
            $crop = [$crop];
            $ref2 = [$ref2];
        }

        $counter = 0;
        $pairs = [];

        foreach ($type as $index => $typeValue) {
            $pairs[] = '(0, '.(int) $typeValue.', '.(int) $from[$index].', '.(int) $to[$index].', '.(int) $ref[$index].', '.(int) $ref2[$index].', '.(int) $time[$index].', '.(int) $endtime[$index].', 0, '.(int) $send[$index].', '.(int) $wood[$index].', '.(int) $clay[$index].', '.(int) $iron[$index].', '.(int) $crop[$index].')';

            if ($counter++ > 25) {
                $q = "INSERT INTO " . TB_PREFIX . "movement VALUES ".implode(', ', $pairs);
                mysqli_query($this->dblink,$q);

                $pairs = [];
                $counter = 0;
            }
        }

        if ($counter > 0) {
            $q = "INSERT INTO " . TB_PREFIX . "movement VALUES " . implode( ', ', $pairs );
            return mysqli_query( $this->dblink, $q );
        } else {
            return true;
        }
	}

	function addAttack($vid, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type, $ctar1, $ctar2, $spy,$b1=0,$b2=0,$b3=0,$b4=0,$b5=0,$b6=0,$b7=0,$b8=0) {
	    if (!is_array($vid)) {
	        $vid = [$vid];
	        $t1 = [$t1];
            $t2 = [$t2];
            $t3 = [$t3];
            $t4 = [$t4];
            $t5 = [$t5];
            $t6 = [$t6];
            $t7 = [$t7];
            $t8 = [$t8];
            $t9 = [$t9];
            $t10 = [$t10];
            $t11 = [$t11];
            $type = [$type];
            $ctar1 = [$ctar1];
            $ctar2 = [$ctar2];
            $spy = [$spy];
            $b1 = [$b1];
            $b2 = [$b2];
            $b3 = [$b3];
            $b4 = [$b4];
            $b5 = [$b5];
            $b6 = [$b6];
            $b7 = [$b7];
            $b8 = [$b8];
        }

        $values = [];
        foreach ($vid as $index => $vidValue) {
            $values[] = '(0, '.(int) $vidValue.', '.(int) $t1[$index].', '.(int) $t2[$index].', '.(int) $t3[$index].', '.
                        (int) $t4[$index].', '.(int) $t5[$index].', '.(int) $t6[$index].', '.(int) $t7[$index].', '.
                        (int) $t8[$index].', '.(int) $t9[$index].', '.(int) $t10[$index].', '.(int) $t11[$index].
                        ', '.(int) $type[$index].', '.(int) $ctar1[$index].', '.(int) $ctar2[$index].', '.
                        (int) $spy[$index].', '.(int) $b1[$index].', '.(int) $b2[$index].', '.(int) $b3[$index].
                        ', '.(int) $b4[$index].', '.(int) $b5[$index].', '.(int) $b6[$index].', '.(int) $b7[$index].
                        ', '.(int) $b8[$index].')';
        }

		$q = "INSERT INTO " . TB_PREFIX . "attacks VALUES ".implode(', ', $values);
		mysqli_query($this->dblink,$q);

		return (count($vid) == 1 ? mysqli_insert_id($this->dblink) : true);
	}

	function modifyAttack2($aid, $unit, $amt, $mode = 1) {
	    list($aid, $unit, $amt) = $this->escape_input((int) $aid, $unit, $amt);

	    if (!is_array($unit)) {
	        $unit = [$unit];
	        $amt = [$amt];
        }

        $pairs = [];
	    foreach ($unit as $index => $unitValue) {
	        $unitValue = 't' . $this->escape($unitValue);
            $pairs[] = $unitValue . ' = ' . $unitValue . (($mode) ? ' + ' : ' - ') . (int) $amt[$index];
        }

		$q = "UPDATE " . TB_PREFIX . "attacks SET ".implode(', ', $pairs)." WHERE id = $aid";
		return mysqli_query($this->dblink,$q);
	}

	function modifyAttack3($aid, $units) {
	    list($aid, $units) = $this->escape_input((int) $aid, $units);

        $q = "UPDATE ".TB_PREFIX."attacks set $units WHERE id = $aid";
        return mysqli_query($this->dblink,$q);
    }

    // no need to cache this method
	function getMovementById($id) {
	    list($id) = $this->escape_input((int) $id);
		$q = "SELECT * FROM ".TB_PREFIX."movement WHERE moveid = ".$id;
		$result = mysqli_query($this->dblink,$q);
		$array = $this->mysqli_fetch_all($result);
		return $array;
	}

}
