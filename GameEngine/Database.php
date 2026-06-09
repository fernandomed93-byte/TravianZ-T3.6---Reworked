<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Project:       TravianZ                                                    ##
##  Version:       22.06.2015                    			       ##
##  Filename       db_MYSQL.php                                                ##
##  Developed by:  Mr.php , Advocaite , brainiacX , yi12345 , Shadow , ronix   ##
##  Fixed by:      Shadow - STARVATION , HERO FIXED COMPL.  		       ##
##  Fixed by:      InCube - double troops				       ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2015. All rights reserved.                ##
##  URLs:          http://travian.shadowss.ro                		       ##
##  Source code:   https://github.com/Shadowss/TravianZ		               ##
##                                                                             ##
#################################################################################

global $autoprefix;

// even with autoloader created, we can't use it here yet, as it's not been created
// ... so, let's see where it is and include it
$autoloader_found = false;
// go max 5 levels up - we don't have folders that go deeper than that
for ($i = 0; $i < 5; $i++) {
    $autoprefix = str_repeat('../', $i);
    if (file_exists($autoprefix.'autoloader.php')) {
        $autoloader_found = true;
        include_once $autoprefix.'autoloader.php';
        break;
    }
}

if (!$autoloader_found) {
    die('Could not find autoloading class.');
}

include_once($autoprefix."GameEngine/config.php");

use App\Database\IDbConnection;
use App\Utils\Math;

require_once "Database/Forum.trait.php";
require_once "Database/User.trait.php";
require_once "Database/Village.trait.php";
require_once "Database/Building.trait.php";
require_once "Database/Alliance.trait.php";
require_once "Database/Troops.trait.php";
require_once "Database/Hero.trait.php";
require_once "Database/Movement.trait.php";
require_once "Database/Market.trait.php";
require_once "Database/Message.trait.php";
require_once "Database/Artifact.trait.php";
require_once "Database/Ranking.trait.php";
require_once "Database/Oasis.trait.php";
require_once "Database/FarmList.trait.php";
require_once "Database/Prisoner.trait.php";
require_once "Database/Automation.trait.php";
require_once "Database/Setup.trait.php";

class MYSQLi_DB implements IDbConnection {
    use DBForum;
    use DBUser;
    use DBVillage;
    use DBBuilding;
    use DBAlliance;
    use DBTroops;
    use DBHero;
    use DBMovement;
    use DBMarket;
    use DBMessage;
    use DBArtifact;
    use DBRanking;
    use DBOasis;
    use DBFarmList;
    use DBPrisoner;
    use DBAutomation;
    use DBSetup;

    private
        /**
         * @var string MySQL server hostname to connect to.
         */
        $hostname = SQL_SERVER,

        /**
         * @var int MySQL server port to connect to.
         */
        $port = SQL_PORT,

        /**
         * @var string Username to authenticate with to the MySQL connection.
         */
        $username = SQL_USER,

        /**
         * @var string Password to authenticate with to the MySQL connection.
         */
        $password = SQL_PASS,

        /**
         * @var string Database to use with TravianZ.
         */
        $dbname = SQL_DB,

        /**
         * @var int Counter of all SELECT queries performed.
         */
        $selectQueryCount = 0,

        /**
         * @var int Counter of all INSERT queries performed.
         */
        $insertQueryCount = 0,

        /**
         * @var int Counter of all UPDATE queries performed.
         */
        $updateQueryCount = 0,

        /**
         * @var int Counter of all DELETE queries performed.
         */
        $deleteQueryCount = 0,

        /**
         * @var int Counter of all REPLACE queries performed.
         */
        $replaceQueryCount = 0;

    // variables for DB-cached data for this request
    private static
        /**
         * @var array Cache of user fields and their values.
         */
        $fieldsCache = [],

        /**
         * @var array Cache of village fields and their values.
         */
        $villageFieldsCache = [],
        $villageFieldsCache2 = [],

        /**
         * @var array Cache of village fields and their values, retrieved by world ID.
         */
        $villageFieldsCacheByWorldID = [],

        /**
         * @var array Cache of village IDs for users.
         */
        $villageIDsCache = [],

        /**
         * @var array Cache of village IDs for users, using simple associative arrays.
         */
        $villageIDsCacheSimple = [],

        /**
         * @var array Cache of village battle data.
         */
        $villageBattleDataCache = [],

        /**
         * @var array Cache of village data by owner IDs.
         */
        $villageDataByOwnerCache = [],

        /**
         * @var array Cache of world and village data.
         */
        $worldAndVillageDataCache = [],

