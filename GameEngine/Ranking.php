<?php

/** --------------------------------------------------- **\
| ********* DO NOT REMOVE THIS COPYRIGHT NOTICE ********* |
+---------------------------------------------------------+
| Credits:     All the developers including the leaders:  |
|              Advocaite & Dzoki & Donnchadh              |
|                                                         |
| Copyright:   TravianZ Project All rights reserved       |
| Optimized:   SQL pagination + user_stats/village_ranks  |
|              cache tables with staleness checks (2024)  |
\** --------------------------------------------------- **/

		class Ranking {

			public $rankarray = [];
			private $rlastupdate;
			private $rankCount = 0;
			public $highlightRank = 0;

			// Staleness thresholds in seconds
			const USER_STATS_TTL = 300;    // 5 minutes
			const VILLAGE_RANKS_TTL = 900;  // 15 minutes

			public function getRank() {
				return $this->rankarray;
			}

			public function getRankCount() {
				return $this->rankCount;
			}

			public function getUserRank($id) {
			    global $database;
				$id = (int)$id;
				$q = "SELECT rank_pos FROM " . TB_PREFIX . "user_stats WHERE uid = $id";
				$result = mysqli_query($database->dblink, $q);
				if ($result && ($row = mysqli_fetch_assoc($result))) {
					return (int)$row['rank_pos'];
				}
				return 0;
			}

			public function getAttackRank($uid) {
				global $database;
				$uid = (int)$uid;

				$access_level = INCLUDE_ADMIN ? "10" : "8";
				$tribeWhere = SHOW_NATARS
					? "u.tribe <= 9 AND (u.id > 5 OR u.id = 3)"
					: "(u.tribe <= 3 OR u.tribe > 5) AND u.id > 5";

				$q = "SELECT COUNT(*) + 1 as rnk
					FROM " . TB_PREFIX . "user_stats us
					JOIN " . TB_PREFIX . "users u ON u.id = us.uid
					WHERE (us.apall > (SELECT apall FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					       OR (us.apall = (SELECT apall FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					           AND us.totalpop > (SELECT totalpop FROM " . TB_PREFIX . "user_stats WHERE uid = $uid))
					       OR (us.apall = (SELECT apall FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					           AND us.totalpop = (SELECT totalpop FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					           AND us.uid > $uid))
					AND us.apall >= 0
					AND u.access < $access_level
					AND $tribeWhere";

				$result = mysqli_query($database->dblink, $q);
				if ($result && ($row = mysqli_fetch_assoc($result))) return (int)$row['rnk'];
				return 0;
			}

			public function getDefenseRank($uid) {
				global $database;
				$uid = (int)$uid;

				$access_level = INCLUDE_ADMIN ? "10" : "8";
				$tribeWhere = SHOW_NATARS
					? "u.tribe <= 9 AND (u.id > 5 OR u.id = 3)"
					: "(u.tribe <= 3 OR u.tribe > 5) AND u.id > 5";

				$q = "SELECT COUNT(*) + 1 as rnk
					FROM " . TB_PREFIX . "user_stats us
					JOIN " . TB_PREFIX . "users u ON u.id = us.uid
					WHERE (us.dpall > (SELECT dpall FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					       OR (us.dpall = (SELECT dpall FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					           AND us.totalpop > (SELECT totalpop FROM " . TB_PREFIX . "user_stats WHERE uid = $uid))
					       OR (us.dpall = (SELECT dpall FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					           AND us.totalpop = (SELECT totalpop FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					           AND us.uid > $uid))
					AND us.dpall >= 0
					AND u.access < $access_level
					AND $tribeWhere";

				$result = mysqli_query($database->dblink, $q);
				if ($result && ($row = mysqli_fetch_assoc($result))) return (int)$row['rnk'];
				return 0;
			}

			public function searchHeroRank($uid) {
				$uid = (int)$uid;
				foreach ($this->rankarray as $i => $row) {
					if ($row != "pad" && isset($row['uid']) && (int)$row['uid'] === $uid) {
						return $i;
					}
				}
				return 0;
			}

			public function getRaceRank($uid, $tribe) {
				global $database;
				$uid = (int)$uid;
				$tribe = (int)$tribe;

				$access_level = INCLUDE_ADMIN ? "10" : "8";

				$q = "SELECT COUNT(*) + 1 as rnk
					FROM " . TB_PREFIX . "user_stats us
					JOIN " . TB_PREFIX . "users u ON u.id = us.uid
					WHERE u.tribe = $tribe
					  AND u.access < $access_level
					  AND u.id > 5
					  AND (us.totalpop > (SELECT totalpop FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					       OR (us.totalpop = (SELECT totalpop FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					           AND us.totalvils > (SELECT totalvils FROM " . TB_PREFIX . "user_stats WHERE uid = $uid))
					       OR (us.totalpop = (SELECT totalpop FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					           AND us.totalvils = (SELECT totalvils FROM " . TB_PREFIX . "user_stats WHERE uid = $uid)
					           AND us.uid > $uid))
					ORDER BY NULL";

				$result = mysqli_query($database->dblink, $q);
				if ($result && ($row = mysqli_fetch_assoc($result))) return (int)$row['rnk'];
				return 0;
			}

			// ========== DISPATCH ==========

			public function procRankReq($get) {
				global $village, $session;
				$isPostSearch = isset($_POST['ft']);
				if (!$isPostSearch) {
					$this->highlightRank = 0;
				}
				if(isset($get['id'])) {
					switch($get['id']) {
						case 1:
							$this->ensureUserStatsFresh();
							if (!$isPostSearch) {
								$rank = $this->searchRank($session->uid, "userid");
								$this->getStart($rank > 0 ? $rank : 1);
								$this->highlightRank = $rank;
							}
							$this->procRankArray();
							break;
						case 8:
							$this->procHeroRankArray();
							if (!$isPostSearch) {
								if($get['hero'] == 0) {
									$this->getStart(1);
								} else {
									$rank = $this->searchHeroRank($session->uid);
									$this->getStart($rank > 0 ? $rank : 1);
									$this->highlightRank = $rank;
								}
							}
							break;
						case 11:
							$this->ensureUserStatsFresh();
							if (!$isPostSearch) {
								$rank = ($session->tribe == 1) ? $this->getRaceRank($session->uid, 1) : 0;
								$this->getStart($rank > 0 ? $rank : 1);
								$this->highlightRank = $rank;
							}
							$this->procRankRaceArray(1);
							break;
						case 12:
							$this->ensureUserStatsFresh();
							if (!$isPostSearch) {
								$rank = ($session->tribe == 2) ? $this->getRaceRank($session->uid, 2) : 0;
								$this->getStart($rank > 0 ? $rank : 1);
								$this->highlightRank = $rank;
							}
							$this->procRankRaceArray(2);
							break;
						case 13:
							$this->ensureUserStatsFresh();
							if (!$isPostSearch) {
								$rank = ($session->tribe == 3) ? $this->getRaceRank($session->uid, 3) : 0;
								$this->getStart($rank > 0 ? $rank : 1);
								$this->highlightRank = $rank;
							}
							$this->procRankRaceArray(3);
							break;
						case 16:
							$this->ensureUserStatsFresh();
							if (!$isPostSearch) {
								$rank = ($session->tribe == 6) ? $this->getRaceRank($session->uid, 6) : 0;
								$this->getStart($rank > 0 ? $rank : 1);
								$this->highlightRank = $rank;
							}
							$this->procRankRaceArray(6);
							break;
						case 17:
							$this->ensureUserStatsFresh();
							if (!$isPostSearch) {
								$rank = ($session->tribe == 7) ? $this->getRaceRank($session->uid, 7) : 0;
								$this->getStart($rank > 0 ? $rank : 1);
								$this->highlightRank = $rank;
							}
							$this->procRankRaceArray(7);
							break;
						case 18:
							$this->ensureUserStatsFresh();
							if (!$isPostSearch) {
								$rank = ($session->tribe == 8) ? $this->getRaceRank($session->uid, 8) : 0;
								$this->getStart($rank > 0 ? $rank : 1);
								$this->highlightRank = $rank;
							}
							$this->procRankRaceArray(8);
							break;
						case 19:
							$this->ensureUserStatsFresh();
							if (!$isPostSearch) {
								$rank = ($session->tribe == 9) ? $this->getRaceRank($session->uid, 9) : 0;
								$this->getStart($rank > 0 ? $rank : 1);
								$this->highlightRank = $rank;
							}
							$this->procRankRaceArray(9);
							break;
						case 31:
							$this->ensureUserStatsFresh();
							if (!$isPostSearch) {
								$rank = $this->getAttackRank($session->uid);
								$this->getStart($rank > 0 ? $rank : 1);
								$this->highlightRank = $rank;
							}
							$this->procAttRankArray();
							break;
						case 32:
							$this->ensureUserStatsFresh();
							if (!$isPostSearch) {
								$rank = $this->getDefenseRank($session->uid);
								$this->getStart($rank > 0 ? $rank : 1);
								$this->highlightRank = $rank;
							}
							$this->procDefRankArray();
							break;
						case 2:
							$this->getVRankPart();
							if (!$isPostSearch) {
								$rank = $this->searchRank($village->wid, "wref");
								$this->getStart($rank);
								$this->highlightRank = $rank;
							}
							break;
						case 4:
							$this->procARankArray();
							if (!$isPostSearch) {
								if($get['aid'] == 0) {
									$this->getStart(1);
								} else {
									$rank = $this->searchRank($get['aid'], "id");
									$this->getStart($rank);
									$this->highlightRank = $rank;
								}
							}
							break;
						case 41:
							$this->procAAttRankArray();
							if (!$isPostSearch) {
								if($get['aid'] == 0) {
									$this->getStart(1);
								} else {
									$rank = $this->searchRank($get['aid'], "id");
									$this->getStart($rank);
									$this->highlightRank = $rank;
								}
							}
							break;
						case 42:
							$this->procADefRankArray();
							if (!$isPostSearch) {
								if($get['aid'] == 0) {
									$this->getStart(1);
								} else {
									$rank = $this->searchRank($get['aid'], "id");
									$this->getStart($rank);
									$this->highlightRank = $rank;
								}
							}
							break;
					}
				} else {
					$this->ensureUserStatsFresh();
					if (!$isPostSearch) {
						$rank = $this->searchRank($session->uid, "userid");
						$this->getStart($rank > 0 ? $rank : 1);
						$this->highlightRank = $rank;
					}
					$this->procRankArray();
				}
			}

			public function procRank($post) {
				if(isset($post['ft'])) {
					switch($post['ft']) {
						case "r1":
						case "r11":
						case "r12":
						case "r13":
						case "r31":
						case "r32":
							if(isset($post['rank']) && $post['rank'] != "") {
								$this->getStart($post['rank']);
								$this->highlightRank = (int)$post['rank'];
							}
							if(isset($post['name']) && $post['name'] != "") {
								$found = $this->searchRank(stripslashes($post['name']), "username");
								$this->getStart($found);
								$this->highlightRank = $found;
							}
							break;
						case "r4":
						case "r42":
						case "r41":
							if(isset($post['rank']) && $post['rank'] != "") {
								$this->getStart($post['rank']);
								$this->highlightRank = (int)$post['rank'];
							}
							if(isset($post['name']) && $post['name'] != "") {
								$found = $this->searchRank(stripslashes($post['name']), "tag");
								$this->getStart($found);
								$this->highlightRank = $found;
							}
							break;
						case "r2":
						case "r8":
							if(isset($post['rank']) && $post['rank'] != "") {
								$this->getStart($post['rank']);
								$this->highlightRank = (int)$post['rank'];
							}
							if(isset($post['name']) && $post['name'] != "") {
								$found = $this->searchRank(stripslashes($post['name']), "name");
								$this->getStart($found);
								$this->highlightRank = $found;
							}
							break;
					}
				}
			}

			private function getStart($search) {
				$multiplier = 1;
				if(!is_numeric($search)) {
					$_SESSION['search'] = htmlspecialchars($search);
				} else {
					$search = max(1, (int)$search);
					while($search > (20 * $multiplier)) {
						$multiplier += 1;
					}
					$start = 20 * $multiplier - 19;
					$_SESSION['search'] = htmlspecialchars($search);
					$_SESSION['start'] = htmlspecialchars($start);
				}
			}

			public function getAllianceRank($id) {
				$this->procARankArray();
				while(1) {
					if(count($this->rankarray) > 1) {
						$key = key($this->rankarray);
						if(isset ($this->rankarray[$key]["id"]) && $this->rankarray[$key]["id"] === $id) {
							return $key;
							break;
						} else {
							if(!next($this->rankarray)) {
								return false;
								break;
							}
						}
					} else {
						return 1;
					}
				}
			}

			// ========== FAST SEARCH USING user_stats ==========

			public function searchRank($name, $field) {
			    global $database;

			    if ($field == 'userid') {
			        $name = (int)$name;
			        $q = "SELECT rank_pos FROM " . TB_PREFIX . "user_stats WHERE uid = $name";
			        $result = mysqli_query($database->dblink, $q);
			        if ($result && ($row = mysqli_fetch_assoc($result))) return (int)$row['rank_pos'];
			        return 0;
			    }

			    if ($field == 'username') {
			        $nameEsc = $database->escape($name);
			        $q = "SELECT us.rank_pos FROM " . TB_PREFIX . "user_stats us
			              JOIN " . TB_PREFIX . "users u ON u.id = us.uid
			              WHERE u.username = '$nameEsc'";
			        $result = mysqli_query($database->dblink, $q);
			        if ($result && ($row = mysqli_fetch_assoc($result))) return (int)$row['rank_pos'];
			        return $name;
			    }

			    if ($field == 'uid') {
			        $name = (int)$name;
			        $q = "SELECT rank_pos FROM " . TB_PREFIX . "user_stats WHERE uid = $name";
			        $result = mysqli_query($database->dblink, $q);
			        if ($result && ($row = mysqli_fetch_assoc($result))) return (int)$row['rank_pos'];
			        return 0;
			    }

			    if ($field == 'tag') {
			        $nameEsc = $database->escape($name);
			        $q = "SELECT rank_pos FROM " . TB_PREFIX . "user_stats WHERE ally_tag = '$nameEsc' LIMIT 1";
			        $result = mysqli_query($database->dblink, $q);
			        if ($result && ($row = mysqli_fetch_assoc($result))) return (int)$row['rank_pos'];
			        return $name;
			    }

			    if ($field == 'id') {
			        $name = (int)$name;
			        // Alliance ID lookup — fallback to array scan
			        if (count($this->rankarray) > 1) {
			            for ($key = 0; $key < count($this->rankarray); $key++) {
			                if ($this->rankarray[$key] != "pad" && isset($this->rankarray[$key]['id']) && $this->rankarray[$key]['id'] == $name) return $key;
			            }
			        }
			        return 0;
			    }

			    if ($field == 'name') {
			        $nameEsc = $database->escape($name);
			        $q = "SELECT rank_pos FROM " . TB_PREFIX . "village_ranks WHERE name = '$nameEsc' LIMIT 1";
			        $result = mysqli_query($database->dblink, $q);
			        if ($result && ($row = mysqli_fetch_assoc($result))) return (int)$row['rank_pos'];
			        return $name;
			    }

			    if ($field == 'wref') {
			        $name = (int)$name;
			        $q = "SELECT rank_pos FROM " . TB_PREFIX . "village_ranks WHERE wref = $name";
			        $result = mysqli_query($database->dblink, $q);
			        if ($result && ($row = mysqli_fetch_assoc($result))) return (int)$row['rank_pos'];
			        return 0;
			    }

			    // Fallback: scan array (for alliance tags, etc.)
			    if (count($this->rankarray) > 1) {
			        for ($key = 0; $key < count($this->rankarray); $key++) {
			            if ($this->rankarray[$key] != "pad" && isset($this->rankarray[$key][$field]) && $this->rankarray[$key][$field] == $name) return $key;
			        }
			    }
			    return ($field != "userid" && $field != "uid") ? $name : 0;
			}

			// ========== CACHE HELPERS ==========

			private function ensureUserStatsFresh() {
				global $database;
				$lastUpdate = $database->getUserStatsLastUpdate();
				if ($lastUpdate < (time() - self::USER_STATS_TTL)) {
					$this->rebuildUserStats();
				}
			}

			private function ensureVillageRanksFresh() {
				global $database;
				$lastUpdate = $database->getVillageRanksLastUpdate();
				if ($lastUpdate < (time() - self::VILLAGE_RANKS_TTL)) {
					$this->rebuildVillageRanks();
				}
			}

			// ========== REBUILD: user_stats ==========

			public function rebuildUserStats() {
				global $database;

				$access_level = INCLUDE_ADMIN ? "10" : "8";

				if(SHOW_NATARS == True){
					$where_conditions_users = "u.access < " . $access_level . " AND u.tribe <= 9 AND (u.id > 5 OR u.id = 3)";
				} else {
					$where_conditions_users = "u.access < " . $access_level . " AND (u.tribe <= 3 OR u.tribe > 5) AND u.id > 5";
				}

				// Populate user_stats with aggregated data from vdata
				$q = "INSERT INTO " . TB_PREFIX . "user_stats
						(uid, totalpop, totalvils, apall, dpall, ally_id, ally_tag, tribe, username, updated_at)
					  SELECT
						u.id,
						COALESCE(SUM(v.pop), 0),
						COUNT(CASE WHEN v.type != 99 THEN v.wref ELSE NULL END),
						COALESCE(u.apall, 0),
						COALESCE(u.dpall, 0),
						u.alliance,
						ad.tag,
						u.tribe,
						u.username,
						UNIX_TIMESTAMP()
					  FROM " . TB_PREFIX . "users u
					  LEFT JOIN " . TB_PREFIX . "vdata v ON v.owner = u.id
					  LEFT JOIN " . TB_PREFIX . "alidata ad ON ad.id = u.alliance
					  WHERE $where_conditions_users
					  GROUP BY u.id
					  ON DUPLICATE KEY UPDATE
						totalpop = VALUES(totalpop),
						totalvils = VALUES(totalvils),
						apall = VALUES(apall),
						dpall = VALUES(dpall),
						ally_id = VALUES(ally_id),
						ally_tag = VALUES(ally_tag),
						tribe = VALUES(tribe),
						username = VALUES(username),
						updated_at = VALUES(updated_at)";

				mysqli_query($database->dblink, $q);

				// Remove stale entries (users that no longer match filters)
				$delWhere = "u.access >= " . $access_level;
				if(SHOW_NATARS == True){
					$delWhere = "(u.access >= " . $access_level . " OR (u.tribe > 9 AND u.id != 3) OR u.id <= 5) AND u.id != 3";
				} else {
					$delWhere = "(u.access >= " . $access_level . " OR (u.tribe = 4 OR u.tribe = 5) OR u.id <= 5)";
				}
				mysqli_query($database->dblink, "DELETE us FROM " . TB_PREFIX . "user_stats us
					LEFT JOIN " . TB_PREFIX . "users u ON u.id = us.uid
					WHERE $delWhere OR u.id IS NULL");

				// Update rank_pos based on totalpop ordering
				mysqli_query($database->dblink, "SET @rank = 0");
				mysqli_query($database->dblink, "UPDATE " . TB_PREFIX . "user_stats
					SET rank_pos = (@rank := @rank + 1)
					ORDER BY totalpop DESC, totalvils DESC, uid DESC");
			}

			// ========== PLAYER RANKING (overview) ==========

			public function procRankArray($offset = null, $limit = null, $full = false) {
				global $multisort, $database;

				$this->ensureUserStatsFresh();

				$access_level = INCLUDE_ADMIN ? "10" : "8";

				if(SHOW_NATARS == True){
					$where_conditions_users = "u.access < " . $access_level . " AND u.tribe <= 9 AND (u.id > 5 OR u.id = 3)";
				} else {
					$where_conditions_users = "u.access < " . $access_level . " AND (u.tribe <= 3 OR u.tribe > 5) AND u.id > 5";
				}

				// Count total
				$countQ = "SELECT COUNT(*) as total FROM " . TB_PREFIX . "user_stats us
				           JOIN " . TB_PREFIX . "users u ON u.id = us.uid
				           WHERE $where_conditions_users";
				$countResult = mysqli_query($database->dblink, $countQ);
				$this->rankCount = (int)mysqli_fetch_assoc($countResult)['total'];

				// Determine offset/limit — $full bypasses pagination for cron use
				if ($full) {
					$offset = 0;
					$limit = 999999;
				} elseif ($offset === null) {
					$page = 1;
					if (isset($_SESSION['start'])) {
						$page = max(1, (int)ceil((int)$_SESSION['start'] / 20));
					}
					if (isset($_GET['rank']) && is_numeric($_GET['rank']) && $_GET['rank'] > 0) {
						$page = max(1, (int)ceil((int)$_GET['rank'] / 20));
					}
					$offset = ($page - 1) * 20;
				}
				$offset = max(0, $offset);
				$limit = max(1, ($limit !== null ? $limit : 20));

				// Query from cache
				$q = "SELECT
						u.id AS userid,
						u.username AS username,
						u.oldrank AS oldrank,
						u.alliance AS alliance,
						u.access AS access,
						COALESCE(us.totalpop, 0) AS totalpop,
						COALESCE(us.totalvils, 0) AS totalvillages,
						COALESCE(us.ally_tag, '') AS allitag,
						COALESCE(us.rank_pos, 0) AS rank_pos,
						COALESCE(us.apall, 0) AS apall,
						COALESCE(us.dpall, 0) AS dpall
					FROM " . TB_PREFIX . "users u
					JOIN " . TB_PREFIX . "user_stats us ON us.uid = u.id
					WHERE $where_conditions_users
					ORDER BY us.rank_pos ASC
					LIMIT $offset, $limit";

				$result = mysqli_query($database->dblink, $q);
				$holder = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$value = [
						'userid' => $row['userid'],
						'username' => $row['username'],
						'oldrank' => $row['oldrank'],
						'alliance' => $row['alliance'],
						'aname' => $row['allitag'],
						'totalpop' => $row['totalpop'],
						'totalvillage' => $row['totalvillages'],
						'access' => $row['access'],
						'rank_pos' => $row['rank_pos'],
					];
					$holder[] = $value;
				}

				$newholder = ["pad"];
				foreach($holder as $key) $newholder[] = $key;

				$this->rankarray = $newholder;
			}

			// ========== PLAYER RACE RANKING ==========

			public function procRankRaceArray($race) {
				global $multisort, $database;
				$race = (int)$race;

				$this->ensureUserStatsFresh();

				$access_level = INCLUDE_ADMIN ? "10" : "8";

				// Count
				$countQ = "SELECT COUNT(*) as total FROM " . TB_PREFIX . "user_stats us
				           JOIN " . TB_PREFIX . "users u ON u.id = us.uid
				           WHERE u.tribe = $race AND u.access < $access_level AND u.id > 5";
				$countResult = mysqli_query($database->dblink, $countQ);
				$this->rankCount = (int)mysqli_fetch_assoc($countResult)['total'];

				$page = 1;
				if (isset($_SESSION['start'])) {
					$page = max(1, (int)ceil((int)$_SESSION['start'] / 20));
				}
				if (isset($_GET['rank']) && is_numeric($_GET['rank']) && $_GET['rank'] > 0) {
					$page = max(1, (int)ceil((int)$_GET['rank'] / 20));
				}
				$offset = ($page - 1) * 20;

				// Since user_stats is ordered by population globally, we need per-tribe ordering
				// For race-specific ranking we re-order by totalpop within the tribe
				$q = "SELECT
						u.id AS userid,
						u.username AS username,
						u.alliance AS alliance,
						COALESCE(us.totalpop, 0) AS totalpop,
						COALESCE(us.totalvils, 0) AS totalvillages,
						COALESCE(us.ally_tag, '') AS allitag,
						ROW_NUMBER() OVER (ORDER BY us.totalpop DESC, us.totalvils DESC, us.uid DESC) AS rank_pos
					FROM " . TB_PREFIX . "user_stats us
					JOIN " . TB_PREFIX . "users u ON u.id = us.uid
					WHERE u.tribe = $race AND u.access < $access_level AND u.id > 5
					ORDER BY us.totalpop DESC, us.totalvils DESC, us.uid DESC
					LIMIT $offset, 20";

				// Fallback if ROW_NUMBER() not available (MySQL < 8.0)
				$result = @mysqli_query($database->dblink, $q);
				if (!$result) {
					// Fallback without ROW_NUMBER — assign ranks in PHP
					$q2 = "SELECT
							u.id AS userid,
							u.username AS username,
							u.alliance AS alliance,
							COALESCE(us.totalpop, 0) AS totalpop,
							COALESCE(us.totalvils, 0) AS totalvillages,
							COALESCE(us.ally_tag, '') AS allitag
						FROM " . TB_PREFIX . "user_stats us
						JOIN " . TB_PREFIX . "users u ON u.id = us.uid
						WHERE u.tribe = $race AND u.access < $access_level AND u.id > 5
						ORDER BY us.totalpop DESC, us.totalvils DESC, us.uid DESC
						LIMIT $offset, 20";
					$result = mysqli_query($database->dblink, $q2);

					$holder = [];
					while ($row = mysqli_fetch_assoc($result)) {
						$value = [
							'userid' => $row['userid'],
							'username' => $row['username'],
							'alliance' => $row['alliance'],
							'aname' => $row['allitag'],
							'totalpop' => $row['totalpop'],
							'totalvillage' => $row['totalvillages'],
							'rank_pos' => $offset + count($holder) + 1,
						];
						$holder[] = $value;
					}
				} else {
					$holder = [];
					while ($row = mysqli_fetch_assoc($result)) {
						$value = [
							'userid' => $row['userid'],
							'username' => $row['username'],
							'alliance' => $row['alliance'],
							'aname' => $row['allitag'],
							'totalpop' => $row['totalpop'],
							'totalvillage' => $row['totalvillages'],
							'rank_pos' => $row['rank_pos'],
						];
						$holder[] = $value;
					}
				}

				if (empty($holder)) {
					$holder[] = [
						'userid' => 0,
						'username' => 'No User',
						'alliance' => '',
						'aname' => '',
						'totalpop' => '',
						'totalvillage' => '',
						'rank_pos' => 0,
					];
				}

				$newholder = ["pad"];
				foreach($holder as $key) $newholder[] = $key;
				$this->rankarray = $newholder;
			}

			// ========== ATTACK / DEFENSE RANKING ==========

			private function procPointRankArray($field, $pointField) {
				global $database;

				$this->ensureUserStatsFresh();

				$access_level = INCLUDE_ADMIN ? "10" : "8";
				$tribeWhere = SHOW_NATARS
					? "u.tribe <= 9 AND (u.id > 5 OR u.id = 3)"
					: "(u.tribe <= 3 OR u.tribe > 5) AND u.id > 5";

				// Count
				$countQ = "SELECT COUNT(*) as total FROM " . TB_PREFIX . "user_stats us
				           JOIN " . TB_PREFIX . "users u ON u.id = us.uid
				           WHERE us.$field >= 0 AND u.access < $access_level AND $tribeWhere";
				$countResult = mysqli_query($database->dblink, $countQ);
				$this->rankCount = (int)mysqli_fetch_assoc($countResult)['total'];

				$page = 1;
				if (isset($_SESSION['start'])) {
					$page = max(1, (int)ceil((int)$_SESSION['start'] / 20));
				}
				if (isset($_GET['rank']) && is_numeric($_GET['rank']) && $_GET['rank'] > 0) {
					$page = max(1, (int)ceil((int)$_GET['rank'] / 20));
				}
				$offset = ($page - 1) * 20;

				$q = "SELECT
						u.id AS userid,
						u.username AS username,
						us.$field AS $pointField,
						COALESCE(us.totalpop, 0) AS totalpop,
						COALESCE(us.totalvils, 0) AS totalvillages
					FROM " . TB_PREFIX . "user_stats us
					JOIN " . TB_PREFIX . "users u ON u.id = us.uid
					WHERE us.$field >= 0 AND u.access < $access_level AND $tribeWhere
					ORDER BY us.$field DESC, us.totalpop DESC, us.uid DESC
					LIMIT $offset, 20";

				$result = mysqli_query($database->dblink, $q);
				$holder = [];
				$rank = $offset + 1;
				while ($row = mysqli_fetch_assoc($result)) {
					$value = [
						'userid' => $row['userid'],
						'username' => $row['username'],
						'id' => $row['userid'],
						'totalpop' => $row['totalpop'],
						'totalvillages' => $row['totalvillages'],
						$pointField => $row[$pointField],
						'rank_pos' => $rank,
					];
					$holder[] = $value;
					$rank++;
				}

				$newholder = ["pad"];
				foreach($holder as $key) $newholder[] = $key;
				return $newholder;
			}

			public function procAttRankArray() {
				$this->rankarray = $this->procPointRankArray('apall', 'apall');
			}

			public function procDefRankArray() {
				$this->rankarray = $this->procPointRankArray('dpall', 'dpall');
			}

			// ========== VILLAGE RANKING ==========

			public function rebuildVillageRanks() {
				global $database;

				$tribeIn = SHOW_NATARS
					? "u.tribe IN(1,2,3,5,6,7,8,9)"
					: "u.tribe IN(1,2,3,6,7,8,9)";
				$accessMax = INCLUDE_ADMIN ? "10" : "8";

				$q = "INSERT INTO " . TB_PREFIX . "village_ranks
						(wref, name, pop, owner, owner_name, x, y, updated_at)
					  SELECT
						v.wref, v.name, v.pop, v.owner, u.username as owner_name, w.x, w.y, UNIX_TIMESTAMP()
					  FROM " . TB_PREFIX . "vdata v
					  JOIN " . TB_PREFIX . "users u ON v.owner = u.id
					  JOIN " . TB_PREFIX . "wdata w ON v.wref = w.id
					  WHERE $tribeIn AND v.wref != '' AND u.access < $accessMax
					  ON DUPLICATE KEY UPDATE
						name = VALUES(name),
						pop = VALUES(pop),
						owner = VALUES(owner),
						owner_name = VALUES(owner_name),
						x = VALUES(x),
						y = VALUES(y),
						updated_at = VALUES(updated_at)";

				mysqli_query($database->dblink, $q);

				// Delete villages that no longer match
				mysqli_query($database->dblink, "DELETE vr FROM " . TB_PREFIX . "village_ranks vr
					LEFT JOIN " . TB_PREFIX . "vdata v ON v.wref = vr.wref
					LEFT JOIN " . TB_PREFIX . "users u ON u.id = v.owner
					WHERE v.wref IS NULL OR u.access >= $accessMax OR u.tribe NOT IN(1,2,3,5,6,7,8,9)");

				// Update rank_pos
				mysqli_query($database->dblink, "SET @rank = 0");
				mysqli_query($database->dblink, "UPDATE " . TB_PREFIX . "village_ranks
					SET rank_pos = (@rank := @rank + 1)
					ORDER BY pop DESC, wref DESC");
			}

			public function getVRankPart($offset = 0, $limit = 20) {
				global $multisort, $database;

				$this->ensureVillageRanksFresh();

				$offset = (int)$offset;
			    $limit = (int)$limit;

				$array = $database->getVRanking($offset, $limit);

				$holder = array();
				foreach($array as $row) {
					$holder[] = $row;
				}

				$newholder = array("pad");
				foreach($holder as $key) {
					array_push($newholder, $key);
				}
				return $newholder;
			}

			public function getCountVillages() {
				global $database;
				$this->ensureVillageRanksFresh();
				$q = "SELECT COUNT(*) as total FROM " . TB_PREFIX . "village_ranks";
				$result = mysqli_query($database->dblink, $q);
				$row = mysqli_fetch_assoc($result);
				return (int)$row['total'];
			}

			public function getVillageRankPosition($wref) {
				global $database;
				$this->ensureVillageRanksFresh();
				$wref = (int)$wref;
				$q = "SELECT rank_pos FROM " . TB_PREFIX . "village_ranks WHERE wref = $wref";
				$result = mysqli_query($database->dblink, $q);
				if ($result && ($row = mysqli_fetch_assoc($result))) {
					return (int)$row['rank_pos'];
				}
				return 1;
			}

			// ========== ALLIANCE RANKING (optimized: 1 query instead of N+1) ==========

			public function procARankArray() {
				global $multisort, $database;

				$access_level = INCLUDE_ADMIN ? "10" : "8";
				$tribeWhere = SHOW_NATARS
					? "u.tribe <= 9 AND (u.id > 5 OR u.id = 3)"
					: "(u.tribe <= 3 OR u.tribe > 5) AND u.id > 5";

				$q = "SELECT
						a.id, a.name, a.tag, a.oldrank, a.Aap, a.Adp,
						COUNT(DISTINCT u.id) as players,
						COALESCE(SUM(vd.totalpop), 0) as totalpop
					FROM " . TB_PREFIX . "alidata a
					LEFT JOIN " . TB_PREFIX . "users u ON u.alliance = a.id AND u.access < $access_level AND $tribeWhere
					LEFT JOIN (
						SELECT owner, SUM(pop) as totalpop
						FROM " . TB_PREFIX . "vdata
						GROUP BY owner
					) vd ON vd.owner = u.id
					WHERE a.id != ''
					GROUP BY a.id
					ORDER BY totalpop DESC";

				$result = mysqli_query($database->dblink, $q);
				$holder = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$row['avg'] = $row['players'] > 0 ? round($row['totalpop'] / $row['players']) : 0;
					$holder[] = $row;
				}

				$this->rankCount = count($holder);

				$newholder = ["pad"];
				foreach($holder as $key) {
					array_push($newholder, $key);
				}
				$this->rankarray = $newholder;
			}

			public function procHeroRankArray() {
				global $multisort;
				$array = $GLOBALS['db']->getHeroRanking();
				$holder = array();
				foreach($array as $value) {
					$value['owner'] = $GLOBALS['db']->getUserField($value['uid'], "username", 0);
					$value['level'];
					$value['name'];
					$value['uid'];

					array_push($holder, $value);
				}
				$holder = $multisort->sorte($holder, "experience", false, 2);
				$newholder = array("pad");
				foreach($holder as $key) {
					array_push($newholder, $key);
				}
				$this->rankarray = $newholder;
				$this->rankCount = count($holder);
			}

			public function procAAttRankArray() {
				global $multisort;
				$array = $GLOBALS['db']->getARanking();
				$holder = array();
				foreach($array as $value) {
					$memberlist = $GLOBALS['db']->getAllMember($value['id']);
					$totalap = 0;
					foreach($memberlist as $member) {
						$totalap += $member['ap'];
					}
					$value['players'] = count($memberlist);
					$value['totalap'] = $totalap;
					if($value['Aap'] > 0 && count($memberlist) > 0) {
						$value['avg'] = round($totalap / count($memberlist));
					} else {
						$value['avg'] = 0;
					}

					array_push($holder, $value);
				}
				$holder = $multisort->sorte($holder, "Aap", false, 2);
				$newholder = array("pad");
				foreach($holder as $key) {
					array_push($newholder, $key);
				}
				$this->rankarray = $newholder;
			}

			public function procADefRankArray() {
				global $multisort;
				$array = $GLOBALS['db']->getARanking();
				$holder = array();
				foreach($array as $value) {
					$memberlist = $GLOBALS['db']->getAllMember($value['id']);
					$totaldp = 0;
					foreach($memberlist as $member) {
						$totaldp += $member['dp'];
					}
					$value['players'] = count($memberlist);
					$value['totaldp'] = $totaldp;
					if($value['Adp'] > 0 && count($memberlist) > 0) {
						$value['avg'] = round($totaldp / count($memberlist));
					} else {
						$value['avg'] = 0;
					}

					array_push($holder, $value);
				}
				$holder = $multisort->sorte($holder, "Adp", false, 2);
				$newholder = array("pad");
				foreach($holder as $key) {
					array_push($newholder, $key);
				}
				$this->rankarray = $newholder;
			}

			public function incrementalRebuildUserStats($lastScan) {
				global $database;
				$lastScan = (int)$lastScan;

				$result = mysqli_query($database->dblink,
					"SELECT DISTINCT v.owner FROM ".TB_PREFIX."vdata v
					 WHERE v.lastupdate_rank > $lastScan AND v.lastupdate_rank > 0");

				$dirtyUsers = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$dirtyUsers[] = (int)$row['owner'];
				}

				if (empty($dirtyUsers)) return;

				$uidList = implode(',', $dirtyUsers);

				$access_level = INCLUDE_ADMIN ? "10" : "8";
				$where_conditions_users = SHOW_NATARS
					? "u.access < " . $access_level . " AND u.tribe <= 9 AND (u.id > 5 OR u.id = 3)"
					: "u.access < " . $access_level . " AND (u.tribe <= 3 OR u.tribe > 5) AND u.id > 5";

				mysqli_query($database->dblink,
					"INSERT INTO ".TB_PREFIX."user_stats
						(uid, totalpop, totalvils, apall, dpall, ally_id, ally_tag, tribe, username, updated_at)
					  SELECT
						u.id,
						COALESCE(SUM(v.pop), 0),
						COUNT(CASE WHEN v.type != 99 THEN v.wref ELSE NULL END),
						COALESCE(u.apall, 0),
						COALESCE(u.dpall, 0),
						u.alliance,
						ad.tag,
						u.tribe,
						u.username,
						UNIX_TIMESTAMP()
					  FROM ".TB_PREFIX."users u
					  LEFT JOIN ".TB_PREFIX."vdata v ON v.owner = u.id
					  LEFT JOIN ".TB_PREFIX."alidata ad ON ad.id = u.alliance
					  WHERE $where_conditions_users AND u.id IN ($uidList)
					  GROUP BY u.id
					  ON DUPLICATE KEY UPDATE
						totalpop = VALUES(totalpop),
						totalvils = VALUES(totalvils),
						apall = VALUES(apall),
						dpall = VALUES(dpall),
						ally_id = VALUES(ally_id),
						ally_tag = VALUES(ally_tag),
						tribe = VALUES(tribe),
						username = VALUES(username),
						updated_at = VALUES(updated_at)");

				$delWhere = "u.access >= " . $access_level;
				if (SHOW_NATARS) {
					$delWhere = "(u.access >= " . $access_level . " OR (u.tribe > 9 AND u.id != 3) OR u.id <= 5) AND u.id != 3";
				} else {
					$delWhere = "(u.access >= " . $access_level . " OR (u.tribe = 4 OR u.tribe = 5) OR u.id <= 5)";
				}
				mysqli_query($database->dblink, "DELETE us FROM ".TB_PREFIX."user_stats us
					LEFT JOIN ".TB_PREFIX."users u ON u.id = us.uid
					WHERE ($delWhere OR u.id IS NULL) AND us.uid IN ($uidList)");

				$this->recalculateUserRanks();
			}

			public function recalculateUserRanks() {
				global $database;
				$p = TB_PREFIX;

				mysqli_query($database->dblink, "DROP TABLE IF EXISTS {$p}user_stats_shadow");
				mysqli_query($database->dblink, "CREATE TABLE {$p}user_stats_shadow LIKE {$p}user_stats");
				mysqli_query($database->dblink, "INSERT INTO {$p}user_stats_shadow SELECT * FROM {$p}user_stats");
				mysqli_query($database->dblink, "SET @rank = 0");
				mysqli_query($database->dblink,
					"UPDATE {$p}user_stats_shadow SET rank_pos = (@rank := @rank + 1)
					 ORDER BY totalpop DESC, totalvils DESC, uid DESC");
				mysqli_query($database->dblink,
					"RENAME TABLE {$p}user_stats TO {$p}user_stats_bak,
							   {$p}user_stats_shadow TO {$p}user_stats");
				mysqli_query($database->dblink, "DROP TABLE IF EXISTS {$p}user_stats_bak");
			}

			public function incrementalRebuildVillageRanks($lastScan) {
				global $database;
				$lastScan = (int)$lastScan;

				$tribeIn = SHOW_NATARS
					? "u.tribe IN(1,2,3,5,6,7,8,9)"
					: "u.tribe IN(1,2,3,6,7,8,9)";
				$accessMax = INCLUDE_ADMIN ? "10" : "8";

				$result = mysqli_query($database->dblink,
					"SELECT v.wref FROM ".TB_PREFIX."vdata v
					 JOIN ".TB_PREFIX."users u ON u.id = v.owner
					 WHERE v.lastupdate_rank > $lastScan AND v.lastupdate_rank > 0
					 AND $tribeIn AND v.wref != '' AND u.access < $accessMax");

				$dirtyVillages = [];
				while ($row = mysqli_fetch_assoc($result)) {
					$dirtyVillages[] = (int)$row['wref'];
				}

				if (empty($dirtyVillages)) return;

				$wrefList = implode(',', $dirtyVillages);

				mysqli_query($database->dblink,
					"INSERT INTO ".TB_PREFIX."village_ranks
						(wref, name, pop, owner, owner_name, x, y, updated_at)
					  SELECT
						v.wref, v.name, v.pop, v.owner, u.username, w.x, w.y, UNIX_TIMESTAMP()
					  FROM ".TB_PREFIX."vdata v
					  JOIN ".TB_PREFIX."users u ON v.owner = u.id
					  JOIN ".TB_PREFIX."wdata w ON v.wref = w.id
					  WHERE v.wref IN ($wrefList)
					  ON DUPLICATE KEY UPDATE
						name = VALUES(name), pop = VALUES(pop),
						owner = VALUES(owner), owner_name = VALUES(owner_name),
						x = VALUES(x), y = VALUES(y),
						updated_at = VALUES(updated_at)");

				mysqli_query($database->dblink, "DELETE vr FROM ".TB_PREFIX."village_ranks vr
					LEFT JOIN ".TB_PREFIX."vdata v ON v.wref = vr.wref
					LEFT JOIN ".TB_PREFIX."users u ON u.id = v.owner
					WHERE (v.wref IS NULL OR u.access >= $accessMax OR u.tribe NOT IN(1,2,3,5,6,7,8,9))
					AND vr.wref IN ($wrefList)");

				$this->recalculateVillageRanks();
			}

			public function recalculateVillageRanks() {
				global $database;
				$p = TB_PREFIX;

				mysqli_query($database->dblink, "DROP TABLE IF EXISTS {$p}village_ranks_shadow");
				mysqli_query($database->dblink, "CREATE TABLE {$p}village_ranks_shadow LIKE {$p}village_ranks");
				mysqli_query($database->dblink, "INSERT INTO {$p}village_ranks_shadow SELECT * FROM {$p}village_ranks");
				mysqli_query($database->dblink, "SET @rank = 0");
				mysqli_query($database->dblink,
					"UPDATE {$p}village_ranks_shadow SET rank_pos = (@rank := @rank + 1)
					 ORDER BY pop DESC, wref DESC");
				mysqli_query($database->dblink,
					"RENAME TABLE {$p}village_ranks TO {$p}village_ranks_bak,
							   {$p}village_ranks_shadow TO {$p}village_ranks");
				mysqli_query($database->dblink, "DROP TABLE IF EXISTS {$p}village_ranks_bak");
			}
		}
		;

		$ranking = new Ranking;

?>
