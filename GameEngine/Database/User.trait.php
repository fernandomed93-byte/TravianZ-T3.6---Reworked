<?php

use App\Utils\Math;

trait DBUser {

    function register($username, $password, $email, $tribe, $act, $uid = 0, $desc = null) {
        list($username, $password, $email, $tribe, $act, $uid, $desc) = $this->escape_input($username, $password, $email, (int) $tribe, $act, (int) $uid, $desc);

		$time = time();
        $startTime = strtotime(START_DATE) - strtotime(date('d.m.Y')) + strtotime(START_TIME);

        //If we're registering the Natars tribe, the protection must be 0
		$protectionTime = $uid != 3 ? (($startTime > $time) ? $stime : $time) + PROTECTION : 0;

		$q = "INSERT INTO " . TB_PREFIX . "users (id, username, password, access, email, timestamp, tribe, act, protect, lastupdate, regtime, desc2, is_bcrypt) VALUES ($uid, '$username', '$password', " . USER . ", '$email', $time, $tribe, '$act', $protectionTime, $time, $time, '$desc', 1)";
		
		if(mysqli_query($this->dblink, $q)) return mysqli_insert_id($this->dblink);		
		else 
		{
		    // if an error has occured, we probably don't have DB converted to handle bcrypt passwords yet
		    $q = "INSERT INTO " . TB_PREFIX . "users (id, username, password, access, email, timestamp, tribe, act, protect, lastupdate, regtime, desc2) VALUES ($uid, '$username', '$password', " . USER . ", '$email', $time, $tribe, '$act', $protectionTime, $time, $time, '$desc')";
		    if(mysqli_query($this->dblink, $q)) return mysqli_insert_id($this->dblink);	      
		    else return false;
		}
	}

    function login($username, $password) {
        static $cachedResult = null;

        if ($cachedResult !== null) {
            return $cachedResult;
        }

        list($username, $password) = $this->escape_input($username, $password);
		$q = "SELECT id,password,sessid,is_bcrypt FROM " . TB_PREFIX . "users where username = '$username'";
		$result = mysqli_query($this->dblink,$q);

		// if we didn't update the database for bcrypt hashes yet...
		if (mysqli_error($this->dblink) != '') {
		    $q = "SELECT id, password,sessid,0 as is_bcrypt FROM " . TB_PREFIX . "users where username = '$username' LIMIT 1";
		    $result = mysqli_query($this->dblink,$q);
		    $bcrypt_update_done = false;
		} else {
		    $bcrypt_update_done = true;
		}

		$dbarray = mysqli_fetch_array($result);

		// even if we didn't do a DB conversion for bcrypt passwords,
		// we still need to check if this password wasn't encrypted via password_hash,
		// since all methods were updated to use that instead of md5 and therefore
		// new passwords in DB will be bcrypt already even without the is_bcrypt field present
		$bcrypted = true;
		$pwOk = password_verify($password, $dbarray['password']);

		if (!$pwOk && !$dbarray['is_bcrypt']) {
		    $pwOk = ($dbarray['password'] == md5($password));
		    $bcrypted = false;
		}

		if($pwOk) {
		    // update password to bcrypt, if correct
		    if (!$dbarray['is_bcrypt'] && !$bcrypted) {
		        mysqli_query($this->dblink, "UPDATE " . TB_PREFIX . "users SET password = '".password_hash($password, PASSWORD_BCRYPT,['cost' => 12])."'".($bcrypt_update_done ? ', is_bcrypt = 1' : '')." where id = ".(int) $dbarray['id']);
		    }
            $cachedResult = true;
		} else {
            $cachedResult = false;
		}

		return $cachedResult;
	}

