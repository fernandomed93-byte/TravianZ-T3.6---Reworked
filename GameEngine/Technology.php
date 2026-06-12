<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       Technology.php                                              ##
##  Developed by:  Dzoki                                                       ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2025. All rights reserved.                ##
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

// this is needed for installation, since the lang file would not be included yet
include_once($autoprefix."GameEngine/Lang/en.php");

class Technology {

	public $unarray = [1 => U1, U2, U3, U4, U5, U6, U7, U8, U9, U10, U11, U12, U13, U14, U15, U16, U17, U18, U19, U20, U21, U22, U23, U24, U25, U26, U27, U28, U29, U30, U31, U32, U33, U34, U35, U36, U37, U38, U39, U40, U41, U42, U43, U44, U45, U46, U47, U48, U49, U50, U51, U52, U53, U54, U55, U56, U57, U58, U59, U60, U61, U62, U63, U64, U65, U66, U67, U68, U69, U70, U71, U72, U73, U74, U75, U76, U77, U78, U79, U80, U81, U82, U83, U84, U85, U86, U87, U88, U89, U90, U99, U0];

	public function grabAcademyRes() {
		global $village;
		$holder = [];
		foreach($village->researching as $research) {
			if(substr($research['tech'], 0, 1) == "t") array_push($holder, $research);
		}
		return $holder;
	}

	public function getABUpgrades($type='a') {
		global $village;
		$holder = [];
		foreach($village->researching as $research) {
			if(substr($research['tech'], 0, 1) == $type) array_push($holder, $research);
		}
		return $holder;
	}

	public function isResearch($tech, $type) {
		global $village;
		
		if(count($village->researching) == 0) return false;			
		else
		{
		switch($type) {
			case 1: $string = "t"; break;
			case 2: $string = "a"; break;
			case 3: $string = "b"; break;
		}
		
		foreach($village->researching as $research) {
			if($research['tech'] == $string.$tech) return true;
		}
		
		return false;
		}
	}

	public function procTech($post) {
		if(isset($post['ft'])) {
			switch($post['ft']) {
				case "t1":
				$this->procTrain($post);
				break;
				case "t3":
				$this->procTrain($post,true);
				break;
				case "t4":
				$this->procHeal($post);
				break;
			}
		}
	}

	private function procHeal($post) {
		global $database, $session, $village;
		$gid = (int)$post['id'];
		$gidType = $database->getResourceLevel($village->wid)['f'.$gid.'t'];

		if ($gidType != 46 && $gidType != 48) return;

		for ($pos = 1; $pos <= 6; $pos++) {
			if (isset($post['t'.$pos]) && $post['t'.$pos] > 0) {
				$amt = (int)$post['t'.$pos];
				if ($amt <= 0) continue;
				$globalId = ($session->tribe - 1) * 10 + $pos;
				$this->trainUnit($globalId, $amt, false, true);
			}
		}
		header("Location: build.php?id=".$post['id']);
		exit;
	}

	public function procTechno($get) {
		global $village;
		if(isset($get['a'])) {
			switch($village->resarray['f'.$get['id'].'t']) {
				case 22:
				$this->researchTech($get);
				break;
				case 13:
				$this->upgradeArmour($get);
				break;
				case 12:
				$this->upgradeSword($get);
				break;
			}
		}
	}

