<?php

trait DBOasis {

    public function countOasisTroops($vref, $use_cache = true) {
	    list($vref) = $this->escape_input((int) $vref);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$oasisTroopsCountCache, $vref)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		//count oasis troops: $troops_o
        $troops_o = 0;
        $o_unit   = $this->getUnit($vref, $use_cache);

        for ( $i = 1; $i < 91; $i ++ ) {
            $troops_o += $o_unit[ 'u'.$i ];
        }

        $troops_o += $o_unit['hero'];
        $o_unit2 = $this->getEnforceVillage($vref, 0, $use_cache);

        foreach ($o_unit2 as $o_unit) {
            for ( $i = 1; $i < 91; $i ++ ) {
                $troops_o += $o_unit[ 'u'.$i ];
            }

            $troops_o += $o_unit['hero'];
        }

        self::$oasisTroopsCountCache[$vref] = $troops_o;
            return self::$oasisTroopsCountCache[$vref];
	}

    public function canConquerOasis($vref, $wref, $use_cache = true) {
        list($vref,$wref) = $this->escape_input($vref,$wref);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$oasisConquerableCache, $vref.$wref)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

        $AttackerFields = $this->getResourceLevel( $vref, $use_cache );
        for ( $i = 19; $i <= 38; $i ++ ) {
            if ( $AttackerFields[ 'f' . $i . 't' ] == 37 ) {
                $HeroMansionLevel = $AttackerFields[ 'f' . $i ];
            }
        }
        if ( $this->VillageOasisCount( $vref ) < floor( ( $HeroMansionLevel - 5 ) / 5 ) ) {
            $OasisInfo = $this->getOasisInfo( $wref );
            //fix by ronix
            if (
                $OasisInfo['conqured'] == 0 ||
                $OasisInfo['conqured'] != 0 &&
                intval( $OasisInfo['loyalty'] ) < ( 99 / min(3, (4 - $this->VillageOasisCount($OasisInfo['conqured'], $use_cache))) )
            ) {
                $CoordsVillage = $this->getCoor( $vref );
                $CoordsOasis   = $this->getCoor( $wref );
                $max           = 2 * WORLD_MAX + 1;
                $x1            = intval( $CoordsOasis['x'] );
                $y1            = intval( $CoordsOasis['y'] );
                $x2            = intval( $CoordsVillage['x'] );
                $y2            = intval( $CoordsVillage['y'] );
                $distanceX     = min( abs( $x2 - $x1 ), abs( $max - abs( $x2 - $x1 ) ) );
                $distanceY     = min( abs( $y2 - $y1 ), abs( $max - abs( $y2 - $y1 ) ) );

                if ( $distanceX <= 3 && $distanceY <= 3 ) {
                    self::$oasisConquerableCache[ $vref . $wref ] = 1; //can
                } else {
                    self::$oasisConquerableCache[ $vref . $wref ] = 2; //can but not in 7x7 field
                }

            } else {
                self::$oasisConquerableCache[ $vref . $wref ] = 3; //loyalty >0
            }

        } else {
            self::$oasisConquerableCache[ $vref . $wref ] = 0; //req level hero mansion
        }

        return self::$oasisConquerableCache[ $vref . $wref ];
    }

	public function conquerOasis($vref,$wref) {
	    list($wref) = $this->escape_input((int) $wref);

		$vinfo = $this->getVillage($vref);
		$uid = (int) $vinfo['owner'];
		$q = "UPDATE `".TB_PREFIX."odata` SET conqured=".(int) $vref. ",loyalty=100,lastupdated=".time().",owner=$uid,name='Occupied Oasis' WHERE wref=".$wref;
		return mysqli_query($this->dblink,$q);
	}

    public function modifyOasisLoyalty($wref) {
        list($wref) = $this->escape_input((int) $wref);

        if($this->isVillageOases($wref) != 0) {
            $OasisInfo = $this->getOasisInfo($wref);
            if($OasisInfo['conqured'] != 0) {
                $LoyaltyAmendment = floor(100 / min(3,(4-$this->VillageOasisCount($OasisInfo['conqured']))));
                $q = "UPDATE `".TB_PREFIX."odata` SET loyalty=loyalty-$LoyaltyAmendment, lastupdated=".time()." WHERE wref=".$wref;
                $result=mysqli_query($this->dblink,$q);
                return $OasisInfo['loyalty']-$LoyaltyAmendment;
            }
        }
    }

	function regenerateOasisUnits($wid, $automation = false) {
	    global $autoprefix;

	    if (is_array($wid)) $wid = '(' . implode('),(', $wid) . ')';	        
	    else $wid = '(' . (int) $wid . ')';

	    // load the oasis regeneration (in-game) and units generation (during install) SQL file
	    // and replace village IDs for the given $wid
	    $str = file_get_contents($autoprefix."var/db/datagen-oasis-troops-regen.sql");
	    $str = preg_replace(["'%PREFIX%'", "'%VILLAGEID%'", "'%NATURE_REG_TIME%'"], [TB_PREFIX, $wid, ($automation ? NATURE_REGTIME : -1)], $str);
	    $result = $this->dblink->multi_query($str);

	    // fetch results of the multi-query in order to allow subsequent query() and multi_query() calls to work
	    while (mysqli_more_results($this->dblink) && mysqli_next_result($this->dblink)) {;}

	    if (!$result) return false;

	    return true;
	}

	/**
	 * Remove all oasis of a specified village if the mode is 1, if it's 0, then it'll remove only the selected oasis
	 *
	 * @param mixed $wref The village ID(s) (mode = 1)/oasis ID (mode = 0) of the oasis owner
	 * @return bool Returns true if the query was successful, false otherwise
	 */
	
	function removeOases($wref, $mode = 0) {
	    list($wref) = $this->escape_input((int) $wref);

	    if(!is_array($wref)) $wref = [$wref];
	    $wrefs = implode(",", $wref);
	    
		$q = "UPDATE ".TB_PREFIX."odata SET conqured = 0, owner = 2, name = 'Unoccupied Oasis' WHERE ".(!$mode ? "wref IN($wrefs)" : "conqured IN($wrefs)");
		return mysqli_query($this->dblink,$q);
	}

    function getOasisV($vid, $use_cache = true) {
	    list($vid) = $this->escape_input((int) $vid);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$oasisFieldsCache, $vid)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * FROM " . TB_PREFIX . "odata where wref = $vid LIMIT 1";
		$result = mysqli_query($this->dblink,$q);

        self::$oasisFieldsCache[$vid] = mysqli_fetch_array($result, MYSQLI_ASSOC);
        return self::$oasisFieldsCache[$vid];
	}

    function getOMInfo($id, $use_cache = true) {
	    list($id) = $this->escape_input((int) $id);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$worldAndOasisDataCache, $id)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * FROM " . TB_PREFIX . "wdata left JOIN " . TB_PREFIX . "odata ON " . TB_PREFIX . "odata.wref = " . TB_PREFIX . "wdata.id where " . TB_PREFIX . "wdata.id = $id LIMIT 1";
		$result = mysqli_query($this->dblink,$q);

        self::$worldAndOasisDataCache[$id] = mysqli_fetch_array($result);
        return self::$worldAndOasisDataCache[$id];
	}

	function getOasis($vid, $use_cache = true) {
	    list($vid) = $this->escape_input((int) $vid);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$oasisFieldsCacheByConqueredID, $vid)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * FROM " . TB_PREFIX . "odata where conqured = $vid";
		$result = mysqli_query($this->dblink,$q);

        self::$oasisFieldsCacheByConqueredID[$vid] = $this->mysqli_fetch_all($result);
        return self::$oasisFieldsCacheByConqueredID[$vid];
	}

	function getOasisInfo($wid, $use_cache = true) {
	    return $this->getOasisV($wid, $use_cache);
	}

    function getOasisField($ref, $field, $use_cache = true) {
        // return all data, don't waste time by selecting fields one by one
        $oasisArray = $this->getOasisV($ref, $use_cache);
        return (isset($oasisArray[$field]) ? $oasisArray[$field] : null);
	}

    function getOasisFields($ref, $use_cache = true) {
        // return all data, don't waste time by selecting fields one by one
        return $this->getOasisV($ref, $use_cache);
    }

    function modifyOasisResource($vid, $wood, $clay, $iron, $crop, $mode) {
	    list($vid, $wood, $clay, $iron, $crop, $mode) = $this->escape_input((int) $vid, (int) $wood, (int) $clay, (int) $iron, (int) $crop, $mode);

        $negativeResources = false;
        $checkres = $this->getOasisV($vid);

        if (!$mode) {
            $nwood = $checkres['wood'] - $wood;
            $nclay = $checkres['clay'] - $clay;
            $niron = $checkres['iron'] - $iron;
            $ncrop = $checkres['crop'] - $crop;

            $negativeResources = $nwood < 0 || $nclay < 0 || $niron < 0 || $ncrop < 0;

            $dwood = ($nwood < 0) ? 0 : $nwood;
            $dclay = ($nclay < 0) ? 0 : $nclay;
            $diron = ($niron < 0) ? 0 : $niron;
            $dcrop = ($ncrop < 0) ? 0 : $ncrop;
        } else {
            $nwood = $checkres['wood'] + $wood;
            $nclay = $checkres['clay'] + $clay;
            $niron = $checkres['iron'] + $iron;
            $ncrop = $checkres['crop'] + $crop;
            $dwood = ($nwood > $checkres['maxstore']) ? $checkres['maxstore'] : $nwood;
            $dclay = ($nclay > $checkres['maxstore']) ? $checkres['maxstore'] : $nclay;
            $diron = ($niron > $checkres['maxstore']) ? $checkres['maxstore'] : $niron;
            $dcrop = ($ncrop > $checkres['maxcrop']) ? $checkres['maxcrop'] : $ncrop;
        }

        if (!$negativeResources) {
            $q = "UPDATE " . TB_PREFIX . "odata SET wood = $dwood, clay = $dclay, iron = $diron, crop = $dcrop WHERE wref = ".$vid;
            return mysqli_query($this->dblink, $q);
        }
        else return false;     
   	}

    function updateOasis($vid) {
        list($vid) = $this->escape_input((int) $vid);

        $time = time();
        $q = "UPDATE " . TB_PREFIX . "odata set lastupdated = $time where wref = $vid";
        return mysqli_query($this->dblink,$q);
    }
    
    // no need to cache this method
	function checkOasisExist($wref) {
	    list($wref) = $this->escape_input((int) $wref);

		$q = "SELECT Count(*) as Total FROM " . TB_PREFIX . "odata where wref = '$wref'";
		$result = mysqli_fetch_array(mysqli_query($this->dblink,$q), MYSQLI_ASSOC);
		if($result['Total']) {
			return true;
		} else {
			return false;
		}
	}

}