        /**
         * @var array Cache of world and oasis data.
         */
        $worldAndOasisDataCache = [],

        /**
         * @var array Cache of last topic check for a category.
         */
        $lastTopicCheckCache = [],

        /**
         * @var array Cache of last post check for a topic.
         */
        $lastPostForTopicCheckCache = [],

        /**
         * @var array Cache of last post for a topic.
         */
        $lastPostForTopicCache = [],

        /**
         * @var array Cache of topics count for a user.
         */
        $topicCountCache = [],

        /**
         * @var array Cache of edit results.
         */
        $editResultsCache = [],

        /**
         * @var array Cache of users count.
         */
        $usersCountCache = [],

        /**
         * @var array Cache of alliances count.
         */
        $allianceCountCache = [],

        /**
         * @var array Cache of alliance data from the DB.
         */
        $allianceDataCache = [],

        /**
         * @var array Cache of alliance permissions.
         */
        $alliancePermissionsCache = [],

        /**
         * @var array Cache of alliance members.
         */
        $allianceMembersCache = [],

        /**
         * @var array Cache of alliance member counts.
         */
        $allianceMembersCountCache = [],

        /**
         * @var array Cache of alliance owner checks.
         */
        $allianceOwnerCheckCache = [],

        /**
         * @var array Cache of alliance allies.
         */
        $allianceAlliesCache = [],

        /**
         * @var array Cache of alliance ranking.
         */
        $allianceRankingCache = [],

        /**
         * @var array Cache of user alliances.
         */
        $userAllianceCache = [],

        /**
         * @var array Cache of user summary fields.
         */
        $userSumFieldCache = [],

        /**
         * @var array Cache of artefact infos.
         */
        $artefactDataCache = [],

        /**
         * @var array Cache of own artefact infos by type.
         */
        $artefactInfoByTypeCache = [],

        /**
         * @var array Cache of heroes.
         */
        $heroCache = [],

        /**
         * @var array Cache of hero field values.
         */
        $heroFieldCache = [],

        /**
         * @var array Cache of market field values.
         */
        $marketFieldCache = [],

        /**
         * @var array Cache of market movement values.
         */
        $marketMovementCache = [],

        /**
         * @var array Cache of units in a village.
         */
        $unitsCache = [],

        /**
         * @var array Cache of reinforcements in a village.
         */
        $villageReinforcementsCache = [],

        /**
         * @var array Cache of reinforcements by from ID and village ID.
         */
        $villageFromReinforcementsCache = [],

        /**
         * @var array Cache of reinforcements by ID.
         */
        $reinforcementsCache = [],

        /**
         * @var array Cache of oasis reinforcements by conquered & from ID.
         */
        $oasisReinforcementsCache = [],

        /**
         * @var array Cache of oasis reinforcements by ID.
         */
        $oasisArrayReinforcementsCache = [],

        /**
         * @var array Cache of prisoners.
         */
        $prisonersCache = [],

        /**
         * @var array Cache of prisoners by ID.
         */
        $prisonersCacheByID = [],

        /**
         * @var array Cache of prisoners by village ID and from ID.
         */
        $prisonersCacheByVillageAndFromIDs = [],
		
		$prisonersCacheByFromID = [],

        /**
         * @var array Cache of resource levels.
         */
        $resourceLevelsCache = [],

        /**
         * @var array Cache of field levels in village search.
         */
        $fieldLevelsInVillageSearchCache = [],

        /**
         * @var array Cache of field levels.
         */
        $fieldLevelsCache = [],

        /**
         * @var array Cache of single field type for users.
         */
        $singleFieldTypeCountCache = [],

        /**
         * @var array Cache of field types.
         */
        $fieldTypeCache = [],

        /**
         * @var array Cache of oasis data.
         */
        $oasisFieldsCache = [],

        /**
         * @var array Cache of oasis data by conquered ID.
         */
        $oasisFieldsCacheByConqueredID = [],

        /**
         * @var array Cache of research.
         */
        $abTechCache = [],

        /**
         * @var array Cache of oasis' count for a village.
         */
        $oasisCountCache = [],

        /**
         * @var array Cache of oasis' troops count.
         */
        $oasisTroopsCountCache = [],

        /**
         * @var array Cache of oasis' conquerable status.
         */
        $oasisConquerableCache = [],

        /**
         * @var array Cache of notices by ID.
         */
        $noticesCacheById = [],

        /**
         * @var array Cache of merchants used count.
         */
        $merchantsUseCountCache = [],