	public function getTrainingList($type) {
		global $database,$village;
		$trainingarray = $database->getTraining($village->wid);
		$listarray = [];
		$barracks = [1, 2, 3, 11, 12, 13, 14, 21, 22, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 51, 52, 53, 61, 62, 63, 71, 72, 73, 74, 81, 82, 83, 84];
		$greatbarracks = [1001, 1002, 1003, 1011, 1012, 1013, 1014, 1021, 1022, 1031, 1032, 1033, 1034, 1035, 1036, 1037, 1038, 1039, 1040, 1041, 1042, 1043, 1044, 1051, 1052, 1053, 1061, 1062, 1063, 1071, 1072, 1073, 1074, 1081, 1082, 1083, 1084];

		$stables = [4, 5, 6, 15, 16, 23, 24, 25, 26, 45, 46, 54, 55, 56, 64, 65, 66, 75, 76, 85, 86];
		$greatstables = [1004, 1005, 1006, 1015, 1016, 1023, 1024, 1025, 1026, 1045, 1046, 1054, 1055, 1056, 1064, 1065, 1066, 1075, 1076, 1085, 1086];

		$workshop = [7, 8, 17, 18, 27, 28, 47, 48, 57, 58, 67, 68, 77, 78, 87, 88];
		$greatworkshop = [1007, 1008, 1017, 1018, 1027, 1028, 1047, 1048, 1057, 1058, 1067, 1068, 1077, 1078, 1087, 1088];

		$residence = [9, 10, 19, 20, 29, 30, 49, 50, 59, 60, 69, 70, 79, 80, 89, 90];
		$trapper = [99];
		$healing = range(2001, 2090);
		
		if(count($trainingarray) > 0) {
			foreach($trainingarray as $train) {
				if($type == 1 && in_array($train['unit'],$barracks)) {
				$train['name'] = $this->unarray[$train['unit']];
				array_push($listarray,$train);
				}
				if($type == 2 && in_array($train['unit'],$stables)) {
					$train['name'] = $this->unarray[$train['unit']];
					array_push($listarray,$train);
				}
				if($type == 3 && in_array($train['unit'],$workshop)) {
					$train['name'] = $this->unarray[$train['unit']];
					array_push($listarray,$train);
				}
				if($type == 4 && in_array($train['unit'],$residence)) {
					$train['name'] = $this->unarray[$train['unit']];
					array_push($listarray,$train);
				}
				if($type == 5 && in_array($train['unit'],$greatbarracks)) {
					$train['name'] = $this->unarray[$train['unit']-1000];
					$train['unit'] -= 1000;
					array_push($listarray,$train);
				}
				if($type == 6 && in_array($train['unit'],$greatstables)) {
					$train['name'] = $this->unarray[$train['unit']-1000];
					$train['unit'] -= 1000;
					array_push($listarray,$train);
				}
				if($type == 7 && in_array($train['unit'],$greatworkshop)) {
					$train['name'] = $this->unarray[$train['unit']-1000];
					$train['unit'] -= 1000;
					array_push($listarray,$train);
				}
				if($type == 8 && in_array($train['unit'],$trapper)) {
					$train['name'] = $this->unarray[$train['unit']];
					array_push($listarray,$train);
				}
				if($type == 9 && in_array($train['unit'],$healing)) {
					$train['name'] = $this->unarray[$train['unit'] - 2000];
					$train['unit'] -= 2000;
					array_push($listarray,$train);
				}
			}
		}
		return $listarray;
	}

	public function getUnitList() {
		global $database, $village;
		
		$unitarray = func_num_args() == 1 ? $database->getUnit(func_get_arg(0)) : $village->unitall;
		$listArray = [];
		for($i = 1; $i < count($this->unarray); $i++) {
			$holder = [];
			if(!empty($unitarray['u'.$i]) && $unitarray['u'.$i] > 0 && !empty($unitarray['u'.$i])) {
				$holder['id'] = $i;
				$holder['name'] = $this->unarray[$i];
				$holder['amt'] = $unitarray['u'.$i];
				array_push($listArray, $holder);
			}
		}

		if($unitarray['hero'] > 0 && !empty($unitarray['hero'])) {
		    $holder['id'] = "hero";
		    $holder['name'] = $this->unarray[$i];
		    $holder['amt'] = $unitarray['hero'];
		    array_push($listArray, $holder);
		}
		return $listArray;
	}

