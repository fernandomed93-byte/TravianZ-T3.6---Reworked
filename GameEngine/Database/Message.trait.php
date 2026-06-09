<?php

trait DBMessage {

        // no need to cache this method
    function getUnreadMessagesCount($uid) {
        $uid = (int) $uid;

        $ids = [$uid];

        if (($this->getUserField($uid, 'access', 0) == ADMIN) && ADMIN_RECEIVE_SUPPORT_MESSAGES) {
            $ids[] = 1;
        }

        if ($this->getUserField($uid, 'access', 0) == MULTIHUNTER) {
            $ids[] = 5;
        }

        $q = 'SELECT Count(*) as numUnread FROM '.TB_PREFIX.'mdata WHERE target IN('.implode(', ', $ids).') AND viewed = 0';
        return mysqli_fetch_array(mysqli_query($this->dblink, $q), MYSQLI_ASSOC)['numUnread'];
    }

    // no need to cache this method
    function getUnreadNoticesCount($uid) {
        $uid = (int) $uid;

        return mysqli_fetch_array(mysqli_query($this->dblink, '
            SELECT Count(*) as numUnread FROM '.TB_PREFIX.'ndata WHERE uid = '.$uid.' AND viewed = 0'
        ), MYSQLI_ASSOC)['numUnread'];
    }

    function sendMessage($client, $owner, $topic, $message, $send, $alliance, $player, $coor, $report, $skip_escaping = false) {
        if (!$skip_escaping) {
           list($client, $owner, $topic, $message, $send, $alliance, $player, $coor, $report) = $this->escape_input((int) $client, (int) $owner, $topic, $message, (int) $send, (int) $alliance, (int) $player, (int) $coor, (int) $report);
        }

        $time = time();

        // add this message to the query cache, so we save some queries
        // if we need to send multiple messages at once
        self::$sendMessageQueryCache[] = "(0,$client,$owner,'$topic','$message',0,0,$send,$time,0,0,$alliance,$player,$coor,$report)";

        // check if we don't have too many messages to be sent out cached,
        // in which case we'll flush the cache and start again
        $retValue = true;
        if (count(self::$sendMessageQueryCache) >= self::$sendMessageQueryCacheMaxRecords) {
            $retValue = mysqli_query($this->dblink, "INSERT INTO " . TB_PREFIX . "mdata VALUES " . implode(', ', self::$sendMessageQueryCache));
            self::$sendMessageQueryCache = [];
        }

        return $retValue;
    }

    public function sendPendingMessages() {
        if (count(self::$sendMessageQueryCache)) {
            mysqli_query($this->dblink, "INSERT INTO " . TB_PREFIX . "mdata VALUES " . implode(', ', self::$sendMessageQueryCache));
        }
    }

    function setArchived($id) {
        if (!is_array($id)) {
            $id = [$id];

            foreach ($id as $index => $idValue) {
                $id[$index] = (int) $idValue;
            }
        }

        $q = "UPDATE " . TB_PREFIX . "mdata set archived = 1 where id IN(".implode(', ', $id).")";
        return mysqli_query($this->dblink,$q);
    }

    function setNorm($id) {
        if (!is_array($id)) {
            $id = [$id];

            foreach ($id as $index => $idValue) {
                $id[$index] = (int) $idValue;
            }
        }

        $q = "UPDATE " . TB_PREFIX . "mdata set archived = 0 where id IN(".implode(',', $id).")";
        return mysqli_query($this->dblink,$q);
    }