	function sitterLogin($username, $password) {
        list($username, $password) = $this->escape_input($username, $password);

		$q = "SELECT sit1,sit2 FROM " . TB_PREFIX . "users where username = '$username' and access != " . BANNED ." LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		if($dbarray['sit1'] != 0) {
		    $q2 = "SELECT password FROM " . TB_PREFIX . "users where id = " . (int) $dbarray['sit1'] . " and access != " . BANNED . " LIMIT 1";
			$result2 = mysqli_query($this->dblink,$q2);
			$dbarray2 = mysqli_fetch_array($result2);
		}
		if($dbarray['sit2'] != 0) {
		    $q3 = "SELECT password FROM " . TB_PREFIX . "users where id = " . (int) $dbarray['sit2'] . " and access != " . BANNED . " LIMIT 1";
				$result3 = mysqli_query($this->dblink,$q3);
				$dbarray3 = mysqli_fetch_array($result3);
		}
		if($dbarray['sit1'] != 0 || $dbarray['sit2'] != 0) {
		    if(password_verify($password, $dbarray2['password']) || password_verify($password, $dbarray3['password'])) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function setDeleting($uid, $mode) {
	    list($uid, $mode) = $this->escape_input((int) $uid, $mode);

		$time = time() + 72 * 3600;
		if(!$mode) {
			$q = "INSERT into " . TB_PREFIX . "deleting values ($uid,$time)";
		} else {
			$q = "DELETE FROM " . TB_PREFIX . "deleting where uid = $uid";
		}
		mysqli_query($this->dblink,$q);
	}

	function isDeleting($uid) {
	    list($uid) = $this->escape_input((int) $uid);

		$q = "SELECT timestamp from " . TB_PREFIX . "deleting where uid = $uid LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		if ($dbarray) {
			return $dbarray['timestamp'];
		}else{
			return null;
		}
	}

	function modifyGold($userid, $amt, $mode) {
	    list($userid, $amt, $mode) = $this->escape_input((int) $userid, (int) $amt, $mode);

	    if(!$mode) $q = "UPDATE " . TB_PREFIX . "users set gold = gold - $amt where id = $userid";		
		else $q = "UPDATE " . TB_PREFIX . "users set gold = gold + $amt where id = $userid";
		
		return mysqli_query($this->dblink,$q);
	}

    function activate($username, $password, $email, $tribe, $locate, $act, $act2) {
        list($username, $password, $email, $tribe, $locate, $act, $act2) = $this->escape_input($username, $password, $email, $tribe, $locate, $act, $act2);

		$time = time();
		$q = "INSERT INTO " . TB_PREFIX . "activate (username,password,access,email,tribe,timestamp,location,act,act2) VALUES ('$username', '$password', " . USER . ", '$email', " . (int) $tribe .", $time, $locate, '$act', '$act2')";
		if(mysqli_query($this->dblink,$q)) return mysqli_insert_id($this->dblink);
		else return false;
	}

	function unreg($username) {
        list($username) = $this->escape_input($username);

		$q = "DELETE from " . TB_PREFIX . "activate where username = '$username'";
		return mysqli_query($this->dblink,$q);
	}

	function updateUserField($ref, $field, $value, $switch) {
        list($ref) = $this->escape_input($ref);

        if (!is_array($field)) {
            $field = [$field];
            $value = [$value];
        }

        $pairs = [];
        foreach ($field as $index => $fieldName) {
            $pairs[] = $this->escape($fieldName) . ' = ' . (Math::isInt($value[$index]) ? $value[$index] : '"'.$this->escape($value[$index]).'"');
        }

        if(!$switch) $q = "UPDATE " . TB_PREFIX . "users SET ".implode(', ', $pairs)." where username = '$ref'";		
        else $q = "UPDATE " . TB_PREFIX . "users SET ".implode(', ', $pairs)." where id = " . (int) $ref;

		// update cached values
		if ($ret = mysqli_query($this->dblink,$q)) {
            foreach ($field as $index => $fieldName) {
                if (isset(self::$fieldsCache[$ref.($switch ? 0 : 1)][$fieldName]))
                self::$fieldsCache[$ref.($switch ? 0 : 1)][$fieldName] = $value[$index];
            }
        }

        return $ret;
	}