	public function maxUnit($unit,$great=false) {
		$unit = "u" . $unit;
		global $village, $$unit, $database;
		
		$unitarray = $$unit;
		$res = $database->getVillage($village->wid, 0, false);
		if($res['wood'] > $res['maxstore']) $res['wood'] = $res['maxstore'];
		if($res['clay'] > $res['maxstore']) $res['clay'] = $res['maxstore'];
		if($res['iron'] > $res['maxstore']) $res['iron'] = $res['maxstore'];
		if($res['crop'] > $res['maxcrop']) $res['crop'] = $res['maxcrop'];
			
		
		$woodcalc = floor($res['wood'] / ($unitarray['wood'] * ($great ? 3 : 1)));
		$claycalc = floor($res['clay'] / ($unitarray['clay'] * ($great ? 3 : 1)));
		$ironcalc = floor($res['iron'] / ($unitarray['iron'] * ($great ? 3 : 1)));
		
		if($res['crop'] > 0) $cropcalc = floor($res['crop'] / ($unitarray['crop'] * ($great ? 3 : 1)));
		else $cropcalc = 0;

		if($unit != "u99") $popcalc = floor($village->getProd("crop") / $unitarray['pop']);		
		else $popcalc = $village->getProd("crop");
	
		return min($woodcalc, $claycalc, $ironcalc, $cropcalc);
	}

	public function maxUnitPlus($unit,$great=false) {
		$unit = "u" . $unit;	
		global $village, $$unit, $database;
		
		$unitarray = $$unit;
		$res = $database->getVillage($village->wid);
		$totalres = $res['wood'] + $res['clay'] + $res['iron'] + $res['crop'];
		$totalresunit = ($unitarray['wood'] * ($great ? 3 : 1)) + ($unitarray['clay'] * ($great ? 3 : 1)) + ($unitarray['iron'] * ($great ? 3 : 1)) + ($unitarray['crop'] * ($great ? 3 : 1));
		$max = floor($totalres / $totalresunit);
		return $max;
	}

	public function getUnits() {
		global $database, $village;
		
		if(func_num_args() == 1) $base = func_get_arg(0);

		$ownunit = func_num_args() == 2 ? func_get_arg(0) : $database->getUnit($base);
		$enforcementarray = func_num_args() == 2 ? func_get_arg(1) : $database->getEnforceVillage($base, 0);
		if(count($enforcementarray) > 0){
			foreach($enforcementarray as $enforce){
				for($i = 1; $i <= 90; $i++) $ownunit['u'.$i] += $enforce['u'.$i];
			}
		}
		return $ownunit;
	}

    function getAllUnits($base, $InVillageOnly = false, $mode = 0, $useCache = true) {
        global $database;
		
        $ownunit = $database->getUnit($base, $useCache);
		$ownunit['u99'] -= $ownunit['u99'];
		$ownunit['u99o'] -= $ownunit['u99o'];
		$enforcementarray = $database->getEnforceVillage($base, 0, $useCache);
		if(count($enforcementarray) > 0){
			foreach($enforcementarray as $enforce){
				for($i = 1; $i <= 90; $i++){
					$ownunit['u' . $i] += $enforce['u' . $i];
				}
				$ownunit['hero'] += $enforce['hero'];
			}
		}
		if($mode == 0){
			$enforceoasis = $database->getOasisEnforce($base, 1, $useCache);
			if(count($enforceoasis) > 0){
				foreach($enforceoasis as $enforce){
					for($i = 1; $i <= 90; $i++){
						$ownunit['u' . $i] += $enforce['u' . $i];
					}
					$ownunit['hero'] += $enforce['hero'];
				}
			}
			
			$prisoners = $database->getPrisoners($base, 0, $useCache);
			if(!empty($prisoners)){
				foreach($prisoners as $prisoner){
					$ownertribe = $database->getVillageOwnerTribe($prisoner['from']);
					$start = ($ownertribe - 1) * 10 + 1;
					$end = ($ownertribe * 10);
					for($i = $start; $i <= $end; $i++){
						$j = $i - $start + 1;
						$ownunit['u' . $i] += $prisoner['t' . $j];
					}
					$ownunit['hero'] += $prisoner['t11'];
				}
			}
		}
		
		if(!$InVillageOnly){
			$movement = $database->getVillageMovement($base);
			if(!empty($movement)){
				for($i = 1; $i <= 90; $i++){
				    if(!isset($ownunit['u'.$i])) $ownunit['u'.$i] = 0;
					$ownunit['u'.$i] += (isset($movement['u'.$i]) ? $movement['u'.$i] : 0);
				}
				
				if(!isset($ownunit['hero'])) $ownunit['hero'] = 0;
				$ownunit['hero'] += (isset($movement['hero']) ? $movement['hero'] : 0);
			}
		}
		return $ownunit;
    }