    /***************************
    Function to get messages
    Mode 1: Get inbox
    Mode 2: Get sent
    Mode 3: Get message
    Mode 4: Set viewed
    Mode 5: Remove message
    Mode 6: Retrieve archive
    References: User ID/Message ID, Mode
    ***************************/
    // no need to cache this method
	function getMessage($id, $mode) {
	    global $session;

	    $mode = (int) $mode;
	    $mode_updated = false;
	    // update $id if we should show Support messages for Admins and we are an admin
	    if (
	       $session->access == ADMIN
	       && ADMIN_RECEIVE_SUPPORT_MESSAGES
	       && in_array($mode, [1,2,6,9,10,11])
	    ) {
	        $id = $id . ', 1';
            $mode_updated = true;
	    }

        // update $id if we should show Multihunter messages for the current player
        if (
            $session->access == MULTIHUNTER
            && in_array($mode, [1,2,6,9,10,11])
        ) {
            $id = $id . ', 5';
            $mode_updated = true;
        }

        if (in_array($mode, [5,7,8])) {
            if (!is_array($id)) {
                $id = [$id];

                foreach ($id as $index => $idValue) {
                    $id[$index] = (int) $idValue;
                }
            }
        } else {
	        if (!$mode_updated) {
                $id = (int) $id;
            }
        }

		switch($mode) {
			case 1:
				$q = "SELECT * FROM " . TB_PREFIX . "mdata WHERE target IN($id) and send = 0 and archived = 0 ORDER BY time ".(isset($_GET['o']) && $_GET['o'] == 1 ? 'ASC' : 'DESC');
				break;
			case 2:
			    $q = "SELECT * FROM " . TB_PREFIX . "mdata WHERE owner IN($id) ORDER BY time ".(isset($_GET['o']) && $_GET['o'] == 1 ? 'ASC' : 'DESC');
				break;
			case 3:
			    $q = "SELECT * FROM " . TB_PREFIX . "mdata where id = $id";
				break;
			case 4:
			    $show_target = $session->uid;
			    if ($session->access == ADMIN && ADMIN_RECEIVE_SUPPORT_MESSAGES) $show_target .= ',1';
                if ($session->access == MULTIHUNTER) $show_target .= ',5';

			    $q = "UPDATE " . TB_PREFIX . "mdata set viewed = 1 where id = $id AND target IN(".$show_target.")";
				break;
			case 5:
				$q = "UPDATE " . TB_PREFIX . "mdata set deltarget = 1, viewed = 1 where id IN(".implode(', ', $id).")";
				break;
			case 6:
				$q = "SELECT * FROM " . TB_PREFIX . "mdata where target IN($id) and send = 0 and archived = 1 ORDER BY time ".(isset($_GET['o']) && $_GET['o'] == 1 ? 'ASC' : 'DESC');
				break;
			case 7:
				$q = "UPDATE " . TB_PREFIX . "mdata set delowner = 1 where id IN(".implode(', ', $id).")";
				break;
			case 8:
				$q = "UPDATE " . TB_PREFIX . "mdata set deltarget = 1, delowner = 1, viewed = 1 where id IN(".implode(', ', $id).")";
				break;
			case 9:
			    $q = "SELECT * FROM " . TB_PREFIX . "mdata WHERE target IN($id) and send = 0 and archived = 0 and deltarget = 0 ORDER BY time ".(isset($_GET['o']) && $_GET['o'] == 1 ? 'ASC' : 'DESC');
				break;
			case 10:
			    $q = "SELECT * FROM " . TB_PREFIX . "mdata WHERE owner IN($id) and delowner = 0 ORDER BY time ".(isset($_GET['o']) && $_GET['o'] == 1 ? 'ASC' : 'DESC');
				break;
			case 11:
			    $q = "SELECT * FROM " . TB_PREFIX . "mdata where target IN($id) and send = 0 and archived = 1 and deltarget = 0 ORDER BY time ".(isset($_GET['o']) && $_GET['o'] == 1 ? 'ASC' : 'DESC');
				break;
		}

		if($mode <= 3 || $mode == 6 || $mode > 8) {
			$result = mysqli_query($this->dblink,$q);
			return $this->mysqli_fetch_all($result);
		}
		else return mysqli_query($this->dblink,$q);
	}

