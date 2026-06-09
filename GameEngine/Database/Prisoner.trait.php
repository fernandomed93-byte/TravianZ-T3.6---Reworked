<?php

trait DBPrisoner {

    function addPrisoners($wid,$from,$t1,$t2,$t3,$t4,$t5,$t6,$t7,$t8,$t9,$t10,$t11) {
	    list($wid,$from,$t1,$t2,$t3,$t4,$t5,$t6,$t7,$t8,$t9,$t10,$t11) = $this->escape_input((int) $wid,(int) $from,(int) $t1,(int) $t2,(int) $t3,(int) $t4,(int) $t5,(int) $t6,(int) $t7,(int) $t8,(int) $t9,(int) $t10,(int) $t11);

		$q = "INSERT INTO " . TB_PREFIX . "prisoners values (0,$wid,$from,$t1,$t2,$t3,$t4,$t5,$t6,$t7,$t8,$t9,$t10,$t11)";
		mysqli_query($this->dblink,$q);
		self::$prisonersCache = [];
		return mysqli_insert_id($this->dblink);
	}

	function updatePrisoners($wid,$from,$t1,$t2,$t3,$t4,$t5,$t6,$t7,$t8,$t9,$t10,$t11) {
	    list($wid,$from,$t1,$t2,$t3,$t4,$t5,$t6,$t7,$t8,$t9,$t10,$t11) = $this->escape_input((int) $wid,(int) $from,(int) $t1,(int) $t2,(int) $t3,(int) $t4,(int) $t5,(int) $t6,(int) $t7,(int) $t8,(int) $t9,(int) $t10,(int) $t11);

        $q = "UPDATE " . TB_PREFIX . "prisoners set t1 = t1 + $t1, t2 = t2 + $t2, t3 = t3 + $t3, t4 = t4 + $t4, t5 = t5 + $t5, t6 = t6 + $t6, t7 = t7 + $t7, t8 = t8 + $t8, t9 = t9 + $t9, t10 = t10 + $t10, t11 = t11 + $t11 where wref = $wid and ".TB_PREFIX."prisoners.from = $from";
        $res = mysqli_query($this->dblink,$q);
        self::$prisonersCache = [];
        return $res;
    }

    /**
     * Used to modify prisoners through the inserted id
     * 
     * @param int $id The prisoner id where prisoners are in the database
     * @param int $unit The type of the unit
     * @param int $amount The amount of the unit you want to sum/subtract
     * @param int $mode 0 for subtracting the inserted amount, 1 for adding it
     * @return bool Returns false on failure and true on success 
     */
    
    function modifyPrisoners($id, $units, $amount, $mode) {
        list($id, $units, $amount, $mode) = $this->escape_input((int) $id, $units, $amount,(int) $mode);
        
        if (!is_array($units))
        {
            $units = [$units];
            $amount = [$amount];
        }
               
        $prisoners = [];
        foreach($units as $index => $unit) 
        {
            $unit = 't'.$this->escape($unit);
            $prisoners[] = $unit." = ".$unit.(!$mode ? " - " : " + ").(int)$amount[$index];
        }
        
        $q = "UPDATE " . TB_PREFIX . "prisoners set ".implode(', ', $prisoners)." WHERE id = $id"; 
        return mysqli_query($this->dblink,$q);
    }

    function getPrisoners($wid, $mode = 0, $use_cache = true) {
        $array_passed = is_array($wid);
        $mode = (int) $mode;

        if (!$array_passed) {
            $wid = [(int) $wid];
        } else {
            foreach ($wid as $index => $widValue) {
                $wid[$index] = (int) $widValue;
            }
        }

        if (!count($wid)) {
            return [];
        }

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && !$array_passed && isset(self::$prisonersCache[$wid[0].$mode]) && is_array(self::$prisonersCache[$wid[0].$mode]) && !count(self::$prisonersCache[$wid[0].$mode])) {
            return self::$prisonersCache[$wid[0].$mode];
        } else if ($use_cache && $array_passed) {
            // check what we can return from cache
            $newWIDs = [];
            foreach ($wid as $key) {
                if (!isset(self::$prisonersCache[$key.$mode])) {
                    $newWIDs [] = $key;
                }
            }

            // everything's cached, just return the cache
            if (!count($newWIDs)) {
                return self::$prisonersCache;
            } else {
                // update remaining IDs to select and cache
                $wid = $newWIDs;
            }
        } else if ($use_cache && !$array_passed && ($cachedValue = self::returnCachedContent(self::$prisonersCache, $wid[0].$mode)) && !is_null($cachedValue)) {
            // special case when we have empty arrays cached for this cache only
            return $cachedValue;
        }

