<?php

trait DBTroops {

    /**
	 * Add the unit table(s) and troops if presents
	 * 
	 * @param mixed $vid The villaged ID(s)
	 * @param array $troopsArray divided in two portion, which contains the types (unidimensional array) and the values of the
	 *              troops that need to be added (bidimensional array)
	 * @return bool Returns true if the query was successful, false otherwise
	 */
	
	function addUnits($vid, $troopsArray = null) {
	    list($vid) = $this->escape_input($vid);
	    
        if(empty($vid)) return;
	    if (!is_array($vid)) $vid = [$vid];
	    $types = $values = "";
	    
	    if($troopsArray != null){
	        $types = $troopsArray[0];
	        $values = $troopsArray[1];
	        
	        $types = ",u".implode(",u", $types);
	    }    
	    
	    foreach ($vid as $index => $vidValue) $vid[$index] = (int) $vidValue.($troopsArray != null ? ",".implode(",", $values[$index]) : "");

        $q = "INSERT into " . TB_PREFIX . "units (vref$types) values (".implode('),(', $vid).")";
		return mysqli_query($this->dblink,$q);
	}

	function getUnit($vid, $use_cache = true) {
	    $array_passed = is_array($vid);

        if (!$array_passed) {
            $singleVillage = true;
            $vid = [$vid];
        } else {
            foreach ($vid as $index => $vidValue) {
                $vid[$index] = (int) $vidValue;
            }
        }

        $returnArray = [];

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && !$array_passed && ($cachedValue = self::returnCachedContent(self::$unitsCache, (int) $vid[0])) && !is_null($cachedValue)) {
            return $cachedValue;
        } else if ($use_cache && $array_passed) {
            $newIDs = [];
            foreach ($vid as $villageID) {
                // don't cache what we don't need to cache
                if (isset(self::$unitsCache[$villageID])) {
                    $returnArray[$villageID] = self::$unitsCache[$villageID];
                } else {
                    // add the uncached ID, so we can select and cache it
                    $newIDs[] = $villageID;
                }
            }
            $vid = $newIDs;

            // nothing to cache? return what we have
            if (!count($vid)) {
                return $returnArray;
            }
        }

		$q = "SELECT * from " . TB_PREFIX . "units where vref IN(".implode(', ', $vid).")";
		$result = mysqli_query($this->dblink,$q);
		$resCount = 0;
		$vidCount = count($vid);

		if (!empty($result) && ($resCount = mysqli_num_rows($result)) && $resCount) {
		    while ($row = mysqli_fetch_assoc($result)) {
                self::$unitsCache[$row['vref']] = $row;
                $returnArray[$row['vref']] = $row;
            }
		} else {
		    // fill everything with nulls
		    foreach ($vid as $id) {
                self::$unitsCache[$id] = null;
                $returnArray[$id] = null;
            }
		}

		// check if we're not missing any return values
        if ($vidCount != $resCount) {
		    // fill-in the gaps, as it would mean some of the IDs we got were not found
            // (which is super-strange, but it's still a mathematical possibility)
            foreach ($vid as $id) {
                if (!isset($returnArray[$id])) {
                    $returnArray[$id] = null;
                }
            }
        }