	public function meetTRequirement($unit) {
		global $session;
		switch($unit) {
		    
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			case 8: return $this->getTech($unit) && $session->tribe == 1;
			
			case 1:
			case 10: return $session->tribe == 1;
			
			case 12:
			case 13:
			case 14:
			case 15:
			case 16:
			case 17:
			case 18: return $session->tribe == 2 && $this->getTech($unit);

			case 11:
			case 20: return $session->tribe == 2;
			
			case 22:
			case 23:
			case 24:
			case 25:
			case 26:
			case 27:
			case 28: return $session->tribe == 3 && $this->getTech($unit);
			
			case 21:
			case 30: return $session->tribe == 3;
			
			case 32:
			case 33:
			case 34:
			case 35:
			case 36:
			case 37:
			case 38: return $session->tribe == 4 && $this->getTech($unit);
			
			case 31:
			case 40: return $session->tribe == 4;
			    
			case 42:
			case 43:
			case 44:
			case 45:
			case 46:
			case 47:
			case 48: return $session->tribe == 5 && $this->getTech($unit);

			case 41:
			case 50: return $session->tribe == 5;

			case 52:
			case 53:
			case 54:
			case 55:
			case 56:
			case 57:
			case 58: return $session->tribe == 6 && $this->getTech($unit);

			case 51:
			case 60: return $session->tribe == 6;

			case 62:
			case 63:
			case 64:
			case 65:
			case 66:
			case 67:
			case 68: return $session->tribe == 7 && $this->getTech($unit);

			case 61:
			case 70: return $session->tribe == 7;

			case 72:
			case 73:
			case 74:
			case 75:
			case 76:
			case 77:
			case 78: return $session->tribe == 8 && $this->getTech($unit);

			case 71:
			case 80: return $session->tribe == 8;

			case 82:
			case 83:
			case 84:
			case 85:
			case 86:
			case 87:
			case 88: return $session->tribe == 9 && $this->getTech($unit);

			case 81:
			case 90: return $session->tribe == 9;
		}
	}

	public function getTech($tech) {
		global $village;
		return (isset($village->techarray['t'.$tech]) && $village->techarray['t'.$tech] == 1);
	}

	private function procTrain($post, $great = false) {
		global $session;
			
		// first of all, check if we're not trying to train chieftain
		// and settlers together - which we cannot, since that can result
		// in 1 chieftain and 3 settlers, then conquering a village, then
		// founding a new one, all with only 1 available slot
		if (
		    !(
		        (!empty($post['t9']) && !empty($post['t10'])) ||
		        (!empty($post['t19']) && !empty($post['t20'])) ||
		        (!empty($post['t29']) && !empty($post['t30'])) ||
		        (!empty($post['t39']) && !empty($post['t40'])) ||
				(!empty($post['t49']) && !empty($post['t50'])) ||
				(!empty($post['t59']) && !empty($post['t60'])) ||
				(!empty($post['t69']) && !empty($post['t70'])) ||
				(!empty($post['t79']) && !empty($post['t80'])) ||
		        (!empty($post['t89']) && !empty($post['t90']))
		        )
		    ) {
		        $start = ($session->tribe - 1) * 10 + 1;
		        $end   = ($session->tribe * 10);
		        for ($i = $start; $i <= $end; $i ++ ) {
		            if (isset($post['t'.$i]) && $post['t'.$i] != 0) {
		                $amt = intval($post['t'.$i]);
		                if ($amt < 0) $amt = 1;
		                $this->trainUnit($i, $amt, $great);
		            }
		        }
		        
		        if($session->tribe == 3)
		        {
		            if (isset($post['t99']) && $post['t99'] != 0) {
		                $amt = intval($post['t99']);
		                if ($amt < 0) $amt = 1;
		                $this->trainUnit(99, $amt, $great);
		            }
		        }
		        
		        header( "Location: build.php?id=" . $post['id'] );
		        exit;
		    }
	}