        if(!$mode) {
            $q = "SELECT * FROM " . TB_PREFIX . "prisoners where wref IN(".implode(', ', $wid).")";
        }else {
            $q = "SELECT * FROM " . TB_PREFIX . "prisoners where `from` IN(".implode(', ', $wid).")";
        }
        $result = $this->mysqli_fetch_all(mysqli_query($this->dblink,$q));

        // return a single value
        if (!$array_passed) {
            if (count($result) == 1) {
                $result = $result[0];
            }
            self::$prisonersCache[$wid[0].$mode] = (count($result) ? [$result] : []);
        } else {
            if ($result && count($result)) {
                if (!isset(self::$prisonersCache[$record[($mode ? 'from' : 'wref')].$mode])) {
                    self::$prisonersCache[$record[($mode ? 'from' : 'wref' )].$mode] = [];
                }

                foreach ($result as $record) {
                    self::$prisonersCache[$record[($mode ? 'from' : 'wref')].$mode][] = $record;
                }
            }

            // check for any missing IDs and fill them in with blanks,
            // since no reinforcements were found for these villages
            foreach ($wid as $key) {
                if (!isset(self::$prisonersCache[$key.$mode])) {
                    self::$prisonersCache[$key.$mode] = [];
                }
            }
        }

        return ($array_passed ? self::$prisonersCache : self::$prisonersCache[$wid[0].$mode]);
    }

	function getPrisoners2($wid,$from, $use_cache = true) {
	    list($wid,$from) = $this->escape_input((int) $wid,(int) $from);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$prisonersCacheByVillageAndFromIDs, $wid.$from)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * FROM " . TB_PREFIX . "prisoners where wref = $wid and " . TB_PREFIX . "prisoners.from = $from";
		$result = mysqli_query($this->dblink,$q);

        self::$prisonersCacheByVillageAndFromIDs[$wid.$from] = $this->mysqli_fetch_all($result);
        return self::$prisonersCacheByVillageAndFromIDs[$wid.$from];
	}

	function getPrisonersByID($id, $use_cache = true) {
	    list($id) = $this->escape_input((int) $id);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$prisonersCacheByID, $id)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * FROM " . TB_PREFIX . "prisoners where id = $id LIMIT 1";
		$result = mysqli_query($this->dblink,$q);

        self::$prisonersCacheByID[$id] = mysqli_fetch_array($result);
        return self::$prisonersCacheByID[$id];
	}

	function getPrisoners3($from, $use_cache = true) {
	    list($from) = $this->escape_input((int) $from);
	    
	    // first of all, check if we should be using cache and whether the field
	    // required is already cached
	    if ($use_cache && ($cachedValue = self::returnCachedContent(self::$prisonersCacheByFromID, $from)) && !is_null($cachedValue)) {
	        return $cachedValue;
	    }
	    
	    $q = "SELECT * FROM " . TB_PREFIX . "prisoners where " . TB_PREFIX . "prisoners.from = $from";
	    $result = mysqli_query($this->dblink,$q);
	    
	    self::$prisonersCacheByFromID[$from] = $this->mysqli_fetch_all($result);
	    return self::$prisonersCacheByFromID[$from];
	}

	function deletePrisoners($id) {
        if (!is_array($id)) {
            $id = [$id];
        }

        foreach ($id as $index => $idValue) {
            $id[$index] = (int) $idValue;
        }

		$q = "DELETE FROM " . TB_PREFIX . "prisoners WHERE id IN(".implode(', ', $id).")";
		mysqli_query($this->dblink,$q);

		self::$prisonersCache = [];
	}

}