	// no need to cache this method
	function getSitee($uid) {
	    list($uid) = $this->escape_input((int) $uid);

		$q = "SELECT id from " . TB_PREFIX . "users where sit1 = $uid or sit2 = $uid";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

	function removeMeSit($uid, $uid2) {
	    list($uid, $uid2) = $this->escape_input((int) $uid, (int) $uid2);

		$q = "UPDATE " . TB_PREFIX . "users set sit1 = 0 where id = $uid and sit1 = $uid2";
		mysqli_query($this->dblink,$q);
		$q2 = "UPDATE " . TB_PREFIX . "users set sit2 = 0 where id = $uid and sit2 = $uid2";
		mysqli_query($this->dblink,$q2);
	}

    function getUserField($ref, $field, $mode, $use_cache = true) {
        // update for Multihunter's username and ID
        if (($mode && $ref == '') || (!$mode && $ref == 0)) {
            $ref = 'Multihunter';
            $mode = 1;
        }

        // return all data, don't waste time by selecting fields one by one
        $userArray = $this->getUserArray($ref, ($mode ? 0 : 1), $use_cache);
        $result = (isset($userArray[$field]) ? $userArray[$field] : null);

        if ($result) {
            // will return the result
        } elseif($field=="username") {
            $result = "[?]";
        } else {
            $result = 0;
        }

        return $result;

    }

    function getUserFields($ref, $fields, $mode, $use_cache = true) {
        // update for Multihunter's username and ID
        if (($mode && $ref == '') || (!$mode && $ref == 0)) {
            $ref = 'Multihunter';
            $mode = 1;
        }

	    // return all data, don't waste time by selecting fields one by one
	    return $this->getUserArray($ref, ($mode ? 0 : 1), $use_cache);
    }

    // no need to cache this method
	function getInvitedUser($uid) {
	    list($uid) = $this->escape_input((int) $uid);

		$q = "SELECT * FROM " . TB_PREFIX . "users where invited = $uid order by regtime desc";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

    // no need to cache this method
	function getActivateField($ref, $field, $mode) {
        list($ref, $field, $mode) = $this->escape_input($ref, $field, $mode);

		if(!$mode) {
		    $q = "SELECT $field FROM " . TB_PREFIX . "activate where id = " . (int) $ref . " LIMIT 1";
		} else {
			$q = "SELECT $field FROM " . TB_PREFIX . "activate where username = '$ref' LIMIT 1";
		}
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		return $dbarray[$field];
	}

	/**
	 * Retrieves the user array via Username or ID
	 * 
	 * @param int $ref The user ID or the username
	 * @param int $mode 0 --> Search by username, 1 --> Search by user ID	 
	 * @param bool $use_cache Will use the cache if true
	 * @return array Returns the user array
	 */
	function getUserArray($ref, $mode, $use_cache = true) {
        list($ref, $mode) = $this->escape_input($ref, $mode);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$fieldsCache, $ref.$mode)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

        if(!$mode) $q = "SELECT * FROM " . TB_PREFIX . "users where username = '$ref' LIMIT 1";
		else $q = "SELECT * FROM " . TB_PREFIX . "users where id = " . (int) $ref . " LIMIT 1";
		
		$result = mysqli_query($this->dblink,$q);

        self::$fieldsCache[$ref.$mode] = mysqli_fetch_array($result);
        return self::$fieldsCache[$ref.$mode];
	}

	function activeModify($username, $mode) {
        list($username, $mode) = $this->escape_input($username, $mode);

		$time = time();
		if(!$mode) {
			$q = "INSERT into " . TB_PREFIX . "active VALUES ('$username',$time)";
		} else {
			$q = "DELETE FROM " . TB_PREFIX . "active where username = '$username'";
		}
		return mysqli_query($this->dblink,$q);
	}

	function addActiveUser($username, $time) {
        list($username, $time) = $this->escape_input($username, $time);

		$q = "REPLACE into " . TB_PREFIX . "active values ('$username',$time)";
		if(mysqli_query($this->dblink,$q)) {
			return true;
		} else {
			return false;
		}
	}

	function updateActiveUser($username, $time) {
	    static $updated = false;

	    if ($updated) {
	        return;
        }

        list($username, $time) = $this->escape_input($username, $time);

        $res1 = $this->addActiveUser($username, $time);
        $q = "UPDATE " . TB_PREFIX . "users set timestamp = $time where username = '$username'";
		$res2 = mysqli_query($this->dblink,$q);
		if($res1 && $res2) {
            $updated = true;
			return true;
		} else {
			return false;
		}
	}

	function submitProfile($uid, $gender, $location, $birthday, $des1, $des2) {
	    // temporarily replace newlines with placeholders, so they don't get escaped and backslashed stripped out of them
	    $des1 = str_replace(['\\r', '\\n'], ['[!RETURN_CARRIAGE!]','[!NEW_LINE!]'], $des1);
	    $des2 = str_replace(['\\r', '\\n'], ['[!RETURN_CARRIAGE!]','[!NEW_LINE!]'], $des2);

	    list($uid, $gender, $location, $birthday, $des1, $des2) = $this->escape_input((int) $uid, (int) $gender, $location, $birthday, $des1, $des2);

	    // return new lines and return carriages to descriptions
	    $des1 = str_replace(['[!RETURN_CARRIAGE!]','[!NEW_LINE!]'], ['\\r', '\\n'], $des1);
	    $des2 = str_replace(['[!RETURN_CARRIAGE!]','[!NEW_LINE!]'], ['\\r', '\\n'], $des2);

		$q = "UPDATE " . TB_PREFIX . "users set gender = $gender, location = '$location', birthday = '$birthday', desc1 = '$des1', desc2 = '$des2' where id = $uid";
		return mysqli_query($this->dblink,$q);
	}

	function gpack($uid, $gpack) {
	    list($uid, $gpack) = $this->escape_input((int) $uid, $gpack);

		$q = "UPDATE " . TB_PREFIX . "users set gpack = '$gpack' where id = $uid";
		return mysqli_query($this->dblink,$q);
	}

    // no need to cache this method
	function GetOnline($uid) {
	    list($uid) = $this->escape_input((int) $uid);

		$q = "SELECT sit FROM " . TB_PREFIX . "online WHERE uid = $uid LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		if ($dbarray) {
            return $dbarray['sit'];
        } else {
            return 0; 
        }

	}