	public function getUpkeep($array, $type, $vid = 0, $prisoners = 0) {
        global $database, $village;

        if ($vid == 0) $vid = $village->wid;
        $upkeep = 0;
        $horsedrinking = $database->getFieldLevelInVillage($vid, 41);
        
        if(!$type){
            $start = 1;
            $end = 50;
        }else{
            $start = ($type - 1) * 10 + 1;
            $end = $type * 10;
        }

        for ($i = $start; $i <= $end; $i ++) {
            $k = $i - $start + 1;
            
            $unit = "u".$i;
            $index = $prisoners == 0 ? $unit : "t".$k;        
            
            global $$unit; 
            $dataarray = $$unit;

            if($horsedrinking > 0) {
                if (($i == 4 && $horsedrinking >= 10) || ($i == 5 && $horsedrinking >= 15) || ( $i == 6 && $horsedrinking == 20)) {
                	$upkeep += ($dataarray['pop'] - 1) * $array[$index];
                }
                else $upkeep += ($dataarray['pop'] * $array[$index]);
            }
            else $upkeep += ($dataarray['pop'] * $array[$index]);    
        }

        $index = $prisoners > 0 ? 't11' : 'hero';
        
        if(!isset($array[$index])) $array[$index] = 0;
        $upkeep += $array[$index] * 6;
        $who = $database->getVillageField($vid, "owner");
        
        //If it's a WW village, halve the crop consumption
        if($database->getVillageField($vid, "natar") == 1) $upkeep /= 2;
        
        return ceil($database->getArtifactsValueInfluence($who, $vid, 4, $upkeep, false));
	}

