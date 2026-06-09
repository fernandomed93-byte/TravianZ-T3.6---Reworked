<?php

trait DBSetup {

    /**
	 * Creates a database structure for the game.
	 * Used during installation.
	 *
	 * @return boolean|number Returns TRUE, FALSE or -1. True is for successful data import
	 *                        (from prepared SQL file), false is in case of an SQL error.
	 *                        -1 will be returned in case of any unexpected behavior
	 *                        and unhandled exceptions.
	 */

    public function createDbStructure() {
        global $autoprefix;

        try {
            // check that we don't have the structure in place already
            // (we'd have at least 1 user present, since 4 are being created by default - Support, Nature, Multihunter & Taskmaster)
            try {
                $data_exist = $this->query_return("SELECT * FROM " . TB_PREFIX . "users LIMIT 1");
                if ($data_exist && count($data_exist)) {
                    return false;
                }
            } catch (\Exception $e) {

            }

            // load the DB structure SQL file
            $str = file_get_contents($autoprefix."var/db/struct.sql");
            $str = preg_replace("'%PREFIX%'", TB_PREFIX, $str);
            $result = $this->dblink->multi_query($str);

            // fetch results of the multi-query in order to allow subsequent query() and multi_query() calls to work
            while (mysqli_more_results($this->dblink) && mysqli_next_result($this->dblink)) {;}

            if (!$result) {
                return false;
            }
        } catch (\Exception $e) {
            echo($e);
            return -1;
        }

        return true;
    }

    /**
     * Populates the game database with Map World Data (i.e. creates the whole
     * world with X,Y coordinate squares and their types).
     *
     * Also populates oasis' table data for the squares where there are oasis.
     *
     * @return boolean|number Returns TRUE, FALSE or -1. True is for successful data import
	 *                        (from prepared SQL file), false is in case of an SQL error.
	 *                        -1 will be returned in case of any unexpected behavior
	 *                        and unhandled exceptions.
     */
    public function populateWorldData() {
        global $autoprefix;

        try {
            // check if we don't already have world data
            $data_exist = $this->query_return("SELECT * FROM " . TB_PREFIX . "wdata LIMIT 1");
            if ($data_exist && count($data_exist)) {
                return false;
            }

            // load the data generation SQL file
            $str = file_get_contents($autoprefix."var/db/datagen-world-data.sql");
            $str = preg_replace(["'%PREFIX%'", "'%WORLDSIZE%'"], [TB_PREFIX, WORLD_MAX], $str);
            $result = $this->dblink->multi_query($str);

            // fetch results of the multi-query in order to allow subsequent query() and multi_query() calls to work
            while (mysqli_more_results($this->dblink) && mysqli_next_result($this->dblink)) {;}

            if (!$result) {
                return -1;
            }

            $result = $this->regenerateOasisUnits(-1);
            if (!$result) {
                return -1;
            }
        } catch (\Exception $e) {
            return -1;
        }

        return true;
    }

    function enableWWstatistics() {
		$q = "UPDATE " . TB_PREFIX . "config SET enableWWstatistics = 1";
		return mysqli_query($this->dblink,$q);
	}

    /***************************
	Function to get WW name
	Made by: Dzoki
	***************************/
	function getWWName($vref) {
	    list($vref) = $this->escape_input((int) $vref);

		$q = "SELECT wwname FROM " . TB_PREFIX . "fdata WHERE vref = $vref LIMIT 1";
		$result = mysqli_query($this->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		return $dbarray['wwname'];
	}

	/***************************
	Function to change WW name
	Made by: Dzoki
	***************************/
	function submitWWname($vref, $name) {
	    list($vref, $name) = $this->escape_input((int) $vref, $name);

		$q = "UPDATE " . TB_PREFIX . "fdata SET `wwname` = '$name' WHERE " . TB_PREFIX . "fdata.`vref` = $vref";
		return mysqli_query($this->dblink,$q);
	}

    function isThereAWinner(){
    	$q = "SELECT Count(*) as Total FROM ".TB_PREFIX."fdata WHERE f99 = 100 and f99t = 40";
    	$result = mysqli_fetch_array(mysqli_query($this->dblink, $q), MYSQLI_ASSOC);
    	return $result['Total'] > 0;
    }

}