	function UpdateOnline($mode, $name = "", $time = "", $uid = 0) {
	    list($mode, $name, $time, $uid) = $this->escape_input($mode, $name, $time, (int) $uid);

		global $session;
		if($mode == "login") {
			$q = "INSERT IGNORE INTO " . TB_PREFIX . "online (name, uid, time, sit) VALUES ('$name', $uid, '" . time() . "', 0)";
			return mysqli_query($this->dblink,$q);
		} else if($mode == "sitter") {
			$q = "INSERT IGNORE INTO " . TB_PREFIX . "online (name, uid, time, sit) VALUES ('$name', $uid, '" . time() . "', 1)";
			return mysqli_query($this->dblink,$q);
		} else {
			$q = "DELETE FROM " . TB_PREFIX . "online WHERE name ='" . $this->escape($session->username) . "'";
			return mysqli_query($this->dblink,$q);
		}
	}	

    function getUserAlliance($id, $use_cache = true) {
	    list($id) = $this->escape_input((int) $id);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$userAllianceCache, $id)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT " . TB_PREFIX . "alidata.tag from " . TB_PREFIX . "users join " . TB_PREFIX . "alidata where " . TB_PREFIX . "users.alliance = " . TB_PREFIX . "alidata.id and " . TB_PREFIX . "users.id = $id LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		if($dbarray['tag'] == "") {
            self::$userAllianceCache[$id] =  "-";
		} else {
            self::$userAllianceCache[$id] = $dbarray['tag'];
		}

