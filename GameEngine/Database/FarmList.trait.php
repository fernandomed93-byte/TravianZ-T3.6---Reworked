<?php

trait DBFarmList {

    // no need to cache this method
	function getVilFarmlist($uid) {
		list($uid) = $this->escape_input((int) $uid);
		
		$q = 'SELECT * FROM ' . TB_PREFIX . 'farmlist WHERE owner = '.$uid.' ORDER BY wref ASC LIMIT 1';
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		return $dbarray['id'] > 0;
	}

    // no need to cache this method
	function getRaidList($id) {
	    list($id) = $this->escape_input((int) $id);

		$q = "SELECT * FROM " . TB_PREFIX . "raidlist WHERE id = ".$id." LIMIT 1";
		$result = mysqli_query($this->dblink, $q);
		return mysqli_fetch_array($result);
	}
	
	/**
	 * Get all informations about a farm list
	 * 
	 * @param int $id The farmlist ID
	 * @return array Returns the seleted farm list informations
	 */

	function getFLData($id) {
		list($id) = $this->escape_input((int) $id);
		
		$q = "SELECT * FROM " . TB_PREFIX . "farmlist where id = $id LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		return mysqli_fetch_array($result);
	}
	

	function delFarmList($id, $owner) {
	    list($id, $owner) = $this->escape_input((int) $id, (int) $owner);

		$q = "DELETE FROM " . TB_PREFIX . "farmlist where id = $id and owner = $owner";
		if(mysqli_query($this->dblink, $q) && mysqli_affected_rows($this->dblink) > 0){
			$q = "DELETE FROM " . TB_PREFIX . "raidlist where lid = $id";
			return mysqli_query($this->dblink, $q);
		}
		return false;
	}

    function delSlotFarm($id, $owner, $lid) {
	    list($id, $owner, $lid) = $this->escape_input((int) $id, (int) $owner, (int) $lid);
	    
		$q = "DELETE FROM " . TB_PREFIX . "raidlist WHERE id = $id AND lid = $lid AND EXISTS(SELECT 1 FROM " . TB_PREFIX . "farmlist WHERE id = $lid AND owner = $owner)";
		return mysqli_query($this->dblink,$q);
	}

	function createFarmList($wref, $owner, $name) {
        list($wref, $owner, $name) = $this->escape_input($wref, $owner, $name);

		$q = "INSERT INTO " . TB_PREFIX . "farmlist (`wref`, `owner`, `name`) VALUES ('$wref', '$owner', '$name')";
		return mysqli_query($this->dblink,$q);
	}

	function addSlotFarm($lid, $towref, $x, $y, $distance, $t1, $t2, $t3, $t4, $t5, $t6) {
        list($lid, $towref, $x, $y, $distance, $t1, $t2, $t3, $t4, $t5, $t6) = $this->escape_input($lid, $towref, $x, $y, $distance, $t1, $t2, $t3, $t4, $t5, $t6);
        
	    for($i = 1; $i <= 6; $i++) {
            if (${'t'.$i} == '') {
                ${'t'.$i} = 0;
            }
        }
		$q = "INSERT INTO " . TB_PREFIX . "raidlist (`lid`, `towref`, `x`, `y`, `distance`, `t1`, `t2`, `t3`, `t4`, `t5`, `t6`) VALUES ('$lid', '$towref', '$x', '$y', '$distance', '$t1', '$t2', '$t3', '$t4', '$t5', '$t6')";
		return mysqli_query($this->dblink,$q);
	}

	function editSlotFarm($eid, $lid, $oldLid, $owner, $wref, $x, $y, $dist, $t1, $t2, $t3, $t4, $t5, $t6) {
		list($eid, $lid, $oldLid, $owner, $wref, $x, $y, $dist, $t1, $t2, $t3, $t4, $t5, $t6) = $this->escape_input((int) $eid, $lid, $oldLid, $owner, $wref, $x, $y, $dist, $t1, $t2, $t3, $t4, $t5, $t6);

	    for($i = 1; $i <= 6; $i++) {
            if (${'t'.$i} == '') {
                ${'t'.$i} = 0;
            }
        }
		$q = "UPDATE " . TB_PREFIX . "raidlist SET lid = '$lid', towref = '$wref', x = '$x', y = '$y', t1 = '$t1', t2 = '$t2', t3 = '$t3', t4 = '$t4', t5 = '$t5', t6 = '$t6' WHERE id = $eid AND lid = $oldLid AND EXISTS(SELECT 1 FROM " . TB_PREFIX . "farmlist WHERE id = $lid AND owner = $owner) AND EXISTS(SELECT 1 FROM " . TB_PREFIX . "farmlist WHERE id = $oldLid AND owner = $owner)";
		return mysqli_query($this->dblink,$q);
	}

}
