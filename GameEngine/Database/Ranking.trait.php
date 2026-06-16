<?php

trait DBRanking {

    // no need to refactor this method
	function getProfileMedal($uid) {
	    list($uid) = $this->escape_input((int) $uid);

		$q = "SELECT id,categorie,plaats,week,img,points from " . TB_PREFIX . "medal where userid = $uid and del = 0 order by id desc";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);

	}

    // no need to refactor this method
	function getProfileMedalAlly($uid) {
	    list($uid) = $this->escape_input((int) $uid);

		$q = "SELECT id,categorie,plaats,week,img,points from " . TB_PREFIX . "allimedal where allyid = $uid and del = 0 order by id desc";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);

	}

        // Reads from village_ranks cache table (populated by Ranking::ensureVillageRanksFresh)
	function getVRanking($offset = 0, $limit = 20) {
	    $q = "SELECT wref, name, pop, owner, owner_name as user, x, y
			  FROM " . TB_PREFIX . "village_ranks
			  ORDER BY rank_pos ASC
			  LIMIT $offset, $limit";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

	function getARanking($use_cache = true) {
        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$allianceRankingCache, 0)) && !is_null($cachedValue)) {
            return $cachedValue;
        }

		$q = "SELECT id,name,tag,oldrank,Aap,Adp FROM " . TB_PREFIX . "alidata where id != '' ORDER BY id DESC";
		$result = mysqli_query($this->dblink,$q);

        self::$allianceRankingCache[0] = $this->mysqli_fetch_all($result);
        return self::$allianceRankingCache[0];
	}

    // no need to cache this method
	function getUserByTribe($tribe) {
	    list($tribe) = $this->escape_input((int) $tribe);
		$q = "SELECT * FROM " . TB_PREFIX . "users where tribe = $tribe";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

    //medal functions
	function addclimberrankpop($user, $cp) {
	    list($user, $cp) = $this->escape_input((int) $user, (int) $cp);

		$q = "UPDATE " . TB_PREFIX . "users set clp = clp + $cp where id = $user";
		return mysqli_query($this->dblink,$q);
	}

	function removeclimberrankpop($user, $cp) {
	    list($user, $cp) = $this->escape_input((int) $user, (int) $cp);

		$q = "UPDATE " . TB_PREFIX . "users set clp = clp - $cp where id = $user";
		return mysqli_query($this->dblink,$q);
	}

	function setclimberrankpop($user, $cp) {
	    list($user, $cp) = $this->escape_input((int) $user, (int) $cp);

		$q = "UPDATE " . TB_PREFIX . "users set clp = $cp where id = $user";
		return mysqli_query($this->dblink,$q);
	}

	function updateoldrank($user, $cp) {
	    list($user, $cp) = $this->escape_input((int) $user, (int) $cp);

		$q = "UPDATE " . TB_PREFIX . "users set oldrank = $cp where id = $user";
		return mysqli_query($this->dblink,$q);
	}

	// ALLIANCE MEDAL FUNCTIONS
	function addclimberrankpopAlly($user, $cp) {
	    list($user, $cp) = $this->escape_input((int) $user, (int) $cp);

		$q = "UPDATE " . TB_PREFIX . "alidata set clp = clp + $cp where id = $user";
		return mysqli_query($this->dblink,$q);
	}

	function removeclimberrankpopAlly($user, $cp) {
	    list($user, $cp) = $this->escape_input((int) $user, (int) $cp);

		$q = "UPDATE " . TB_PREFIX . "alidata set clp = clp - $cp where id = $user";
		return mysqli_query($this->dblink,$q);
	}

	function updateoldrankAlly($user, $cp) {
	    list($user, $cp) = $this->escape_input((int) $user, (int) $cp);

		$q = "UPDATE " . TB_PREFIX . "alidata set oldrank = $cp where id = $user";
		return mysqli_query($this->dblink,$q);
	}

		function addGeneralAttack($casualties) {
        list($casualties) = $this->escape_input($casualties);

		$time = time();
		$q = "INSERT INTO " . TB_PREFIX . "general values (0,'$casualties','$time',1)";
		return mysqli_query($this->dblink,$q);
	}

    // no need to cache this method
	function getAttackByDate($time) {
        list($time) = $this->escape_input($time);

		$q = "SELECT time FROM " . TB_PREFIX . "general where shown = 1";
		$result = $this->query_return($q);
		$attack = 0;
		foreach($result as $general) {
		    if(date("j. M",$time) == date("j. M",$general['time'])){
		        $attack += 1;
		    }
		}
		return $attack;
	}

    // no need to cache this method
	function getAttackCasualties($time) {
        list($time) = $this->escape_input($time);

		$q = "SELECT time, casualties FROM " . TB_PREFIX . "general where shown = 1";
		$result = $this->query_return($q);
		$casualties = 0;
		foreach($result as $general){
		    if(date("j. M",$time) == date("j. M",$general['time'])){
		        $casualties += $general['casualties'];
		    }
		}
		return $casualties;
	}

    // Returns aggregated attack stats for the last 7 days in a single query
    // Replaces 14 separate queries + PHP loops per general.tpl page load
    function getWeeklyAttackStats() {
        $sevenDaysAgo = time() - (86400 * 7);
        $q = "SELECT 
                DATE(FROM_UNIXTIME(time)) as attack_date,
                COUNT(*) as attack_count,
                COALESCE(SUM(casualties), 0) as total_casualties
              FROM " . TB_PREFIX . "general 
              WHERE shown = 1 
                AND time >= $sevenDaysAgo
              GROUP BY DATE(FROM_UNIXTIME(time))";
        $result = $this->query_return($q);
        $stats = [];
        foreach ($result as $row) {
            $stats[$row['attack_date']] = $row;
        }
        return $stats;
    }

    // ========== BATCH UPDATES (substitui N queries individuais) ==========

    function batchAddClimberPop(array $changes) {
        if (empty($changes)) return;
        $whens = []; $ids = [];
        foreach ($changes as $uid => $delta) {
            $uid = (int)$uid; $delta = (int)$delta;
            $ids[] = $uid;
            $whens[] = "WHEN $uid THEN clp + $delta";
        }
        $q = "UPDATE ".TB_PREFIX."users SET clp = CASE id "
            . implode(' ', $whens) . " ELSE clp END WHERE id IN(" . implode(',', $ids) . ")";
        return mysqli_query($this->dblink, $q);
    }

    function batchSetClimberPop(array $sets) {
        if (empty($sets)) return;
        $whens = []; $ids = [];
        foreach ($sets as $uid => $value) {
            $uid = (int)$uid; $value = (int)$value;
            $ids[] = $uid;
            $whens[] = "WHEN $uid THEN $value";
        }
        $q = "UPDATE ".TB_PREFIX."users SET clp = CASE id "
            . implode(' ', $whens) . " ELSE clp END WHERE id IN(" . implode(',', $ids) . ")";
        return mysqli_query($this->dblink, $q);
    }

    function batchUpdateOldrank(array $ranks) {
        if (empty($ranks)) return;
        $whens = []; $ids = [];
        foreach ($ranks as $uid => $rank) {
            $uid = (int)$uid; $rank = (int)$rank;
            $ids[] = $uid;
            $whens[] = "WHEN $uid THEN $rank";
        }
        $q = "UPDATE ".TB_PREFIX."users SET oldrank = CASE id "
            . implode(' ', $whens) . " ELSE oldrank END WHERE id IN(" . implode(',', $ids) . ")";
        return mysqli_query($this->dblink, $q);
    }

    // Sincroniza aliança do jogador no user_stats instantaneamente (sem esperar o cron)
    function syncAllianceToUserStats($uid) {
        $uid = (int)$uid;
        $q = "UPDATE " . TB_PREFIX . "user_stats us
              JOIN " . TB_PREFIX . "users u ON u.id = us.uid
              LEFT JOIN " . TB_PREFIX . "alidata a ON a.id = u.alliance
              SET us.ally_id = COALESCE(u.alliance, 0),
                  us.ally_tag = COALESCE(a.tag, '')
              WHERE us.uid = $uid";
        return mysqli_query($this->dblink, $q);
    }

    // Returns max updated_at timestamp from user_stats (for staleness check)
    function getUserStatsLastUpdate() {
        $q = "SELECT MAX(updated_at) as last_update FROM " . TB_PREFIX . "user_stats";
        $result = mysqli_query($this->dblink, $q);
        if ($row = mysqli_fetch_assoc($result)) {
            return (int)$row['last_update'];
        }
        return 0;
    }

    // Returns max updated_at timestamp from village_ranks (for staleness check)
    function getVillageRanksLastUpdate() {
        $q = "SELECT MAX(updated_at) as last_update FROM " . TB_PREFIX . "village_ranks";
        $result = mysqli_query($this->dblink, $q);
        if ($row = mysqli_fetch_assoc($result)) {
            return (int)$row['last_update'];
        }
        return 0;
    }

}
