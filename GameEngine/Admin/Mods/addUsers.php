<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       addUsers.php                                                ##
##  Created by     KFCSpike                                                    ##
##  Improve by     ronix                                                       ##
##  Developed by:  Shadow                                                      ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2025. All rights reserved.                ##
##                                                                             ##
#################################################################################

use App\Entity\User;

// go max 5 levels up - we don't have folders that go deeper than that
$autoprefix = '';
for ($i = 0; $i < 5; $i++) {
    $autoprefix = str_repeat('../', $i);
    if (file_exists($autoprefix.'autoloader.php')) {
        // we have our path, let's leave
        break;
    }
}

include_once($autoprefix."GameEngine/config.php");
include_once($autoprefix."GameEngine/Session.php");
include_once($autoprefix."GameEngine/Database.php");
include_once($autoprefix."GameEngine/Admin/database.php");

$wgarray=array(1=>1200,1700,2300,3100,4000,5000,6300,7800,9600,11800,14400,17600,21400,25900,31300,37900,45700,55100,66400,80000);

foreach ($_POST as $key => $value) {
    $_POST[$key] = $database->escape($value);
}

$id = (int) $_POST['id'];
$baseName = $_POST['users_base_name'];
$amount = (int) $_POST['users_amount'];
$beginnersProtection = $_POST['users_protection'];
$postTribe = (int) $_POST['tribe'];