	function unarchiveNotice($id) {
        if (!is_array($id)) {
            $id = [$id];

            foreach ($id as $index => $idValue) {
                $id[$index] = (int) $idValue;
            }
        }

		$q = "UPDATE " . TB_PREFIX . "ndata set ntype = archive, archive = 0 where id IN(".implode(',', $id).")";
		return mysqli_query($this->dblink,$q);
	}

	function archiveNotice($id) {
        if (!is_array($id)) {
            $id = [$id];

            foreach ($id as $index => $idValue) {
                $id[$index] = (int) $idValue;
            }
        }

		$q = "update " . TB_PREFIX . "ndata set archive = ntype, ntype = 9 where id IN(".implode(',', $id).")";
		return mysqli_query($this->dblink,$q);
	}

	function removeNotice($id) {
        if (!is_array($id)) {
            $id = [$id];

            foreach ($id as $index => $idValue) {
                $id[$index] = (int) $idValue;
            }
        }
		
		if (implode(',', $id) == "" || implode(',', $id) == 0) return;

		$q = "UPDATE " . TB_PREFIX . "ndata set del = 1,viewed = 1 where id IN(".implode(',', $id).")";
		return mysqli_query($this->dblink,$q);
	}

	function noticeViewed($id) {
	    list($id) = $this->escape_input((int) $id);

		$q = "UPDATE " . TB_PREFIX . "ndata set viewed = 1 where id = $id";
		return mysqli_query($this->dblink,$q);
	}

    function addNotice($uid, $toWref, $ally, $type, $topic, $data, $time = 0) {
        list($uid, $toWref, $ally, $type, $topic, $data, $time) = $this->escape_input((int) $uid, (int) $toWref, (int) $ally, (int) $type, $topic, $data, (int) $time);
        
        //We don't need to send reports to Nature or Natars
        if($uid == 2 || $uid == 3) return;
        if($time == 0) $time = time();
    	
    	$q = "INSERT INTO " . TB_PREFIX . "ndata (id, uid, toWref, ally, topic, ntype, data, time, viewed) values (0,'$uid','$toWref','$ally','$topic',$type,'$data',$time,0)";
    	return mysqli_query($this->dblink,$q);
    }

    // no need to cache this method
	function getNotice($uid) {
	    list($uid) = $this->escape_input((int) $uid);

		$q = "SELECT * FROM " . TB_PREFIX . "ndata where uid = $uid and del = 0 ORDER BY time ".(isset($_GET['o']) && $_GET['o'] == 1 ? 'ASC' : 'DESC');
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}
	