    private function trainUnit($unit, $amt, $great = false, $heal = false) {
		global $session, $database, ${'u'.$unit}, $building, $village, $bid19, $bid20, $bid21, $bid25, $bid26, $bid29, $bid30, $bid36, $bid41, $bid44, $bid46, $bid48, $bid49;

		if($heal || $this->getTech($unit) || $unit % 10 <= 1 || $unit == 99) {
			$footies = [1, 2, 3, 11, 12, 13, 14, 21, 22, 31, 32, 33, 34, 41, 42, 43, 44, 51, 52, 53, 61, 62, 63, 71, 72, 73, 74, 81, 82, 83, 84];
			$calvary = [4, 5, 6, 15, 16, 23, 24, 25, 26, 35, 36, 45, 46, 54, 55, 56, 64, 65, 66, 75, 76, 85, 86];
			$workshop = [7, 8, 17, 18, 27, 28, 37, 38, 47, 48, 57, 58, 67, 68, 77, 78, 87, 88];
			$special = [9, 10, 19, 20, 29, 30, 39, 40, 49, 50, 59, 60, 69, 70, 79, 80, 89, 90];
			$trapper = [99];
			
			//Check if the player is trying to train troops without the needed buildings
			if($heal) {
				if($building->getTypeLevel(46) == 0 && $building->getTypeLevel(48) == 0) return;
			} else {
				if((in_array($unit, $footies) && ($building->getTypeLevel(19) == 0 && $building->getTypeLevel(29) == 0)) ||
				    (in_array($unit, $calvary) && ($building->getTypeLevel(20) == 0 && $building->getTypeLevel(30) == 0)) ||
				    (in_array($unit, $workshop) && ($building->getTypeLevel(21) == 0 && $building->getTypeLevel(49) == 0)) ||
				    (in_array($unit, $special) && ($building->getTypeLevel(25) < 10 && $building->getTypeLevel(26) < 10 && $building->getTypeLevel(44) < 10)) ||
				    (in_array($unit, $trapper) && $building->getTypeLevel(36) == 0)) return;
			}
			
			
			if($heal) {
				$gid = $building->getTypeLevel(48) > 0 ? 48 : 46;
				$bid = ($gid == 48) ? $bid48 : $bid46;
				$each = round(($bid[$building->getTypeLevel($gid)]['attri'] / 100) * ${'u'.$unit}['time'] / 2 / SPEED);
			} elseif(in_array($unit, $footies)) {		    
				if($great) {
					$each = round(($bid29[$building->getTypeLevel(29)]['attri'] / 100) * ${'u'.$unit}['time'] / SPEED);
				} else {
					$each = round(($bid19[$building->getTypeLevel(19)]['attri'] / 100) * ${'u'.$unit}['time'] / SPEED);
				}
			}
			elseif(in_array($unit, $calvary)) {		    
				if($great) {
					$each = round(($bid30[$building->getTypeLevel(30)]['attri'] * ($building->getTypeLevel(41)>=1?(1/$bid41[$building->getTypeLevel(41)]['attri']):1) / 100) * ${'u'.$unit}['time'] / SPEED);
				} else {
					$each = round(($bid20[$building->getTypeLevel(20)]['attri'] * ($building->getTypeLevel(41)>=1?(1/$bid41[$building->getTypeLevel(41)]['attri']):1) / 100) * ${'u'.$unit}['time'] / SPEED);
				}
			}
			elseif(in_array($unit, $workshop)) {	    
				if($great) {
					$each = round(($bid49[$building->getTypeLevel(49)]['attri'] / 100) * ${'u'.$unit}['time'] / SPEED);
				} else {
					$each = round(($bid21[$building->getTypeLevel(21)]['attri'] / 100) * ${'u'.$unit}['time'] / SPEED);
				}
			}
			elseif(in_array($unit, $special)) {			    
				if($building->getTypeLevel(25) > 0){
					$each = round(($bid25[$building->getTypeLevel(25)]['attri'] / 100) * ${'u'.$unit}['time'] / SPEED);
				} elseif($building->getTypeLevel(44) > 0) {
					$each = round(($bid44[$building->getTypeLevel(44)]['attri'] / 100) * ${'u'.$unit}['time'] / SPEED);
				} else {
					$each = round(($bid26[$building->getTypeLevel(26)]['attri'] / 100) * ${'u'.$unit}['time'] / SPEED);
				}
			}
			elseif(in_array($unit, $trapper)) {
			    
					$each = round(($bid19[$building->getTypeLevel(36)]['attri'] / 100) * ${'u'.$unit}['time'] / SPEED);
			}	
			
			if($unit % 10 == 0 || $unit % 10 == 9 && $unit != 99) {
				if($this->maxUnit($unit, $great) < $amt) $amt = 0;
				else
				{
			   		$slots = $database->getAvailableExpansionTraining();
			   		if($unit % 10 == 0 && $slots['settlers'] <= $amt) $amt = $slots['settlers'];
			   		if($unit % 10 == 9 && $slots['chiefs'] <= $amt) $amt = $slots['chiefs'];
				}
			}else{
			    if($unit != 99){
			        if($this->maxUnit($unit, $great) < $amt) $amt = 0;
			    }else{
			        $trainlist = $this->getTrainingList(8);
			        
			        foreach($trainlist as $train) $train_amt += $train['amt'];
			        
			        $max = 0;
			        for($i = 19; $i < 41; $i++){
			            if($village->resarray['f'.$i.'t'] == 36){
			                $max += $bid36[$village->resarray['f'.$i]]['attri']*TRAPPER_CAPACITY;
			            }
			        }
			        $max1 = $max - ($village->unitarray['u99'] + $train_amt);
			        if($max1 < $amt) $amt = 0;
			    }
			}
			$wood = ${'u'.$unit}['wood'] * $amt * ($great ? 3 : 1);
			$clay = ${'u'.$unit}['clay'] * $amt * ($great ? 3 : 1);
			$iron = ${'u'.$unit}['iron'] * $amt * ($great ? 3 : 1);
			$crop = ${'u'.$unit}['crop'] * $amt * ($great ? 3 : 1);

			if($database->modifyResource($village->wid, $wood , $clay, $iron, $crop, 0) && $amt > 0) {
				if($heal) {
					$pos = (($unit - 1) % 10) + 1;
					if ($pos >= 1 && $pos <= 6) {
						$database->updateWounded($village->wid, [$pos => $amt], 1);
					}
				}
				$dbUnit = $heal ? (2000 + $unit) : ($great ? (1000 + $unit) : $unit);
				$database->trainUnit($village->wid, $dbUnit, $amt, ${'u'.$unit}['pop'], $each, 0);
			}
		}
	}

