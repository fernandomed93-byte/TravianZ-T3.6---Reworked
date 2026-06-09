<?php

use App\Utils\Math;

trait DBVillage {

    function updateResource($vid, $what, $number) {
	    $vid = (int) $vid;

	    if (!is_array($what)) {
	        $what = [$what];
	        $number = [$number];
        }

        $pairs = [];
        foreach ($what as $index => $whatValue) {
            $pairs[] = $this->escape($whatValue) . ' = ' . (Math::isInt($number[$index]) ? $number[$index] : '"'.$this->escape($number[$index]).'"');
        }

		$q = "UPDATE " . TB_PREFIX . "vdata SET ".implode(', ', $pairs)." WHERE wref = $vid";
		$result = mysqli_query($this->dblink,$q);
		return mysqli_query($this->dblink,$q);
	}

	// no need to cache this method
	public function hasBeginnerProtection($vid) {
        list($vid) = $this->escape_input($vid);
            $q = "SELECT u.protect FROM ".TB_PREFIX."users u,".TB_PREFIX."vdata v WHERE u.id=v.owner AND v.wref=".(int) $vid." LIMIT 1";
        $result = mysqli_query($this->dblink,$q);
        $dbarray = mysqli_fetch_array($result);
        if(!empty($dbarray)) {
            if(time()<$dbarray[0]) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // no need to cache this method
	function getVilWref($x, $y) {
	    list($x, $y) = $this->escape_input((int) $x, (int) $y);

		$q = "SELECT id FROM " . TB_PREFIX . "wdata where x = $x AND y = $y LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		return $dbarray['id'];
	}
	
	/**
	 * Converts from coordinates to village IDs
	 * 
	 * @param array $coordinatesArray The coordinates array, containing the coordinates which need to be converted
	 * @return array Returns the converted coordinates
	 */
	
	function getVilWrefs($coordinatesArray) {
	    list($coordinatesArray) = $this->escape_input($coordinatesArray);
	    
	    if(!is_array($coordinatesArray[0])) $coordinatesArray = [$coordinatesArray];
	    
	    $conditions = [];
	    foreach($coordinatesArray as $coordinate){
	        $conditions[] = "(x = ".round($coordinate[0])." AND y = ".round($coordinate[1]).")";
	    }
	    
	    $q = "SELECT id FROM " . TB_PREFIX . "wdata WHERE ".implode(" OR ", $conditions);
	    $result = mysqli_query($this->dblink, $q);
	    
	    while($row = mysqli_fetch_assoc($result)) $wids[] = $row['id'];
	    return $wids;
	}

    function getVrefField($ref, $field, $use_cache = true) {
        return $this->getVillage($ref, 0, $use_cache)[$field];
	}

    // no need to cache this method
	function getVrefCapital($ref) {
	    $vdata = $this->getProfileVillages($ref);

	    foreach($vdata as $village){
	    	if($village['capital']) return $village;
        }
        return false;
	}

    /**
	 * Generates a list of "free to take" villages
	 * 
	 * @param int $sector The map sector, + | -, - | + , + | +, - | - (0 and > 3, 1, 2, 3)
	 * @param int $mode 0 if villages need be generated under certain filters, 1 if not
	 * @param bool $respect_gametime If is false, we generate user base really anywhere
	 * and that means we can generate farms closer to the middle of the map as well.
	 * Otherwise we'd only generate farms at corner edges in late game, which
	 * sucks for people in the middle who registered too soon
	 * @param int $numberOfVillages Number of villages which need to be generated
	 * @return array Return the generated villages 
	 */ 
	 
	function generateBaseNoFilter($sector, $capitalWid, $mode = 0, $numberOfVillages = 1) {
		list($sector, $mode, $numberOfVillages) = $this->escape_input((int) $sector, (int) $mode, (int)$numberOfVillages);

		@set_time_limit(0); // Evita timeout do PHP
		$count = 0;
		$foundVillages = []; // Array para armazenar os resultados ['id' => id, 'fieldtype' => fieldtype]
		$time = time();

		while ($numberOfVillages > 0) {
			// Lógica de cálculo de $radiusMin e $radiusMax (igual à generateBase original)
			switch($mode){
				case 1:
				default:
					$radiusMin = 1;
					$radiusMax = pow(WORLD_MAX, 2);
					break;
			}

			// Lógica de $newSector (igual à generateBase original)
			switch($sector){
				case 1: $newSector = "x <= 0 AND y >= 0"; break; // - | +
				case 2: $newSector = "x >= 0 AND y >= 0"; break; // + | +
				case 3: $newSector = "x <= 0 AND y <= 0"; break; // - | -
				default: $newSector = "x >= 0 AND y <= 0"; // + | -
			}

			$q = "SELECT id, fieldtype, x, y FROM ".TB_PREFIX."wdata WHERE id <> $capitalWid AND($newSector) AND occupied = 0 AND oasistype = 0 AND (POWER(x, 2) + POWER(y, 2) >= $radiusMin AND POWER(x, 2) + POWER(y, 2) <= $radiusMax) ORDER BY RAND() LIMIT $numberOfVillages";
			//$q = "SELECT id, fieldtype FROM ".TB_PREFIX."wdata WHERE " . ($excludedVillagesClause ? "$excludedVillagesClause AND " : "") . " ($newSector) AND (POWER(x, 2) + POWER(y, 2) >= $radiusMin AND POWER(x, 2) + POWER(y, 2) <= $radiusMax) AND occupied = 0 AND oasistype = 0 ORDER BY RAND() LIMIT $numberOfVillages";
			$result = mysqli_query($this->dblink, $q);
			
				
				$resultedRows = mysqli_num_rows($result);
			if($resultedRows == 0 && $count >= WORLD_MAX * 2) {
				 break;
			}

			$villagesData = $this->mysqli_fetch_all($result);
			if ($villagesData) {
				 $foundVillages = array_merge($foundVillages, $villagesData);
			}

			$numberOfVillages -= $resultedRows; // Diminui o número de vilas restantes a encontrar
			$count++;

			if ($count > intval(WORLD_MAX / 10)) {
				$sector = rand(1, 4);
			}
		}

		return $foundVillages;
	}

	function generateBase($sector, $mode = 0, $numberOfVillages = 1) {
	    list($sector, $mode, $numberOfVillages) = $this->escape_input((int) $sector, (int) $mode, (int)$numberOfVillages);

        // don't let SQL time out when 30-500 seconds (depending on php.ini) is not enough
        @set_time_limit(0);
        $num_rows = $count = 0;
        $villages = [];
        $time = time();
        
        while ($numberOfVillages > 0) {
            switch($mode){
                case 0:
                    $daysPassedFromStart = ($time - strtotime(START_DATE) - strtotime(date('d.m.Y')) + strtotime(START_TIME)) / 86400;

                    $radiusMin = min(round(pow(2 * ($daysPassedFromStart / 5 * SPEED), 2)), round(pow(WORLD_MAX * 0.8, 2)) + round(pow(WORLD_MAX * 0.8, 2)));
                    $radiusMax = min(round(pow(4 * ($daysPassedFromStart / 5 * SPEED), 2)) + pow($count, 2), pow(WORLD_MAX, 2) + pow(WORLD_MAX, 2));
                    break;
                    
                case 1:
                default:
                    $radiusMin = 1;
                    $radiusMax = pow(WORLD_MAX, 2);
                    break;
                    
                case 2: //Small artifacts & WW building plans
                    $radiusMin = round(pow(WORLD_MAX * 0.50, 2));
                    $radiusMax = round(pow(WORLD_MAX * 0.75, 2));
                    break;
                
                case 3: //Large artifacts
                    $radiusMin = round(pow(WORLD_MAX * 0.35, 2));
                    $radiusMax = round(pow(WORLD_MAX * 0.55, 2));
                    break;
                
                case 4: //Unique artifacts
                    $radiusMin = round(pow(WORLD_MAX * 0.05, 2));
                    $radiusMax = round(pow(WORLD_MAX * 0.25, 2));
                    break;

                case 5: //WW villages
                    $radiusMin = round(pow(WORLD_MAX * 0.8, 2));
                    $radiusMax = round(pow(WORLD_MAX, 2));
                    break;
            }

            switch($sector){
                case 1: $newSector = "x <= 0 AND y >= 0"; break; // - | +       
                case 2: $newSector = "x >= 0 AND y >= 0"; break; // + | +                   
                case 3: $newSector = "x <= 0 AND y <= 0"; break; // - | -            
                default: $newSector = "x >= 0 AND y <= 0"; // + | -                 
            }

            //Choose villages beetween two circumferences, by using their formula (x^2 + y^2 = r^2)
            $q = "SELECT id FROM ".TB_PREFIX."wdata WHERE fieldtype = 3 AND ($newSector) AND (POWER(x, 2) + POWER(y, 2) >= $radiusMin AND POWER(x, 2) + POWER(y, 2) <= $radiusMax) AND occupied = 0 ORDER BY RAND() LIMIT $numberOfVillages";
            $result = mysqli_query($this->dblink, $q);

            //Prevent an infinite loop
            $resultedRows = mysqli_num_rows($result);
            if($resultedRows == 0 && $count >= WORLD_MAX * 2) break;
            
            //Fill the villages array
            $villages = array_merge($villages, $this->mysqli_fetch_all($result));
            
            $num_rows += $resultedRows;
            $numberOfVillages -= $resultedRows;
            $count++;
            
            //If there are no more free cells in that sector, it have to be changed
            //This instruction will be used only (in the next cicle(s)) if not all wids have been generated yet
            if ($count > intval(WORLD_MAX / 10)) $sector = rand(1, 4);
        }

        foreach($villages as $village) $wids[] = $village['id'];

        return $num_rows == 1 ? $wids[0] : $wids;
    }

	function setFieldTaken($id) {
        if(empty($id)) return;
        if (!is_array($id)) {
            $id = [$id];
        }

        foreach ($id as $index => $idValue) {
            $id[$index] = (int) $idValue;
        }

		$q = "UPDATE " . TB_PREFIX . "wdata SET occupied = 1 WHERE id IN(". implode(', ', $id).")";
		return mysqli_query($this->dblink,$q);
	}

	/**
	 * Creates new villages
	 *
	 * @param array $villageArrays The array of the villages which have to be created
	 * @param int $uid The user ID
	 * @param string $username The username of the future owner
	 * @param array $troopsArray The troops that need to be added in the village(s)
	 * @param array $buildingsArray The buildings that need to be created in the village(s)
	 * @return array Returns the created villages ID
	 */
	
	function generateVillages($villageArrays, $uid, $username, $troopsArray = null, $buildingsArray = null){	
	    list($villageArrays, $uid, $username, $troopsArray, $buildingsArray) = $this->escape_input($villageArrays, (int) $uid, $username, $troopsArray, $buildingsArray);
		
	    $wids = $takenWids = $countedWids = $generatedWids = $i = [];
	    
	    //Count each kid in its own array, to check how many villages must be created
	    foreach($villageArrays as $village){
	        if($village['wid'] == 0) $countedWids[$village['mode']][$village['kid']]++;
	    }
	    
	    //Generate the number of desired village for each kid
	    //and merge them with the more general "wids" array
	    foreach($countedWids as $mode => $totalCount){
	        foreach($totalCount as $sector => $count){
	            $generatedWids = $this->generateBase($sector, $mode, $count);
	            $wids[$mode] = array_merge((array)$wids[$mode], !is_array($generatedWids) ? [$generatedWids] : $generatedWids);
	            if(empty($i[$mode])) $i[$mode] = 0;
	        }
	    }
	    
	    //Create the villages
		foreach($villageArrays as $village){
		    
		    //Check if the village wid isn't already set and assing one among the generated ones
		    if($village['wid'] == 0) $village['wid'] = $wids[$village['mode']][$i[$village['mode']]++];
		    
		    //Merge the wids into an unique array
		    $takenWids[] = $village['wid'];
		    $villageTypes[] = $village['type'];
		    
		    //Add the village and its buildings		    
			$this->addVillage($village['wid'], $uid, $username, $village['capital'], $village['pop'], $village['name'], $village['natar']);
		}
		
        //Create tables for the just generated villages
		$this->addResourceFields($takenWids, $villageTypes, $buildingsArray);
		$this->setFieldTaken($takenWids);
		$this->addUnits($takenWids, $troopsArray);
		$this->addTech($takenWids);
		$this->addABTech($takenWids);

		return count($takenWids) > 1 ? $takenWids : $takenWids[0];
	}

    /**
	 * 
	 * Create a village
	 * 
	 * @param int $wid The village ID
	 * @param int $uid The User ID, the village's owner
	 * @param string $username The username
	 * @param int $capital 1 if it's a capital village, 0 otherwise
	 * @param int $pop The default village population
	 * @param string $villageName The default village name
	 * @return bool Returns true if the query was successful, false otherwise
	 */
	
	function addVillage($wid, $uid, $username, $capital, $pop = 2, $villageName = null, $isNatar = 0) {
	    list($wid, $uid, $username, $capital, $pop, $villageName, $isNatar) = $this->escape_input((int) $wid, (int) $uid, $username, (int) $capital, (int) $pop, $villageName, (int) $isNatar);

	    $total = count($this->getVillagesID($uid));
	    if($villageName == null) $villageName = $username."\'s village ".($total >= 1 ? $total + 1 : "");

		$time = time();
		$q = "INSERT into " . TB_PREFIX . "vdata (wref, owner, name, capital, pop, cp, celebration, wood, clay, iron, maxstore, crop, maxcrop, lastupdate, created, natar) values ($wid, $uid, '$villageName', $capital, $pop, 1, 0, 750, 750, 750, ".STORAGE_BASE.", 750, ".STORAGE_BASE.", $time, $time, $isNatar)";
		return mysqli_query($this->dblink,$q);
	}

	/**
	 * 
	 * Add the buildings tables to a specified village(s), and its relative buildings
	 * 
	 * @param mixed $vid The village ID(s)
	 * @param mixed $type int if there's only one village, array if there are multiple villages
	 * @param array $buildingsArray divided in two portion, which contains the types (unidimensional array) and the values of the
	 *              buildings that need to be added (bidimensional array)
	 * @return bool Return true if the query was successful, false otherwise
	 */
	
	function addResourceFields($vids, $types, $buildingsArray = null) {
	    list($vids, $types, $buildingsArray) = $this->escape_input($vids, $types, $buildingsArray);

	    if(!is_array($vids)){
	        $vids = [$vids];
	        $types = [$types];
	    }

	    //Set the default villages structure (resources fields and main building)
	    $defaultVillage = "vref,f1t,f2t,f3t,f4t,f5t,f6t,f7t,f8t,f9t,f10t,f11t,f12t,f13t,f14t,f15t,f16t,f17t,f18t"
	                       .($buildingsArray != null ? ",".implode(",",$buildingsArray[0]) : ",f26,f26t");
	    $defaultValues = [];
	    
		//Select the village type and assemble the building values
	    foreach($vids as $index => $vid){
	        $stringValues = "";
	        $stringValues .= "(".$vid.",";
	        switch($types[$index]) {            
	            case 1: $stringValues .= "4,4,1,4,4,2,3,4,4,3,3,4,4,1,4,2,1,2"; break;
	            case 2: $stringValues .= "3,4,1,3,2,2,3,4,4,3,3,4,4,1,4,2,1,2"; break;
	            case 3: $stringValues .= "1,4,1,3,2,2,3,4,4,3,3,4,4,1,4,2,1,2"; break;
	            case 4: $stringValues .= "1,4,1,2,2,2,3,4,4,3,3,4,4,1,4,2,1,2"; break;
	            case 5: $stringValues .= "1,4,1,3,1,2,3,4,4,3,3,4,4,1,4,2,1,2"; break;
	            case 6: $stringValues .= "4,4,1,3,4,4,4,4,4,4,4,4,4,4,4,2,4,4"; break;
	            case 7: $stringValues .= "1,4,4,1,2,2,3,4,4,3,3,4,4,1,4,2,1,2"; break;
	            case 8: $stringValues .= "3,4,4,1,2,2,3,4,4,3,3,4,4,1,4,2,1,2"; break;
	            case 9: $stringValues .= "3,4,4,1,1,2,3,4,4,3,3,4,4,1,4,2,1,2"; break;
	            case 10: $stringValues .= "3,4,1,2,2,2,3,4,4,3,3,4,4,1,4,2,1,2"; break;
	            case 11: $stringValues .= "3,1,1,3,1,4,4,3,3,2,2,3,1,4,4,2,4,4"; break;
	            case 12: $stringValues .= "1,4,1,1,2,2,3,4,4,3,3,4,4,1,4,2,1,2"; break;
	            default: $stringValues .= "4,4,1,4,4,2,3,4,4,3,3,4,4,1,4,2,1,2";
	        }
	        
	        $stringValues .= $buildingsArray != null ? ",".implode(",",$buildingsArray[1][$index]).")" : ",1,15)";
	        $defaultValues[] = $stringValues;
	    }
	
	    $q = "INSERT INTO " . TB_PREFIX . "fdata ($defaultVillage) values".implode(",",$defaultValues);
		return mysqli_query($this->dblink, $q);
	}

    function isVillageOases($wref, $use_cache = true) {
        // retirieve form cache
        return $this->getVillageByWorldID($wref, $use_cache)['oasistype'];
    }

	public function VillageOasisCount($vref, $use_cache = true) {
	    list($vref) = $this->escape_input((int) $vref);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$oasisCountCache, $vref)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT count(*) FROM `".TB_PREFIX."odata` WHERE conqured=". $vref;
		$result = mysqli_query($this->dblink,$q);
		$row = mysqli_fetch_row($result);

        self::$oasisCountCache[$vref] = $row[0];
        return self::$oasisCountCache[$vref];
	}

	/**
	 * Calculates the distance from a village to another
	 * 
	 * @param int $coorx1 X coordinate of the first village
	 * @param int $coory1 Y coordinate of the second village
	 * @param int $coorx2 X coordinate of the first village
	 * @param int $coory2 Y coordinate of the second village
	 * @return int Returns the calculated distance
	 */
	
	public function getDistance($coorx1, $coory1, $coorx2, $coory2) {
		$max = 2 * WORLD_MAX + 1;
		$x1 = intval($coorx1);
		$y1 = intval($coory1);
		$x2 = intval($coorx2);
		$y2 = intval($coory2);
		$distanceX = min(abs($x2 - $x1), abs($max - abs($x2 - $x1)));
		$distanceY = min(abs($y2 - $y1), abs($max - abs($y2 - $y1)));
		return round(sqrt(pow($distanceX, 2) + pow($distanceY, 2)), 1);
	}

    /***************************
	Function to retrieve type of village via ID
	References: Village ID
	***************************/
	function getVillageType($wref, $use_cache = true) {
        // retrieve this value from the full village data cache
        return $this->getVillageByWorldID($wref, $use_cache)['fieldtype'];
	}

	/*****************************************
	Function to retrieve if is occupied via ID
	References: Village ID
	*****************************************/
	function getVillageState($wref, $use_cache = true) {
        // retrieve this value from the full village data cache
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$villageFieldsCacheByWorldID, $wref)) && !is_null($cachedValue)) {
            return ($cachedValue['occupied'] != 0 || $cachedValue['oasistype'] != 0);
        } else {
            $vil = $this->getVillageByWorldID($wref, $use_cache);
            return ($vil['occupied'] != 0 || $vil['oasistype'] != 0);
        }
	}
	
	/**
	 * Get the first free village, if there's one
	 * 
	 * @param array $wids The village IDs
	 * @return int Returns the wid of the first free village, if they're all taken, returns 0
	 */
	
	function getFreeVillage($wids){
	    list($wids) = $this->escape_input($wids);
	    
	    if(!is_array($wids)) $wids = [$wids];
	    
	    $q = "SELECT id FROM ".TB_PREFIX."wdata WHERE id IN(".implode(",", $wids).") AND occupied = 0 AND oasistype = 0 LIMIT 1";
	    $result = mysqli_query($this->dblink, $q);
	    return mysqli_num_rows($result) > 0 ? mysqli_fetch_array($result)[0] : 0;
	}

	function getVillageID($uid, $use_cache = true) {
	    // load cached value
	    return $this->getVillagesID($uid, $use_cache)[0];
	}

	function getVillagesID($uid, $use_cache = true) {
	    list($uid) = $this->escape_input((int) $uid);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$villageIDsCache, $uid)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

        $array = $this->getProfileVillages($uid, 0, $use_cache);
		$newarray = array();

		for($i = 0; $i < count($array); $i++) {
			array_push($newarray, $array[$i]['wref']);
		}

		self::$villageIDsCache[$uid] = $newarray;
		return self::$villageIDsCache[$uid];
	}

	function getVillagesID2($uid, $use_cache = true) {
	    list($uid) = $this->escape_input((int) $uid);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$villageIDsCacheSimple, $uid)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

        $array = $this->getProfileVillages($uid, 0, $use_cache);
        self::$villageIDsCacheSimple[$uid] = $array;

        return self::$villageIDsCacheSimple[$uid];
	}

	function findAlreadyCachedVillageData($vid, $mode) {
        // check if we don't actually have this data cached already in one of the other modes
        for ($i = 0; $i <= 4; $i++) {
            if ($mode !== $i && isset(self::$villageFieldsCache[$vid.$i])) {
                // loop through cached values
                foreach (self::$villageFieldsCache[$vid.$i] as $index => $value) {
                    // check for existing record with our requested ID/name/owner...
                    switch ($mode) {
                        case 0: if ($value['wref'] == $vid) {
                                    return $value;
                                }
                                break;

                        case 1: if ($value['name'] == $vid) {
                                    return $value;
                                }
                                break;

                        case 2: if ($value['owner'] == $vid) {
                                    return $value;
                                }
                                break;

                        case 3: if ((isset($value['owner']) && isset($value['capital'])) && $value['owner'] == $vid && $value['capital'] == 1) {
                                    return $value;
                                }
                                break;

                        case 4: if ($value['owner'] == 4) {
                                    return $value;
                                }
                                break;
                    }
                }
            }
        }

        return false;
    }

	function getVillage($vid, $mode = 0, $use_cache = true) {
	    // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$villageFieldsCache, ((int) $vid).$mode)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

        if ($use_cache && ($altCachedContentSearch = $this->findAlreadyCachedVillageData($vid, $mode))) {
            return $altCachedContentSearch;
        }

        switch ($mode) {
            // by WREF
            case 0: $vid = (int) $vid;
                    $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE wref = $vid LIMIT 1";
                    break;

            // by name
            case 1: $name = $this->escape($vid);
                    $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE `name` = '$name' LIMIT 1";
                    break;

            // by owner ID
            case 2: $vid = (int) $vid;
                    $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE owner = $vid LIMIT 1";
                    break;

            // by owner ID and capital = 1
            case 3: $vid = (int) $vid;
                    $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE owner = $vid AND capital = 1 LIMIT 1";
                    break;

            // by owner = Taskmaster (Adaptado para Multihunter já que TaskMaster não tem vila criada no setup)
            case 4: $vid = (int) $vid;
                    $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE owner = 5 LIMIT 1";
                    break;
        }

		$result = mysqli_query($this->dblink,$q);

        self::$villageFieldsCache[$vid.$mode] = mysqli_fetch_array($result, MYSQLI_ASSOC);
        return self::$villageFieldsCache[$vid.$mode];
	}

    function getProfileVillages($uid, $mode = 0, $use_cache = true) {
        $arrayPassed = is_array($uid);

        if (!$arrayPassed) {
            $uid = [(int) $uid];
        } else {
            foreach ($uid as $index => $uidValue) {
                $uid[$index] = (int) $uidValue;
            }
        }

        if (!count($uid)) {
            return [];
        }

        // first of all, check if we should be using cache
        if ($use_cache && !$arrayPassed && ($cachedValue = self::returnCachedContent(self::$userVillagesCache, $uid[0].$mode)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

        // if we've given a number of villages to preload, remove those that already are
        if ($use_cache && $arrayPassed) {
            $newIDs = [];
            foreach ($uid as $id) {
                if (!isset(self::$userVillagesCache[$id.$mode])) {
                    $newIDs[] = $id;
                }
            }
            $uid = $newIDs;
        }

        // nothing left to cache, return the full cache
        if (!count($uid)) {
            return self::$userVillagesCache;
        }

        switch ($mode) {
            // by owner ID
            case 0: $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE owner IN(".implode(', ', $uid).") ORDER BY pop DESC";
                    break;

            // capital villages where owner is a real player (i.e. not Natars etc.)
            case 1: $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE capital = 1 and owner > 5";
                    break;

            // villages with starvation data
            case 2: $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE starv != 0 and owner != 3";
                    break;

            // field distance calculator query
            case 3: $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE owner > 4 and wref != ".$uid[0];
                    break;

            // villages in need of celebration data update
            case 4: $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE celebration < ".$uid[0]." AND celebration != 0";
                    break;

            // by vref ID
            case 5: $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE wref IN(".implode(', ', $uid).")";
                    break;

            // by loyalty updates required
            case 6: $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE loyalty < 100";
                    break;
                    
            // villages without starvation data, Support, Nature, Natars, Taskmaster, Multihunter are all excluded
            case 7: $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE starv = 0 and owner > 5";
                    break;
					
			case 8: $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE owner IN(".implode(', ', $uid).") ORDER BY name ASC";
                    break;
            	
			case 9: $q = "SELECT wref, owner FROM " . TB_PREFIX . "vdata WHERE wref IN(".implode(', ', $uid).")";
					break;
        }

        $result = mysqli_query($this->dblink,$q);

        if (!$arrayPassed) {
            $result                             = $this->mysqli_fetch_all($result);
            self::$userVillagesCache[ $uid[0].$mode ] = $result;

            // cache each village individually into the fields cache as well
            foreach ($result as $v) {
                $amode = 0;
                self::$villageFieldsCache[((int) $v['wref']).$amode] = $v;
            }
        } else {
            // we're preloading, cache all the data individually
            if (mysqli_num_rows($result)) {
                $amode = 0;
                while ( $row = mysqli_fetch_array( $result, MYSQLI_ASSOC ) ) {
                    if ( ! isset( self::$userVillagesCache[ $row['owner'].$mode ] ) ) {
                        self::$userVillagesCache[ $row['owner'].$mode ] = [];
                    }

                    self::$userVillagesCache[ $row['owner'].$mode ][] = $row;
                    self::$villageFieldsCache[((int) $row['wref']).$amode] = $row;
                    self::$villageFieldsCache2[(int) $row['wref']] = $row;
                }

                // just return the full cache if we've given an array of IDs to load villages for
                $result = self::$userVillagesCache;

                if ($mode == 9) {
                    $result = self::$villageFieldsCache2;
                }
            }
        }

        return $result;
    }

    function cacheVillageByWorldIDs($uid, $mode = 0) {
	    if (!is_array($uid)) {
	        $uid = [(int) $uid];
        } else {
	        foreach ($uid as $index => $uidValue) {
	            $uid[$index] = (int) $uidValue;
            }
        }

        $result = mysqli_query($this->dblink, "
          SELECT
            *
          FROM
            " . TB_PREFIX . "wdata as wdata
            LEFT JOIN " . TB_PREFIX . "vdata as vdata ON wdata.id = vdata.wref
          WHERE vdata.owner IN(".implode('', $uid).")"
        );

	    if (mysqli_num_rows($result)) {
	        $result = $this->mysqli_fetch_all($result);

	        $amode = 0;
	        foreach ($result as $row) {
                self::$villageFieldsCacheByWorldID[$row['id']] = $row;

                // cache village fields by wref as well, for future use
                if (!isset(self::$villageFieldsCache[((int) $row['wref']).$amode])) {
                    self::$villageFieldsCache[ ( (int) $row['wref'] ) . $amode ] = $row;
                }
            }
        }
    }
    
    function getVillageByWorldID($vid, $use_cache = true) {
        $array_passed = is_array($vid);

        if (!$array_passed) {
            $vid = [(int) $vid];
        } else {
            foreach ($vid as $index => $ivdValue) {
                $vid[$index] = (int) $ivdValue;
            }
        }

        if (!count($vid)) {
            return [];
        }

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && !$array_passed && isset(self::$villageFieldsCacheByWorldID[$vid[0]]) && is_array(self::$villageFieldsCacheByWorldID[$vid[0]]) && !count(self::$villageFieldsCacheByWorldID[$vid[0]])) {
            return self::$villageFieldsCacheByWorldID[$vid[0]];
        } else if ($use_cache && $array_passed) {
            // check what we can return from cache
            $newVIDs = [];
            foreach ($vid as $key) {
                if (!isset(self::$villageFieldsCacheByWorldID[$key])) {
                    $newVIDs [] = $key;
                }
            }

            // everything's cached, just return the cache
            if (!count($newVIDs)) {
                return self::$villageFieldsCacheByWorldID;
            } else {
                // update remaining IDs to select and cache
                $vid = $newVIDs;
            }
        } else if ($use_cache && !$array_passed && ($cachedValue = self::returnCachedContent(self::$villageFieldsCacheByWorldID, $vid[0])) && !is_null($cachedValue)) {
            return $cachedValue;
        }

        $q = "SELECT * FROM " . TB_PREFIX . "wdata where id IN(".implode(', ', $vid).")";
        $result = $this->mysqli_fetch_all(mysqli_query($this->dblink,$q));

        // return a single value
        if (!$array_passed) {
            self::$villageFieldsCacheByWorldID[$vid[0]] = $result[0];
        } else {
            if ($result && count($result)) {
                foreach ( $result as $record ) {
                    self::$villageFieldsCacheByWorldID[$record['id']] = $record;
                }
            }

            // check for any missing IDs and fill them in with blanks,
            // since no reinforcements were found for these villages
            foreach ($vid as $key) {
                if (!isset(self::$villageFieldsCacheByWorldID[$key])) {
                    self::$villageFieldsCacheByWorldID[$key] = [];
                }
            }
        }

        return ($array_passed ? self::$villageFieldsCacheByWorldID : self::$villageFieldsCacheByWorldID[$vid[0]]);
    }

    function getVillageField($ref, $field, $use_cache = true) {
        // return all data, don't waste time by selecting fields one by one
        $villageArray = $this->getVillage($ref, 0, $use_cache);
        $result = (isset($villageArray[$field]) ? $villageArray[$field] : null);

        if($result){
            // will return the result
        }elseif($field=="name"){
            $result = "[?]";
        }else $result = 0;

        return $result;
    }

    function getVillageFields($ref, $fields, $use_cache = true) {
        // return all data, don't waste time by selecting fields one by one
        return $this->getVillage($ref, 0, $use_cache);
    }

	function setVillageField($ref, $field, $value) {
	    if (!is_array($field)) {
	        $field = [$field];
	        $value = [$value];
        }

        $pairs = [];
	    foreach ($field as $index => $fieldValue) {
            $rawValue = $value[$index];
            $newValue = ((Math::isInt($value[$index]) || Math::isFloat($value[$index])) ? $value[$index] : '"'.$this->escape($value[$index]).'"');
	        $pairs[] = $this->escape($fieldValue).' = '.$newValue;

	        // update cache
	        if (isset(self::$villageFieldsCache[$ref])) {
                self::$villageFieldsCache[$ref][$fieldValue] = $rawValue;
            }
        }

		$q = "UPDATE " . TB_PREFIX . "vdata SET ".implode(', ', $pairs)." WHERE wref = ".(int) $ref;
		return mysqli_query($this->dblink,$q);
	}

    function setVillageFields($ref, $fields, $values) {
        list($ref, $fields, $values) = $this->escape_input((int) $ref, $fields, $values);

        if (!count($fields)) {
            return;
        }

        // build the field-value query parts
        $fieldValues = [];
        foreach ($fields as $id => $fieldName) {
            $rawValue = $values[$id];
            $newValue = ((Math::isInt($values[$id]) || Math::isFloat($values[$id])) ? $values[$id] : '"'.$this->escape($values[$id]).'"');
            $fieldValues[] = $this->escape($fieldName).' = '. $newValue;
            // update cache
	        if (isset(self::$villageFieldsCache[$ref])) {
                self::$villageFieldsCache[$ref][$fieldName] = $rawValue;
            }
        }

        $q = "UPDATE " . TB_PREFIX . "vdata set ".implode(', ', $fieldValues)." where wref = $ref";
        return mysqli_query($this->dblink,$q);
    }

	function setVillageLevel($ref, $fields, $values) {
	    list($ref, $fields, $values) = $this->escape_input((int) $ref, $fields, $values);

        // build the field-value query parts
        $fieldValues = [];

        if (!is_array($fields)) {
            $fields = [$fields];
            $values = [$values];
        }

        foreach ($fields as $id => $fieldName) {
            $rawValue = $values[$id];
            $newValue = ((Math::isInt($values[$id]) || Math::isFloat($values[$id])) ? $values[$id] : '"'.$this->escape($values[$id]).'"');
            $fieldValues[] = $this->escape($fieldName).' = '. $newValue;
            // update cache
	        if (isset(self::$resourceLevelsCache[$ref])) {
                self::$resourceLevelsCache[$ref][$fieldName] = $rawValue;
            }
        }

		$q = "UPDATE " . TB_PREFIX . "fdata set ".implode(', ', $fieldValues)." where vref = " . $ref;
		return mysqli_query($this->dblink,$q);
	}

	function cacheResourceLevels($vids) {
        if (!is_array($vids)) {
            $vids = [$vids];
        }

        $newVids = [];
	    foreach ($vids as $index => $vidValue) {
            $vids[ $index ] = (int) $vidValue;

            // don't cache what's cached
	        if (!isset(self::$resourceLevelsCache[$vids[ $index ]])) {
                $newVids[] = $vids[ $index ];
            }
        }
        $vids = $newVids;

	    if (!count($vids)) {
	        return [];
        }

        $q = "SELECT * FROM " . TB_PREFIX . "fdata WHERE vref IN(".implode(', ', $vids).")";
        $result = mysqli_query($this->dblink,$q);

        foreach ( $this->mysqli_fetch_all( $result ) as $row ) {
            self::$resourceLevelsCache[ $row['vref'] ] = $row;
        }

        return self::$resourceLevelsCache;
    }

	function getResourceLevel($vid, $use_cache = true) {
	    list($vid) = $this->escape_input((int) $vid);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$resourceLevelsCache, $vid)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * from " . TB_PREFIX . "fdata where vref = $vid";
		$result = mysqli_query($this->dblink,$q);

        self::$resourceLevelsCache[$vid] = mysqli_fetch_assoc($result);
        return self::$resourceLevelsCache[$vid];
	}

	public static function clearResourseLevelsCache($vid = null) {
        if ($vid !== null) {
            // Se um ID de vila for fornecido, limpa apenas o cache daquela vila
            unset(self::$resourceLevelsCache[$vid]);
            unset(self::$fieldLevelsInVillageSearchCache[$vid]);
            unset(self::$fieldLevelsCache[$vid]);
        } else {
            // Se nenhum ID for fornecido, limpa tudo (comportamento antigo)
            self::$resourceLevelsCache = [];
            self::$fieldLevelsInVillageSearchCache = [];
            self::$fieldLevelsCache = [];
        }
    }

    function getCoor($wref, $use_cache = true) {
	    // retirieve form cache
        return $this->getVillageByWorldID($wref, $use_cache);
	}

    /////////////ADDED BY BRAINIAC - THANK YOU
	function modifyResource($vid, $wood, $clay, $iron, $crop, $mode) {
	     list($vid, $wood, $clay, $iron, $crop, $mode) = $this->escape_input((int) $vid, $wood, $clay, $iron, $crop, $mode);
         $sign = (!$mode ? '-' : '+');

         $q = "
            UPDATE " . TB_PREFIX . "vdata
                SET
                    wood = IF(wood $sign $wood < 0, 0, IF(wood $sign $wood > maxstore, maxstore, wood $sign $wood)),
                    clay = IF(clay $sign $clay < 0, 0, IF(clay $sign $clay > maxstore, maxstore, clay $sign $clay)),
                    iron = IF(iron $sign $iron < 0, 0, IF(iron $sign $iron > maxstore, maxstore, iron $sign $iron)),
                    crop = IF(crop $sign $crop < 0, 0, IF(crop $sign $crop > maxcrop, maxcrop, crop $sign $crop))
                WHERE
                    wref = " . $vid ;
					
         return mysqli_query( $this->dblink, $q);
	}

    function updateResourceandStarvData($vid, $wood, $clay, $iron, $crop, $cropProd, $mode) {
	     list($vid, $wood, $clay, $iron, $crop, $cropProd, $mode) = $this->escape_input((int) $vid, $wood, $clay, $iron, $crop, $cropProd, $mode);
         $sign = (!$mode ? '-' : '+');
         $time = time();
         $starv = (float)$cropProd < 0 ? abs((float)$cropProd) : 0;

         $q = "
            UPDATE " . TB_PREFIX . "vdata
                SET
                    wood = IF(wood $sign $wood < 0, 0, IF(wood $sign $wood > maxstore, maxstore, wood $sign $wood)),
                    clay = IF(clay $sign $clay < 0, 0, IF(clay $sign $clay > maxstore, maxstore, clay $sign $clay)),
                    iron = IF(iron $sign $iron < 0, 0, IF(iron $sign $iron > maxstore, maxstore, iron $sign $iron)),
                    crop = IF(crop $sign $crop < 0, 0, IF(crop $sign $crop > maxcrop, maxcrop, crop $sign $crop)),
                    starv = " . $starv . ",
                    lastupdate = " . $time . ",
                    starvupdate = IF(" . ($starv == 0 ? "1" : "starvupdate = 0") . ", " . ($starv == 0 ? "0" : $time) . ", starvupdate)
                WHERE
                    wref = " . $vid ;
					
         return mysqli_query( $this->dblink, $q);
	}

   	function setMaxStoreForVillage($vid, $maxLevel) {
	    $vid = (int) $vid;
	    $maxLevel = (int) $maxLevel;

        $this->query("
                        UPDATE
                            ".TB_PREFIX."vdata
                        SET
                            `maxstore` = IF( `maxstore` - $maxLevel < ".STORAGE_BASE.", ".STORAGE_BASE.", `maxstore` - $maxLevel )
                        WHERE
                            wref=$vid");
    }

    function setMaxCropForVillage($vid, $maxLevel) {
        $vid = (int) $vid;
        $maxLevel = (int) $maxLevel;

        $this->query("
                        UPDATE
                            ".TB_PREFIX."vdata
                        SET
                            `maxcrop` = IF( `maxcrop` - $maxLevel < ".STORAGE_BASE.", ".STORAGE_BASE.", `maxcrop` - $maxLevel )
                        WHERE
                            wref=$vid");
    }

    function updateVSumField($field) {
        list($field) = $this->escape_input($field);

        //fix by ronix
        if (SPEED >10) {
            $speed = 10;
        } else {
            $speed = SPEED;
        }

        // cultural points to gain during a day
        $dur_day = (86400/SPEED);

        if ($dur_day < 3600) {
            $dur_day = 3600;
        }

        $q = "
            UPDATE " . TB_PREFIX . "users as users
                SET cp = cp + (
                        ( SELECT sum($field) FROM " . TB_PREFIX . "vdata as vdata WHERE vdata.owner = users.id ".($field == 'cp' ? ' AND vdata.natar = 0' : '')." ) *
                        (UNIX_TIMESTAMP() - lastupdate) / $dur_day
                    ),
                    lastupdate = UNIX_TIMESTAMP()
                WHERE
                    lastupdate < (UNIX_TIMESTAMP() - 600)
        "; // recount every 10 minutes

        mysqli_query($this->dblink, $q);
    }

    function getVSumField($uid, $field, $use_cache = true) {
        list($field) = $this->escape_input($field);

        $array_passed = is_array($uid);
        if (!$array_passed) {
            $uid = [(int) $uid];
        } else {
            foreach ($uid as $index => $uidValue) {
                $uid[$index] = (int) $uidValue;
            }
        }

        if (!count($uid)) {
            return [];
        }

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && !$array_passed && ($cachedValue = self::returnCachedContent(self::$userSumFieldCache, $uid[0].$field)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

        if($field != "cp"){
            $q = "SELECT owner, MIN(lastupdate), SUM(" . $field . ") as Total FROM " . TB_PREFIX . "vdata where owner IN(".implode(', ', $uid).") GROUP BY owner";
        }else{
            $q = "SELECT owner, MIN(lastupdate), SUM(" . $field . ") as Total FROM " . TB_PREFIX . "vdata where owner IN(".implode(', ', $uid).") and natar = 0 GROUP BY owner";
        }

        $result = mysqli_query($this->dblink,$q);

        // return a single value
        if (!$array_passed) {
            $row = mysqli_fetch_row( $result );
            self::$userSumFieldCache[$row[0].$field] = $row[2];
        } else {
            $result = $this->mysqli_fetch_all($result);
            if ($result && count($result)) {
                foreach ( $result as $record ) {
                    self::$userSumFieldCache[ $record['owner'] . $field ] = $record['Total'];
                }
            }
        }

        return ($array_passed ? $result : self::$userSumFieldCache[$uid[0].$field]);
    }

    function updateVillage($vid) {
        list($vid) = $this->escape_input((int) $vid);

        $time = time();
        $q = "UPDATE " . TB_PREFIX . "vdata set lastupdate = $time where wref = $vid";
        return mysqli_query($this->dblink,$q);
    }

    function setVillageName($vid, $name) {
        list($vid, $name) = $this->escape_input((int) $vid, $name);

        if(!empty($name)){
			$q = "UPDATE " . TB_PREFIX . "vdata set name = '$name' where wref = $vid";
			return mysqli_query($this->dblink, $q);
		}
    }

    function modifyPop($vid, $pop, $mode) {
        list($vid, $pop, $mode) = $this->escape_input((int) $vid, (int) $pop, $mode);

        if(!$mode) {
            $q = "UPDATE " . TB_PREFIX . "vdata set pop = pop + $pop where wref = $vid";
        } else {
            $q = "UPDATE " . TB_PREFIX . "vdata set pop = pop - $pop where wref = $vid";
        }
        return mysqli_query($this->dblink,$q);
    }

    function addCel($ref, $cel, $type) {
        list($ref, $cel, $type) = $this->escape_input((int) $ref, (int) $cel, (int) $type);

        $q = "UPDATE " . TB_PREFIX . "vdata set celebration = $cel, type= $type where wref = $ref";
        return mysqli_query($this->dblink,$q);
    }

    // no need to cache this method
    function getCel() {
        return $this->getProfileVillages(time(), 4);
    }

    function clearCel($ref) {
        list($ref) = $this->escape_input((int) $ref);

        $q = "UPDATE " . TB_PREFIX . "vdata set celebration = 0, type = 0 where wref = $ref";
        return mysqli_query($this->dblink,$q);
    }

    function setCelCp($user, $cp) {
        list($user, $cp) = $this->escape_input((int) $user, (int) $cp);

        $q = "UPDATE " . TB_PREFIX . "users set cp = cp + $cp where id = $user";
        return mysqli_query($this->dblink,$q);
    }

    /**
     * Delete a single village or multiple ones
     *
     * @param mixed $wref The Village ID(s)
     */
    function DelVillage($wref){
        list($wref) = $this->escape_input($wref);
        global $units;
        
        //Check if we've to delete a single village or multiple ones
        if(!is_array($wref)) $wref = [$wref];
        
        //Create the list of village IDs
        $wrefs = implode(", ", $wref);        

        mysqli_begin_transaction($this->dblink);
        try {
        
            $this->clearExpansionSlot($wref);
            $q = "DELETE FROM ".TB_PREFIX."abdata where vref IN($wrefs)";
            $this->query($q);
            $q = "DELETE FROM ".TB_PREFIX."bdata where wid IN($wrefs)";
            $this->query($q);
            $q = "DELETE FROM ".TB_PREFIX."market where vref IN($wrefs)";
            $this->query($q);
            $q = "DELETE FROM ".TB_PREFIX."research where vref IN($wrefs)";
            $this->query($q);
            $q = "DELETE FROM ".TB_PREFIX."tdata where vref IN($wrefs)";
            $this->query($q);
            $q = "DELETE FROM ".TB_PREFIX."fdata where vref IN($wrefs)";
            $this->query($q);
            $q = "DELETE FROM ".TB_PREFIX."training where vref IN($wrefs)";
            $this->query($q);
            $q = "DELETE FROM ".TB_PREFIX."units where vref IN($wrefs)";
            $this->query($q);
            $q = "DELETE FROM ".TB_PREFIX."farmlist where wref IN($wrefs)";
            $this->query($q);
            $q = "UPDATE ".TB_PREFIX."artefacts SET del = 1 where vref IN($wrefs)";
            $this->query($q);
            $q = "DELETE FROM ".TB_PREFIX."raidlist where towref IN($wrefs)";
            $this->query($q);
            $q = "DELETE FROM ".TB_PREFIX."route where wid IN($wrefs) OR `from` IN($wrefs)";
            $this->query($q);
            $q = "DELETE FROM ".TB_PREFIX."movement where proc = 0 AND ((`to` IN($wrefs) AND sort_type = 4) OR (`from` IN($wrefs) AND sort_type = 3))";
            $this->query($q);
            $this->removeOases($wref, 1);
            
            /*
            //RESPONSABILIDADE DELEGADA PARA ATTACKHANDLER
            $getmovement = $this->getMovement(3, $wref, 1);
            
            $moveIDs = [];
            $time = microtime(true);
            $types = [];
            $froms = [];
            $tos = [];
            $refs = [];
            $times = [];
            $endtimes = [];
            
            foreach($getmovement as $movedata) {
                $time2 = $time - $movedata['starttime'];
                $moveIDs[] = $movedata['moveid'];
                $types[] = 4;
                $froms[] = $movedata['to'];
                $tos[] = $movedata['from'];
                $refs[] = $movedata['ref'];
                $times[] = $time;
                $endtimes[] = $time+$time2;
            }
            
            $this->setMovementProc(implode(', ', $moveIDs));
            $this->addMovement($types, $froms, $tos, $refs, $times, $endtimes);
            */
            
            $q = "DELETE FROM ".TB_PREFIX."enforcement WHERE `from` IN($wrefs)";
            $this->query($q);
            
            //check return enforcement from del village
            foreach($wref as $w) $units->returnTroops($w);

            $q = "DELETE FROM ".TB_PREFIX."vdata WHERE `wref` IN($wrefs)";
            $this->query($q);
            
            if (mysqli_affected_rows($this->dblink) > 0) {
                $q = "UPDATE ".TB_PREFIX."wdata set occupied = 0 where id IN($wrefs)";
                $this->query($q);
                
                // clear expansion slots, if this village is an expansion of any other village
                $this->clearExpansionSlot($wref, 1);
                
                $getprisoners = $this->getPrisoners($wref);
                foreach($getprisoners as $pris) {
                    if (!isset($pris['id'])) continue;
                    $troops = 0;
                    for($i = 1; $i < 12; $i++) $troops += $pris['t'.$i];
                    $this->modifyUnit($pris['wref'], ["99o"], [$troops], [0]);
                    $this->deletePrisoners($pris['id']);
                }
                
                $getprisoners = $this->getPrisoners($wref, 1);
                foreach($getprisoners as $pris) {
                    if (!isset($pris['id'])) continue;
                    $troops = 0;
                    for($i = 1; $i < 12; $i++) $troops += $pris['t'.$i];
                    $this->modifyUnit($pris['wref'], ["99o"], [$troops], [0]);
                    $this->deletePrisoners($pris['id']);
                }
            }
            mysqli_commit($this->dblink); // Se tudo deu certo, confirma as alterações
        } catch (Exception $e) {
            mysqli_rollback($this->dblink); // Se algo deu errado, desfaz tudo
            // Opcional: Logar o erro $e->getMessage() para depuração
            // error_log("Falha ao deletar vila: " . $e->getMessage());
        }
    }

    /**
     * Clear the expansion slots of a specified village(s)
     * 
     * @param mixed $id The village ID(s)
     * @param number $mode 0 If there's the need to clear all expansion slots of a village,
     *        1 if there's the need to clear a single expansion slot of a village 
     */
    
    function clearExpansionSlot($id, $mode = 0) {
        list($id) = $this->escape_input((int) $id);

        if(!is_array($id)) $id = [$id];
        $ids = implode(",", $id);
        
        if(!$mode){ 
            $pairs = [];
            for($i = 1; $i <= 3; $i++) $pairs[] = 'exp'.$i.' = 0';
            
            $q = "UPDATE " . TB_PREFIX . "vdata SET ".implode(',', $pairs)." WHERE wref IN($ids)";
        }else{
            $q = "
                UPDATE
                    ".TB_PREFIX."vdata
                SET
                    exp1 = IF(exp1 IN($ids), 0, exp1),
                    exp2 = IF(exp2 IN($ids), 0, exp2),
                    exp3 = IF(exp3 IN($ids), 0, exp3)
                WHERE
                    exp1 IN($ids) OR
                    exp2 IN($ids) OR
                    exp3 IN($ids)";
        }
        mysqli_query($this->dblink, $q);
    }	

    function getVillageByName($name, $use_cache = true) {
        return $this->getVillage($name, 1, $use_cache)['wref'];
	}

    function getCropProdstarv($wref, $use_cache = true) {
	    global $bid4, $bid8, $bid9, $technology;

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$cropProductionStarvationValueCache, $wref)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

        $basecrop = $grainmill = $bakery = $cropo = 0;
		$owner = $this->getVrefField($wref, 'owner', $use_cache);
		$bonus = $this->getUserField($owner, 'b4', 0);

		$buildarray = $this->getResourceLevel($wref);
		$cropholder = [];
		for($i = 1; $i <= 38; $i++){
			if($buildarray['f'.$i.'t'] == 4) array_push($cropholder, 'f'.$i);
			if($buildarray['f'.$i.'t'] == 8) $grainmill = $buildarray['f'.$i];
			if($buildarray['f'.$i.'t'] == 9) $bakery = $buildarray['f'.$i];
		}
		
		$q = "SELECT type FROM `" . TB_PREFIX . "odata` WHERE conqured = ".(int) $wref;
		$oasis = $this->query_return($q);
		foreach($oasis as $oa){
			switch($oa['type']) {
                case 3:
                case 6:
                case 9:
                case 10:
                case 11:
                    $cropo++;
                    break;
                case 12:
                    $cropo += 2;
                    break;
			}
		}
		
        for($i = 0; $i <= count($cropholder) - 1; $i++){
			$basecrop += $bid4[$buildarray[$cropholder[$i]]]['prod'];
		}
		
		$crop = $basecrop + $basecrop * 0.25 * $cropo;
		
		if($grainmill >= 1 || $bakery >= 1){
			$crop += $basecrop / 100 * ((isset($bid8[$grainmill]['attri']) ? $bid8[$grainmill]['attri'] : 0) + (isset($bid9[$bakery]['attri']) ? $bid9[$bakery]['attri'] : 0));
		}
		if($bonus > time()) $crop *= 1.25;

		$crop *= SPEED;

        self::$cropProductionStarvationValueCache[$wref] = $crop;
        return self::$cropProductionStarvationValueCache[$wref];
	}

	/**
	 * Adds the starvation data in villages with a negative value of crop
	 *
	 * @param int $wref The village ID where the crop is negative
	 */
	
	public function addStarvationData($wref){
	    global $technology;
	    
		$isoasis = $this->isVillageOases($wref);
		if ($isoasis > 0) return;
		
	    $getVillage = $this->getVillage($wref);
		
		//Exlude Support, Nature, Natars, TaskMaster and Multihunter
		if ($getVillage['owner'] > 5){	        
			$crop = $this->getCropProdstarv($wref, false);
			$unitArrays = $technology->getAllUnits($wref, false, 0, false);
			$villageUpkeep = $getVillage['pop'] + $technology->getUpkeep($unitArrays, 0, $wref);
			$starv = $getVillage['starv'];
			
			if ($crop < $villageUpkeep){
				//Add starvation data
				$fields = ['starv'];
				$values = [$villageUpkeep];
				
				//Update the starvupdate if it's set to 0
				if($getVillage['starvupdate'] == 0) {
					$fields[] = 'starvupdate';
					$values[] = time();
				}

				//Update the starvation datas
				
			}else{
                $fields = ['starv'];
				$values = ['0'];
				
				$fields[] = 'starvupdate';
				$values[] = '0';
            }

            $this->setVillageFields($wref, $fields, $values);
		}
	}

    /**
     * Changed the actual capital with a new one
     * 
     * @param int $wref The village ID that will became the new capital
     * @return bool Return true if the query was successful, false otherwise
     */  
    function changeCapital($wref, $mode = 1){
    	list($wref, $mode) = $this->escape_input($wref, $mode);
    	
    	$q = "UPDATE ".TB_PREFIX."vdata SET capital = ".$mode." WHERE wref = $wref";
    	return mysqli_query($this->dblink, $q);
    }

    function setVillageEvasion($vid) {
        list($vid) = $this->escape_input($vid);

        $village = $this->getVillage((int) $vid);
		if($village['evasion'] == 0){
		$q = "UPDATE " . TB_PREFIX . "vdata SET evasion = 1 WHERE wref = $vid";
		}else{
		$q = "UPDATE " . TB_PREFIX . "vdata SET evasion = 0 WHERE wref = $vid";
		}
		return mysqli_query($this->dblink,$q);
	}

    function getMInfo($id, $use_cache = true) {
	    list($id) = $this->escape_input((int) $id);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$worldAndVillageDataCache, $id)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * FROM " . TB_PREFIX . "wdata left JOIN " . TB_PREFIX . "vdata ON " . TB_PREFIX . "vdata.wref = " . TB_PREFIX . "wdata.id where " . TB_PREFIX . "wdata.id = $id LIMIT 1";
		$result = mysqli_query($this->dblink,$q);

        self::$worldAndVillageDataCache[$id] = mysqli_fetch_array($result);
        return self::$worldAndVillageDataCache[$id];
	}

    function getVillageType2($wref) {
        // retirieve form cache
        return $this->getVillageByWorldID($wref, $use_cache)['oasistype'];
	}

	// no need to cache this method
	function checkVilExist($wref) {
	    list($wref) = $this->escape_input((int) $wref);

	    // first of all, check if this exists in our cache already - and if so, we don't need an extra query
        $mode = 0;
        if (isset(self::$villageFieldsCache[((int) $wref).$mode])) {
            return true;
        }

		$q = "SELECT Count(*) as Total FROM " . TB_PREFIX . "vdata where wref = '$wref'";
		$result = mysqli_fetch_array(mysqli_query($this->dblink,$q), MYSQLI_ASSOC);
		
		return $result['Total'];
	}	

    // no need to cache this method
	function getArrayMemberVillage($uid) {
	    list($uid) = $this->escape_input((int) $uid);
		$q = 'SELECT a.wref, a.name, b.x, b.y from '.TB_PREFIX.'vdata AS a left join '.TB_PREFIX.'wdata AS b ON b.id = a.wref where owner = '.$uid.' ORDER BY name ASC';
		$result = mysqli_query($this->dblink,$q);
		$array = $this->mysqli_fetch_all($result);
		return $array;
	}

}