        /**
         * @var array Cache of crop production starvation value.
         */
        $cropProductionStarvationValueCache = [],

        /**
         * @var array Cache of profile villages.
         */
        $userVillagesCache = [],

        /**
         * @var array Cache of fdata research values.
         */
        $isResearchedCache = [],

        /**
         * @var array Cache of items in research.
         */
        $researchingCache = [],

        /**
         * @var array Cache of buildings being under construction.
         */
        $buildingsUnderConstructionCache = [],

        /**
         * @var array Cache of messages to be sent out to players,
         *            so we can collect them and send them out together
         *            at the end of script execution.
         */
        $sendMessageQueryCache = [],

        /**
         * @var int Maximum number of INSERT query values to cache in the sendMessageQueryCache.
         *          Once this amount is reached, the cache is flushed and a single query with all
         *          the cached values is executed.
         */
        $sendMessageQueryCacheMaxRecords = 75;

        private $automationConfigCache = null;
        private $configCache = null;

	public $dblink;

	/**
	 *
	 * Constructor.
	 * Will initialize the connection to MySQL
	 * and die on any error it would encounter.
	 *
	 * @example $db = new MYSQLi_DB(SQL_SERVER, SQL_USER, SQL_PASS, SQL_DB);
	 *
	 * @param   string $hostname Hostname of the MySQL server.
	 * @param   string $username Username to be used to to connect.
	 * @param   string $password Password to be used to to connect.
	 * @param   string $dbname   Name of the database to use.
	 * @param   int    $port     [Optional] server port to connect to. Default: 3306
	 * @return  void   This method doesn't have a return value.
	 */
	public function __construct($hostname, $username, $password, $dbname, $port = 3306) {
	    $this->hostname = $hostname;
	    $this->port     = $port;
	    $this->username = $username;
	    $this->password = $password;
	    $this->dbname   = $dbname;

	    // connect to the DB
	    if (!$this->connect()) die(mysqli_error($this->dblink));

		// we will operate in UTF8
		mysqli_query($this->dblink,"SET NAMES 'UTF8'");
	}

	/**
	 * {@inheritDoc}
	 * @see \App\Database\IDbConnection::connect()
	 */
	public function connect() {
	    // try to connect
        try {
            $this->dblink = mysqli_connect( $this->hostname, $this->username, $this->password, $this->dbname, $this->port );
        } catch (\Exception $exception) {
            $this->dblink = mysqli_connect( $this->hostname . ':' . $this->port, $this->username, $this->password );

            // return on error
            if (mysqli_error($this->dblink)) {
                return false;
            }

            // select the DB to use
            mysqli_select_db($this->dblink, $this->dbname);
        }

	    // return on error
	    if (mysqli_error($this->dblink)) {
	        return false;
	    } else {
	        // connected and DB exists, we're good to go
	        return true;
	    }
	}

