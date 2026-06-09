<?php

trait DBAlliance {

    function getAllianceName($id, $use_cache = true) {
        // return from cache
		if ($id && $id > 0){
			return $this->getAlliance($id, $use_cache)['tag'];
		}else{
			return "";
		}
	}

	// no need to cache this method
	function getAlliancePermission($ref, $field, $mode) {
        list($ref, $field, $mode) = $this->escape_input($ref, $field, $mode);

		if(!$mode) {
		    $q = "SELECT ali.$field FROM " . TB_PREFIX . "ali_permission as ali where uid = ". (int) $ref . " LIMIT 1";
		} else {
			$q = "SELECT ali.$field FROM " . TB_PREFIX . "ali_permission as ali where username = '$ref' LIMIT 1";
		}
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		return $dbarray[$field];
	}

	function getAlliance($id, $use_cache = true) {
	    $id = (int) $id;

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$allianceDataCache, $id)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * from " . TB_PREFIX . "alidata where id = $id";
		$result = mysqli_query($this->dblink,$q);

        self::$allianceDataCache[$id] = mysqli_fetch_assoc($result);
        return self::$allianceDataCache[$id];
	}

	function setAlliName($aid, $name, $tag) {
	    list($aid, $name, $tag) = $this->escape_input((int) $aid, $name, $tag);
        $name = $this->RemoveXSS($name);
        $tag = $this->RemoveXSS($tag);

		$q = "UPDATE " . TB_PREFIX . "alidata set name = '$name', tag = '$tag' where id = $aid";
		return mysqli_query($this->dblink,$q);
	}

    function isAllianceOwner($id, $use_cache = true) {
	    $id = (int) $id;

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$allianceOwnerCheckCache, $id)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT id from " . TB_PREFIX . "alidata where leader = ". $id;
		$result = mysqli_query($this->dblink,$q);
		if(mysqli_num_rows($result)) {
		    $result = mysqli_fetch_assoc($result);
			$result = $result['id'];
		} 
		else $result = false;

        self::$allianceOwnerCheckCache[$id] = $result;
        return self::$allianceOwnerCheckCache[$id];
	}

	function countAllianceMembers($aid, $use_cache = true) {
	    $aid = (int) $aid;

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$allianceMembersCountCache, $aid)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

	    $q = "SELECT Count(*) as Total from ".TB_PREFIX."users WHERE alliance = ".$aid;
	    $membersCount = $this->query_return($q);

        self::$allianceMembersCountCache[$aid] = $membersCount[0]['Total'];
        return self::$allianceMembersCountCache[$aid];
	}

    // no need to cache this method
	function aExist($ref, $type) {
        list($ref, $type) = $this->escape_input($ref, $type);

		$q = "SELECT $type FROM " . TB_PREFIX . "alidata where $type = '$ref'";
		$result = mysqli_query($this->dblink,$q);
		return mysqli_num_rows($result);
	}

	function modifyPoints($aid, $points, $amt) {
	    $aid = (int) $aid;

	    if (!is_array($points)) {
	        $points = [$points];
	        $amt    = [$amt];
        }

        $updates = [];
        foreach ($points as $index => $value) {
            $value = $this->escape($value);
	        $updates[] = $value.' = ' . $value . ' + ' . (int) $amt[$index];
        }

		$q = "UPDATE " . TB_PREFIX . "users SET ".implode(', ', $updates)." WHERE id = $aid";
		return mysqli_query($this->dblink,$q);
	}

	function modifyPointsAlly($aid, $points, $amt) {
        $aid = (int) $aid;

        if (!is_array($points)) {
            $points = [$points];
            $amt    = [$amt];
        }

        $updates = [];
        foreach ($points as $index => $value) {
            $value = $this->escape($value);
            $updates[] = $value.' = ' . $value . ' + ' . (int) $amt[$index];
        }

		$q = "UPDATE " . TB_PREFIX . "alidata SET ".implode(', ', $updates)." WHERE id = $aid";
		return mysqli_query($this->dblink,$q);
	}

	function createAlliance($tag, $name, $uid, $max) {
	    list($tag, $name, $uid, $max) = $this->escape_input($tag, $name, (int) $uid, (int) $max);
        $tag = $this->RemoveXSS($tag);
        $name = $this->RemoveXSS($name);

	    $q = "INSERT into " . TB_PREFIX . "alidata values (0,'$name','$tag',$uid,0,0,0,'','',$max,0,0,0,0,0,0,0,0,0)";
		mysqli_query($this->dblink,$q);
		return mysqli_insert_id($this->dblink);
	}

	function procAllyPop($aid) {
        list($aid) = $this->escape_input($aid);

		$ally = $this->getAlliance($aid);
        if (!$ally) return;
        
		$memberlist = $this->getAllMember($ally['id']);
		$oldrank = 0;
        $memberIDs = [];

        foreach($memberlist as $member) {
            $memberIDs[] = $member['id'];
        }

        $data = $this->getVSumField($memberIDs,"pop");

        if (count($data)) {
            foreach ($data as $row) {
                $oldrank += $row['Total'];
            }
        }

		if($ally['oldrank'] != $oldrank){
			if($ally['oldrank'] < $oldrank) {
				$totalpoints = $oldrank - $ally['oldrank'];
				$this->addclimberrankpopAlly($ally['id'], $totalpoints);
				$this->updateoldrankAlly($ally['id'], $oldrank);
			} else
				if($ally['oldrank'] > $oldrank) {
					$totalpoints = $ally['oldrank'] - $oldrank;
					$this->removeclimberrankpopAlly($ally['id'], $totalpoints);
					$this->updateoldrankAlly($ally['id'], $oldrank);
				}
		}
	}

	function insertAlliNotice($aid, $notice) {
        list($aid, $notice) = $this->escape_input($aid, $notice);

		$time = time();
		$q = "INSERT into " . TB_PREFIX . "ali_log values (0,'$aid','$notice',$time)";
		mysqli_query($this->dblink,$q);
		return mysqli_insert_id($this->dblink);
	}

    /*****************************************
	Function to delete alliance if empty
	References:
	*****************************************/
	function deleteAlliance($aid) {
	    list($aid) = $this->escape_input((int) $aid);

	    $result = mysqli_fetch_array(mysqli_query($this->dblink,"SELECT Count(*) as Total FROM " . TB_PREFIX . "users where alliance = $aid"), MYSQLI_ASSOC);
		if ($result['Total'] == 0) {
	        // remove the alliance
	        $q = "DELETE FROM " . TB_PREFIX . "alidata WHERE id = $aid";
	        mysqli_query($this->dblink,$q);

	        // remove all permissions for that alliance
	        $q = "DELETE FROM " . TB_PREFIX . "ali_permission WHERE alliance = $aid";
	        mysqli_query($this->dblink,$q);

	        // remove all logs for that alliance
	        $q = "DELETE FROM " . TB_PREFIX . "ali_log WHERE aid = $aid";
	        mysqli_query($this->dblink,$q);

	        // remove all medals for that alliance
	        $q = "DELETE FROM " . TB_PREFIX . "allimedal WHERE allyid = $aid";
	        mysqli_query($this->dblink,$q);

	        // remove all invitations for that alliance
	        $q = "DELETE FROM " . TB_PREFIX . "ali_invite WHERE alliance = $aid";
	        mysqli_query($this->dblink,$q);
	    }
	}

	/*****************************************
	Function to read all alliance news
	References:
	*****************************************/
	function readAlliNotice($aid) {
	    list($aid) = $this->escape_input((int) $aid);

		$q = "SELECT * from " . TB_PREFIX . "ali_log where aid = $aid ORDER BY date DESC";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

	/*****************************************
	Function to create alliance permissions
	References: ID, notice, description
	*****************************************/
	function createAlliPermissions($uid, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8) {
        list($uid, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8) = $this->escape_input($uid, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8);


		$q = "INSERT into " . TB_PREFIX . "ali_permission values(0,'$uid','$aid','$rank','$opt1','$opt2','$opt3','$opt4','$opt5','$opt6','$opt7','$opt8')";
		mysqli_query($this->dblink,$q);

		// update cache
        $insertID = mysqli_insert_id($this->dblink);
        self::$alliancePermissionsCache[$uid.$aid] = [
            'id' => $insertID,
            'uid' => $uid,
            'alliance' => $aid,
            'rank' => $rank,
            'opt1' => $opt1,
            'opt2' => $opt2,
            'opt3' => $opt3,
            'opt4' => $opt4,
            'opt5' => $opt5,
            'opt6' => $opt6,
            'opt7' => $opt7,
            'opt8' => $opt8
        ];

		return $insertID;
	}

	/*****************************************
	Function to update alliance permissions
	References:
	*****************************************/
	function deleteAlliPermissions($uid) {
        list($uid) = $this->escape_input($uid);

		$q = "DELETE from " . TB_PREFIX . "ali_permission where uid = '$uid'";
		return mysqli_query($this->dblink,$q);
	}
	/*****************************************
	Function to update alliance permissions
	References:
	*****************************************/
	function updateAlliPermissions($uid, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7) {
	    list($uid, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7) = $this->escape_input((int) $uid, (int) $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7);

        // update cache
        if (isset(self::$alliancePermissionsCache[$uid.$aid])) {
            self::$alliancePermissionsCache[ $uid . $aid ]['rank'] = $rank;
            self::$alliancePermissionsCache[ $uid . $aid ]['opt1'] = $opt1;
            self::$alliancePermissionsCache[ $uid . $aid ]['opt2'] = $opt2;
            self::$alliancePermissionsCache[ $uid . $aid ]['opt3'] = $opt3;
            self::$alliancePermissionsCache[ $uid . $aid ]['opt4'] = $opt4;
            self::$alliancePermissionsCache[ $uid . $aid ]['opt5'] = $opt5;
            self::$alliancePermissionsCache[ $uid . $aid ]['opt6'] = $opt6;
            self::$alliancePermissionsCache[ $uid . $aid ]['opt7'] = $opt7;
            self::$alliancePermissionsCache[ $uid . $aid ]['opt8'] = $opt8;
        }

		$q = "UPDATE " . TB_PREFIX . "ali_permission as alp
        SET alp.rank = '$rank', alp.opt1 = '$opt1', alp.opt2 = '$opt2', alp.opt3 = '$opt3', alp.opt4 = '$opt4', alp.opt5 = '$opt5', alp.opt6 = '$opt6', alp.opt7 = '$opt7' 
        where alp.uid = $uid && alp.alliance =$aid";
		return mysqli_query($this->dblink,$q);
	}

	/*****************************************
	Function to read alliance permissions
	References: ID, notice, description
	*****************************************/
	function getAlliPermissions($uid, $aid, $use_cache = true) {
	    list($uid, $aid) = $this->escape_input((int) $uid, (int) $aid);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$alliancePermissionsCache, $uid.$aid)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * FROM " . TB_PREFIX . "ali_permission where uid = $uid && alliance = $aid";
		$result = mysqli_query($this->dblink,$q);

        self::$alliancePermissionsCache[$uid.$aid] = mysqli_fetch_assoc($result);
        return self::$alliancePermissionsCache[$uid.$aid];
	}

	/*****************************************
	Function to update an alliance description and notice
	References: ID, notice, description
	*****************************************/
	function submitAlliProfile($aid, $notice, $desc) {
	    list($aid, $notice, $desc) = $this->escape_input((int) $aid, $notice, $desc);


		$q = "UPDATE " . TB_PREFIX . "alidata SET `notice` = '$notice', `desc` = '$desc' where id = $aid";
		return mysqli_query($this->dblink,$q);
	}

	function diplomacyInviteAdd($alli1, $alli2, $type) {
	    list($alli1, $alli2, $type) = $this->escape_input((int) $alli1, (int) $alli2, $type);

		$q = "INSERT INTO " . TB_PREFIX . "diplomacy (alli1,alli2,type,accepted) VALUES ($alli1,$alli2," . (int)intval($type) . ",0)";
		return mysqli_query($this->dblink,$q);
	}

	function diplomacyOwnOffers($sessionAlliance) {
	    list($sessionAlliance) = $this->escape_input((int) $sessionAlliance);

	    $q = "SELECT * FROM " . TB_PREFIX . "diplomacy WHERE alli1 = $sessionAlliance AND accepted = 0";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

    // no need to cache this method
	function getAllianceID($name) {
        list($name) = $this->escape_input($name);

		$q = "SELECT id FROM " . TB_PREFIX . "alidata WHERE tag ='" . $this->RemoveXSS($name) . "' LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		return $dbarray['id'];
	}

	function diplomacyCancelOffer($id, $sessionAlliance) {
	    list($id, $sessionAlliance) = $this->escape_input((int) $id, (int) $sessionAlliance);

		$q = "DELETE FROM " . TB_PREFIX . "diplomacy WHERE id = $id AND alli1 = $sessionAlliance";
		return mysqli_query($this->dblink,$q);
	}

	function diplomacyInviteAccept($id, $sessionAlliance) {
	    list($id, $sessionAlliance) = $this->escape_input((int) $id, (int) $sessionAlliance);

	    $q = "UPDATE " . TB_PREFIX . "diplomacy SET accepted = 1 WHERE id = $id AND alli2 = $sessionAlliance";
		return mysqli_query($this->dblink,$q);
	}

    function diplomacyInviteDenied($id, $sessionAlliance) {
	    list($id, $sessionAlliance) = $this->escape_input((int) $id, (int) $sessionAlliance);

	    $q = "DELETE FROM " . TB_PREFIX . "diplomacy WHERE id = $id AND alli2 = $sessionAlliance";
		return mysqli_query($this->dblink,$q);
	}

    // no need to cache this method
	function diplomacyInviteCheck($sessionAlliance) {
	    list($sessionAlliance) = $this->escape_input((int) $sessionAlliance);

	    $q = "SELECT * FROM " . TB_PREFIX . "diplomacy WHERE alli2 = $sessionAlliance AND accepted = 0";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

    // no need to cache this method
	function diplomacyInviteCheck2($ally1, $ally2) {
	    list($ally1, $ally2) = $this->escape_input((int) $ally1, (int) $ally2);

		$q = "SELECT * FROM " . TB_PREFIX . "diplomacy WHERE (alli1 = $ally1 OR alli2 = $ally1) AND (alli1 = $ally2 OR alli2 = $ally2)";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

    // no need to cache this method
	function getAllianceDipProfile($aid, $type) {
	    list($aid, $type) = $this->escape_input($aid, $type);
		$q = "SELECT alli1, alli2 FROM ".TB_PREFIX."diplomacy WHERE alli1 = '$aid' AND type = '$type' AND accepted = '1' OR alli2 = '$aid' AND type = '$type' AND accepted = '1'";
		$array = $this->query_return($q);
		$text = "";

		if($array){
			foreach($array as $row){
				if($row['alli1'] == $aid) $alliance = $this->getAlliance($row['alli2']);			
				elseif($row['alli2'] == $aid) $alliance = $this->getAlliance($row['alli1']);
				$text .= "";
				$text .= "<a href=allianz.php?aid=" . $alliance['id'] . ">" . $alliance['tag'] . "</a><br> ";
			}
		}
		if(strlen($text) == 0){
			$text = "-<br>";
		}
		return $text;
	}

    // no need to cache this method
	function getAllianceWar($aid) {
	    list($aid) = $this->escape_input($aid);
		$q = "SELECT alli1, alli2 FROM ".TB_PREFIX."diplomacy WHERE alli1 = '$aid' AND type = '3' OR alli2 = '$aid' AND type = '3' AND accepted = '1'";
		$array = $this->query_return($q);
        $text = "";

        if ($array) {
    		foreach($array as $row){
    			if($row['alli1'] == $aid){
    			$alliance = $this->getAlliance($row['alli2']);
    			}elseif($row['alli2'] == $aid){
    			$alliance = $this->getAlliance($row['alli1']);
    			}
    			$text .= "";
    			$text .= "<a href=allianz.php?aid=".$alliance['id'].">".$alliance['tag']."</a><br> ";
    		}
        }
		if(strlen($text) == 0){
			$text = "-<br>";
		}
		return $text;
	}

	function getAllianceAlly($aid, $type, $use_cache = true) {
	    list($aid, $type) = $this->escape_input($aid, $type);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$allianceAlliesCache, $aid.$type)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * FROM ".TB_PREFIX."diplomacy WHERE (alli1 = '$aid' or alli2 = '$aid') AND (type = '$type' AND accepted = '1')";
		$result = mysqli_query($this->dblink,$q);

        self::$allianceAlliesCache[$aid.$type] = $this->mysqli_fetch_all($result);
        return self::$allianceAlliesCache[$aid.$type];
	}

    // no need to cache this method
	function getAllianceWar2($aid) {
	    list($aid) = $this->escape_input($aid);
		$q = "SELECT * FROM ".TB_PREFIX."diplomacy WHERE alli1 = '$aid' AND type = '3' OR alli2 = '$aid' AND type = '3' AND accepted = '1'";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

    // no need to cache this method
	function diplomacyExistingRelationships($sessionAlliance) {
	    list($sessionAlliance) = $this->escape_input((int) $sessionAlliance);

	    $q = "SELECT * FROM " . TB_PREFIX . "diplomacy WHERE (alli1 = $sessionAlliance OR alli2 = $sessionAlliance) AND accepted = 1";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

	function diplomacyCancelExistingRelationship($id, $sessionAlliance) {
	    list($id, $sessionAlliance) = $this->escape_input((int) $id, (int) $sessionAlliance);

	    $q = "DELETE FROM " . TB_PREFIX . "diplomacy WHERE (alli1 = $sessionAlliance OR alli2 = $sessionAlliance) AND id = $id ";
		return mysqli_query($this->dblink,$q);
	}

    // no need to cache this method
	function diplomacyCheckLimits($aid, $type) {
	    list($aid, $type) = $this->escape_input((int) $aid, (int) $type);
	    if($type == 3) return true;
	    
		$q = "SELECT Count(case when alli1 = $aid then 1 end) as Total1, Count(case when alli2 = $aid then 1 end) as Total2 FROM " . TB_PREFIX . "diplomacy WHERE type = $type";
		$result = mysqli_fetch_array(mysqli_query($this->dblink,$q), MYSQLI_ASSOC);
		return $result['Total1'] < 3 && $result['Total2'] < 3;
	}

	function setAlliForumdblink($aid, $dblink) {
	    list($aid, $dblink) = $this->escape_input((int) $aid, $dblink);

		$q = "UPDATE " . TB_PREFIX . "alidata SET `forumlink` = '$dblink' WHERE id = $aid";
		return mysqli_query($this->dblink,$q);
	}

    // no need to cache this method
    function getInvitation($uid) {
        list($uid) = $this->escape_input((int) $uid);

        $q = "SELECT * FROM " . TB_PREFIX . "ali_invite where uid = $uid";
        $result = mysqli_query($this->dblink,$q);
        return $this->mysqli_fetch_all($result);
    }

    // no need to cache this method
    function getInvitation2($uid, $aid) {
        list($uid, $aid) = $this->escape_input((int) $uid, (int) $aid);

        $q = "SELECT * FROM " . TB_PREFIX . "ali_invite where uid = $uid and alliance = $aid";
        $result = mysqli_query($this->dblink,$q);
        return $this->mysqli_fetch_all($result);
    }

    // no need to cache this method
    function getAliInvitations($aid) {
        list($aid) = $this->escape_input((int) $aid);

        $q = "SELECT * FROM " . TB_PREFIX . "ali_invite where alliance = $aid && accept = 0";
        $result = mysqli_query($this->dblink,$q);
        return $this->mysqli_fetch_all($result);
    }

    function sendInvitation($uid, $alli, $sender) {
        list($uid, $alli, $sender) = $this->escape_input((int) $uid, (int) $alli, (int) $sender);

        $time = time();
        $q = "INSERT INTO " . TB_PREFIX . "ali_invite values (0,$uid,$alli,$sender,$time,0)";
        return mysqli_query($this->dblink,$q);
    }

    function removeInvitation($id) {
        list($id) = $this->escape_input((int) $id);

        $q = "DELETE FROM " . TB_PREFIX . "ali_invite where id = $id";
        return mysqli_query($this->dblink,$q);
    }

    function countAlli($use_cache = true) {
        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$allianceCountCache, 0)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT count(id) FROM " . TB_PREFIX . "alidata where id != 0";
		$result = mysqli_query($this->dblink,$q);
		$row = mysqli_fetch_row($result);

        self::$allianceCountCache[0] = $row[0];
        return self::$allianceCountCache[0];
	}

	function getAllMember($aid, $limit = 0, $use_cache = true) {
	    list($aid) = $this->escape_input((int) $aid);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$allianceMembersCache, $aid)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT * FROM " . TB_PREFIX . "users where alliance = $aid order  by (SELECT sum(pop) FROM " . TB_PREFIX . "vdata WHERE owner =  " . TB_PREFIX . "users.id) desc, " . TB_PREFIX . "users.id desc".($limit > 0 ? ' LIMIT '.(int) $limit : '');
		$result = mysqli_query($this->dblink,$q);

        self::$allianceMembersCache[$aid] = $this->mysqli_fetch_all($result);
        return self::$allianceMembersCache[$aid];
	}

	function getAllMember2($aid) {
	    return $this->getAllMember($aid, 1);
	}

}
