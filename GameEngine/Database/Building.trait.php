<?php

use App\Utils\Math;

trait DBBuilding {

    function getFieldLevelInVillage($vid, $fieldType, $use_cache = true) {
        $vid = (int) $vid;

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$fieldLevelsInVillageSearchCache, $vid.$fieldType)) && !is_null($cachedValue)) {
            return $cachedValue;
        }
 
        // $fieldType can be both, integer and string, to be used in the IN statement,
        // so we need to handle it correctly here
        if (!Math::isInt($fieldType)) {
            $fieldType = $this->escape($fieldType);
        }

        // please don't scream...
        // with the current table structure, there really IS NOT another way
        // (except for stored procedures, which we can't rely on to be allowed on the server)
        $result = mysqli_query($this->dblink, 'SELECT '.
         'CASE '.
           'WHEN `f1t` IN ('.$fieldType.') THEN `f1` '.
           'WHEN `f2t` IN ('.$fieldType.') THEN `f2` '.
           'WHEN `f3t` IN ('.$fieldType.') THEN `f3` '.
           'WHEN `f4t` IN ('.$fieldType.') THEN `f4` '.
           'WHEN `f5t` IN ('.$fieldType.') THEN `f5` '.
           'WHEN `f6t` IN ('.$fieldType.') THEN `f6` '.
           'WHEN `f7t` IN ('.$fieldType.') THEN `f7` '.
           'WHEN `f8t` IN ('.$fieldType.') THEN `f8` '.
           'WHEN `f9t` IN ('.$fieldType.') THEN `f9` '.
           'WHEN `f10t` IN ('.$fieldType.') THEN `f10` '.
           'WHEN `f11t` IN ('.$fieldType.') THEN `f11` '.
           'WHEN `f12t` IN ('.$fieldType.') THEN `f12` '.
           'WHEN `f13t` IN ('.$fieldType.') THEN `f13` '.
           'WHEN `f14t` IN ('.$fieldType.') THEN `f14` '.
           'WHEN `f15t` IN ('.$fieldType.') THEN `f15` '.
           'WHEN `f16t` IN ('.$fieldType.') THEN `f16` '.
           'WHEN `f17t` IN ('.$fieldType.') THEN `f17` '.
           'WHEN `f18t` IN ('.$fieldType.') THEN `f18` '.
           'WHEN `f19t` IN ('.$fieldType.') THEN `f19` '.
           'WHEN `f20t` IN ('.$fieldType.') THEN `f20` '.
           'WHEN `f21t` IN ('.$fieldType.') THEN `f21` '.
           'WHEN `f22t` IN ('.$fieldType.') THEN `f22` '.
           'WHEN `f23t` IN ('.$fieldType.') THEN `f23` '.
           'WHEN `f24t` IN ('.$fieldType.') THEN `f24` '.
           'WHEN `f25t` IN ('.$fieldType.') THEN `f25` '.
           'WHEN `f26t` IN ('.$fieldType.') THEN `f26` '.
           'WHEN `f27t` IN ('.$fieldType.') THEN `f27` '.
           'WHEN `f28t` IN ('.$fieldType.') THEN `f28` '.
           'WHEN `f29t` IN ('.$fieldType.') THEN `f29` '.
           'WHEN `f30t` IN ('.$fieldType.') THEN `f30` '.
           'WHEN `f31t` IN ('.$fieldType.') THEN `f31` '.
           'WHEN `f32t` IN ('.$fieldType.') THEN `f32` '.
           'WHEN `f33t` IN ('.$fieldType.') THEN `f33` '.
           'WHEN `f34t` IN ('.$fieldType.') THEN `f34` '.
           'WHEN `f35t` IN ('.$fieldType.') THEN `f35` '.
           'WHEN `f36t` IN ('.$fieldType.') THEN `f36` '.
           'WHEN `f37t` IN ('.$fieldType.') THEN `f37` '.
           'WHEN `f38t` IN ('.$fieldType.') THEN `f38` '.
           'WHEN `f39t` IN ('.$fieldType.') THEN `f39` '.
           'WHEN `f40t` IN ('.$fieldType.') THEN `f40` '.
           'WHEN `f99t` IN ('.$fieldType.') THEN `f99` '.
           'ELSE 0 '.
         'END AS level '.
         'FROM `'.TB_PREFIX.'fdata` '.
         'WHERE '.
           '`vref` = '.$vid.' '.
           'AND ('.
          '`f1t` IN ('.$fieldType.') OR '.
          '`f2t` IN ('.$fieldType.') OR '.
          '`f3t` IN ('.$fieldType.') OR '.
          '`f4t` IN ('.$fieldType.') OR '.
          '`f5t` IN ('.$fieldType.') OR '.
          '`f6t` IN ('.$fieldType.') OR '.
          '`f7t` IN ('.$fieldType.') OR '.
          '`f8t` IN ('.$fieldType.') OR '.
          '`f9t` IN ('.$fieldType.') OR '.
          '`f10t` IN ('.$fieldType.') OR '.
          '`f11t` IN ('.$fieldType.') OR '.
          '`f12t` IN ('.$fieldType.') OR '.
          '`f13t` IN ('.$fieldType.') OR '.
          '`f14t` IN ('.$fieldType.') OR '.
          '`f15t` IN ('.$fieldType.') OR '.
          '`f16t` IN ('.$fieldType.') OR '.
          '`f17t` IN ('.$fieldType.') OR '.
          '`f18t` IN ('.$fieldType.') OR '.
          '`f19t` IN ('.$fieldType.') OR '.
          '`f20t` IN ('.$fieldType.') OR '.
          '`f20t` IN ('.$fieldType.') OR '.
          '`f21t` IN ('.$fieldType.') OR '.
          '`f22t` IN ('.$fieldType.') OR '.
          '`f23t` IN ('.$fieldType.') OR '.
          '`f24t` IN ('.$fieldType.') OR '.
          '`f25t` IN ('.$fieldType.') OR '.
          '`f26t` IN ('.$fieldType.') OR '.
          '`f27t` IN ('.$fieldType.') OR '.
          '`f28t` IN ('.$fieldType.') OR '.
          '`f29t` IN ('.$fieldType.') OR '.
          '`f30t` IN ('.$fieldType.') OR '.
          '`f30t` IN ('.$fieldType.') OR '.
          '`f31t` IN ('.$fieldType.') OR '.
          '`f32t` IN ('.$fieldType.') OR '.
          '`f33t` IN ('.$fieldType.') OR '.
          '`f34t` IN ('.$fieldType.') OR '.
          '`f35t` IN ('.$fieldType.') OR '.
          '`f36t` IN ('.$fieldType.') OR '.
          '`f37t` IN ('.$fieldType.') OR '.
          '`f38t` IN ('.$fieldType.') OR '.
          '`f39t` IN ('.$fieldType.') OR '.
          '`f40t` IN ('.$fieldType.') OR '.
          '`f99t` IN ('.$fieldType.')) '.
         'LIMIT 1');
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		
		$level = 0; // Define um valor padrão
        if ($row && isset($row['level'])) { // Verifica se $row é válido E se a chave 'level' existe
            $level = $row['level'];
        } else {
            // Opcional: Logar se $row foi retornado mas sem 'level', o que seria estranho
             if ($row) {
                 // error_log("Database->getFieldLevelInVillage: Chave 'level' não encontrada na linha para vid=$vid / fieldType=$fieldType");
             }
             // Se $row for null (sem resultado), $level permanece 0, que é o esperado.
        }

        self::$fieldLevelsInVillageSearchCache[$vid.$fieldType] = $level;
        return self::$fieldLevelsInVillageSearchCache[$vid.$fieldType];
    }

	function getFieldLevel($vid, $field, $use_cache = true) {
	    list($vid, $field) = $this->escape_input((int) $vid, $field);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$resourceLevelsCache, $vid.$field)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT f" . $field . " from " . TB_PREFIX . "fdata where vref = $vid LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$row = mysqli_fetch_array($result);

        self::$resourceLevelsCache[$vid.$field] = $row["f" . $field];
        return self::$resourceLevelsCache[$vid.$field];
	}

	function getSingleFieldTypeCount($uid, $field, $lvlComparisonSign = '=', $lvl = false, $use_cache = true) {
	    $uid = (int) $uid;
	    $field = (int) $field;
	    $lvl = ($lvl === false ? $lvl : (int) $lvl);

	    if (!in_array($lvlComparisonSign, ['=', '<', '>', '>=', '<=', '!='])) {
	        $lvlComparisonSign = '=';
	    }

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$singleFieldTypeCountCache, $uid.$field.$lvlComparisonSign.($lvl ? 1 : 0))) && !is_null($cachedValue)) {
            return $cachedValue;
        }

	    $q = "
            SELECT
            	Count(*) as Total
            FROM
            	".TB_PREFIX."fdata f
            	LEFT JOIN ".TB_PREFIX."vdata v ON f.vref = v.wref
                LEFT JOIN ".TB_PREFIX."users u ON v.owner = u.id
            WHERE
            	u.id = ".$uid."
                AND
                (
                    (f1t = ".$field.($lvl !== false ? ' AND f1 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f2t = ".$field.($lvl !== false ? ' AND f2 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f3t = ".$field.($lvl !== false ? ' AND f3 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f4t = ".$field.($lvl !== false ? ' AND f4 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f5t = ".$field.($lvl !== false ? ' AND f5 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f6t = ".$field.($lvl !== false ? ' AND f6 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f7t = ".$field.($lvl !== false ? ' AND f7 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f8t = ".$field.($lvl !== false ? ' AND f8 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f9t = ".$field.($lvl !== false ? ' AND f9 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f10t = ".$field.($lvl !== false ? ' AND f10 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f11t = ".$field.($lvl !== false ? ' AND f11 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f12t = ".$field.($lvl !== false ? ' AND f12 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f13t = ".$field.($lvl !== false ? ' AND f13 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f14t = ".$field.($lvl !== false ? ' AND f14 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f15t = ".$field.($lvl !== false ? ' AND f15 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f16t = ".$field.($lvl !== false ? ' AND f16 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f17t = ".$field.($lvl !== false ? ' AND f17 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f18t = ".$field.($lvl !== false ? ' AND f18 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f19t = ".$field.($lvl !== false ? ' AND f19 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f20t = ".$field.($lvl !== false ? ' AND f20 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f21t = ".$field.($lvl !== false ? ' AND f21 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f22t = ".$field.($lvl !== false ? ' AND f22 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f23t = ".$field.($lvl !== false ? ' AND f23 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f24t = ".$field.($lvl !== false ? ' AND f24 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f25t = ".$field.($lvl !== false ? ' AND f25 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f26t = ".$field.($lvl !== false ? ' AND f26 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f27t = ".$field.($lvl !== false ? ' AND f27 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f28t = ".$field.($lvl !== false ? ' AND f28 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f29t = ".$field.($lvl !== false ? ' AND f29 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f30t = ".$field.($lvl !== false ? ' AND f30 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f31t = ".$field.($lvl !== false ? ' AND f31 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f32t = ".$field.($lvl !== false ? ' AND f32 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f33t = ".$field.($lvl !== false ? ' AND f33 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f34t = ".$field.($lvl !== false ? ' AND f34 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f35t = ".$field.($lvl !== false ? ' AND f35 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f36t = ".$field.($lvl !== false ? ' AND f36 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f37t = ".$field.($lvl !== false ? ' AND f37 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f38t = ".$field.($lvl !== false ? ' AND f38 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f39t = ".$field.($lvl !== false ? ' AND f39 '.$lvlComparisonSign.' '.$lvl : '').")
                    OR (f40t = ".$field.($lvl !== false ? ' AND f40 '.$lvlComparisonSign.' '.$lvl : '').")
                )";

	    $result = mysqli_query($this->dblink,$q);
	    $row = mysqli_fetch_array($result);

        self::$singleFieldTypeCountCache[$uid.$field.$lvlComparisonSign.($lvl ? 1 : 0)] = $row["Total"];
        return self::$singleFieldTypeCountCache[$uid.$field.$lvlComparisonSign.($lvl ? 1 : 0)];
	}

	function getFieldType($vid, $field, $use_cache = true) {
	    list($vid, $field) = $this->escape_input((int) $vid, $field);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$fieldTypeCache, $vid.$field)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

	    if ($field && $vid) {
    		$q = "SELECT f" . $field . "t from " . TB_PREFIX . "fdata where vref = $vid LIMIT 1";
    		$result = mysqli_query($this->dblink,$q);
    		$row = mysqli_fetch_array($result);
            self::$fieldTypeCache[$vid.$field] = $row["f" . $field . "t"];
	    } else {
            self::$fieldTypeCache[$vid.$field] = 0;
	    }

	    return self::$fieldTypeCache[$vid.$field];
	}

    function getClosestNeighbourOptimized($wid, $worldMax) {
        list($wid) = $this->escape_input((int) $wid);
        
        // 1. Obter Coordenadas da Vila Atual
        $coor = $this->getCoor($wid);
        $x1 = intval($coor['x']);
        $y1 = intval($coor['y']);
        $max_map = 2 * $worldMax + 1; // Tamanho total do mapa
        
        // 2. Loop de Expansão de Raio
        $radius = 3; // Começa com um raio de 3 (quadrado 7x7)
        $max_radius = $worldMax; // Limite máximo para não causar loop infinito
        
        while ($radius <= $max_radius) {
            
            // 3. Calcular Limites do Quadrado (com toroidalidade)
            
            // Define os limites brutos
            $min_x = $x1 - $radius;
            $max_x = $x1 + $radius;
            $min_y = $y1 - $radius;
            $max_y = $y1 + $radius;

            $x_condition = "";
            $y_condition = "";

            // Condições Toroidais para X
            if ($min_x < 0) {
                $x_condition = "(wdata.x >= 0 AND wdata.x <= $max_x) OR (wdata.x >= " . ($max_map + $min_x) . " AND wdata.x < $max_map)";
            } elseif ($max_x >= $max_map) {
                $x_condition = "(wdata.x >= $min_x AND wdata.x < $max_map) OR (wdata.x >= 0 AND wdata.x <= " . ($max_x - $max_map) . ")";
            } else {
                $x_condition = "wdata.x BETWEEN $min_x AND $max_x";
            }
            
            // Condições Toroidais para Y
            if ($min_y < 0) {
                $y_condition = "(wdata.y >= 0 AND wdata.y <= $max_y) OR (wdata.y >= " . ($max_map + $min_y) . " AND wdata.y < $max_map)";
            } elseif ($max_y >= $max_map) {
                $y_condition = "(wdata.y >= $min_y AND wdata.y < $max_map) OR (wdata.y >= 0 AND wdata.y <= " . ($max_y - $max_map) . ")";
            } else {
                $y_condition = "wdata.y BETWEEN $min_y AND $max_y";
            }

            // 4. Consulta SQL (Busca na Área)
            $q = "
                SELECT 
                    vdata.wref, vdata.name, wdata.x, wdata.y
                FROM 
                    " . TB_PREFIX . "wdata AS wdata
                INNER JOIN 
                    " . TB_PREFIX . "vdata AS vdata ON wdata.id = vdata.wref
                WHERE 
                    vdata.owner > 5
                    AND wdata.id != $wid
                    AND ($x_condition)
                    AND ($y_condition)
            ";

            $result = $this->query($q);

            // 5. Se encontrar resultados, processa no PHP e retorna
            if ($result->num_rows > 0) {
                $closest_wref = 0;
                $min_dist = -1;
                
                // Itera APENAS sobre as vilas encontradas no raio (conjunto pequeno)
                while ($row = $result->fetch_assoc()) {
                    $x2 = intval($row['x']);
                    $y2 = intval($row['y']);
                    
                    // Função de Distância Toroidal (a mesma que você tinha no código original)
                    $distanceX = min(abs($x2 - $x1), abs($max_map - abs($x2 - $x1)));
                    $distanceY = min(abs($y2 - $y1), abs($max_map - abs($y2 - $y1)));
                    $dist = sqrt(pow($distanceX, 2) + pow($distanceY, 2));

                    if ($dist < $min_dist || $min_dist == -1) {
                        $min_dist = $dist;
                        $closest_wref = $row;
                    }
                }
                return $closest_wref;
            }

            // 6. Se não encontrar, aumenta o raio e tenta novamente
            $radius += 5; // Aumenta o raio em 5 (do 7x7 vai para 17x17, 27x27, etc.)
        }
        
        // Retorno de fallback (se não encontrar nenhuma vila válida)
        return 0;
    }

	// no need to cache this method
	function getFieldDistance($wid) {
	    list($wid) = $this->escape_input((int) $wid);
        return $this->getClosestNeighbourOptimized($wid, WORLD_MAX);
    }

    function addBuilding($wid, $field, $type, $loop, $time, $master, $level) {
	    list($wid, $field, $type, $loop, $time, $master, $level) = $this->escape_input((int) $wid, $field, (int) $type, (int) $loop, (int) $time, (int) $master, (int) $level);

		$x = "UPDATE " . TB_PREFIX . "fdata SET f" . $field . "t=" . $type . " WHERE vref=" . $wid;
		mysqli_query($this->dblink,$x);
		$q = "INSERT into " . TB_PREFIX . "bdata values (0, $wid, $field, $type, $loop, $time, $master, $level)";
		return mysqli_query($this->dblink,$q);
	}

	/**
	 * Get the time required to build a specified building
	 * 
	 * @param int $id The ID where the building is located
	 * @param int $tid The type of the building
	 * @param int $plus The construction queue count
	 * @param int $wref The village ID
	 * @param array $buildingArray The array containing the buildings in the village
	 * @return int Returns the building time
	 */
	
	function getBuildingTime($id, $tid, $plus, $wref, $buildingArray) {
		list($id, $tid, $plus, $wref, $buildingArray) = $this->escape_input((int) $id, (int) $tid, (int) $plus, (int) $wref, $buildingArray);
		global ${'bid'.$tid}, $bid15;
		
		$dataArray = ${'bid'.$tid};
		
		//Check if we've the main building or not
		$mainBuilding = $this->getFieldLevelInVillage($wref, 15);
		if($tid == 15){
			if($mainBuilding == 0) return round($dataArray[$buildingArray['f'.$id] + $plus]['time'] / SPEED * 5);
			else return round($dataArray[$buildingArray['f'.$id] + $plus]['time'] / SPEED);
		}else{
			if($mainBuilding > 0) {
				return round($dataArray[$buildingArray['f'.$id] + $plus]['time'] * ($bid15[$mainBuilding]['attri'] / 100)  / SPEED);
			}
			else return round($dataArray[$buildingArray['f'.$id] + $plus]['time'] * 5 / SPEED);
		}
	}
	
	/**
	 * Called when removing a queued building by a player or because destroyed by catapults
	 * 
	 * @param int $d The ID of the building which needs to be deleted
	 * @param int $tribe The tribe of the player
	 * @param int $wid The village ID of the player
	 * @param array $fieldsArray Optional, the array containing the village building/resource fields
	 * @return bool Returns true if the building was delete successfully, false otherwise
	 */
	
	function removeBuilding($d, $tribe, $wid, $fieldsArray = []) {
		list($d, $tribe, $wid, $fieldsArray) = $this->escape_input((int) $d, (int) $tribe, (int) $wid, $fieldsArray);

		//Variables initialization
		$jobToDelete = [];
		$canBeRemoved = true;
		$time = time();
		$newTime = $loopTime = 0;
		if(empty($fieldsArray)) $fieldsArray = $this->getResourceLevel($wid);
		$jobs = $this->getJobsOrderByID($wid);
		
		//Search the job which needs to be deleted	
		foreach($jobs as $job){	
			//We need to modify waiting loop orders
			if(!empty($jobToDelete) && $job['loopcon'] == 1 && ($tribe != 1 || ($tribe == 1 && (($jobToDelete['field'] <= 18 && $job['field'] <= 18) || ($jobToDelete['field'] >= 19 && $job['field'] >= 19))))){
				
				//Does this job have the same field of the deleted one?
				$sameBuilding = $jobToDelete['field'] == $job['field'] ? 1 : 0;
                $isWW = $job['field'] == 99;
				
				//Can the building be completely removed from the village?
				if($sameBuilding && $canBeRemoved) $canBeRemoved = !$sameBuilding;		
				
				//Get the time required to upgrade the building at the given level	
				$newTime = $this->getBuildingTime(
						$job['field'],
						$job['type'], 
						$job['level'] - $fieldsArray['f'.$job['field']] - $sameBuilding, 
						$wid, 
						$fieldsArray);
				
				//Increase the looptime
				$loopTime += $newTime;
				
				//Update the values
				$q = "UPDATE
							" .TB_PREFIX. "bdata
 					  SET
							".($job['master'] ? "" : "loopcon = 0,")."
							timestamp = ".($job['master'] ? $newTime : $loopTime + $time)."
							".($sameBuilding ? ", level = level - 1" : "")."
					  WHERE
							id = ".$job['id'];
				mysqli_query($this->dblink, $q);
						
			}
			
			//We found the job that needs to be deleted
			if($job['id'] == $d) $jobToDelete = $job;
		}	

		if($canBeRemoved && $jobToDelete['field'] > 18 && $jobToDelete['field'] != 99 && $jobToDelete['level'] - 1 == 0){
			if (!$isWW) $this->setVillageLevel($wid, ["f".$jobToDelete['field']."t"], [0]);
		}
		
        $q = "DELETE FROM " . TB_PREFIX . "bdata where id = $d";
        return mysqli_query($this->dblink, $q);
	}

	function addDemolition($wid, $field) {
	    list($wid, $field) = $this->escape_input((int) $wid, (int) $field);

		global $building, $village, $session;

        $fLevel = $this->getFieldLevel($wid,$field);

		// check if we're not demolishing an Embassy if the player is in an alliance
		if ($this->getFieldType($wid,$field) == 18 && $session->alliance) {

		    // get field level, alliance members count and the minimum
		    // level of Embassy to be able to hold this number of people
		    $membersCount    = $this->countAllianceMembers($session->alliance);
		    $minEmbassyLevel = $this->getMinEmbassyLevel($membersCount);
		    $isOwner         = $this->isAllianceOwner($session->uid) == $session->alliance;

		    // make sure minimum Embassy level is 3 of the player is alliance owner
            if ($isOwner && $minEmbassyLevel < 3) {
                $minEmbassyLevel = 3;
            }

		    // check if this user is the founder of the alliance
		    // and whether we're not trying to demolish under the lowest level
		    // which can hold current number of members
		    if ($fLevel == $minEmbassyLevel && $session->alliance && $isOwner) {
		        // check if we have any other players in this alliance left
		        if ($membersCount > 1) {
		            // check if this player has only 1 last Embassy on a sufficient level
		            if ($this->getSingleFieldTypeCount($session->uid, 18, '>=', $minEmbassyLevel) == 1) {
    		            // cannot demolish Embassy further until the player quits the alliance,
    		            // as they are founder and there are still other players in the alliance,
    		            // thus destroying Embassy would evict this player from the alliance
    		            // and leave a new random leader
    		            return 18;
		            }
		        }
		    }
		}

		$q = "DELETE FROM ".TB_PREFIX."bdata WHERE field=$field AND wid=$wid";
		mysqli_query($this->dblink,$q);

		$uprequire = $building->resourceRequired($field,$village->resarray['f'.$field.'t'],0);
		$q = "INSERT INTO ".TB_PREFIX."demolition VALUES (".$wid.",".$field.",".($fLevel - 1).",".(time()+floor($uprequire['time']/2)).")";
		mysqli_query($this->dblink,$q);

		return true;
	}

    // no need to cache this method
	function getDemolition($wid = 0) {
	    list($wid) = $this->escape_input((int) $wid);

		if($wid) {
			$q = "SELECT * FROM " . TB_PREFIX . "demolition WHERE vref=" . $wid;
		} else {
			$q = "SELECT * FROM " . TB_PREFIX . "demolition WHERE timetofinish<=" . time();
		}
		$result = mysqli_query($this->dblink,$q);
		if(!empty($result)) {
			return $this->mysqli_fetch_all($result);
		} else {
			return NULL;
		}
	}

	function delDemolition($wid, $checkEmbassy = false) {
	    $wid = (int) $wid;

	    if ($checkEmbassy) {
	        // check if we've demolished an Embassy
	        // and select the user it belonged to as well,
	        // so we can potentially evict them from the alliance
	        // and remove it - if they don't have any more Embassies
	        //                 or if the they are founder and they have no more lvl 3+ Embassies
	        $q = '
            SELECT
                u.id, u.username, u.alliance, d.buildnumber, d.lvl
            FROM
                '.TB_PREFIX.'demolition d
                LEFT JOIN '.TB_PREFIX.'vdata v ON d.vref = v.wref
                LEFT JOIN '.TB_PREFIX.'users u ON u.id = v.owner
            WHERE d.vref = '.$wid;

	        $res = $this->mysqli_fetch_all(mysqli_query($this->dblink, $q), MYSQLI_ASSOC);
	        foreach ($res as $key) {
	            // if this building being demolished is an Embassy or was demolished completely
	            // and the player is in an alliance, check and update their alliance status
	            if (($key['alliance'] > 0) && ($key['lvl'] == 0 || $this->getFieldType($wid, $key['buildnumber']) == 18)) {
                    $this->checkAllianceEmbassiesStatus($key, true);
                }
	        }
	    }

		$q = "DELETE FROM " . TB_PREFIX . "demolition WHERE vref=" . $wid;
		return mysqli_query($this->dblink,$q);
	}

	/**
	 * Returns a minimum level for an Embassy in order to accomodate
	 * the given number of members.
	 *
	 * @param int $membersCount Number of members for an alliance to accomodate.
	 *                          Maximum = 60
	 *
	 * @return number Returns the level of Embassy required to accomodate
	 *                the given number of members.
	 */
	public function getMinEmbassyLevel($membersCount) {
	    $membersCount = (int) $membersCount;

	    if ($membersCount > 60) {
	        $membersCount = 60;
	    }

	    if ($membersCount < 0) {
	        $membersCount = 0;
	    }

	    return ceil((20 / 60) * $membersCount);
	}

    /**
	 * Checks and potentially updates the status of a player-alliance
	 * relationship given the user input.
	 *
	 * @param array $userData     Data of the user for which we want to check
	 *                            the player-alliance relationship.
	 * @param boolean $demolition Determines whether the request came from
	 *                            a buiding demolition (true) or from a battle
	 *                            report (false).
	 *
	 * @return boolean            Returns TRUE if there was no change
	 *                            to the player-alliance relationship
	 *                            FALSE if the player was an alliance
	 *                            leader and the alliance was destroyed
	 *                            and 0 when the player was evicted from
	 *                            the alliance due to Embassy damage.
	 */
	public function checkAllianceEmbassiesStatus($userData, $demolition = false, $use_cache = true) {
	    // TODO: refactor this and break it into more smaler methods
	    //global $session;

	    if ($userData['alliance']) {
            // check whether this player is an alliance owner
            $isOwner = ($userData['alliance'] && $this->isAllianceOwner($userData['id'], $use_cache) == $userData['alliance']);

            $minimumExistingEmbassyRecords = 1;

            // if they are not an alliance owner, simply check whether we have any Embassies
            // at lvl 1+ standing somewhere
            if (!$isOwner) {
                // TODO: replace magic numbers by constants (18 = Embassy)
                if ($this->getSingleFieldTypeCount($userData['id'], 18, '>=', 1, $use_cache) < $minimumExistingEmbassyRecords) {

                    // the player has no more Embassies, evict them from the alliance
                    mysqli_query($this->dblink, 'UPDATE '.TB_PREFIX.'users SET alliance = 0 WHERE id = '.$userData['id']);

                    // unset the alliance in session, if we're evicting
                    // currently logged-in player
                    //if ($session->uid == $userData['id']) {
                    //    $_SESSION['alliance_user'] = 0;
                    //}

                    // notify them via in-game messaging, if we come from a demolition,
                    // otherwise return a result which can be used in battle reports
                    if ($demolition) {
                        $this->sendMessage(
                            $userData['id'],
                            4,
                            'You left the alliance',
                            $this->escape("Hi, ".$userData['username']."!\n\nThis is to inform you that due to a finished demolition of your last Embassy, you have now successfully left your alliance.\n\nYours sincerely,\n<i>Server Robot :)</i>"),
                            0,
                            0,
                            0,
                            0,
                            0,
                            true);
                        $this->deleteAlliPermissions($userData['id']);
                    } else {
                        // player has been removed from the alliance
                        $this->sendMessage(
                            $userData['id'],
                            4,
                            'An attack has forced you to leave the alliance',
                            $this->escape("Hi, ".$userData['username']."!\n\nThis is to inform you that due to a successful attack and destruction of your last Embassy, you have been forced to leave your alliance.\n\nTo re-establish your position in this alliance, you will need to build a new Embassy and ask the leader to send you an invite again.\n\nYours sincerely,\n<i>Server Robot :)</i>"),
                            0,
                            0,
                            0,
                            0,
                            0,
                            true);
                        $this->deleteAlliPermissions($userData['id']);
                        return 0;
                    }

                }
            } else {
                // the player IS an alliance owner, check if we need to take any action
                $membersCount            = $this->countAllianceMembers($userData['alliance'], $use_cache);
                $minAllianceEmbassyLevel = $this->getMinEmbassyLevel($membersCount);

                // in this case, the minimum Embassy level cannot go below 3,
                // since this player is a leader and as such, he needs at least
                // a level 3 Embassy
                if ($minAllianceEmbassyLevel < 3) {
                    $minAllianceEmbassyLevel = 3;
                }

                $takeAction              = (
                    // was the Embassy taken below a threshold level?
                    ($userData['lvl'] <= $minAllianceEmbassyLevel)
                    &&
                    // check for standing Embassies with sufficient level
                    // TODO: replace magic numbers by constants (18 = Embassy)
                    ($this->getSingleFieldTypeCount($userData['id'], 18, '>=', $minAllianceEmbassyLevel, false, $use_cache) < $minimumExistingEmbassyRecords)
                );

                // the Embassy got damaged below a sufficient level and there are no more Embassies
                // at that level standing on this player's account, additional actions are needed
                if ($takeAction) {

                    // load all alliance members
                    $members = $this->getAllMember($userData['alliance'], 0, $use_cache);

                    // if we come from demolition, we need to evict all new members
                    // that accepted an invitation while level 3 of the last
                    // Embassy was already under demolition. The demolition dialog itself
                    // already checks if there are no more people other than the owner
                    // present before the demolition is allowed.
                    if ($demolition) {
                        $evicts = [];
                        foreach ($members as $member) {
                            // evict the player from the alliance
                            $evicts[] = $member['id'];

                            // notify them via in-game messaging
                            $this->sendMessage(
                                $member['id'],
                                4,
                                'Your alliance was disbanded',
                                (
                                    ($member['id'] == $userData['id'])
                                    ?
                                    $this->escape("Hi, ".$userData['username']."!\n\nThis is to inform you that due to a finished demolition of your last Embassy at level 3, and the fact that you were the leader of your alliance, this alliance has been disbanded.\n\nIn order to found a new alliance, please build a level 3 Embassy again in one of your villages.\n\nYours sincerely,\n<i>Server Robot :)</i>")
                                    :
                                    $this->escape("Hi, ".$member['username']."!\n\nThis is to inform you that due to a demolition of your alliance founder's last Embassy below level 3, this alliance has been disbanded.\n\n\You can now accept invitations from other alliances or found a new alliance yourself.\n\nYours sincerely,\n<i>Server Robot :)</i>")
                                ),
                                0,
                                0,
                                0,
                                0,
                                0,
                                true);
                            $this->deleteAlliPermissions($member['id']);
                        }

                        mysqli_query($this->dblink, 'UPDATE '.TB_PREFIX.'users SET alliance = 0 WHERE id IN('.implode(',', $evicts).")");
                    } else {
                        // we come from a battle result, therefore we need to check
                        // for the first player in the alliance who has a sufficient
                        // level Embassy and to which we can auto-reassign the leadership
                        $newLeaderFound = false;

                        // in case we'll need these later to disband the alliance,
                        // we'll collect them inside this foeach loop
                        $memberIDs      = [];

                        // no need for this whole foreach loop if this player is the lone
                        // founder and member of their alliance
                        if ($membersCount > 1) {
                            foreach ($members as $member) {
                                if (!$newLeaderFound && $this->getSingleFieldTypeCount($member['id'], 18, '>=', $minAllianceEmbassyLevel) >= 1) {
                                    // found a new leader for the alliance
                                    $newLeaderFound = true;
                                    $newleader = $member['id'];
                                    $q = "UPDATE " . TB_PREFIX . "alidata set leader = ".(int) $newleader." where id = ".(int) $userData['alliance'];
                                    $this->query($q);
                                    $this->updateAlliPermissions($newleader, $userData['alliance'], "Leader", 1, 1, 1, 1, 1, 1, 1);
                                    Automation::updateMax($newleader);

                                    // update permissions for the old leader
                                    $this->updateAlliPermissions($userData['id'], $userData['alliance'], "Former Leader", 0, 0, 0, 0, 0, 0, 0);

                                    // notify new leader via in-game messaging
                                    $this->sendMessage(
                                        $newleader,
                                        4,
                                        'You are now the alliance leader',
                                        $this->escape("Hi, ".$member['username']."!\n\nThis is to inform you that there was a successful attack on player <a href=\"spieler.php?uid=".$userData['id']."\">".$userData['username']."</a> which has damaged their Embassy badly enough that they are no longer able to sustain the leadership of your alliance.\n\nSince your Embassy level is of a sufficient level, you have been auto-elected to the position of a new leader of your alliance with all duties and responsibilities thereof.\n\nYours sincerely,\n<i>Server Robot :)</i>"),
                                        0,
                                        0,
                                        0,
                                        0,
                                        0,
                                        true);
                                }

                                $memberIDs[] = $member['id'];
                            }
                        } else {
                            // if there is only 1 member and it's the actual founder
                            $memberIDs[] = $userData['id'];
                        }

                        // if there wasn't anyone with a sufficient level of Embassy
                        // among the existing members, disperse this alliance
                        if (!$newLeaderFound) {

                            // evict all members from the alliance
                            mysqli_query($this->dblink, 'UPDATE '.TB_PREFIX.'users SET alliance = 0 WHERE id IN('.implode(',', $memberIDs).")");

                            // notify all of them via in-game messaging
                            foreach ($members as $member) {
                                $this->sendMessage(
                                    $member['id'],
                                    4,
                                    'Your alliance was dispersed',
                                    (
                                        ($member['id'] == $userData['id'])
                                        ?
                                        $this->escape("Hi, ".$userData['username']."!\n\nThis is to inform you that due to a successful attack that has degraded your last Embassy to a level ".($membersCount > 1 ? "which is unable to hold all ".$membersCount." alliance members, and because there was no other alliance member with an Embassy on a high enough level to overtake the leadership," : "lower then 3 - which is required to found and hold your own alliance - ")." your alliance has been dispersed.\n\nYours sincerely,\n<i>Server Robot :)</i>")
                                        :
                                        $this->escape("Hi, ".$member['username']."!\n\nThis is to inform you that due to a successful attack on your alliance leader's Embassy by another player that degraded it below threshold allowed to hold all ".$membersCount." alliance members, and because there was no other alliance member with an Embassy on a high enough level to overtake the leadership, your alliance has been dispersed.\n\nYours sincerely,\n<i>Server Robot :)</i>")
                                    ),
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    true);
                            }
                            $this->deleteAlliPermissions($member['id']);
                        } else {
                            // let's determine whether to keep currently attacked player
                            // in the alliance or not
                            if ($userData['lvl'] > 0 || $this->getSingleFieldTypeCount($member['id'], 18, '>=', 1, $use_cache) >= $minimumExistingEmbassyRecords) {
                                $keepCurrentPlayer = true;
                            } else {
                                $keepCurrentPlayer = false;
                            }

                            // if a new leader was found, notify all alliance member of this change
                            // notify all of them via in-game messaging
                            foreach ($members as $member) {
                                // don't send duplicate messages to the new leader
                                if ($member['id'] != $newleader) {
                                    // also, don't send to the attacked player if we're
                                    // not keeping them in alliance
                                    if ($keepCurrentPlayer || (!$keepCurrentPlayer && $member['id'] != $userData['id']))
                                        $this->sendMessage(
                                            $member['id'],
                                            4,
                                            'Your alliance has a new leader',
                                            (
                                                ($member['id'] == $userData['id'])
                                                ?
                                                $this->escape("Hi, ".$userData['username']."!\n\nThis is to inform you that due to a successful attack that has degraded your last Embassy to a level which is unable to hold all ".$membersCount." alliance members, another alliance member who meets these criteria has been auto-elected as a new alliance leader.\n\nAdditionally - due to the Embassy destruction - you have been forcefuly evicted from your alliance.\n\nPlease re-establish the connection with your alliance by building a new Embassy and contacting <a href=\"spieler.php?uid=".$newleader."\">the new leader</a> for an invitation.\n\nYours sincerely,\n<i>Server Robot :)</i>")
                                                :
                                                $this->escape("Hi, ".$member['username']."!\n\nThis is to inform you that due to a successful attack on your alliance leader's Embassy by another player, <a href=\"spieler.php?uid=".$newleader."\">another alliance member</a> with enough Embassy capacity has been auto-elected as the new alliance leader.\n\nYours sincerely,\n<i>Server Robot :)</i>")
                                                ),
                                            0,
                                            0,
                                            0,
                                            0,
                                            0,
                                            true);
                                }
                                $this->deleteAlliPermissions($member['id']);
                            }

                            // evict current player from the alliance
                            // if this was their last Embassy and was completely destroyed
                            if (!$keepCurrentPlayer) {
                                mysqli_query($this->dblink, 'UPDATE '.TB_PREFIX.'users SET alliance = 0 WHERE id = '.$userData['id']);

                                // unset the alliance in session, if we're evicting
                                // currently logged-in player
                                if ($session->uid == $userData['id']) {
                                    $_SESSION['alliance_user'] = 0;
                                }

                                // notify the evicted player
                                $this->sendMessage(
                                    $userData['id'],
                                    4,
                                    'An attack has forced you to leave the alliance',
                                    $this->escape("Hi, ".$userData['username']."!\n\nThis is to inform you that due to a successful attack and destruction of your last Embassy, you have been forced to leave your alliance.\n\nTo re-establish your position in this alliance, you will need to build a new Embassy and ask the <a href=\"spieler.php?uid=".$newleader."\">newly auto-elected leader</a> to send you an invite again.\n\nYours sincerely,\n<i>Server Robot :)</i>"),
                                    0,
                                    0,
                                    0,
                                    0,
                                    0,
                                    true);
                            }
                            $this->deleteAlliPermissions($userData['id']);
                        }
                    }

                    // execute a method that will delete an alliance
                    // if no members are left in it
                    $this->deleteAlliance($userData['alliance']);

                    return isset($newLeaderFound) && $newLeaderFound === true;
                }
            }
        }

	    // no changes in player-to-alliance relationship
	    return true;
	}

	function checkEmbassiesAfterBattle($vid, $current_level, $use_cache = true) {
        $userData = $this->getUserArray($this->getVillageField($vid, "owner"), 1);

        Automation::updateMax($this->getVillageField($vid,"owner"));
        $allianceStatus = $this->checkAllianceEmbassiesStatus([
            'id'       => $userData['id'],
            'alliance' => $userData["alliance"],
            'username' => $userData["username"],
            'lvl'      => $current_level
        ], false, $use_cache);

        if ($allianceStatus === false) return ' This player\'s alliance has been dispersed.';    
	    else if ($allianceStatus === 0) return ' Player was forced to leave their alliance.';          
	    else return ''; // all is good, no need to append additional alliance-related text 
    }

    /**
     * Modify or delete a building being constructed/in queue
     * 
     * @param int The village ID
     * @param int $field The field where the building is located
     * @param array $levels The new level of the building and the old one
     * @param int $tribe The player's tribe
     */
    
    function modifyBData($wid, $field, $levels, $tribe){
    	// 1. Tipagem manual e segura via type casting (Dispensa o escape_input se forem todos inteiros)
        $wid = (int) $wid;
        $field = (int) $field;
        $tribe = (int) $tribe;
        
        // Garante que as posições do array sejam inteiros
        $levelNew = (int) $levels[0];
        $levelOld = (int) $levels[1];

        // 2. Lógica de remoção ou atualização
        if($levelNew == 0){ 
            $q = "SELECT id FROM " .TB_PREFIX. "bdata WHERE wid = $wid AND field = $field";
            $orders = $this->mysqli_fetch_all(mysqli_query($this->dblink, $q));
            
            foreach($orders as $order) {
                $this->removeBuilding($order['id'], $tribe, $wid);
            }
        }
        else {
            // Usando as variáveis limpas na query
            $q = "UPDATE " .TB_PREFIX. "bdata SET level = level - $levelOld + $levelNew WHERE wid = $wid AND field = $field";
            mysqli_query($this->dblink, $q);
        }
        
    }
    
    private function getBData($wid, $use_cache = true, $orderByID = false) {
	    $wid = (int) $wid;

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && isset(self::$buildingsUnderConstructionCache[$wid]) && is_array(self::$buildingsUnderConstructionCache[$wid]) && !count(self::$buildingsUnderConstructionCache[$wid])) {
            return [];
        } else if ($use_cache && ($cachedValue = self::returnCachedContent(self::$buildingsUnderConstructionCache, $wid)) && !is_null($cachedValue)) {
            return self::$buildingsUnderConstructionCache[$wid];
        }

        $q = "SELECT * FROM " . TB_PREFIX . "bdata where wid = $wid order by ".(!$orderByID ? "master,timestamp" : "id")." ASC";
        $result = $this->mysqli_fetch_all(mysqli_query($this->dblink,$q));

        self::$buildingsUnderConstructionCache[$wid] = $result;
        return $result;
    }

    // do not cache output, as building jobs can change when using instant build (PLUS) etc.
	function getJobs($wid) {
	    return $this->getBData($wid, false);
	}
	
	function getJobsOrderByID($wid) {
	    return $this->getBData($wid, false, true);
	}

	function FinishWoodcutter($wid) {
	    $bdata = $this->getBData($wid);
		$time = time()-1;

		// find our woodcutter
        $dbarray = [];
        foreach ($bdata as $row) {
            if ($row['type'] == 1) {
                $dbarray = $row;
                break;
            }
        }

        // no woodcutters? just return
        if (!count($dbarray)) {
            return;
        }

        // make it complete
		$q = "UPDATE ".TB_PREFIX."bdata SET timestamp = $time WHERE id = ".$dbarray['id'];
		$this->query($q);

		$tribe = $this->getUserField($this->getVillageField($wid, "owner"), "tribe", 0);

		// find first field that's the next one in the loop after our finished woodcutter
		$dbarray2 = [];
        foreach ($bdata as $row) {
            if ($row['loopcon'] == 1 && ($tribe == 1 ? $row['field'] >= 19 : true)) {
                $dbarray2 = $row;
                break;
            }
        }

        // if found, update it's finish time by subtracting the resulting time for our woodcutter,
        // which is now finished
		if (count($dbarray2)){
            $wc_time = $dbarray['timestamp'];
            $q2 = "UPDATE ".TB_PREFIX."bdata SET timestamp = timestamp - $wc_time WHERE id = ".$dbarray2['id'];
            $this->query($q2);
		}
	}

	function getMasterJobs($wid) {
	    // cache data
        $bdata = $this->getBData($wid);

        // return all master jobs
        $data = [];
        foreach ($bdata as $row) {
            if ($row['master'] == 1) $data[] = $row;
        }

		return $data;
	}

	function getMasterJobsByField($wid,$field) {
        // cache data
        $bdata = $this->getBData($wid);

        // return all master jobs for the requested field
        $data = [];
        foreach ($bdata as $row) {
            if ($row['master'] == 1 && $row['field'] == $field) {
                $data[] = $row;
            }
        }

        return $data;
	}

	function getBuildingByField($wid,$field) {
        // cache data
        $bdata = $this->getBData($wid);

        // return all non-master jobs for the requested field
        $data = [];
        foreach ($bdata as $row) {
            if ($row['master'] == 0 && $row['field'] == $field) {
                $data[] = $row;
            }
        }

        return $data;
	}

    // no need to cache this method
	function getBuildingByField2($wid,$field) {
	    list($wid,$field) = $this->escape_input((int) $wid,(int) $field);

		$q = "SELECT Count(*) as Total FROM " . TB_PREFIX . "bdata where wid = $wid and field = $field and master = 0";
		$result = mysqli_fetch_array(mysqli_query($this->dblink,$q), MYSQLI_ASSOC);
		return $result['Total'];
	}

	function getBuildingByType($wid,$type) {
        // cache data
        $bdata = $this->getBData($wid);
        $type = (strpos($type, ',') === false ? [(int) $type] : explode(',', str_replace(' ', '', $this->escape($type))));

        // return all jobs which are of the requested type
        $data = [];
        foreach ($bdata as $row) {
            if (in_array($row['field'], $type)) {
                $data[] = $row;
            }
        }

        return $data;
	}

	function getBuildingByType2($wid,$type) {
	    $wid = (int) $wid;

	    if (!is_array($type)) {
	        $type = [$type];
        } else {
	        foreach ($type as $index => $typeValue) {
	            $type[$index] = (int) $typeValue;
            }
        }

		$q = "SELECT CONCAT(type, \"=\", Count(type)) FROM " . TB_PREFIX . "bdata where wid = $wid and type IN(".implode(', ', $type).") and master = 0 GROUP BY type";
		$result = mysqli_query($this->dblink, $q);
		$newresult = [];

		if (mysqli_num_rows($result)) {
		    while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
		        if ($row[0]) {
                    $val                  = explode( '=', $row[0] );
                    $newresult[ $val[0] ] = $val[1];
                }
            }

            $result = $newresult;

        } else {
		    $result = [];
        }

		return $result;
	}

	function getDorf1Building($wid) {
        // cache data
        $bdata = $this->getBData($wid);

        // return all non-master jobs for field type under 19
        $data = [];
        foreach ($bdata as $row) {
            if ($row['master'] == 0 && $row['field'] < 19) $data[] = $row;
        }

        return $data;
	}

	function getDorf2Building($wid) {
        // cache data
        $bdata = $this->getBData($wid);

        // return all non-master jobs for field type above 18
        $data = [];
        foreach ($bdata as $row) {
            if ($row['master'] == 0 && $row['field'] > 18) $data[] = $row;
        }

        return $data;
	}

	function updateBuildingWithMaster($id, $time, $loop) {
	    list($id, $time, $loop) = $this->escape_input((int) $id, (int) $time, (int) $loop);

		$q = "UPDATE " . TB_PREFIX . "bdata SET master = 0, timestamp = ".$time.", loopcon = ".$loop." WHERE id = ".$id."";
		return mysqli_query($this->dblink,$q);
	}

    function getBuildLock($wid) {
        $wid = (int) $wid;
        $result = mysqli_query($this->dblink, "SELECT GET_LOCK('build_village_$wid', 1) AS locked");
        $row = mysqli_fetch_assoc($result);
        return $row['locked'] == 1;
    }

    function releaseBuildLock($wid) {
        $wid = (int) $wid;
        mysqli_query($this->dblink, "SELECT RELEASE_LOCK('build_village_$wid')");
    }

    function getResearchLock($wid) {
        $wid = (int) $wid;
        $result = mysqli_query($this->dblink, "SELECT GET_LOCK('research_village_$wid', 1) AS locked");
        $row = mysqli_fetch_assoc($result);
        return $row['locked'] == 1;
    }

    function releaseResearchLock($wid) {
        $wid = (int) $wid;
        mysqli_query($this->dblink, "SELECT RELEASE_LOCK('research_village_$wid')");
    }
}