	/**
	 * {@inheritDoc}
	 * @see \App\Database\IDbConnection::disconnect()
	 */
	public function disconnect() {
	    if ($this->dblink) {
	        if (!$this->dblink->close()) {
	            return false;
	        }

	        $this->dblink = null;
	    }

	    return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \App\Database\IDbConnection::reconnect()
	 */
	public function reconnect() {
	    $this->disconnect();
	    return $this->connect();
	}

	/**
	 * {@inheritDoc}
	 * @see \App\Database\IDbConnection::query_new()
	 */
	public function query_new($statement, ...$params) {
	    if ($prep = mysqli_prepare($this->dblink, $statement)) {
	        // if we're doing a multi-update/insert/delete query,
	        // we'll need to mark it as such
	        $is_multi_query = false;

	        // determine the nature of this query
	        preg_match('/[^AZ-az]*(\()?[^AZ-az]*SELECT/i', $statement, $select_matches);
	        preg_match('/[^AZ-az]*(\()?[^AZ-az]*DELETE/i', $statement, $delete_matches);
	        preg_match('/[^AZ-az]*(\()?[^AZ-az]*INSERT/i', $statement, $insert_matches);
	        preg_match('/[^AZ-az]*(\()?[^AZ-az]*REPLACE/i', $statement, $replace_matches);
	        preg_match('/[^AZ-az]*(\()?[^AZ-az]*UPDATE/i', $statement, $update_matches);

	        // a single array parameter means that we're batching multiple
	        // value feeds for a single prepared statement, so we just use
	        // the first array value to actually prepare the statement
	        // and determine all the binding types
	        if (count($params) == 1) {
	            $paramsArray = $params[0];
	            $is_multi_query = true;
	        } else {
	            $paramsArray = $params;
	            // convert method parameters into an array,
	            // so we can reuse it in both cases - when we're executing
	            // just a single prepared statement and also when we're
	            // batching up multiple values for an insert/update/delete statement
	            $params = [$params];
	        }

	        // determine and prepare parameter types
	        $types = [];
	        foreach ($paramsArray as $param) {
	            // default to string, change if neccessary
	            $paramType = 's';

	            if (Math::isInt($param)) {
	                $paramType = 'i';
	            } else if (Math::isFloat($param)) {
	                $paramType = 'd';
	            }

	            $types[] = $paramType;
	        }

	        // dynamically bind each data batch using previously
	        // defined parameters
	        $implodedNames = [implode('', $types)];
	        $outputValues = [];

	        foreach ($params as $dataBatch) {
	            $bind_names = $implodedNames;
    	        for ($i=0; $i<count($dataBatch); $i++) {
    	            $bind_name = 'bind' . $i;
    	            $$bind_name = $dataBatch[$i];
    	            $bind_names[] = &$$bind_name;
    	        }
    	        call_user_func_array(array($prep, 'bind_param'), $bind_names);

        	    // SELECT
        	    if (count($select_matches)) {
                    // execute the statement to get its value back
                    if (mysqli_stmt_execute($prep)) {
                        $this->selectQueryCount++;
                        $queryResult = [];

                        // read metadata, so we know what fields we were actually selecting
                        // and can prepare our temporary variables to read them into
                        $resultMetaData = mysqli_stmt_result_metadata($prep);

                        $stmtRow = array();
                        $rowReferences = array();
                        while ($field = mysqli_fetch_field($resultMetaData)) {
                            $rowReferences[] = &$stmtRow[$field->name];
                        }
                        mysqli_free_result($resultMetaData);

                        // now call bind_result with all our variables to recive the data prepared
                        call_user_func_array(array($prep, 'bind_result'), $rowReferences);

                        // prepare the array-ed result
                        while(mysqli_stmt_fetch($prep)){
                            $row = array();
                            foreach($stmtRow as $key => $value){
                                $row[$key] = $value;
                            }
                            $queryResult[] = $row;
                        }

                        // free the result
                        mysqli_stmt_free_result($prep);

                        $outputValues[] = $queryResult;
                    } else {
                        throw new Exception('Failed to execute an SQL statement!');
                    }
        	    }
    	    }

    	    // free the prepared statement
    	    mysqli_stmt_close($prep);

    	    // return the expected result
    	    if (count($select_matches)) {
    	        // if there is only a single result, return it alone
    	        if (count($outputValues) === 1) {
    	            return $outputValues[0];
    	        } else {
    	            // otherwise, return all the data
    	            return $outputValues;
    	        }
    	    }
        } else {
            throw new Exception('Failed to prepare an SQL statement!');
	    }

	    return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \App\Database\IDbConnection::is_connected()
	 */
	public function is_connected() {
	    return ($this->dblink ? true : false);
	}

    /***************************
    Function to process MYSQLi->fetch_all (Only exist in MYSQL)
    References: Result
     ***************************/
    function mysqli_fetch_all($result) {
        list($result) = $this->escape_input($result);

        $all = [];
        if($result) {
            while($row = mysqli_fetch_assoc($result)) {
                $all[] = $row;
            }
            return $all;
        }
    }

    function query_return($q) {
        $result = mysqli_query($this->dblink,$q);
        return $this->mysqli_fetch_all($result);
    }

    /***************************
    Function to do free query
    References: Query
     ***************************/
    function query($query) {
        return mysqli_query($this->dblink,$query);
    }

    function RemoveXSS($val) {
        list($val) = $this->escape_input($val);

        return htmlspecialchars($val, ENT_QUOTES);
    }

    /**
     * Returns a value previously cached from the database, if present.
     *
     * @param $arrayVariable  array  Reference to the static array in Database class to use for the lookup.
     * @param $arrayFieldName string The actual array field name to look a cached value for.
     *
     * @return Returns the requested cached value or null if it's not cached yet.
     */
	private static function returnCachedContent(&$arrayVariable, $arrayStructure) {
        if (!isset($arrayVariable[$arrayStructure])) {
            $arrayVariable[$arrayStructure] = [];
        }

        if (isset($arrayVariable[$arrayStructure]) && !empty($arrayVariable[$arrayStructure])) {
            return $arrayVariable[$arrayStructure];
        } 
        else return null;
    }

    /**
     * Clears cached village data, so after automation is run, we can re-load new data (like resource levels etc)
     * to be displayed in the front-end.
     */
    public static function clearVillageCache() {
        self::$villageFieldsCache          = [];
        self::$villageFieldsCacheByWorldID = [];
    }

    /**
     * Returns a string value safely escaped to be used in mysqli_query() method.
     *
     * @param $value string The value to sanitize.
     *
     * @return string Returns a sanitized string, safe for SQL queries.
     */
	function escape($value) {
	    if (is_string($value)) {
            $value = stripslashes( $value );
            return mysqli_real_escape_string($this->dblink, $value);
        } else {
	        return $value;
        }
	}

    /**
     * Returns a list of safely escaped values which can be used to re-retrieve
     * them in a list() method.
     *
     * @example list($username, $password) = $database->escape_input($username, $password);
     *
     * @return array Returns an array with all items sanitized and safe to be used in SQL statements.
     */
	function escape_input() {
	    $numargs = func_num_args();
	    $arg_list = func_get_args();
	    $ret = [];

	    for ($i = 0; $i < $numargs; $i++) {
	        if (is_string($arg_list[$i])) {
               $arg_list[$i] = stripslashes($arg_list[$i]);
	           $res[] = mysqli_real_escape_string($this->dblink, $arg_list[$i]);
	        } else {
	           $res[] = $arg_list[$i];
	        }
	    }

	    return $res;
	}

	function return_link() {
		return $this->dblink;
	}  

	function getAdminLog() {
		$q = "SELECT id,user,log,time from " . TB_PREFIX . "admin_log where id != 0 ORDER BY id DESC";
		$result = mysqli_query($this->dblink,$q);
		return $this->mysqli_fetch_all($result);
	}

	/**
	 * Display a system message to all players
	 * 
	 * @param string $message The text of the system message that will be written and displayed to all players
	 */
	
	function displaySystemMessage($message){	
		list($message) = $this->escape_input($message);
		global $autoprefix;
		
		$myFile = $autoprefix."Templates/text.tpl";
		$fh = fopen($myFile, 'w');
		$text = file_get_contents($autoprefix."Templates/text_format.tpl");
		$text = preg_replace("'%TEKST%'", $message, $text);
		fwrite($fh, $text);
		
		//Set "OK" to 1 to all players, so they can visualize the message
		$this->setUsersOk();
	}
	
	/**
	 * Escreve uma mensagem de log enriquecida com o contexto da execução.
	 * Inclui o nome da página/script e o método/função que originou a chamada.
	 *
	 * @param string $message A mensagem de log que você quer registrar.
	 * @param string $level Opcional: Um nível de log como "INFO", "WARNING", "DEBUG".
	 */
	function log_with_context($message, $level = 'INFO') {
		// 1. Obter a "pilha de chamadas" (backtrace)
		// O limite '2' é para performance, só pegamos o item atual e quem o chamou.
		// DEBUG_BACKTRACE_IGNORE_ARGS ignora os argumentos das funções, deixando o log mais limpo.
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

		$caller = 'N/A';
		// O item [1] do array de trace é quem chamou a função log_with_context
		if (isset($trace[1])) {
			$callerInfo = $trace[1];
			// Formata o nome do chamador, incluindo a classe se for um método de objeto
			$caller = (isset($callerInfo['class']) ? $callerInfo['class'] . $callerInfo['type'] : '') . $callerInfo['function'];
		}

		// 2. Obter a página atual que está sendo executada
		$page = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'CLI'; // 'CLI' para scripts de linha de comando

		// 3. Montar a mensagem de log final e formatada
		$logMessage = sprintf(
			"[%s] [%s] [%s] >> %s",
			$level,
			$page,
			$caller,
			$message
		);

		// 4. Escrever no log de erros padrão do PHP
		error_log($logMessage);
	}
	
};

// database is not needed if we're displaying static pages
$req_file = basename($_SERVER['PHP_SELF']);
if (!in_array($req_file, ['tutorial.php', 'anleitung.php'])) {
    $database = new MYSQLi_DB(SQL_SERVER, SQL_USER, SQL_PASS, SQL_DB, (defined('SQL_PORT') ? SQL_PORT : 3306));
    $link = $database->return_link();
    $GLOBALS['db'] = $database;
    $GLOBALS['link'] = $database->return_link();

    // register all functions to be executed when the script is over,
    // so we can flush any SQL caches we may still have pending
    register_shutdown_function(function() {
        global $database;
        $database->sendPendingMessages();
    });
}


?>