		return self::$userAllianceCache[$id];
	}

    /***************************
	Function to get user alliance name!
	Made by: Dzoki
	***************************/
	function getUserAllianceID($id) {
	    list($id) = $this->escape_input((int) $id);

		$q = "SELECT alliance FROM " . TB_PREFIX . "users where id = $id LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		return $dbarray['alliance'];
	}

    // no need to cache this method
	function getNeedDelete() {
		$time = time();
		$q = "SELECT uid FROM " . TB_PREFIX . "deleting where timestamp < $time";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

	function countUser($use_cache = true) {
        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$usersCountCache, 0)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT count(id) FROM " . TB_PREFIX . "users where id > 5";
		$result = mysqli_query($this->dblink,$q);
		$row = mysqli_fetch_row($result);

        self::$usersCountCache[0] = $row[0];
        return self::$usersCountCache[0];
	}

    /**
	 * Called when a system message is sent or Natars/Artifacts have been spawned
	 * 
	 * @param int $value 1 to make a system message visible to all users, 0 to hide it 
	 * @return bool Returns true if the query was successful, false otherwise
	 */
	function setUsersOk($value = 1){
		list($value) = $this->escape_input((int) $value);
		
		$q = "UPDATE " . TB_PREFIX . "users SET ok = $value";
		return mysqli_query($this->dblink, $q);
	}

    // no need to cache this method
	function getLinks($id) {
	    list($id) = $this->escape_input((int) $id);
		$q = 'SELECT * FROM `' . TB_PREFIX . 'links` WHERE `userid` = ' . $id . ' ORDER BY `pos` ASC';
		return mysqli_query($this->dblink,$q);
	}

	function removeLinks($id,$uid) {
	    list($id,$uid) = $this->escape_input((int) $id,(int) $uid);
		$q = "DELETE FROM " . TB_PREFIX . "links WHERE `id` = ".$id." and `userid` = ".$uid;
		return mysqli_query($this->dblink,$q);
	}

    function addPassword($uid, $npw, $cpw) {
	    list($uid, $npw, $cpw) = $this->escape_input((int) $uid, $npw, $cpw);
		$q = "REPLACE INTO `" . TB_PREFIX . "password`(uid, npw, cpw) VALUES ($uid, '$npw', '$cpw')";
		mysqli_query($this->dblink,$q);
	}

	function resetPassword($uid, $cpw) {
	    list($uid, $cpw) = $this->escape_input((int) $uid, $cpw);
		$q = "SELECT npw FROM `" . TB_PREFIX . "password` WHERE uid = $uid AND cpw = '$cpw' AND used = 0 LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);

		if(!empty($dbarray)) {
		    if(!$this->updateUserField($uid, 'password', password_hash($dbarray['npw'], PASSWORD_BCRYPT,['cost' => 12]), 1)) return false;
			$q = "UPDATE `" . TB_PREFIX . "password` SET used = 1 WHERE uid = $uid AND cpw = '$cpw' AND used = 0";
			mysqli_query($this->dblink,$q);
			return true;
		}

		return false;
	}

	function addFriend($uid, $column, $friend) {
	    list($uid, $column, $friend) = $this->escape_input((int) $uid, $column, (int) $friend);

		$q = "UPDATE " . TB_PREFIX . "users SET $column = $friend WHERE id = $uid";
		return mysqli_query($this->dblink,$q);
	}

	function deleteFriend($uid, $column) {
	    list($uid, $column) = $this->escape_input((int) $uid, $column);

		$q = "UPDATE " . TB_PREFIX . "users SET $column = 0 WHERE id = $uid";
		return mysqli_query($this->dblink,$q);
	}

    // no need to cache this method
	function checkFriends($uid) {
        list($uid) = $this->escape_input($uid);
        global $session;
        
		$user = $this->getUserArray($uid, 1);
		for($i = 0; $i <= 19; $i++){
			if($user['friend'.$i] == 0 && $user['friend'.$i.'wait'] == 0){
				for($j = $i + 1; $j <= 19; $j++){
					$k = $j - 1;
					if($user['friend'.$j] != 0){
						$friend = $this->getUserField($uid, "friend".$j, 0);
						$this->addFriend($uid, "friend".$k, $friend);
						$this->deleteFriend($uid, "friend".$j);
					}
					
					if($user['friend'.$j.'wait'] == 0){
						$friendwait = $this->getUserField($uid, "friend".$j."wait", 0);
						$this->addFriend($session->uid, "friend".$k."wait", $friendwait);
						$this->deleteFriend($uid, "friend".$j."wait");
					}
				}
			}
		}
	}

    /*****************************************
    Function to vacation mode - by advocaite
    References:
    *****************************************/
   function setvacmode($uid, $days) {
        // TODO: refactor vacation mode
        list ($uid, $days) = $this->escape_input((int) $uid, (int) $days);
        $days1 = 60 * 60 * 24 * $days;
        $time = time() + $days1;
        $q = "UPDATE " . TB_PREFIX . "users SET vac_mode = '1' , vac_time=" . $time . " WHERE id=" . $uid . "";
        $result = mysqli_query($this->dblink, $q);
		return;
    }

    function removevacationmode($uid){
        // TODO: refactor vacation mode
        list ($uid) = $this->escape_input((int) $uid);
        $q = "UPDATE " . TB_PREFIX . "users SET vac_mode = '0' , vac_time='0' WHERE id=" . $uid . "";
        $result = mysqli_query($this->dblink, $q);
		return;
    }

    function getvacmodexy($wref){
        // TODO: refactor vacation mode
        list ($wref) = $this->escape_input((int) $wref);
        $q = "SELECT id,oasistype,occupied FROM " . TB_PREFIX . "wdata where id = $wref";
        $result = mysqli_query($this->dblink, $q);
        $dbarray = mysqli_fetch_array($result);
        if ($dbarray['occupied'] != 0 && $dbarray['oasistype'] == 0) {
            $q1 = "SELECT owner FROM " . TB_PREFIX . "vdata where wref = " . (int) $dbarray['id'] . "";
            $result1 = mysqli_query($this->dblink, $q1);
            $dbarray1 = mysqli_fetch_array($result1);
            if ($dbarray1['owner'] != 0) {
                $q2 = "SELECT vac_mode,vac_time FROM " . TB_PREFIX . "users where id = " . (int) $dbarray1['owner'] . "";
                $result2 = mysqli_query($this->dblink, $q2);
                $dbarray2 = mysqli_fetch_array($result2);
                return $dbarray2['vac_mode'] == 1;
            }
        } 
        else return false;
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

		if ($aid > 5){
			$village = $this->getVillageID($aid);
			$this->setLastUpdateRank($village);
		}

		$q = "UPDATE " . TB_PREFIX . "users SET ".implode(', ', $updates)." WHERE id = $aid";
		return mysqli_query($this->dblink,$q);
	}

	function batchModifyPoints($pointsByUser) {
		if (empty($pointsByUser)) return;
		$cases = [];
		$uids = [];
		$villageUpdates = [];
		foreach ($pointsByUser as $uid => $amt) {
			$uid = (int)$uid;
			$amt = (int)$amt;
			$cases[] = "WHEN $uid THEN RR + $amt";
			$uids[] = $uid;
			if ($uid > 5) {
				$villageUpdates[] = $uid;
			}
		}
		$q = "UPDATE " . TB_PREFIX . "users SET RR = CASE id " . implode(' ', $cases) . " ELSE RR END WHERE id IN(" . implode(',', $uids) . ")";
		mysqli_query($this->dblink, $q);

		// Batch rank updates
		if (!empty($villageUpdates)) {
			$vids = [];
			foreach ($villageUpdates as $uid) {
				$v = $this->getVillageID($uid);
				if ($v) $vids[] = (int)$v;
			}
			if (!empty($vids)) {
				$t = time();
				mysqli_query($this->dblink, "UPDATE " . TB_PREFIX . "vdata SET lastupdate_rank = $t WHERE wref IN(" . implode(',', $vids) . ")");
			}
		}
	}

}