	public function meetRRequirement($tech) {
		global $unitRequirements, $building;
		if (!isset($unitRequirements[$tech])) return false;
		foreach ($unitRequirements[$tech] as $gid => $level) {
			if ($building->getTypeLevel($gid) < $level) return false;
		}
		return true;
	}

	private function researchTech($get) {
		global $database,$session,${'r'.$get['a']},$bid22,$building,$village,$logging;
		if($this->meetRRequirement($get['a']) && $get['c'] == $session->mchecker) {
			$data = ${'r'.$get['a']};
			$time = time() + round(($data['time'] * ($bid22[$building->getTypeLevel(22)]['attri'] / 100))/SPEED);
			$database->modifyResource($village->wid,$data['wood'],$data['clay'],$data['iron'],$data['crop'],0);
			$database->addResearch($village->wid,"t".$get['a'],$time);
			$logging->addTechLog($village->wid,"t".$get['a'],1);
		}
		$session->changeChecker();
		header("Location: build.php?id=".$get['id']);
		exit;
	}

	//TODO: Merge these two functions in one function, they're very similar to each other
	
	private function upgradeSword($get) {
		global $database,$session,$bid12,$building,$village,$logging;
		$ABTech = $database->getABTech($village->wid);
		$ABUpgrades = $this->getABUpgrades('b');
		$ABUpgradesCount = count($ABUpgrades);
		
		$ups = 0;
		if($ABUpgradesCount > 0){
		    foreach($ABUpgrades as $upgrade){
		        if(in_array(("b".$get['a']), $upgrade)) $ups++;
		    }
		}
		
		$CurrentTech = $ABTech["b".$get['a']]+$ups;
		$unit = ($session->tribe-1)*10+intval($get['a']);
		if(($ABUpgradesCount < 2 && $session->plus || $ABUpgradesCount == 0) && ($this->getTech($unit) || ($unit % 10) == 1) && ($CurrentTech < $building->getTypeLevel(12)) && $get['c'] == $session->mchecker) {
			global ${'ab'.strval($unit)};
			$data = ${'ab'.strval($unit)};
			$time = time() + round(($data[$CurrentTech+1]['time'] * ($bid12[$building->getTypeLevel(12)]['attri'] / 100))/SPEED) + ($ABUpgradesCount > 0 ? ($ABUpgrades[$ABUpgradesCount-1]['timestamp'] - time()) + ceil(60/SPEED) : 0);
			if ($database->modifyResource($village->wid,$data[$CurrentTech+1]['wood'],$data[$CurrentTech+1]['clay'],$data[$CurrentTech+1]['iron'],$data[$CurrentTech+1]['crop'],0)) {
				$database->addResearch($village->wid,"b".$get['a'],$time);
				$logging->addTechLog($village->wid,"b".$get['a'],$CurrentTech+1);
			}
		}
		$session->changeChecker();
		header("Location: build.php?id=".$get['id']);
		exit;
	}