		return (!isset($singleVillage) ? $returnArray : reset($returnArray));
	}

    // no need to cache this method
	function getUnitsNumber($vid, $mode = 1, $use_cache = false) {
        list( $vid ) = $this->escape_input( (int) $vid );

        $dbarray     = $this->getUnit( $vid );
        $totalunits  = 0;
        for ( $i = 1; $i <= 90; $i ++ ) {
            $totalunits += $dbarray[ 'u' . $i ];
        }
        
        $totalunits += $dbarray['hero'];
        if(!$mode) return $totalunits;
      
        $movingunits      = $this->getVillageMovement( $vid );
        $reinforcingunits = $this->getEnforceArray( $vid, 1 );
        $owner            = $this->getVillageField( $vid, "owner" );
        $ownertribe       = $this->getUserField( $owner, "tribe", 0 );
        $start            = ( $ownertribe - 1 ) * 10 + 1;
        $end              = ( $ownertribe * 10 );

        for ( $i = $start; $i <= $end; $i ++ ) {
            $totalunits += $movingunits[ 'u' . $i ];
            $totalunits += $reinforcingunits[ 'u' . $i ];
        }

        $totalunits += $movingunits['hero'];
        $totalunits += $reinforcingunits['hero'];

		return $totalunits;
	}

	function addTech($vid) {
        if(empty($vid)) return;
        if (!is_array($vid)) {
            $vid = [$vid];
        }

        foreach ($vid as $index => $vidValue) {
            $vid[$index] = (int) $vidValue;
        }

		$q = "INSERT INTO " . TB_PREFIX . "tdata (vref) VALUES (".implode('),(', $vid).")";
		return mysqli_query($this->dblink,$q);
	}

	function addABTech($vid) {
        if(empty($vid)) return;
        if (!is_array($vid)) {
            $vid = [$vid];
        }

        foreach ($vid as $index => $vidValue) {
            $vid[$index] = (int) $vidValue;
        }

        self::$abTechCache = [];
		$q = "INSERT INTO " . TB_PREFIX . "abdata (vref) VALUES (".implode('),(', $vid).")";
		return mysqli_query($this->dblink,$q);
	}

	function getABTech($vid, $use_cache = true) {
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
        if ($use_cache && !$array_passed && isset(self::$abTechCache[$vid[0]]) && is_array(self::$abTechCache[$vid[0]]) && !count(self::$abTechCache[$vid[0]])) {
            return self::$abTechCache[$vid[0]];
        } else if ($use_cache && $array_passed) {
            // check what we can return from cache
            $newVIDs = [];
            foreach ($vid as $key) {
                if (!isset(self::$abTechCache[$key])) {
                    $newVIDs [] = $key;
                }
            }

            // everything's cached, just return the cache
            if (!count($newVIDs)) {
                return self::$abTechCache;
            } else {
                // update remaining IDs to select and cache
                $vid = $newVIDs;
            }
        } else if ($use_cache && !$array_passed && ($cachedValue = self::returnCachedContent(self::$abTechCache, $vid[0])) && !is_null($cachedValue)) {
            // special case when we have empty arrays cached for this cache only
            return $cachedValue;
        }

		$q = "SELECT * FROM " . TB_PREFIX . "abdata where vref IN(".implode(', ', $vid).")";
		$result = $this->mysqli_fetch_all(mysqli_query($this->dblink,$q));

        // return a single value
        if (!$array_passed) {
            self::$abTechCache[$vid[0]] = $result[0];
        } else {
            if ($result && count($result)) {
                foreach ( $result as $record ) {
                    self::$abTechCache[ $record['vref']] = $record;
                }
            }

            // check for any missing IDs and fill them in with blanks,
            // since no reinforcements were found for these villages
            foreach ($vid as $key) {
                if (!isset(self::$abTechCache[$key])) {
                    self::$abTechCache[$key] = [];
                }
            }
        }

        return ($array_passed ? self::$abTechCache : self::$abTechCache[$vid[0]]);
	}

	function addResearch($vid, $tech, $time) {
	    list($vid, $tech, $time) = $this->escape_input((int) $vid, $tech, (int) $time);

        self::$researchingCache = [];
		$q = "INSERT into " . TB_PREFIX . "research values (0,$vid,'$tech',$time)";
		return mysqli_query($this->dblink,$q);
	}

	function getResearching($vid, $use_cache = true) {
        $vid = (int) $vid;

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && isset(self::$researchingCache[$vid]) && is_array(self::$researchingCache[$vid]) && !count(self::$researchingCache[$vid])) {
            return self::$researchingCache[$vid];
        } else if ($use_cache && ($cachedValue = self::returnCachedContent(self::$researchingCache, $vid)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * FROM " . TB_PREFIX . "research where vref = $vid ORDER BY timestamp ASC";
		$result = mysqli_query($this->dblink,$q);
        self::$researchingCache[$vid] = $this->mysqli_fetch_all($result);
        return self::$researchingCache[$vid];
	}

	function checkIfResearched($vref, $unit, $use_cache = true) {
	    list($vref, $unit) = $this->escape_input((int) $vref, $unit);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$isResearchedCache, $vref)) && !is_null($cachedValue)) {
            return $cachedValue[$unit];
        }

		$q = "SELECT * FROM " . TB_PREFIX . "tdata WHERE vref = $vref LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result, MYSQLI_ASSOC);

        self::$isResearchedCache[$vref] = $dbarray;
        return self::$isResearchedCache[$vref][$unit];
	}

	function getTech($vid) {
	    // this is a somewhat non-ideal, externally non-changeable way of caching
        // but since we're only ever going to be calling this from Village constructor
        // for our current village, this will more than suffice
	    static $cachedData = [];
	    $vid = (int) $vid;

	    if (isset($cachedData[$vid])) {
            return $cachedData[$vid];
        }

		$q = "SELECT * from " . TB_PREFIX . "tdata where vref = $vid";
		$result = mysqli_query($this->dblink,$q);
        $cachedData[$vid] = mysqli_fetch_assoc($result);

        return $cachedData[$vid];
	}

    // no need to cache this method
	function getTraining($vid) {
	    list($vid) = $this->escape_input((int) $vid);

		$q = "SELECT * FROM " . TB_PREFIX . "training where vref = $vid ORDER BY id";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

	function trainUnit($vid, $unit, $amt, $pop, $each, $mode) {
	    list($vid, $unit, $amt, $pop, $each, $mode) = $this->escape_input((int) $vid, (int) $unit, (int) $amt, (int) $pop, (int) $each, $mode);

		global $technology;

		if(!$mode) {

            $barracks = [1, 2, 3, 11, 12, 13, 14, 21, 22, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 51, 52, 53, 61, 62, 63, 71, 72, 73, 74, 81, 82, 83, 84];
            $greatbarracks = [1001, 1002, 1003, 1011, 1012, 1013, 1014, 1021, 1022, 1031, 1032, 1033, 1034, 1035, 1036, 1037, 1038, 1039, 1040, 1041, 1042, 1043, 1044, 1051, 1052, 1053, 1061, 1062, 1063, 1071, 1072, 1073, 1074, 1081, 1082, 1083, 1084];

            $stables = [4, 5, 6, 15, 16, 23, 24, 25, 26, 45, 46, 54, 55, 56, 64, 65, 66, 75, 76, 85, 86];
            $greatstables = [1004, 1005, 1006, 1015, 1016, 1023, 1024, 1025, 1026, 1045, 1046, 1054, 1055, 1056, 1064, 1065, 1066, 1075, 1076, 1085, 1086];

            $workshop = [7, 8, 17, 18, 27, 28, 47, 48, 57, 58, 67, 68, 77, 78, 87, 88];
            $greatworkshop = [1007, 1008, 1017, 1018, 1027, 1028, 1047, 1048, 1057, 1058, 1067, 1068, 1077, 1078, 1087, 1088];

            $residence = [9, 10, 19, 20, 29, 30, 49, 50, 59, 60, 69, 70, 79, 80, 89, 90];
            $trapper = [99];
            $healing = range(2001, 2090);

			if(in_array($unit, $barracks)) $queued = $technology->getTrainingList(1);	
		    elseif(in_array($unit, $stables)) $queued = $technology->getTrainingList(2);				
		    elseif(in_array($unit, $workshop)) $queued = $technology->getTrainingList(3);
	        elseif(in_array($unit, $residence)) $queued = $technology->getTrainingList(4);
	        elseif(in_array($unit, $greatbarracks)) $queued = $technology->getTrainingList(5);
            elseif(in_array($unit, $greatstables)) $queued = $technology->getTrainingList(6);	
			elseif(in_array($unit, $greatworkshop)) $queued = $technology->getTrainingList(7);
			elseif(in_array($unit, $trapper)) $queued = $technology->getTrainingList(8);
			elseif(in_array($unit, $healing)) $queued = $technology->getTrainingList(9);
		
			$now = time();
            $uid = $this->getVillageField($vid, "owner");
            $each = $this->getArtifactsValueInfluence($uid, $vid, 5, $each);
            
            $time2 = $now + $each;
            $time = $now + ($each * $amt);
            if(count($queued) > 0){
                $time  += $queued[count($queued) - 1]['timestamp'] - $now;
                $time2 += $queued[count($queued) - 1]['timestamp'] - $now;
            }            
            
			$q = "INSERT INTO " . TB_PREFIX . "training values (0, $vid, $unit, $amt, $pop, $time, $each, $time2)";
		} 
		else $q = "DELETE FROM " . TB_PREFIX . "training where id = $vid";
		
		return mysqli_query($this->dblink,$q);
	}

	function updateTraining($id, $trained, $each) {
	    list($id, $trained, $each) = $this->escape_input((int) $id, (int) $trained, (int) $each);

		$q = "UPDATE " . TB_PREFIX . "training set amt = amt - $trained, timestamp2 = timestamp2 + $each where id = $id";
		return mysqli_query($this->dblink,$q);
	}

	function updateWounded($vref, $wounds, $mode) {
		if (empty($wounds)) return;
		$vref = (int)$vref;
		$sets = [];
		foreach ($wounds as $pos => $count) {
			$count = (int)$count;
			if ($count <= 0 || $pos < 1 || $pos > 6) continue;
			$col = 'w'.$pos;
			$sets[] = ($mode == 0)
				? "$col = $col + $count"
				: "$col = GREATEST(0, $col - $count)";
		}
		if (empty($sets)) return;
		if ($mode == 0) {
			$q = "INSERT INTO ".TB_PREFIX."wounded (vref) VALUES ($vref)
				  ON DUPLICATE KEY UPDATE ".implode(', ', $sets);
		} else {
			$q = "UPDATE ".TB_PREFIX."wounded SET ".implode(', ', $sets)." WHERE vref = $vref";
		}
		return mysqli_query($this->dblink, $q);
	}

	function modifyUnit($vref, $array_unit, $array_amt, $array_mode) {
        if (!is_array($array_unit)) $array_unit = [$array_unit];
        if (!is_array($array_amt))  $array_amt  = [$array_amt];
        if (!is_array($array_mode)) $array_mode = [$array_mode];

	    list($vref, $array_unit, $array_amt, $array_mode) = $this->escape_input((int) $vref, $array_unit, $array_amt, $array_mode);
		$i = -1;
		$number = count($array_unit);
		$units='';
		
		foreach($array_unit as $unit){
			if($unit == "hero") $unit = 'hero';
			else if (substr($unit, 0, 1) !== 'u') {
                $unit = 'u' . $unit;
            }
            ++$i;
			
            //Fixed part of negative troops (double troops) - by InCube
            $array_amt[$i] = (int) $array_amt[$i] < 0 ? 0 : $array_amt[$i];
			
            //Fixed part of negative troops (double troops) - by InCube
            $units .= $unit.' = '.$unit.' '.(($array_mode[$i] == 1)? '+':'-').'  '.
            (($array_amt[$i]>0) ? 
            $array_amt[$i] : 0).(($number > $i+1) ? ', ' : '');
		}
		
		if ($units){
			$q = "UPDATE ".TB_PREFIX."units set $units WHERE vref = $vref";
			return mysqli_query($this->dblink, $q);
		}
		
	}

	function getEnforce($vid, $from, $use_cache = true) {
	    $array_passed = is_array($vid);
	    if (!$array_passed) {
	        $vid = [$vid];
	        $from = [$from];
        } else {
            foreach ($vid as $index => $vidValue) {
                $vid[$index] = (int) $vidValue;
                $from[$index] = (int) $from[$index];
            }
        }

        if (!count($vid)) {
            return [];
        }

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && !$array_passed && isset(self::$villageFromReinforcementsCache[$vid[0].$from[0]]) && is_array(self::$villageFromReinforcementsCache[$vid[0].$from[0]]) && !count(self::$villageFromReinforcementsCache[$vid[0].$from[0]])) {
            return self::$villageFromReinforcementsCache[$vid[0].$from[0]];
        }  else if ($use_cache && $array_passed) {
            // check what we can return from cache
            $newVIDs = [];
            $newFROMs = [];
            foreach ($vid as $index => $vidValue) {
                if (!isset(self::$villageFromReinforcementsCache[$vidValue.$from[$index]])) {
                    $newVIDs[] = $vidValue;
                    $newFROMs[] = $from[$index];
                }
            }

            // everything's cached, just return the cache
            if (!count($newVIDs)) {
                return self::$villageFromReinforcementsCache;
            } else {
                // update remaining IDs to select and cache
                $vid = $newVIDs;
                $from = $newFROMs;
            }
        } else if ($use_cache && !$array_passed && ($cachedValue = self::returnCachedContent(self::$villageFromReinforcementsCache, $vid[0].$from[0])) && !is_null($cachedValue)) {
            return $cachedValue;
        }
        
        // build SELECT pairs
        $pairs = [];
        foreach ($vid as $index => $vidValue) {
            $pairs[] = '(`from` = '.(int) $from[$index].' AND vref = '.(int) $vidValue.')';
        }

		$q = "SELECT * FROM " . TB_PREFIX . "enforcement WHERE ".implode(' OR ', $pairs);
		$result = $this->mysqli_fetch_all(mysqli_query($this->dblink,$q));

        // return a single value
        if (!$array_passed) {
            self::$villageFromReinforcementsCache[$vid[0].$from[0]] = $result[0];
        } else {
            if ($result && count($result)) {
                foreach ( $result as $record ) {
                    self::$villageFromReinforcementsCache[$record['vref'].$record['from']] = $record;
                }
            }

            // check for any missing IDs and fill them in with blanks,
            // since no reinforcements were found for these villages
            foreach ($vid as $index => $vidValue) {
                if (!isset(self::$villageFromReinforcementsCache[$vidValue.$from[$index]])) {
                    self::$villageFromReinforcementsCache[$vidValue.$from[$index]] = [];
                }
            }
        }

        return ($array_passed ? self::$villageFromReinforcementsCache : self::$villageFromReinforcementsCache[$vid[0].$from[0]]);
	}

    function getOasisEnforce($ref, $mode=0, $use_cache = true) {
        $array_passed = is_array($ref);
        $mode = (int) $mode;

        if (!$array_passed) {
            $ref = [(int) $ref];
        } else {
            foreach ($ref as $index => $refValue) {
                $ref[$index] = (int) $refValue;
            }
        }

        if (!count($ref)) {
            return [];
        }

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && !$array_passed && isset(self::$oasisReinforcementsCache[$ref[0].$mode]) && is_array(self::$oasisReinforcementsCache[$ref[0].$mode]) && !count(self::$oasisReinforcementsCache[$ref[0].$mode])) {
            return self::$oasisReinforcementsCache[$ref[0].$mode];
        } else if ($use_cache && $array_passed) {
            // check what we can return from cache
            $newREFs = [];
            foreach ($ref as $key) {
                if (!isset(self::$oasisReinforcementsCache[$key.$mode])) {
                    $newREFs [] = $key;
                }
            }

            // everything's cached, just return the cache
            if (!count($newREFs)) {
                return self::$oasisReinforcementsCache;
            } else {
                // update remaining IDs to select and cache
                $ref = $newREFs;
            }
        } else if ($use_cache && !$array_passed && ($cachedValue = self::returnCachedContent(self::$oasisReinforcementsCache, $ref[0].$mode)) && !is_null($cachedValue)) {
            // special case when we have empty arrays cached for this cache only
            return $cachedValue;
        }

        if (!$mode) {
            $q = "SELECT e.*,o.conqured FROM ".TB_PREFIX."enforcement as e LEFT JOIN ".TB_PREFIX."odata as o ON e.vref=o.wref where o.conqured IN(".implode(', ', $ref).") AND e.from NOT IN(".implode(', ', $ref).")";
        }else if ($mode == 1) {
            $q = "SELECT e.*,o.conqured FROM ".TB_PREFIX."enforcement as e LEFT JOIN ".TB_PREFIX."odata as o ON e.vref=o.wref where o.conqured IN(".implode(', ', $ref).")";
        } else if ($mode == 2) {
            $q = "SELECT e.*,o.conqured,o.wref,o.high, o.owner as ownero, v.owner as ownerv FROM ".TB_PREFIX."enforcement as e LEFT JOIN ".TB_PREFIX."odata as o ON e.vref=o.wref LEFT JOIN ".TB_PREFIX."vdata as v ON e.from=v.wref where o.conqured IN(".implode(', ', $ref).") AND o.owner<>v.owner";
        } else if ($mode == 3) {
            $q = "SELECT e.*,o.conqured,o.wref,o.high, o.owner as ownero, v.owner as ownerv FROM ".TB_PREFIX."enforcement as e LEFT JOIN ".TB_PREFIX."odata as o ON e.vref=o.wref LEFT JOIN ".TB_PREFIX."vdata as v ON e.from=v.wref where o.conqured IN(".implode(', ', $ref).") AND o.owner=v.owner";
        }
        $result = $this->mysqli_fetch_all(mysqli_query($this->dblink,$q));

        // return a single value
        if (!$array_passed) {
            self::$oasisReinforcementsCache[$ref[0].$mode] = $result;
        } else {
            if ($result && count($result)) {
                foreach ( $result as $record ) {
                    if ( ! isset( self::$oasisReinforcementsCache[ $record['conqured'] . $mode ] ) ) {
                        self::$oasisReinforcementsCache[ $record['conqured'] . $mode ] = [];
                    }

                    self::$oasisReinforcementsCache[ $record['conqured'] . $mode ][] = $record;
                }
            }

            // check for any missing IDs and fill them in with blanks,
            // since no reinforcements were found for these villages
            foreach ($ref as $key) {
                if (!isset(self::$oasisReinforcementsCache[$key.$mode])) {
                    self::$oasisReinforcementsCache[$key.$mode] = [];
                }
            }
        }

        return ($array_passed ? self::$oasisReinforcementsCache : self::$oasisReinforcementsCache[$ref[0].$mode]);
    }

    function getOasisEnforceArray($id, $mode=0, $use_cache = true) {
        list($id, $mode) = $this->escape_input((int) $id, $mode);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$oasisArrayReinforcementsCache, $id.$mode)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

        if (!$mode) {
            $q = "SELECT e.*,o.conqured FROM ".TB_PREFIX."enforcement as e LEFT JOIN ".TB_PREFIX."odata as o ON e.vref=o.wref where e.id = $id";
        }else{
            $q = "SELECT e.*,o.conqured FROM ".TB_PREFIX."enforcement as e LEFT JOIN ".TB_PREFIX."odata as o ON e.from=o.wref where e.id =$id";
        }
        $result = mysqli_query($this->dblink,$q);

        self::$oasisArrayReinforcementsCache[$id.$mode] = mysqli_fetch_assoc($result);
        return self::$oasisArrayReinforcementsCache[$id.$mode];
    }

	function addEnforce($data) {
        list($data) = $this->escape_input($data);

        $q = "INSERT into " . TB_PREFIX . "enforcement (vref,`from`) values (" . (int) $data['to'] . "," . (int) $data['from'] . ")";
		mysqli_query($this->dblink,$q);
		$id = mysqli_insert_id($this->dblink);
		$owntribe = $this->getUserField($this->getVillageField($data['from'], "owner"), "tribe", 0);
		$start = ($owntribe - 1) * 10 + 1;
		$end = ($owntribe * 10);
		//add unit
		$j = 1;
		$units = [];
		$amounts = [];
		$modes = [];

		for($i = $start; $i <= $end; $i++) {
		    $units[] = ($i < 0 ? 0 : $i);
		    $amounts[] = $data['t' . $j . ''];
		    $modes[] = 1;
			$j++;
		}

		// add hero
        $units[] = 'hero';
        $amounts[] = $data['t11'];
        $modes[] = 1;

		$this->modifyEnforce($id,$units, $amounts, $modes);
	}

	function addEnforce2($data,$tribe,$dead1,$dead2,$dead3,$dead4,$dead5,$dead6,$dead7,$dead8,$dead9,$dead10,$dead11) {
        list($data,$tribe,$dead1,$dead2,$dead3,$dead4,$dead5,$dead6,$dead7,$dead8,$dead9,$dead10,$dead11) = $this->escape_input($data,$tribe,$dead1,$dead2,$dead3,$dead4,$dead5,$dead6,$dead7,$dead8,$dead9,$dead10,$dead11);

        $q = "INSERT into " . TB_PREFIX . "enforcement (vref,`from`) values (" . (int) $data['to'] . "," . (int) $data['from'] . ")";
		mysqli_query($this->dblink,$q);
		$id = mysqli_insert_id($this->dblink);
		$owntribe = $this->getUserField($this->getVillageField($data['from'], "owner"), "tribe", 0);
		$start = ($owntribe - 1) * 10 + 1;
		$end = ($owntribe * 10);
		$start2 = ($tribe - 1) * 10 + 1;
		$start3 = ($tribe - 1) * 10;
		if($start3 == 0){
		    $start3 = "";
		}
		$end2 = ($tribe * 10);
		//add unit
		$j = 1;

        $units = [];
        $amounts = [];
        $modes = [];

		for($i = $start; $i <= $end; $i++) {
            $units[] = ($i < 0 ? 0 : $i);
            $amounts[] = $data['t' . $j . ''];
            $modes[] = 1;

            $units[] = ($i < 0 ? 0 : $i);
            $amounts[] = ${'dead'.$j};
            $modes[] = 0;

			$j++;
		}

        // process heroes
        $units[] = 'hero';
        $amounts[] = $data['t11'];
        $modes[] = 1;

        $units[] = 'hero';
        $amounts[] = $dead11;
        $modes[] = 0;

        $this->modifyEnforce($id,$units, $amounts, $modes);
	}

	function modifyEnforce($id, $unit, $amt, $mode) {
	    $id = (int) $id;

		// prepare pairing array, even if we're not passing arrays, so we can use the same logic
        $pairs = [];
		if (!is_array($unit)) {
		    $unit = [$unit];
		    $amt = [(int) $amt];
		    $mode = [(int) $mode];
        }

        foreach ($unit as $index => $unitType) {
            if ($unitType != 'hero' && substr($unitType, 0, 1) !== 'u') {
                $unitType = 'u' . $this->escape($unitType);
            } else {
                $unitType = $this->escape($unitType);
            }
		    $pairs[] = $unitType . ' = ' . $unitType . (!(int) $mode[$index] ? ' - ' : ' + ') . (int) $amt[$index];
        }

		$q = "UPDATE " . TB_PREFIX . "enforcement SET ".implode(', ', $pairs)." WHERE id = $id";
		mysqli_query($this->dblink,$q);

		// clear enforce cache
        self::$villageReinforcementsCache = [];
        self::$villageFromReinforcementsCache = [];
        self::$reinforcementsCache = [];
	}

	function getEnforceArray($id, $mode, $use_cache = true) {
	    list($id, $mode) = $this->escape_input((int) $id, $mode);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$reinforcementsCache, $id.$mode)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		if(!$mode) {
			$q = "SELECT * from " . TB_PREFIX . "enforcement where id = $id";
		} else {
			$q = "SELECT * from " . TB_PREFIX . "enforcement where `from` = $id";
		}
		$result = mysqli_query($this->dblink,$q);

        self::$reinforcementsCache[$id.$mode] = mysqli_fetch_assoc($result);
        return self::$reinforcementsCache[$id.$mode];
	}

    function getEnforceRatTask($id) {
	    list($id) = $this->escape_input((int) $id);

		$q = "SELECT * from " . TB_PREFIX . "enforcement where vref = $id and u31 > 0";
		$result = mysqli_query($this->dblink,$q);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            return true;
        }
        return false;
	}

	function getEnforceVillage($id, $mode, $use_cache = true) {
        $array_passed = is_array($id);
        $mode = (int) $mode;

        if (!$array_passed) {
            $id = [(int) $id];
        } else {
            foreach ($id as $index => $idValue) {
                $id[$index] = (int) $idValue;
            }
        }

        if (!count($id)) {
            return [];
        }

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && !$array_passed && isset(self::$villageReinforcementsCache[$id[0].$mode]) && is_array(self::$villageReinforcementsCache[$id[0].$mode]) && !count(self::$villageReinforcementsCache[$id[0].$mode])) {
            return self::$villageReinforcementsCache[$id[0].$mode];
        } else if ($use_cache && $array_passed) {
            // check what we can return from cache
            $newIDs = [];
            foreach ($id as $key) {
                if (!isset(self::$villageReinforcementsCache[$key.$mode])) {
                    $newIDs [] = $key;
                }
            }

            // everything's cached, just return the cache
            if (!count($newIDs)) {
                return self::$villageReinforcementsCache;
            } else {
                // update remaining IDs to select and cache
                $id = $newIDs;
            }
        } else if ($use_cache && !$array_passed && ($cachedValue = self::returnCachedContent(self::$villageReinforcementsCache, $id[0].$mode)) && !is_null($cachedValue)) {
            // special case when we have empty arrays cached for this cache only
            return $cachedValue;
        }

		if(!$mode) {
			$q = "SELECT * from " . TB_PREFIX . "enforcement where vref IN(".implode(', ', $id).")";
		} else if ($mode == 1) {
			$q = "SELECT * from " . TB_PREFIX . "enforcement where `from` IN(".implode(', ', $id).")";
		} else if ($mode == 2) {
            $q = "SELECT e.*, v.owner as ownerv, v1.owner as owner1 FROM ".TB_PREFIX."enforcement as e LEFT JOIN ".TB_PREFIX."vdata as v ON e.from=v.wref LEFT JOIN ".TB_PREFIX."vdata as v1 ON e.vref=v1.wref where e.vref IN(".implode(', ', $id).") AND v.owner<>v1.owner";
        } else if ($mode == 3) {
            $q = "SELECT e.*, v.owner as ownerv, v1.owner as owner1 FROM ".TB_PREFIX."enforcement as e LEFT JOIN ".TB_PREFIX."vdata as v ON e.from=v.wref LEFT JOIN ".TB_PREFIX."vdata as v1 ON e.vref=v1.wref where e.vref IN(".implode(', ', $id).") AND v.owner=v1.owner";
        } else if ($mode == 4) {
            $q = "SELECT e.*, v.owner as ownerv, v1.owner as owner1 FROM ".TB_PREFIX."enforcement as e LEFT JOIN ".TB_PREFIX."vdata as v ON e.from=v.wref LEFT JOIN ".TB_PREFIX."vdata as v1 ON e.vref=v1.wref where e.vref IN(".implode(', ', $id).") AND v.owner=v1.owner";
        }
		$result = $this->mysqli_fetch_all(mysqli_query($this->dblink,$q));

        // return a single value
        if (!$array_passed) {
            self::$villageReinforcementsCache[$id[0].$mode] = $result;
        } else {
            if ($result && count($result)) {
                foreach ( $result as $record ) {
                    if ( ! isset( self::$villageReinforcementsCache[ $record['vref'] . $mode ] ) ) {
                        self::$villageReinforcementsCache[ $record['vref'] . $mode ] = [];
                    }

                    self::$villageReinforcementsCache[ $record['vref'] . $mode ][] = $record;
                }
            }

            // check for any missing IDs and fill them in with blanks,
            // since no reinforcements were found for these villages
            foreach ($id as $key) {
                if (!isset(self::$villageReinforcementsCache[$key.$mode])) {
                    self::$villageReinforcementsCache[$key.$mode] = [];
                }
            }
        }

        return ($array_passed ? self::$villageReinforcementsCache : self::$villageReinforcementsCache[$id[0].$mode]);
	}

    // no need to cache this method
	function getVillageMovement($id) {
		$id = (int) $id;
		$prefix = TB_PREFIX;

		$vtribe = $this->getVillageOwnerTribe($id);
		$movingunits = [];

		$q = "(SELECT 'outgoing' AS _src, t1,t2,t3,t4,t5,t6,t7,t8,t9,t10,t11
			   FROM {$prefix}movement m, {$prefix}attacks a
			   WHERE m.`from` = $id AND m.ref = a.id AND m.proc = 0 AND m.sort_type = 3)
			  UNION ALL
			  (SELECT 'returning' AS _src, t1,t2,t3,t4,t5,t6,t7,t8,t9,t10,t11
			   FROM {$prefix}movement m, {$prefix}attacks a
			   WHERE m.`to` = $id AND m.ref = a.id AND m.proc = 0 AND m.sort_type = 4)
			  UNION ALL
			  (SELECT 'settler' AS _src, 0,0,0,0,0,0,0,0,0,0,0
			   FROM {$prefix}movement m
			   WHERE m.`from` = $id AND m.sort_type = 5 AND m.proc = 0)";

		$result = mysqli_query($this->dblink, $q);
		if ($result) {
			$settlerCount = 0;
			while ($row = mysqli_fetch_assoc($result)) {
				switch ($row['_src']) {
					case 'outgoing':
					case 'returning':
						for ($i = 1; $i <= 10; $i++) {
							$key = 'u' . (($vtribe - 1) * 10 + $i);
							if (!isset($movingunits[$key])) $movingunits[$key] = 0;
							$movingunits[$key] += (int)($row['t' . $i] ?? 0);
						}
						if (!isset($movingunits['hero'])) $movingunits['hero'] = 0;
						$movingunits['hero'] += (int)($row['t11'] ?? 0);
						break;

					case 'settler':
						$settlerCount++;
						break;
				}
			}
			if ($settlerCount > 0) {
				$key = 'u' . ($vtribe * 10);
				if (!isset($movingunits[$key])) $movingunits[$key] = 0;
				$movingunits[$key] += 3 * $settlerCount;
			}
		}
		return $movingunits;
	}

    function deleteReinf($id) {
	    list($id) = $this->escape_input((int) $id);

		$q = "DELETE from " . TB_PREFIX . "enforcement where id = '$id'";
		mysqli_query($this->dblink,$q);
		self::clearReinforcementsCache();
	}

    // no need to cache this method
	function getTrainingList() {
		$q = "SELECT * FROM " . TB_PREFIX . "training where vref IS NOT NULL";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

	public function getWoundedData($vref) {
		$vref = (int)$vref;

		$result = mysqli_fetch_assoc(mysqli_query($this->dblink,
			"SELECT * FROM ".TB_PREFIX."wounded WHERE vref = $vref"));

		$qRow = mysqli_fetch_assoc(mysqli_query($this->dblink,
			"SELECT
				SUM(CASE WHEN unit % 10 = 1 THEN amt ELSE 0 END) as h1,
				SUM(CASE WHEN unit % 10 = 2 THEN amt ELSE 0 END) as h2,
				SUM(CASE WHEN unit % 10 = 3 THEN amt ELSE 0 END) as h3,
				SUM(CASE WHEN unit % 10 = 4 THEN amt ELSE 0 END) as h4,
				SUM(CASE WHEN unit % 10 = 5 THEN amt ELSE 0 END) as h5,
				SUM(CASE WHEN unit % 10 = 6 THEN amt ELSE 0 END) as h6
			FROM ".TB_PREFIX."training
			WHERE vref = $vref AND unit > 2000"));

		$data = ['wounded' => [], 'inQueue' => []];
		for ($pos = 1; $pos <= 6; $pos++) {
			$data['wounded'][$pos] = (int)($result['w'.$pos] ?? 0);
			$data['inQueue'][$pos] = (int)($qRow['h'.$pos] ?? 0);
		}
		return $data;
	}

	public function woundedDecay() {
		$db = $this->dblink;

		$row = mysqli_fetch_assoc(mysqli_query($db, "SELECT lastWoundedDecay FROM ".TB_PREFIX."config"));
		$last = (int)$row['lastWoundedDecay'];

		if ($last > 0 && (time() - $last) < 86400) return false;

		mysqli_query($db, "UPDATE ".TB_PREFIX."wounded SET
			w1 = CEIL(w1 * 0.9),
			w2 = CEIL(w2 * 0.9),
			w3 = CEIL(w3 * 0.9),
			w4 = CEIL(w4 * 0.9),
			w5 = CEIL(w5 * 0.9),
			w6 = CEIL(w6 * 0.9)
			WHERE w1+w2+w3+w4+w5+w6 > 0");

		mysqli_query($db, "UPDATE ".TB_PREFIX."config SET lastWoundedDecay = ".time());
		return true;
	}

    // no need to cache, not used in any loops or more than once for each page load
	public function getAvailableExpansionTraining() {
		global $building, $session, $technology, $village;

		$vilData = $this->getVillage($village->wid);
		$maxslots = (($vilData['exp1'] == 0 ? 1 : 0) + ($vilData['exp2'] == 0 ? 1 : 0) + ($vilData['exp3'] == 0 ? 1 : 0));
		$residence = $building->getTypeLevel(25);
		$palace = $building->getTypeLevel(26);
        $comCenter = $building->getTypeLevel(44); 

		if($residence > 0) {
			$maxslots -= (3 - floor($residence / 10));
		}

		if($palace > 0) {
			$maxslots -= (3 - floor(($palace - 5) / 5));
		}

        if($comCenter > 0) {
			$maxslots -= (3 - floor(($comCenter - 5) / 5));
		}

		$q = "SELECT (u10+u20+u30+u60+u70+u80+u90) as R1, (u9+u19+u29+u59+u69+u79+u89) as R2 FROM " . TB_PREFIX . "units WHERE vref = ". (int) $village->wid;
		$result = mysqli_query($this->dblink,$q);
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		$settlers = $row['R1'];
		$chiefs = $row['R2'];

		$settlers += 3 * count($this->getMovement(5, $village->wid, 0));
		
		$current_movement = $this->getMovement(3, $village->wid, 0);
		if(!empty($current_movement)) {
			foreach($current_movement as $build) {
				$settlers += $build['t10'];
				$chiefs += $build['t9'];
			}
		}

		$current_movement = $this->getMovement(4, $village->wid, 1);
		if(!empty($current_movement)) {
			foreach($current_movement as $build) {
				$settlers += $build['t10'];
				$chiefs += $build['t9'];
			}
		}

		$q = "SELECT (u10+u20+u30) FROM " . TB_PREFIX . "enforcement WHERE `from` = ".(int) $village->wid;
		$result = mysqli_query($this->dblink,$q);
		$row = mysqli_fetch_row($result);
		if(!empty($row)) {
			foreach($row as $reinf) {
				$settlers += $reinf[0];
			}
		}

		$q = "SELECT (u9+u19+u29) FROM " . TB_PREFIX . "enforcement WHERE `from` = ".(int) $village->wid;
		$result = mysqli_query($this->dblink,$q);
		$row = mysqli_fetch_row($result);
		if(!empty($row)) {
			foreach($row as $reinf) {
				$chiefs += (int) $reinf[0];
			}
		}

		$trainlist = $technology->getTrainingList(4);
		if(!empty($trainlist)) {
			foreach($trainlist as $train) {
				if($train['unit'] % 10 == 0) {
					$settlers += $train['amt'];
				}
				if($train['unit'] % 10 == 9) {
					$chiefs += $train['amt'];
				}
			}
		}

		$trappedTroops = $this->getPrisoners($village->wid, 1);
		if(!empty($trappedTroops)){
		    foreach($trappedTroops as $trapped){
		        $settlers += $trapped['t10'];
		        $chiefs += $trapped['t9'];
		    }
		}

		$settlerslots = ($maxslots * 3) - ($chiefs * 3) - $settlers;
		$chiefslots = $maxslots - $chiefs - floor(($settlers + 2) / 3);

		if(!$technology->getTech(($session->tribe - 1) * 10 + 9)) {
			$chiefslots = 0;
		}

		return ["chiefs" => $chiefslots, "settlers" => $settlerslots];
	}

    public static function clearReinforcementsCache() {
	    self::$reinforcementsCache = [];
	    self::$villageReinforcementsCache = [];
	    self::$villageFromReinforcementsCache = [];
	    self::$oasisArrayReinforcementsCache = [];
	    self::$oasisReinforcementsCache = [];
	    self::clearUnitsCache();
    }

    public static function clearUnitsCache() {
        self::$unitsCache = [];
    }

}