	function getNoticeLite($uid, $page = 1, $types = [], $is_archived = 0, $sort_order = 'DESC', $per_page = 10, $has_hero = false) {
        // --- 1. Sanitização e Preparação ---
        $uid = (int) $uid;
        $page = max(1, (int) $page); // Garante que a página seja pelo menos 1
        $per_page = (int) $per_page;
        $sort_order = (strtoupper($sort_order) === 'ASC') ? 'ASC' : 'DESC'; // Garante ASC ou DESC

        // Calcula o OFFSET para a consulta SQL
        $offset = ($page - 1) * $per_page;

        // Arrays para os parâmetros da consulta preparada
        $params = [];
        $types_str = ''; // String para os tipos de parâmetros (ex: 'iiis')

        // --- 2. Construção da Query SQL ---
        $sql = "SELECT * FROM " . TB_PREFIX . "ndata WHERE uid = ? AND del = 0";
        $params[] = $uid;
        $types_str .= 'i'; // uid é integer

        // Filtro de arquivados
        if ($is_archived == 0 || $is_archived == 1) {
            $sql .= " AND archive = ?";
            $params[] = $is_archived;
            $types_str .= 'i';
        }
        // Se $is_archived for -1, não adiciona o filtro (busca ambos)

        // Filtro de tipos (ntype)
        if (!empty($types) && is_array($types)) {
            // Cria placeholders (?) para cada tipo
            $placeholders = implode(',', array_fill(0, count($types), '?'));
            $sql .= " AND ntype IN ($placeholders)";
            // Adiciona cada tipo ao array de parâmetros e à string de tipos
            foreach ($types as $type) {
                $params[] = $type;
                $types_str .= 'i'; // Assumindo que ntype é integer
            }
        }

        if ($has_hero) {
            $sql .= "AND CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(data, ',', 179), ',', -1) AS UNSIGNED) > 0";
        }

        // Ordenação e Paginação
        $sql .= " ORDER BY time $sort_order LIMIT ? OFFSET ?";
        $params[] = $per_page;
        $params[] = $offset;
        $types_str .= 'ii'; // LIMIT e OFFSET são integers

        // --- 3. Execução da Query ---
        $stmt = $this->dblink->prepare($sql);

        if ($stmt === false) {
            // Logar o erro: error_log("Erro ao preparar a query getNoticeLite: " . $this->dblink->error . " | SQL: " . $sql);
            return []; // Retorna array vazio em caso de erro
        }

        // Binda os parâmetros dinamicamente
        $stmt->bind_param($types_str, ...$params);

        if (!$stmt->execute()) {
            // Logar o erro: error_log("Erro ao executar a query getNoticeLite: " . $stmt->error);
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $data = $this->mysqli_fetch_all($result); // Usa sua função existente
        $stmt->close();

        return $data;
    }
	
	function getNoticeCount($uid, $types = [], $is_archived = 0) {
        // --- 1. Sanitização e Preparação ---
        $uid = (int) $uid;

        $params = [];
        $types_str = '';

        // --- 2. Construção da Query SQL ---
        $sql = "SELECT COUNT(*) as total FROM " . TB_PREFIX . "ndata WHERE uid = ? AND del = 0";
        $params[] = $uid;
        $types_str .= 'i';

        // Filtro de arquivados
        if ($is_archived == 0 || $is_archived == 1) {
            $sql .= " AND archive = ?";
            $params[] = $is_archived;
            $types_str .= 'i';
        }

        // Filtro de tipos (ntype)
        if (!empty($types) && is_array($types)) {
            $placeholders = implode(',', array_fill(0, count($types), '?'));
            $sql .= " AND ntype IN ($placeholders)";
            foreach ($types as $type) {
                $params[] = $type;
                $types_str .= 'i';
            }
        }

        // --- 3. Execução da Query ---
        $stmt = $this->dblink->prepare($sql);

        if ($stmt === false) {
            // Logar o erro: error_log("Erro ao preparar a query getNoticeCount: " . $this->dblink->error);
            return 0;
        }

        $stmt->bind_param($types_str, ...$params);

        if (!$stmt->execute()) {
            // Logar o erro: error_log("Erro ao executar a query getNoticeCount: " . $stmt->error);
            $stmt->close();
            return 0;
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return ($row) ? (int) $row['total'] : 0;
    }

	function getNotice2($id, $field = null, $use_cache = true) {
        list($id, $field) = $this->escape_input((int) $id, $field);

        // first of all, check if we should be using cache and whether the field
        // required is already cached
        if ($use_cache && ($cachedValue = self::returnCachedContent(self::$noticesCacheById, $id)) && !is_null($cachedValue)) {
            return $cachedValue[$field];
        }

		$q = "SELECT * FROM " . TB_PREFIX . "ndata where `id` = $id ORDER BY time ".(isset($_GET['o']) && $_GET['o'] == 1 ? 'ASC' : 'DESC')." LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);

        self::$noticesCacheById[$id] = $dbarray;
        return is_null($field) ? self::$noticesCacheById[$id] : self::$noticesCacheById[$id][$field];
	}

	function getUnViewNotice($uid) {
	    list($uid) = $this->escape_input((int) $uid);

		$q = "SELECT * FROM " . TB_PREFIX . "ndata where uid = $uid AND viewed=0 ORDER BY time ".(isset($_GET['o']) && $_GET['o'] == 1 ? 'ASC' : 'DESC');
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

}