	private function upgradeArmour($get) {
		global $database,$session,$bid13,$building,$village,$logging;
		$ABTech = $database->getABTech($village->wid);
		$ABUpgrades = $this->getABUpgrades('a');
		$ABUpgradesCount = count($ABUpgrades);
		
		$ups = 0;
		if($ABUpgradesCount > 0){
		    foreach($ABUpgrades as $upgrade){
		        if(in_array(("a".$get['a']), $upgrade)) $ups++;
		    }
		}
		
		$CurrentTech = $ABTech["a".$get['a']]+$ups;
		$unit = ($session->tribe-1)*10+intval($get['a']);
		if(($ABUpgradesCount < 2 && $session->plus || $ABUpgradesCount == 0) && ($this->getTech($unit) || ($unit % 10) == 1) && ($CurrentTech < $building->getTypeLevel(13)) && $get['c'] == $session->mchecker) {
			global ${'ab'.strval($unit)};
			$data = ${'ab'.strval($unit)};
			$time = time() + round(($data[$CurrentTech+1]['time'] * ($bid13[$building->getTypeLevel(13)]['attri'] / 100))/SPEED) + ($ABUpgradesCount > 0 ? ($ABUpgrades[$ABUpgradesCount-1]['timestamp'] - time()) + ceil(60/SPEED) : 0);
			if ($database->modifyResource($village->wid,$data[$CurrentTech+1]['wood'],$data[$CurrentTech+1]['clay'],$data[$CurrentTech+1]['iron'],$data[$CurrentTech+1]['crop'],0)) {
				$database->addResearch($village->wid,"a".$get['a'],$time);
				$logging->addTechLog($village->wid,"a".$get['a'],$CurrentTech+1);
			}
		}
		$session->changeChecker();
		header("Location: build.php?id=".$get['id']);
		exit;
	}

	public function getUnitName($i) {
		return $this->unarray[$i];
	}

	public function finishTech() {
        global $database,$village;
        $q = "UPDATE ".TB_PREFIX."research SET timestamp=".(time()-1)." WHERE vref = ".(int) $village->wid;
        $result = $database->query($q);
        return mysqli_affected_rows($database->dblink);
    }

	public function calculateAvaliable($id, $resarray = []) {
		global $village,$generator,${'r'.$id};
		if(count($resarray)==0) {
			$resarray['wood'] = ${'r'.$id}['wood'];
			$resarray['clay'] = ${'r'.$id}['clay'];
			$resarray['iron'] = ${'r'.$id}['iron'];
			$resarray['crop'] = ${'r'.$id}['crop'];
		}
		$rwtime = ($resarray['wood']-$village->awood) / $village->getProd("wood") * 3600;
		$rcltime = ($resarray['clay']-$village->aclay) / $village->getProd("clay") * 3600;
		$ritime = ($resarray['iron']-$village->airon) / $village->getProd("iron") * 3600;
		$rctime = ($resarray['crop']-$village->acrop) / $village->getProd("crop") * 3600;
		if($village->getProd("crop") >= 0) {
			$reqtime = max($rwtime,$rcltime,$ritime,$rctime) + time();
		} else {
			$reqtime = max($rwtime,$rcltime,$ritime);
			if($reqtime > $rctime) {
				$reqtime = 0;
			} else {
				$reqtime += time();
			}
		}
		return $generator->procMtime($reqtime);
	}

	public function checkReinf($id, $use_cache = true) {
		global $database;
		$enforce=$database->getEnforceArray($id, 0, $use_cache);
		$fail=0;

        for ($i=1; $i<50; $i++) {
            if($enforce['u'.$i.'']>0){
                $fail=1;
            }
        }

        if ($enforce['hero']>0) $fail=1;
        if($fail==0){
            $database->deleteReinf($id);
        }

	}

}
$technology = new Technology;
?>
