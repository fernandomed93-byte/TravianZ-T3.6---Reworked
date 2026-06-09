<?php

use App\Utils\Math;

trait DBHero {

	function getHero($uid=0, $all=0, $include_dead = false, $use_cache = true) {
	    list($uid,$all) = $this->escape_input((int) $uid,$all);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$heroCache, $uid.$all.($include_dead ? 1 : 0))) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		if ($all) {
			$q = "SELECT * FROM ".TB_PREFIX."hero WHERE uid=$uid ORDER BY lastupdate DESC";
		} elseif (!$uid) {
			$q = "SELECT * FROM ".TB_PREFIX."hero";
		} else {
			$q = "SELECT * FROM ".TB_PREFIX."hero WHERE ".($include_dead ? '' : "dead=0 AND ")."uid=$uid LIMIT 1";
		}

		$result = mysqli_query($this->dblink,$q);
		if (!empty($result)) {
            self::$heroCache[$uid.$all.($include_dead ? 1 : 0)] = $this->mysqli_fetch_all($result);
		} else {
            self::$heroCache[$uid.$all.($include_dead ? 1 : 0)] = null;
		}

		return self::$heroCache[$uid.$all.($include_dead ? 1 : 0)];
	}

	function getHeroField($uid,$field, $use_cache = true) {
	    list($uid,$field) = $this->escape_input((int) $uid,$field);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$heroFieldCache, $uid.$field)) && !is_null($cachedValue)) {
            return $cachedValue[$field];
        }

        $q = "SELECT * FROM ".TB_PREFIX."hero WHERE uid = $uid";
        $result = mysqli_query($this->dblink,$q);

        if (mysqli_num_rows($result) > 0) {
            // Se encontrou um herói, processa a primeira (e única) linha
            $heroData = mysqli_fetch_assoc($result);
            self::$heroFieldCache[$uid.$field] = $heroData;
            return $heroData[$field];
        } else {
            // Se NENHUM herói foi encontrado, armazena e retorna null para evitar erros
            self::$heroFieldCache[$uid.$field] = null;
            return null;
        }
	}

	function modifyHero($column,$value,$heroid,$mode=null) {
	    if (!is_array($column)) {
	        $column = [$column];
	        $value = [$value];
	        $mode = [$mode];
        }

        $pairs = [];
	    foreach ($column as $index => $columnValue) {
            if($mode[$index] === null) {
                $pairs[] = "$columnValue = ".(Math::isInt($value[$index]) ? $value[$index] : '"'.$this->escape($value[$index]).'"');
            } elseif($mode[$index] == 1) {
                $pairs[] = "$columnValue = $columnValue + ".(int) $value[$index];
            } else {
                $pairs[] = "$columnValue = $columnValue - ".(int) $value[$index];
            }
        }

        $q = "UPDATE `".TB_PREFIX."hero` SET ".implode(', ', $pairs)." WHERE heroid = $heroid";
		return mysqli_query($this->dblink,$q);
	}

	function modifyHeroXp($column,$value,$heroid) {
	    list($column,$value,$heroid) = $this->escape_input($column,(int) $value,(int) $heroid);

		$q = "UPDATE ".TB_PREFIX."hero SET $column = $column + $value WHERE heroid=$heroid";
		return mysqli_query($this->dblink,$q);
	}

    /***************************
	Function to Kill hero if not found
	Made by: Shadow and brainiacX
	***************************/
    function KillMyHero($id) {
        list( $id ) = $this->escape_input( (int) $id );

        $q = "UPDATE " . TB_PREFIX . "hero set dead = 1, intraining = 0, inrevive = 0, health = 0 where uid = " . $id . " AND dead = 0";
        return mysqli_query( $this->dblink, $q );
    }

    function KillHeroId($id) {
        list( $id ) = $this->escape_input( (int) $id );

        $q = "UPDATE " . TB_PREFIX . "hero set dead = 1, intraining = 0, inrevive = 0, health = 0 where heroid = " . $id . " AND dead = 0";
        return mysqli_query( $this->dblink, $q );
    }

	/***************************
    Function to find Hero place
    Made by: ronix
    ***************************/
    // no need to cache this method
    function FindHeroInVil($wid) {
        list($wid) = $this->escape_input($wid);

        $result = $this->query("SELECT hero FROM ".TB_PREFIX."units WHERE hero>0 AND vref='".$wid."' LIMIT 1");
        if (!empty($result)) {
            $dbarray = mysqli_fetch_array($result);
            if(isset($dbarray['hero'])) {
                $this->query("UPDATE ".TB_PREFIX."units SET hero=0 WHERE vref='".$wid."'");
                unset($dbarray);
                return true;
            }
        }
        return false;
    }

    // no need to cache this method
    function FindHeroInDef($wid) {
        list($wid) = $this->escape_input($wid);

            $delDef=true;
            $result = $this->query_return("SELECT * FROM ".TB_PREFIX."enforcement WHERE hero>0 AND `from` = ".$wid);
            if (!empty($result)) {
                $dbarray = $result;
                if(isset($dbarray['hero'])) {
                    $this->query("UPDATE ".TB_PREFIX."enforcement SET hero=0 WHERE `from` = ".$wid);
                    for ($i=0;$i<=90;$i++) {
                        if($dbarray['u'.$i]>0) {
                            $delDef=false;
                            break;
                        }
                    }
                    if ($delDef) $this->deleteReinf($wid);
                    unset($dbarray);
                    return true;
                }
            }
            return false;
    }

    // no need to cache this method
    function FindHeroInOasis($uid) {
        list($uid) = $this->escape_input($uid);

        $delDef=true;
        $dbarray = $this->query_return("SELECT e.*,o.conqured,o.owner FROM ".TB_PREFIX."enforcement as e LEFT JOIN ".TB_PREFIX."odata as o ON e.vref=o.wref where o.owner=".$uid." AND e.hero>0");
        if(!empty($dbarray)) {
            foreach($dbarray as $defoasis) {
                if($defoasis['hero']>0) {
                    $this->query("UPDATE ".TB_PREFIX."enforcement SET hero=0 WHERE `from` = ".$defoasis['from']);
                    for ($i=0;$i<=90;$i++) {
                        if($dbarray['u'.$i]>0) {
                            $delDef=false;
                            break;
                        }
                    }
                    if ($delDef) $this->deleteReinf($defoasis['from']);
                    unset($dbarray);
                    return true;
                }
            }
        }
        return 0;
    }

    // no need to cache this method
    function FindHeroInMovement($wid) {
        list($wid) = $this->escape_input($wid);

        $outgoingarray = $this->getMovement(3, $wid, 0);
        if(!empty($outgoingarray)) {
            foreach($outgoingarray as $out) {
                if ($out['t11']>0) {
                    $dbarray = $this->query("UPDATE ".TB_PREFIX."attacks SET t11=0 WHERE `id` = ".$out['ref']);
                    return true;
                    break;
                }
            }
        }
        $returningarray = $this->getMovement(4, $wid, 1);
        if(!empty($returningarray)) {
            foreach($returningarray as $ret) {
                if($ret['attack_type'] != 1 && $ret['t11']>0) {
                    $dbarray = $this->query("UPDATE ".TB_PREFIX."attacks SET t11=0 WHERE `id` = ".$ret['ref']);
                    return true;
                    break;
                }
            }
        }
        return false;
    }
    
    /**
     * Register the hero to the capital village and kills it
     * 
     * @param int $wref The village ID where the hero is registered
     * @return bool Return true if the query was successful, false otherwise
     */
    
    function reassignHero($wref){
    	list($wref) = $this->escape_input($wref);
    	
    	$q = "UPDATE 
					".TB_PREFIX."hero AS hero
			  INNER JOIN ".TB_PREFIX."vdata AS vdata
					ON vdata.owner = hero.uid AND vdata.capital = 1
		      SET 
					hero.dead = 1, hero.health = 0, hero.wref = vdata.wref
		      WHERE 
					hero.wref = $wref";
    	return mysqli_query($this->dblink, $q);
    }

    // no need to cache this method
	function getHeroRanking() {
		$q = "SELECT * FROM " . TB_PREFIX . "hero WHERE dead = 0";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

}