// Some basic error checking
if (strlen($baseName) < 4){
    header("Location: ../../../Admin/admin.php?p=addUsers&e=BN2S&bn=$baseName&am=$amount");
	exit;
} elseif (strlen($baseName) > 20){
    header("Location: ../../../Admin/admin.php?p=addUsers&e=BN2L&bn=$baseName&am=$amount");
	exit;
} elseif ($amount < 1){
    header("Location: ../../../Admin/admin.php?p=addUsers&e=AMLO&bn=$baseName&am=$amount");
	exit;
} elseif ($amount > 200){ // TODO: Make this a config variable?
    header("Location: ../../../Admin/admin.php?p=addUsers&e=AMHI&bn=$baseName&am=$amount");
	exit;
} else {
    // Looks OK, let's go for it
    $created = 0;
    $skipped = 0;
	$totalVillagesCreated = 0;
	$createdUserIds = []; // Array para armazenar os IDs dos usuários criados
    
		for ($i= 1; $i <= $amount; $i++){
			
			$userName = $baseName . $i;
			$password = $generator->generateRandStr(8); // Random passwords disallow admin logging in to use the accounts
			$email = $baseName . $i . '@example.com';
			
			// Leaving the line below but commented out - could be used to
			// allow admin to log in to the generated accounts and play them
			// Easily guessed by players so should only be used for testing
			//$password = $baseName . $i . 'PASS';

			$tribe = ($postTribe == 0) ? rand(1, 3) : $postTribe;
			
			// Create in a random quad
			//1 - North West (-|+) 2 - North East (+|+) 3 - South West (-|-) 4 - South East (+|-)
			
			$kid = rand(1,4);
			//$kid = 2; // Definir região
			$act = ""; // Dont need to activate, not 100% sure we need to initialise $act
			
			
			if(User::exists($database, $userName)){ // Check username not already registered
				$skipped ++; // Name already used, do nothing except update $skipped
				continue;
			} 			
			
			$uid = $database->register($userName, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $email, $tribe, $act); // Register them and build the village
			
			if (!$uid) { // Falha no registro
				$skipped++;
				continue;
			}
			
			$createdUserIds[] = $uid; // Armazena o ID do usuário criado
			
			$q_desc = "UPDATE " . TB_PREFIX . "users SET desc2 = '[#0]' WHERE id = ".(int) $uid;
			mysqli_query($GLOBALS["link"], $q_desc) or die(mysqli_error($database->dblink));

			if (!$beginnersProtection) {
				$protection = time();
				$q_protect = "UPDATE " . TB_PREFIX . "users SET protect = '" . $protection . "' WHERE id = " . (int) $uid;
				mysqli_query($GLOBALS["link"], $q_protect) or die(mysqli_error($database->dblink));
			}

			$database->updateUserField($uid,"act","",1);
			$database->updateUserField($uid,"access",USER,1);
			
			$numVillagesToCreate = rand(5, 40);
			$villagesData = [];
			$userVillageWRefs = []; // Coletar IDs das vilas (wref)
			
			$villNameEnd = 0;
			$isFirstVillage = true;
			$capitalWid = $database->generateBase($kid, 1, 1); // Gera a capital
			
			if ($capitalWid) {
				$villagesData[] = ['id' => $capitalWid, 'fieldtype' => 3]; // Adiciona a capital
				$numVillagesToCreate--; // Diminui o contador
			} else {
				$skipped++;
				continue; // Pula para o próximo usuário no loop 'for'
			}
			
			$excludedVillagesClause = "";
			if (!empty($occupiedVillageIds)) {
				$excludedIds = implode(',', array_map('intval', $capitalWid));
				$excludedVillagesClause = "id NOT IN ($excludedIds)";
			}
			
			if ($numVillagesToCreate > 0) {
				$additionalVillages = $database->generateBaseNoFilter($kid, $capitalWid, 1, $numVillagesToCreate); // Chama a nova função para encontrar as vilas restantes
				if (!empty($additionalVillages)) {
						$villagesData = array_merge($villagesData, $additionalVillages); // Junta os dados das vilas adicionais aos dados da capital
				}
			}

			foreach ($villagesData as $villageInfo) {
				
				$wid = $villageInfo['id'];
				$fieldType = $villageInfo['fieldtype']; // Agora você tem o fieldtype!
				
				if (!$wid) continue; // Pula se o ID da vila for inválido
				$userVillageWRefs[] = $wid; // Adicionar à lista para chamadas agrupadas
				$database->setFieldTaken($wid); // Marcar como tomada
				
				$rand_resource=rand(30000, 80000);
				$level_storage=rand(15, 20);
				$cap_storage=$wgarray[$level_storage]*(STORAGE_BASE/800);
				$rand_resource=($rand_resource>$cap_storage)? $cap_storage:$rand_resource;
				$time = time();
				
				//Insere a vila com dados padrão
				if ($villNameEnd < 10) $villNameEnd = "0". $villNameEnd;
				$villageName = $isFirstVillage ? $userName." - [00]" : $userName." - [" . ($villNameEnd) . "]"; // Nome dinâmico
				$isCapital = $isFirstVillage ? 1 : 0;
				
				$q_vdata = "INSERT INTO ".TB_PREFIX."vdata (`wref`,`owner`,`name`,`capital`,`pop`,`cp`,`celebration`,`type`,`wood`,`clay`,`iron`,`maxstore`,`crop`,`maxcrop`,`lastupdate`,`loyalty`,`exp1`,`exp2`,`exp3`,`created`) 
				values (".(int) $wid.",".(int) $uid.",'".$database->escape($villageName)."',$isCapital,200,1,0,0,$rand_resource,$rand_resource,$rand_resource,$cap_storage,$rand_resource,$cap_storage,$time,100,0,0,0,$time)";
				mysqli_query($GLOBALS["link"], $q_vdata);
				
				$resource_distributions = [
					1 => [4,4,1,4,4,2,3,4,4,3,3,4,4,1,4,2,1,2], // 3-3-3-9 (Exemplo)
					2 => [3,4,1,3,2,2,3,4,4,3,3,4,4,1,4,2,1,2], // 3-4-5-6 (Exemplo)
					3 => [1,4,1,3,2,2,3,4,4,3,3,4,4,1,4,2,1,2], // 4-4-4-6 (Padrão)
					4 => [1,4,1,2,2,2,3,4,4,3,3,4,4,1,4,2,1,2], // 4-5-3-6 (Exemplo)
					5 => [1,4,1,3,1,2,3,4,4,3,3,4,4,1,4,2,1,2], // 5-3-4-6 (Exemplo)
					6 => [4,4,1,3,4,4,4,4,4,4,4,4,4,4,4,2,4,4], // 1-1-1-15 (Padrão 15c)
					7 => [1,4,4,1,2,2,3,4,4,3,3,4,4,1,4,2,1,2], // 4-4-3-7 (Exemplo)
					8 => [3,4,4,1,2,2,3,4,4,3,3,4,4,1,4,2,1,2], // 3-4-4-7 (Exemplo)
					9 => [3,4,4,1,1,2,3,4,4,3,3,4,4,1,4,2,1,2], // 4-3-4-7 (Exemplo)
					10 => [3,4,1,2,2,2,3,4,4,3,3,4,4,1,4,2,1,2], // 3-5-4-6 (Exemplo)
					11 => [3,1,1,3,1,4,4,3,3,2,2,3,1,4,4,2,4,4], // 4-3-5-6 (Exemplo)
					12 => [1,4,1,1,2,2,3,4,4,3,3,4,4,1,4,2,1,2], // 5-4-3-6 (Exemplo)
					13 => [4,4,1,4,4,2,3,4,4,3,3,4,4,1,4,2,1,2], // Default
				];
			
				$current_distribution = $resource_distributions[$fieldType] ?? $resource_distributions[13];
				$resource_fields_sql = "";
				for ($f = 1; $f <= 18; $f++) {
					$field_level = 10; // Nível máximo
					$field_type = $current_distribution[$f - 1]; // Pega o tipo do array de distribuição
					$resource_fields_sql .= "$field_level, $field_type,";
				}

				$building_fields_sql = "20,10,1,25,".rand(13,20).",17,".rand(13,20).",24,$level_storage,10,20,11,".rand(13,20).",37,".rand(14,20).",15,$level_storage,11,$level_storage,10,5,8,".rand(3,5).",9,
				0,0,$level_storage,11,0,0,".rand(13,20).",22,".rand(13,20).",19,".rand(13,20).",20,".rand(10,20).",12,".rand(10,20).",13,".rand(13,20).",16,0,0,0,0,'World Wonder'";
				
				$q_fdata = "INSERT INTO ".TB_PREFIX."fdata (
					`vref`,
					`f1`, `f1t`, `f2`, `f2t`, `f3`, `f3t`, `f4`, `f4t`, `f5`, `f5t`, `f6`, `f6t`,
					`f7`, `f7t`, `f8`, `f8t`, `f9`, `f9t`, `f10`, `f10t`, `f11`, `f11t`, `f12`, `f12t`,
					`f13`, `f13t`, `f14`, `f14t`, `f15`, `f15t`, `f16`, `f16t`, `f17`, `f17t`, `f18`, `f18t`,
					`f19`, `f19t`, `f20`, `f20t`, `f21`, `f21t`, `f22`, `f22t`, `f23`, `f23t`, `f24`, `f24t`,
					`f25`, `f25t`, `f26`, `f26t`, `f27`, `f27t`, `f28`, `f28t`, `f29`, `f29t`, `f30`, `f30t`,
					`f31`, `f31t`, `f32`, `f32t`, `f33`, `f33t`, `f34`, `f34t`, `f35`, `f35t`, `f36`, `f36t`,
					`f37`, `f37t`, `f38`, `f38t`, `f39`, `f39t`, `f40`, `f40t`, `f99`, `f99t`, `wwname`
				) VALUES ($wid, $resource_fields_sql $building_fields_sql)";
				mysqli_query($GLOBALS["link"], $q_fdata);			

				$villNameEnd++;
				$isFirstVillage = false; // As próximas vilas não serão capitais
				$totalVillagesCreated++;
			}
			
			if (!empty($userVillageWRefs)) {
				$database->addUnits($userVillageWRefs);
				$database->addTech($userVillageWRefs);
				$database->addABTech($userVillageWRefs);

				if ($capitalWid){
	
					$q_capital_rat = "INSERT into ".TB_PREFIX."enforcement (`u31`,`vref`,`from`) values (1, $capitalWid, 0);";
					mysqli_query($GLOBALS["link"], $q_capital_rat);
					
					// Insere unidades aleatórias nesta vila específica
					//$q_units = "UPDATE " . TB_PREFIX . "units SET u".(($tribe-1)*10+1)." = ".rand(100, 2000).", u".(($tribe-1)*10+2)." = ".rand(100, 2400).", u".(($tribe-1)*10+3)." = ".rand(100, 1600).", u".(($tribe-1)*10+4)." = ".rand(100, 1500).", u".(($tribe-1)*10+5)." = " .rand(48, 1700).", u".(($tribe-1)*10+6)." = ".rand(60, 1800)." WHERE vref = '".$wid."'";
					//mysqli_query($GLOBALS["link"], $q_units);
				}
			}
			
			$created ++;
		}
		
		global $admin;
		foreach ($createdUserIds as $uid) { // Itera sobre os IDs armazenados
            $admin->recountPopUser($uid);
        }

    header("Location: ../../../Admin/admin.php?p=addUsers&g=OK&bn=$baseName&am=$created&sk=$skipped&bp=$beginnersProtection&tr=$tribe");
}
?>