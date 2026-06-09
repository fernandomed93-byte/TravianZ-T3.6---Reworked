<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Project:       TravianZ                                                    ##
##  Version:       22.06.2015                    			       ##
##  Filename       Battle.php                                                  ##
##  Developed by:  Dzoki & Dixie   					       ##
##  Fixed by:      Shadow 				  		       ##
##  Thanks to:     Akakori, Elmar & Kirilloid                                  ##
##  Reworked and Fix by:   ronix                                               ##
##  Fixed by:      InCube - double troops				       ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2015. All rights reserved.                ##
##  URLs:          http://travian.shadowss.ro                		       ##
##  Source code:   https://github.com/Shadowss/TravianZ		               ##
##                                                                             ##
#################################################################################


class Battle {

        /**
        * 
        * @author Kirilloid --> https://github.com/kirilloid/travian/blob/master/src/model/base/combat.ts
        * @var double The number of attacking catapults: 1 = 100%, 0 = 0%
        * 
        */
    
        private $sigma;
        
        public function __construct(){
            
            $this->sigma = function($x) { return ($x > 1 ? 2 - $x ** -1.5 : $x ** 1.5) / 2; }; 
            
        }
        
		public function procSim($post) {
			global $form;
			
			// receive form and process
			if(isset($post['a1_v']) && (isset($post['a2_v1']) || isset($post['a2_v2']) || isset($post['a2_v3']) || isset($post['a2_v4']) || isset($post['a2_v5'])
                 || isset($post['a2_v6']) || isset($post['a2_v7']) || isset($post['a2_v8']) || isset($post['a2_v9']))){
				$_POST['mytribe'] = $post['a1_v'];
				
				$target = [];
				if(isset($post['a2_v1'])) array_push($target, 1);
				if(isset($post['a2_v2'])) array_push($target, 2);
				if(isset($post['a2_v3'])) array_push($target, 3);
				if(isset($post['a2_v4'])) array_push($target, 4);
				if(isset($post['a2_v5'])) array_push($target, 5);
                if(isset($post['a2_v6'])) array_push($target, 6);
                if(isset($post['a2_v7'])) array_push($target, 7);
                if(isset($post['a2_v8'])) array_push($target, 8);
                if(isset($post['a2_v9'])) array_push($target, 9);
				
				$_POST['target'] = $target;
				if(isset($post['h_off_bonus'])){
					if(intval($post['h_off_bonus']) > 20) $post['h_off_bonus'] = 20;
				}
				else $post['h_off_bonus'] = 0;
					
				if(isset($post['h_def_bonus'])){
					if(intval($post['h_def_bonus']) > 20) $post['h_def_bonus'] = 20;
				}
				else $post['h_def_bonus'] = 0;
				
				$sum = 0;
				for($i = 1; $i <= 10; $i++) $sum += (!empty($post['a1_'.$i]) ? $post['a1_'.$i] : 0);
				
				if($sum > 0){
					$post['palast'] = intval($post['palast']);
					if($post['palast'] > 20) $post['palast'] = 20;
					
					for($i = 1; $i <= 5; $i++){
						if(isset($post['wall'.$i])){
							$post['wall'.$i] = intval($post['wall'.$i]);
							if($post['wall'.$i] > 20) $post['wall'.$i] = 20;
							elseif($post['wall'.$i] < 0) $post['wall'.$i] = 0;
							$post['walllevel'] = $post['wall'.$i];
						}
					}

					$post['tribe'] = $target[0];
					$_POST['result'] = $this->simulate($post);
					$newWallLevel = 0;
					$oldWallLevel = 0;
					
					if (isset($_POST['result'][7]) && !empty($_POST['result'][7])){
						$newWallLevel = $_POST['result'][7];
					}
					if (isset($_POST['result'][8]) && !empty($_POST['result'][8])){
						$oldWallLevel = $_POST['result'][8];
					}
					
					//If the wall level is reduce, we have to re-do the whole battle
					if($newWallLevel != $oldWallLevel){
						$post['walllevel'] = $newWallLevel;
						$_POST['result'] = $this->simulate($post);
						
						//Reset the datas
						$_POST['result'][7] = $newWallLevel;
						$_POST['result'][8] = $post['walllevel'] = $oldWallLevel;
					}
					
					$form->valuearray = $post;
				}
			}
		}
		
		private function getBattleHero($uid) {
			global $database;
			$heroarray = $database->getHero($uid);

			if (!count($heroarray)) return ['heroid' => 0, 'unit' =>'','atk' => 0,'di' => 0,'dc' => 0,'ob' => 0,'db' => 0,'health' => 0];

			$herodata = $GLOBALS["h".$heroarray[0]['unit']];
			if(!isset($heroarray[0]['health'])) $heroarray[0]['health'] = 0;
			$h_atk = $herodata['atk'] + ($heroarray[0]['attack'] * $herodata['atkp']);
			$h_di = $herodata['di'] + 5 * floor($heroarray[0]['defence'] * $herodata['dip'] / 5);
			$h_dc = $herodata['dc'] + 5 * floor($heroarray[0]['defence'] * $herodata['dcp'] / 5);
			$h_ob = 1 + 0.010 * ($heroarray[0]['attackbonus'] / 5);
			$h_db = 1 + 0.010 * ($heroarray[0]['defencebonus'] / 5);

			return [
                'heroid' => (int) $heroarray[0]['heroid'], 
                'unit' => $heroarray[0]['unit'], 
                'atk' => $h_atk, 
                'di' => $h_di, 
                'dc' => $h_dc, 
                'ob' => $h_ob, 
                'db' => $h_db, 
                'health' => $heroarray[0]['health']
            ];
		}

		private function getBattleHeroSim($attbonus) {
            $h_atk = 0;
            $h_ob = 1 + 0.010 * $attbonus;

            return ['unit' => 16,'atk' => $h_atk,'ob' => $h_ob];
		}

		private function simulate($post) {
				//set the arrays with attacking and defending units
				$attacker = [
                    'u1' => 0, 'u2' => 0, 'u3' => 0, 'u4' => 0, 'u5' => 0,
                    'u6' => 0, 'u7' => 0, 'u8' => 0, 'u9' => 0, 'u10' => 0,
                    'u11' => 0, 'u12' => 0, 'u13' => 0, 'u14' => 0, 'u15' => 0,
                    'u16' => 0, 'u17' => 0, 'u18' => 0, 'u19' => 0, 'u20' => 0,
                    'u21' => 0, 'u22' => 0, 'u23' => 0, 'u24' => 0, 'u25' => 0,
                    'u26' => 0, 'u27' => 0, 'u28' => 0, 'u29' => 0, 'u30' => 0,
                    'u31' => 0, 'u32' => 0, 'u33' => 0, 'u34' => 0, 'u35' => 0,
                    'u36' => 0, 'u37' => 0, 'u38' => 0, 'u39' => 0, 'u40' => 0,
                    'u41' => 0, 'u42' => 0, 'u43' => 0, 'u44' => 0, 'u45' => 0,
                    'u46' => 0, 'u47' => 0, 'u48' => 0, 'u49' => 0, 'u50' => 0,
                    'u51' => 0, 'u52' => 0, 'u53' => 0, 'u54' => 0, 'u55' => 0,
                    'u56' => 0, 'u57' => 0, 'u58' => 0, 'u59' => 0, 'u60' => 0,
                    'u61' => 0, 'u62' => 0, 'u63' => 0, 'u64' => 0, 'u65' => 0,
                    'u66' => 0, 'u67' => 0, 'u68' => 0, 'u69' => 0, 'u70' => 0,
                    'u71' => 0, 'u72' => 0, 'u73' => 0, 'u74' => 0, 'u75' => 0,
                    'u76' => 0, 'u77' => 0, 'u78' => 0, 'u79' => 0, 'u80' => 0,
                    'u81' => 0, 'u82' => 0, 'u83' => 0, 'u84' => 0, 'u85' => 0,
                    'u86' => 0, 'u87' => 0, 'u88' => 0, 'u89' => 0, 'u90' => 0
                ];
				$start = ($post['a1_v'] - 1) * 10 + 1;
				$offhero = intval($post['h_off_bonus']);
				$hero_strenght = intval($post['h_off']);
				$deffhero = intval($post['h_def_bonus']);
				for($i = $start, $index = 1; $i <= $start + 9; $i++, $index++) {
				    if(isset($post['a1_'.$index]) && !empty($post['a1_'.$index])) {
				        $attacker['u'.$i] = $post['a1_'.$index];
				    }
				    else $attacker['u'.$i] = 0;
				    
				    if($index <=8 && isset($post['f1_'.$index]) && !empty($post['f1_'.$index])) {
				        ${'att_ab'.$index} = $post['f1_'.$index];
				    }
				    else ${'att_ab'.$index} = 0;
				}

				$defender = [];
				$defscout = 0;
				//fix by ronix
				for($i = 1;$i <= 90; $i++) {
				    if(isset($post['a2_'.$i]) && !empty($post['a2_'.$i])) {
				        $defender['u'.$i] = $post['a2_'.$i];
				        if (isset($post['f2_'.$i]) && !empty($post['f2_'.$i])){
							$def_ab[$i] = $post['f2_'.$i];
						}else{
							$def_ab[$i] = 0;
						}
				        if($i == 4 || $i == 14 || $i == 23 || $i == 44 || $i == 53 || $i == 64 || $i == 72 || $i == 84){
				            $defscout += $defender['u'.$i];
				        }
				        
				    }
				    else {
				        $defender['u'.$i] = 0;
				        $def_ab[$i] = 0;
				    }
				}
				
				$deftribe = $post['tribe'];
				$wall = 0;

				if(empty($post['kata'])) $post['kata'] = 0;

				// check scout

				$scout = 1;
				for($i = $start; $i <= $start + 9 ; $i++) {
				    if($i == 4 || $i == 14 || $i == 23 || $i == 44 || $i == 53 || $i == 64 || $i == 72 || $i == 84){
				    }else{
				        if($attacker['u'.$i] > 0) {
				            $scout = 0;
				            break;
				        }
				    }
				}
				
				if (isset($post['walllevel']) && !empty($post['walllevel'])){
					$walllevel = $post['walllevel'];
				}else{
					$walllevel = 0;
				}
				$wall = $walllevel;
				$palast = $post['palast'];

				if($scout == 1 && $defscout == 0) $walllevel = $wall = $palast = 0;

				if($scout == 1) $palast = 0; //no def point palace and residence when scout

				if(!$scout) return $this->calculateBattle($attacker,$defender,$wall,$post['a1_v'],$deftribe,$palast,$post['ew1'],$post['ew2'],$post['ktyp']+3,$def_ab,$att_ab1,$att_ab2,$att_ab3,$att_ab4,$att_ab5,$att_ab6,$att_ab7,$att_ab8,$post['kata'],$post['stonemason'],$walllevel,$offhero,$post['h_off'],$deffhero,0,0,0,0,0);
				else return $this->calculateBattle($attacker,$defender,$wall,$post['a1_v'],$deftribe,$palast,$post['ew1'],$post['ew2'],1,$def_ab,$att_ab1,$att_ab2,$att_ab3,$att_ab4,$att_ab5,$att_ab6,$att_ab7,$att_ab8,$post['kata'],$post['stonemason'],$walllevel,0,0,0,0,0,0,0,0);
				
		}

	 public function getTypeLevel($tid, $vid) {
		global $village,$database;

		$keyholder = [];
		$resourcearray = $database->getResourceLevel($vid);

		foreach(array_keys($resourcearray, $tid) as $key) {
			if(strpos($key,'t')) {
				$key = preg_replace("/[^0-9]/", '', $key);
				array_push($keyholder, $key);
			}
		}

		$element = count($keyholder);
		if($element >= 2) {
			if($tid <= 4) {
				$temparray = [];
				for($i = 0; $i <= $element - 1; $i++){
					array_push($temparray, $resourcearray['f'.$keyholder[$i]]);
				}
				foreach ($temparray as $key => $val) {
					if ($val == max($temparray))
					$target = $key;
				}
			}
			else {
				$target = 0;
				for($i=1;$i<=$element-1;$i++) {
					if($resourcearray['f'.$keyholder[$i]] > $resourcearray['f'.$keyholder[$target]]) {
						$target = $i;
					}
				}
			}
		}
		else if($element == 1) $target = 0;
		else return 0;
			
		
		if(!empty($keyholder[$target])) return $resourcearray['f'.$keyholder[$target]];
		else return 0;
	}

    //1 raid 0 normal
    function calculateBattle($Attacker, $Defender, $def_wall, $att_tribe, $def_tribe, $residence, $attpop, $defpop, $type, $def_ab, $att_ab1, $att_ab2, $att_ab3, $att_ab4, $att_ab5, $att_ab6, $att_ab7, $att_ab8, $tblevel, $stonemason, $walllevel, $offhero, $hero_strenght, $deffhero, $AttackerID, $DefenderID, $AttackerWref, $DefenderWref, $conqureby, $defReinforcements = null) {
        global $bid34, $bid35, $database;

        // Define the array, with the units
        $calvary = [4, 5, 6, 15, 16, 23, 24, 25, 26, 45, 46, 54, 55, 56, 64, 65, 66, 75, 76, 85, 86];
        $catapult = [8, 18, 28, 48, 58, 68, 78, 88];
        $rams = [7, 17, 27, 47, 57, 67, 77, 87]; 
        $catp = $ram = 0;
        
        // Array to return the result of the calculation back
        $result = [];
        $involve = 0;
        $winner = false;
        
        // at 0 all partial results
        
        //cap = Cavalry attack points
        //ap = Infantry attack points
        //cdp = Cavalry attack points
        //dp = Infantry defense points
        //rap = Result attack points
        //rdp = Result defense points
        //detected = Detected or not by defender spies
        $cap = $ap = $dp = $cdp = $rap = $rdp = 0;
        $detected = false;
        
        //Get involved artifacts
        $attacker_artefact = $database->getArtifactsValueInfluence($AttackerID, $AttackerWref, 3, 1, false);
        $defender_artefact = $database->getArtifactsValueInfluence($DefenderID, $DefenderWref, 3, 1, false);
        $strongerbuildings = $database->getArtifactsValueInfluence($DefenderID, $DefenderWref, 1, 1, false);
        $isWWVillage = $database->getVillageField($DefenderWref, 'natar');
        
        if(isset($Attacker['uhero']) && $Attacker['uhero'] > 0){
            $atkhero = $this->getBattleHero($AttackerID);
        }
        if(isset($Defender['hero']) && $Defender['hero'] > 0){
            $defenderhero = $this->getBattleHero($DefenderID);
        }
        //own defender units
        if ($type == 1) {
            $datadefScout = $this->getDataDefScout($Defender, $def_ab, $defender_artefact);
            $dp += $datadefScout['dp'];
            $cdp += $datadefScout['cdp'];
            $involve = $datadefScout['involve'];
            if(!$detected && $datadefScout['detect']) $detected = $datadefScout['detect'];
        }else{
            $datadef = $this->getDataDef($Defender, $def_ab);
            $dp += $datadef['dp'];
            $cdp += $datadef['cdp'];
            $involve = $datadef['involve'];
            if(isset($Defender['hero']) && $Defender['hero'] != 0){
                $units['Def_unit']['hero'] = $Defender['hero'];
                $cdp += $defenderhero['dc'];
                $dp += $defenderhero['di'];
                $dp *= $defenderhero['db'];
                $cdp *= $defenderhero['db'];
            }
        }
        $DefendersAll = (!is_null($defReinforcements) ? $database->getEnforceVillage($DefenderWref, 0) : $defReinforcements);

        if(!empty($DefendersAll) && $DefenderWref > 0){
            // preload village IDs
            $vilIDs = [];
            foreach($DefendersAll as $defenders) {
                $vilIDs[$defenders['from']] = true;
                $vilIDs[$defenders['to']] = true;
            }
            $vilIDs = array_keys($vilIDs);
            $database->getABTech($vilIDs);

            foreach($DefendersAll as $defenders) {
                for ($i = 1; $i <= 90; $i++) $def_ab[$i] = 0;
                $fromvillage = $defenders['from'];

                $userdataCache[$fromvillage] = $database->getUserArray($database->getVillageField($fromvillage, "owner"), 1);

                $enforcetribe = $userdataCache[$fromvillage]["tribe"];
                $ud=($enforcetribe - 1) * 10;
                if($defenders['from'] > 0) { //don't check nature tribe
                    $armory = $database->getABTech($defenders['from']); // Armory level every village enforcement
                    $def_ab[$ud + 1] = $armory['a1'];
                    $def_ab[$ud + 2] = $armory['a2'];
                    $def_ab[$ud + 3] = $armory['a3'];
                    $def_ab[$ud + 4] = $armory['a4'];
                    $def_ab[$ud + 5] = $armory['a5'];
                    $def_ab[$ud + 6] = $armory['a6'];
                    $def_ab[$ud + 7] = $armory['a7'];
                    $def_ab[$ud + 8] = $armory['a8'];
                }
                if ($type == 1) {
                    $datadefScout = $this->getDataDefScout($defenders, $def_ab, $defender_artefact);
                    $dp += $datadefScout['dp'];
                    $cdp += $datadefScout['cdp'];
                    $involve = $datadefScout['involve'];
                    if(!$detected && $datadefScout['detect']) $detected = $datadefScout['detect'];
                }else{
                    $datadef = $this->getDataDef($defenders, $def_ab);
                    $dp += $datadef['dp'];
                    $cdp += $datadef['cdp'];
                    $involve = $datadef['involve'];
                }
                $reinfowner = $database->getVillageField($fromvillage, "owner");
                $defhero = $this->getBattleHero($reinfowner);
                
                //calculate def hero from enforcement
                if($defenders['hero'] != 0){
                    $cdp += $defhero['dc'];
                    $dp += $defhero['di'];
                    $dp *= $defhero['db'];
                    $cdp *= $defhero['db'];
                }
            }
        }
        // Calculate the total number of points Attacker
        $start = ($att_tribe - 1) * 10 + 1;
        $end = $att_tribe * 10;
        
        if($att_tribe == 3) $abcount = 3;
        else $abcount = 4;

        if($type == 1) {//scout
                for($i = $start;$i <= $end; $i++) {
                    global ${'u'.$i};
                    $j = $i - $start + 1;
                    if($Attacker['u'.$i] > 0 && ($i == 4 || $i == 14 || $i == 23 || $i == 44 || $i == 53 || $i == 64 || $i == 72 || $i == 84)){
                        if(${'att_ab'.$abcount} > 0) {
                            $ap += round(35 + (35 + 300 * ${'u'.$i}['pop'] / 7) * (pow(1.007, ${'att_ab'.$abcount}) - 1), 4) * $Attacker['u'.$i];
                        }
                        else $ap += $Attacker['u'.$i] * 35;
                    }
                    $involve += $Attacker['u'.$i];
                    $units['Att_unit'][$i] = $Attacker['u'.$i];

                }
                $ap *= $attacker_artefact;

            }else{ //type=3 normal 4=raid
                $abcount = 1;
                for($i = $start; $i <= $end; $i++) {
                    global ${'u'.$i};
                    $j = $i - $start + 1;
                    if($abcount <= 8 && ${'att_ab'.$abcount} > 0) {
                        if(in_array($i,$calvary)) {
                            $cap += round(${'u'.$i}['atk'] + (${'u'.$i}['atk'] + 300 * ${'u'.$i}['pop'] / 7) * (pow(1.007, ${'att_ab'.$abcount}) - 1), 4) * (int) $Attacker['u'.$i];
                        }else{
                            $ap += round(${'u'.$i}['atk'] + (${'u'.$i}['atk'] + 300 * ${'u'.$i}['pop'] / 7) * (pow(1.007, ${'att_ab'.$abcount}) - 1), 4) * (int) $Attacker['u'.$i];
                        }
                    }else{
                        if(in_array($i,$calvary)) $cap += (int) $Attacker['u'.$i]*${'u'.$i}['atk'];                       
                        else $ap += (int) $Attacker['u'.$i]*${'u'.$i}['atk'];                      
                    }
                    
                    $abcount += 1;
                    
                    // Points catapult the attacker
                    if(in_array($i, $catapult)) $catp += (int) $Attacker['u'.$i];

                    // Points of the Rams attacker
                    if(in_array($i, $rams)) $ram += (int) $Attacker['u'.$i];

                    $involve += (int) $Attacker['u'.$i];
                    $units['Att_unit'][$i] = (int) $Attacker['u'.$i];
                }
                if (isset($Attacker['uhero']) && $Attacker['uhero'] != 0){
                    $units['Att_unit']['hero'] = $Attacker['uhero'];
                    $ap *= $atkhero['ob'];
                    $cap *= $atkhero['ob'];
                    $ap += $atkhero['atk'];
                }

                if ($offhero > 0 || $hero_strenght > 0) {
                    $atkhero= $this->getBattleHeroSim($offhero);
                    $ap *= $atkhero['ob'];
                    $cap *= $atkhero['ob'];
                    $ap += $hero_strenght;
                }
                if ($deffhero > 0) {
                    $dfdhero = $this->getBattleHeroSim($deffhero);
                    $dp *= $dfdhero['ob'];
                    $cdp *= $dfdhero['ob'];
                }
            }
         // Formula for calculating the bonus defensive wall and Residence

        if($def_wall > 0) {
            switch ($def_tribe) {
                case 1:
                    $factor = 1.030;
                    break;
                case 2:
                case 8:
                    $factor = 1.020;
                    break;
                case 3:
                case 7:
                    $factor = 1.025;
                    break;
                case 6:
                case 9:
                default:
                    $factor = 1.015;
                    break;
            }

            switch ($def_tribe) {
                case 1:
                case 8:
                    $wall_base_def = 10;
                    break;
                case 2:
                case 6:
                case 9:
                    $wall_base_def = 6;
                    break;
                case 3:
                case 7:
                    $wall_base_def = 8;
                    break;
                default:
                    $wall_base_def = 10;
                    break;
            }

            $wallMultiplier = round(pow($factor, $def_wall), 3);

            $dp += $wall_base_def * $def_wall;
            $cdp += $wall_base_def * $def_wall;

            // Defense infantry = Infantry * Wall (%)
            // Defense calvary calvary = * Wall (%)          
           if ($type == 1) {
                $dp *= $wallMultiplier;
            } else {
                $dp *= $wallMultiplier;
                $cdp *= $wallMultiplier;
            }
        }
        
        if ($type != 1) {
            $dp += (2 * pow($residence, 2));
            $cdp += (2 * pow($residence, 2));
        } else {
            $dp += (2 * pow($residence, 2));
        }

        // Formula for calculating Attacking Points (Infantry & Cavalry)
        if($AttackerWref != 0){
            $rap = round(($ap + $cap) + (($ap + $cap) / 100 * (isset($bid35[$this->getTypeLevel(35, $AttackerWref)]) ? $bid35[$this->getTypeLevel(35, $AttackerWref)]['attri'] : 0)));
        }
        else $rap = round($ap + $cap);

        // Formula for calculating Defensive Points
        if ($rap == 0) $rdp = round(($dp) + ($cdp));          
        else $rdp = round(round($cap / $rap, 4) * ($cdp) + round($ap / $rap, 4) * ($dp));

        // The Winner is....:
        $result['Attack_points'] = $rap;
        $result['Defend_points'] = $rdp;
        $winner = ($rap > $rdp);

        // Formula for calculating the Morale bonus
        // WW villages aren't affected by this bonus
        if($attpop > $defpop && !$isWWVillage) {
            $moralbonus = 1 / round(max(0.667, pow($defpop / $attpop, 0.2 * min(1, $rap / ($rdp > 0 ? $rdp : 1)))), 3);  
        }
        else $moralbonus = 1.0;

        if($involve >= 1000 && $type != 1) $Mfactor = 2 * round((1.8592 - pow($involve, 0.015)), 4);
        else $Mfactor = 1.5;
        
        if ($Mfactor < 1.2578) $Mfactor = 1.2578;
        elseif ($Mfactor > 1.5) $Mfactor = 1.5;
        
        // Formula for calculating losses
        // $type = 1 Scout, 2 Enforcement
        // $type = 3 Normal, 4 Raid
        if($type == 1){
            $holder = pow((($rdp * $moralbonus) / $rap), $Mfactor);
            if($holder > 1) $holder = 1;
            if ($rdp > $rap) $holder = 1;
            
            //Birds of Prey cannot die when scouting
            //Spies cannot die if the attacked village has no defending spies
            //Attacker result
            $result[1] = ($att_tribe == 5 || !$detected) ? 0 : $holder;

            //Defender result
            $result[2] = 0;
        }else if($type == 4) {
            $holder = ($winner) ? pow((($rdp * $moralbonus) / $rap), $Mfactor) : pow(($rap / ($rdp * $moralbonus)), $Mfactor);
            $holder = $holder / (1 + $holder);
            //Attacker result
            $result[1] = $winner ? $holder : 1 - $holder;
            //Defender result
            $result[2] = $winner ? 1 - $holder : $holder;
            $ram -= round($ram * $result[1] / 100);
            $catp -= round($catp * $result[1] / 100);
        }else if($type == 3){
            
            // Attacker
            $result[1] = ($winner) ? pow((($rdp * $moralbonus) / $rap), $Mfactor) : 1;
            
            if ($result[1] > 1){
                $result[1] = 1;
                $winner = false;
                $result['Winner'] = "defender";
            }

            // Defender
            $result[2] = (!$winner) ? pow(($rap / ($rdp * $moralbonus)), $Mfactor) : 1;
            
            if ($result[1] == 1) $result[2] = pow(($rap / ($rdp * $moralbonus)), $Mfactor);

            if ($result[2] > 1) {
                $result[2] = 1;
                $result['Winner'] = "attacker";
                $winner = true;
            }
            
            // If attacked with "Hero"
            $ku = ($att_tribe - 1) * 10 + 9;
            $kings = (int) $Attacker['u'.$ku];

            $aviables = $kings - round($kings * (int) $result[1]);
            if ($aviables > 0){
                    switch($aviables){
                    case 1: $fealthy = rand(20, 30); break;
                    case 2: $fealthy = rand(40, 60); break;
                    case 3: $fealthy = rand(60, 80); break;
                    case 4: $fealthy = rand(80, 100); break;
                    default: $fealthy = 100; break;
                }
                $result['hero_fealthy'] = $fealthy;
            }
            
            $ram -= ($winner) ? round($ram * $result[1] / 100) : round($ram * $result[2] / 100);
            $catp -= ($winner) ? round($catp * $result[1] / 100) : round($catp * $result[2] / 100);
        }

        if($catp > 0 && $tblevel != 0) {
            
            //Catapults blacksmith upgrades
            $upgrades = round(200 * pow(1.0205, $att_ab8)) / 200; 
            
            //Buildings durability
            $durability = ($stonemason > 0 ? $bid34[$stonemason]['attri'] / 100 : 1);

            //Calculates the catapults morale bonus
            $catpMoraleBonus = min(max(($attpop / ($defpop > 0 ? $defpop : 1)) ** 0.3, 1), 3);

            //New level of the building (only for warsim.php)
            $catapultsDamage = $this->calculateCatapultsDamage($catp, $upgrades, $durability, $rap / $rdp, $strongerbuildings, $catpMoraleBonus);
            $result[3] = $this->calculateNewBuildingLevel($tblevel, $catapultsDamage);
            $result[4] = $tblevel;
            
            //Results for Automation.php          
            $result['catapults']['upgrades'] = $upgrades;
            $result['catapults']['durability'] = $durability;
            $result['catapults']['attackDefenseRatio'] = $rap / $rdp;
            $result['catapults']['strongerBuildings'] = $strongerbuildings;
            $result['catapults']['moraleBonus'] = $catpMoraleBonus;
        }
        
        if($ram > 0 && $walllevel != 0) {
            
            //Rams blacksmith upgrades
            $upgrades = round(200 * pow(1.0205, $att_ab7)) / 200;

            //Building durability
            $durability = ($stonemason > 0 ? $bid34[$stonemason]['attri'] / 100 : 1);
           
            // New level of the building (only for warsim.php)
            $ramsDamage = $this->calculateCatapultsDamage($ram, $upgrades, $durability, $rap / $rdp, $strongerbuildings, 1);
            $result[7] = $this->calculateNewBuildingLevel($walllevel, $ramsDamage);
            $result[8] = $walllevel;

            // Results for Automation.php
            $result['rams']['upgrades'] = $upgrades;
            $result['rams']['durability'] = $durability;
            $result['rams']['attackDefenseRatio'] = $rap / $rdp;
            $result['rams']['strongerBuildings'] = $strongerbuildings;
            $result['rams']['moraleBonus'] = 1;
        }

        $result[6] = pow($rap / ($rdp * $moralbonus > 0 ? $rdp * $moralbonus : 1), $Mfactor);
        $result['moralBonus'] = $moralbonus;
        
        $total_att_units = count($units['Att_unit']);
        $start = intval(($att_tribe - 1) * 10 + 1);
        $end = intval($att_tribe * 10);
        
        for($i = $start; $i <= $end; $i++){
            $y = $i - (($att_tribe - 1) * 10);
            $result['casualties_attacker'][$y] = round($result[1] * $units['Att_unit'][$i]);
        }

        if (isset($units['Att_unit']['hero']) && $units['Att_unit']['hero'] >0){

            $_result = mysqli_query($database->dblink,"select heroid, health from " . TB_PREFIX . "hero where `dead`='0' and `heroid`=".(int) $atkhero['heroid']);
            $fdb = mysqli_fetch_array($_result);
            $hero_id = (int) $fdb['heroid'];
            $hero_health = $fdb['health'];
            $damage_health = round(100 * $result[1]);

            if ($hero_health <= $damage_health || $damage_health > 90){
                //hero die
                $result['casualties_attacker'][11] = 1;
                mysqli_query($database->dblink,"update " . TB_PREFIX . "hero set `dead` = 1, `health` = 0 where `heroid`=".(int) $hero_id);
            }else{
                mysqli_query($database->dblink,"update " . TB_PREFIX . "hero set `health`=`health`-".(int) $damage_health." where `heroid`=".(int) $hero_id);
            }
        }
        unset($_result, $fdb, $hero_id, $hero_health, $damage_health);


        if (isset($units['Def_unit']['hero']) && $units['Def_unit']['hero'] >0){

            $_result = mysqli_query($database->dblink,"select heroid, health from " . TB_PREFIX . "hero where `dead`='0' and `heroid`=".(int) $defenderhero['heroid']);
            $fdb = mysqli_fetch_array($_result);
            $hero_id = (int) $fdb['heroid'];
            $hero_health = $fdb['health'];
            $damage_health = round(100 * $result[2]);
            if ($hero_health <= $damage_health || $damage_health > 90){
                //hero die
                $result['deadherodef'] = 1;
                mysqli_query($database->dblink,"update " . TB_PREFIX . "hero set `dead` = 1, `health` = 0 where `heroid`=".(int) $hero_id);
            }else{
                $result['deadherodef'] = 0;
                mysqli_query($database->dblink,"update " . TB_PREFIX . "hero set `health`=`health`-".(int) $damage_health." where `heroid`=".(int) $hero_id);
            }
        }
        unset($_result, $fdb, $hero_id, $hero_health, $damage_health);

        if(!empty($DefendersAll)){
            $battleHeroesCache = [];
            foreach($DefendersAll as $defenders) {
                if($defenders['hero'] > 0) {
                    $battleHeroesCache[$defenders['from']] = $this->getBattleHero($database->getVillageField($defenders['from'],"owner"));
                    $heroarraydefender = $battleHeroesCache[$defenders['from']];
                    $_result = mysqli_query($database->dblink,"select heroid, health from " . TB_PREFIX . "hero where `dead`='0' and `heroid`=".(int) $heroarraydefender['heroid']);
                    $fdb = mysqli_fetch_array($_result);
                    $hero_id = (int) $fdb['heroid'];
                    $hero_health = $fdb['health'];
                    $damage_health = round(100 * $result[2]);
                    if ($hero_health <= $damage_health || $damage_health > 90){
                        //hero die
                        $result['deadheroref'][$defenders['id']] = 1;
                        mysqli_query($database->dblink,"update " . TB_PREFIX . "hero set `dead` = 1, `health` = 0 where `heroid`=".(int) $hero_id);
                    }else{
                        $result['deadheroref'][$defenders['id']] = 0;
                        mysqli_query($database->dblink,"update " . TB_PREFIX . "hero set `health`=`health`-".(int) $damage_health." where `heroid`=".(int) $hero_id);
                    }
                }
            }
        }
        unset($_result, $fdb, $hero_id, $hero_health, $damage_health);


        // Work out bounty
        $start = ($att_tribe - 1) * 10 + 1;
        $end = ($att_tribe * 10);

        $max_bounty = 0;

        for($i = $start; $i <= $end; $i++) {
            $j = $i - $start + 1;
            $y = $i -(($att_tribe - 1) * 10);

            $max_bounty += ((int) $Attacker['u'.$i] - (int) $result['casualties_attacker'][$y]) * (int) ${'u'.$i}['cap'];
        }

        $result['bounty'] = $max_bounty;
        return $result;
    }

    public function getDataDefScout($defenders, $def_ab, $defender_artefact) {
        $abcount = 1;
        $invol = $dp = $cdp = 0;
        $detected = false;
        
        for($y = 4; $y <= 90; $y++) {
            if($y == 4 || $y == 14 || $y == 23 || $y == 44 || $y == 53 || $y == 64 || $y == 72 || $y == 84){
                global ${'u'.$y};

                if($defenders['u'.$y] > 0 && $def_ab[$y] > 0){
                    $dp += round(20 + (20 + 300 * ${'u'.$y}['pop'] / 7) * (pow(1.007, $def_ab[$y]) - 1), 4) * $defenders['u'.$y] * $defender_artefact;
                    $detected = true;
                }else{
                	if($defenders['u'.$y] > 0){
                		$dp +=  $defenders['u'.$y] * 20 * $defender_artefact;
                		$detected = true;
                	}
                }
                
                $invol += $defenders['u'.$y]; //total troops
                $units['Def_unit'][$y] = $defenders['u'.$y];
            }
        }

        $datadef['dp'] = $dp;
        $datadef['cdp'] = $cdp;
        $datadef['detect'] = $detected;
        $datadef['involve'] = $invol;
        return $datadef;
    }

    public function getDataDef($defenders,$def_ab) {
        $dp = $cdp = $invol = 0;
        for($y = 1;$y <= 90; $y++) {
            global ${'u'.$y};
            if ($defenders['u'.$y] > 0) {
                if (!isset($def_ab[$y])) {
                    $def_ab[$y] = 0;
                }
                if ($def_ab[$y] > 0) {
                    $dp +=  round(${'u'.$y}['di'] + (${'u'.$y}['di'] + 300 * ${'u'.$y}['pop'] / 7) * (pow(1.007, $def_ab[$y]) - 1), 4) * $defenders['u'.$y];
                    $cdp += round(${'u'.$y}['dc'] + (${'u'.$y}['dc'] + 300 * ${'u'.$y}['pop'] / 7) * (pow(1.007, $def_ab[$y]) - 1), 4) * $defenders['u'.$y];
                }else{
                    $dp += $defenders['u'.$y] * ${'u'.$y}['di'];
                    $cdp += $defenders['u'.$y] * ${'u'.$y}['dc'];
                }

            }
            $invol += $defenders['u'.$y]; //total troops
            $units['Def_unit'][$y] = $defenders['u'.$y];
        }
        $datadef['dp'] = $dp;
        $datadef['cdp'] = $cdp;
        $datadef['involve'] = $invol;
        return $datadef;
    }
    
    /**
     * @author Kirilloid --> https://github.com/kirilloid/travian/blob/master/src/model/base/combat.ts
     * 
     * Calculates the new building level, after damaging it
     * 
     * @param int $oldLevel The old building level
     * @param float $damage The damage done by catapults
     * @return int Returns the new building level
     */
    
    public function calculateNewBuildingLevel($oldLevel, $damage){
        $damage -= 0.5;
        if ($damage < 0) return $oldLevel;
        
        while ($damage >= $oldLevel && $oldLevel) $damage -= $oldLevel--;
        
        return $oldLevel;
    }
    
    /**
     * @author Kirilloid --> https://github.com/kirilloid/travian/blob/master/src/model/base/combat.ts
     * 
     * Calculates the damage done by catapults
     * 
     * @param int $catapultsQuantity The quantity of catapults which take part in the attack
     * @param double $catapultsUpgrade The catapults upgrade multiplier, affected by the cataputls level in the blacksmith
     * @param double $durability The building durability, affected by the stonemason's lodge
     * @param double $ADRatio The attack points / defensive points ratio
     * @param double $strongerBuildings The artifacts multiplier, which strengthens the building, affected by durability artifacts
     * @param double $moraleBonus The defender morale bonus
     * @return double Returns the damage done by catapults
     */
    
    public function calculateCatapultsDamage($catapultsQuantity, $catapultsUpgrade, $durability, $ADRatio, $strongerBuildings, $moraleBonus){
        $catapultsEfficiency = floor($catapultsQuantity / ($durability * $strongerBuildings));
        return 4 * ($this->sigma)($ADRatio) * $catapultsEfficiency * $catapultsUpgrade / $moraleBonus;
    }

    /**
     * newCalculateBattle - Versão Monolítica e Completa
     * Uma versão refatorada que mantém toda a lógica em um só lugar,
     * mas com a vantagem de uma entrada e saída de dados estruturada.
     * Não tem efeitos colaterais e retorna um resultado completo para o AttackHandler.
     *
     * @param array $context O contexto completo da batalha.
     * @return array O resultado estruturado da batalha.
     */
    public function newCalculateBattle(array $context) {
        global $database;
        // --- 1. SETUP INICIAL E EXTRAÇÃO DE DADOS DO CONTEXTO ---
        $attacker = $context['attacker'];
        $attackData = $context['attackData'];
        $isScout = ($attackData['attack_type'] == 1);
        $defender = $context['defender'];
        $wallLevelOverride = isset($context['wallLevelOverride']) ? $context['wallLevelOverride'] : -1;


        $finalResult = [
            'winner' => 'defender',
            'moralBonus' => 1.0,
            'bounty' => 0,
            'casualties' => ['attacker' => [], 'defender' => ['own' => [], 'reinforcements' => []],'defender_hero' => 0],
            'hero_outcomes' => ['attacker' => ['damage' => 0], 'defender' => ['own' => ['damage' => 0]]],
            'siege_results' => [
                'ram_info' => '',
                'catapult_info' => '',
                'wall' => ['old' => 0, 'new' => 0],
                'destroyed_buildings' => []
            ],
            'units' => ['defender_sent' => [], 'defender_sent_hero' => 0],
            'xp_gained' => ['attacker' => 0, 'defender' => 0],
            'totals' => []
        ];

        // --- 2. CÁLCULO DOS PONTOS DE ATAQUE ---
        $ap = 0; $cap = 0;
        $attacker_units_sent = [];
        $att_ab = $attacker['forces']['abTech'];
        $spy_unit_ids = [4, 14, 23, 44, 53, 64, 72, 84]; // IDs das unidades de espionagem

        foreach ($attacker['forces']['units'] as $unit_id_str => $count) {
            if ($count <= 0 || !ctype_digit(substr($unit_id_str, 1))) continue;

            $unit_id = (int) substr($unit_id_str, 1);
            global ${'u' . $unit_id};
            $unit_data = ${'u' . $unit_id};
            $attacker_units_sent[$unit_id] = $count;

            // Se for um ataque de espionagem, calcule o "poder de ataque" dos espiões
            if ($isScout) {
                if (in_array($unit_id, $spy_unit_ids)) {
                    $ab_level = $att_ab['b' . (($unit_id - 1) % 10 + 1)];
                    // Fórmula de espionagem do código antigo
                    $unit_attack = 35 + ($ab_level > 0 ? (35 + 300 * $unit_data['pop'] / 7) * (pow(1.007, $ab_level) - 1) : 0);
                    $ap += $count * $unit_attack; // Adicionamos tudo a $ap para simplificar
                }
                // Outras unidades não contribuem com poder em um ataque de espionagem
            } 
            // Senão, use a lógica de ataque normal
            else {
                $ab_level = 0;
                $unit_for_abtech = (($unit_id - 1) % 10 + 1);
                if ($unit_for_abtech != 9 || $unit_for_abtech != 10 ) {
                    $ab_level = $att_ab['b' . (($unit_id - 1) % 10 + 1)];           
                }
                $unit_attack = $unit_data['atk'] + ($ab_level > 0 ? ($unit_data['atk'] + 300 * $unit_data['pop'] / 7) * (pow(1.007, $ab_level) - 1) : 0);
                (in_array($unit_id, [4, 5, 6, 15, 16, 23, 24, 25, 26, 45, 46])) ? $cap += $count * $unit_attack : $ap += $count * $unit_attack;
            }
        }
        if (!$isScout && isset($attacker['forces']['units']['uhero']) && $attacker['forces']['units']['uhero'] > 0) {
            $atkhero = $this->getBattleHero($attacker['info']['id']);
            if ($atkhero['heroid'] > 0){
                $ap *= $atkhero['ob']; 
                $cap *= $atkhero['ob']; 
                $ap += $atkhero['atk'];
                $attacker_units_sent['hero'] = 1;
                $finalResult['hero_outcomes']['attacker']['id'] = $atkhero['heroid'];
                $finalResult['hero_outcomes']['attacker']['health'] = $atkhero['health'];
            }
        }

        // --- 3. CÁLCULO DOS PONTOS DE DEFESA (PRÉ-RECÁLCULO) ---
        $def_points_data = $this->calculate_def_points_new($context, $wallLevelOverride, $finalResult, $isScout);

        $rap = round($ap + $cap);
        if ($attacker['info']['tribe'] == 2) { // 2 = Teutões
            $brewery_level = $this->getTypeLevel(35, $attacker['info']['wref']); // 35 = GID da Cervejaria
            if ($brewery_level > 0) {
                $brewery_bonus = $GLOBALS['bid35'][$brewery_level]['attri'];
                $rap += round($rap * ($brewery_bonus / 100));
            }
        }
        if ($isScout){
            $rdp = round($def_points_data['dp']);
        }else{
            $rdp = ($rap == 0) ? round($def_points_data['dp'] + $def_points_data['cdp']) : round(($cap / $rap) * $def_points_data['cdp'] + ($ap / $rap) * $def_points_data['dp']);
        }
                
        // --- 4. CÁLCULO DE DANO DE ARÍETES E POSSÍVEL RECÁLCULO ---
        $old_wall_level = $defender['forces']['wallLevel'];
        $new_wall_level = $old_wall_level;
        $ram_count = (isset($attacker_units_sent[7]) ? $attacker_units_sent[7] : 0) +
                     (isset($attacker_units_sent[17]) ? $attacker_units_sent[17] : 0) +
                     (isset($attacker_units_sent[27]) ? $attacker_units_sent[27] : 0) +
                     (isset($attacker_units_sent[47]) ? $attacker_units_sent[47] : 0);
        if ($ram_count > 0 && $attackData['attack_type'] == 3 && $old_wall_level > 0) {
            $ram_upgrades = pow(1.0205, $att_ab['b7']);
            $stonemason = $this->getTypeLevel(34, $defender['info']['wref']);
            $durability = $stonemason > 0 ? $GLOBALS['bid34'][$stonemason]['attri'] / 100 : 1;
            $ram_damage = $this->calculateCatapultsDamage($ram_count, $ram_upgrades, $durability, $rap / $rdp, 1, 1);
            $new_wall_level = $this->calculateNewBuildingLevel($old_wall_level, $ram_damage);
        }
        $finalResult['siege_results']['wall']['old'] = $old_wall_level;
        $finalResult['siege_results']['wall']['new'] = $new_wall_level;
        
        if ($new_wall_level < $old_wall_level) {
            $def_points_data = $this->calculate_def_points_new($context, $new_wall_level, $finalResult);
            $rdp = ($rap == 0) ? round($def_points_data['dp'] + $def_points_data['cdp']) : round(($cap / $rap) * $def_points_data['cdp'] + ($ap / $rap) * $def_points_data['dp']);
        }

        // --- 5. CÁLCULO FINAL DAS PERDAS DE TROPAS ---
        $moralbonus = 1.0;
        if ($attacker['population'] > $defender['population'] && $defender['info']['owner'] != 3 && $attacker['info']['owner'] != 3){//Ataque a natars não tem moral bonus
            $moralbonus = 1 / pow($defender['population'] / $attacker['population'], 0.2);
            $moralbonus = min($moralbonus, 1.5);
        }
        
        if ($moralbonus > 1) $rdp *= $moralbonus;
        $finalResult['moralBonus'] = $moralbonus;

        $total_involved = $attacker['population'] + $defender['population'];
        $Mfactor = ($total_involved >= 1000) ? 2 * (1.8592 - pow($total_involved, 0.015)) : 1.5;
        $ratio = $rdp > 0 ? $rap / $rdp : $rap;
        
        if ($isScout) {
            $detected = $def_points_data['detected'];
            $defender_loss_percentage = 0; // Defensor nunca perde espiões na defesa.

            // Se o defensor não tem espiões, ou se o atacante é da tribo Natar, a perda é zero.
            if (!$detected || $attacker['info']['tribe'] == 5) {
                $attacker_loss_percentage = 0;
            } else {
                // Se foi detectado, calcula a perda proporcional.
                // Se $rap (ataque) for 0, a divisão por zero causará erro, então tratamos isso.
                if ($rap > 0) {
                    $attacker_loss_percentage = pow($rdp / $rap, 1.5);
                } else {
                    $attacker_loss_percentage = 1; // Se o ataque é 0 e a defesa não, perda total.
                }

                // Garante que a perda não exceda 100%.
                if ($attacker_loss_percentage > 1) {
                    $attacker_loss_percentage = 1;
                }
            }
            $attacker_loss_percentage = ($rap > 0 && $rap < $rdp) ? pow($rdp / $rap, 1.5) : 0;
            if ($attacker_loss_percentage > 1) $attacker_loss_percentage = 1;
            
        } 
        else if ($attackData['attack_type'] == 4) { // Lógica de perdas para ASSALTO (Raid)
            $winner = $ratio > (1 / pow(1.0000001, $Mfactor));
            $holder = $winner ? pow(1/$ratio, $Mfactor) : pow($ratio, $Mfactor);
            $holder = $holder / (1 + $holder);

            $attacker_loss_percentage = $winner ? $holder : 1 - $holder;
            $defender_loss_percentage = $winner ? 1 - $holder : $holder;
        } else { // Lógica de perdas para ATAQUE NORMAL
            $attacker_loss_percentage = ($ratio >= 1) ? pow(1/$ratio, $Mfactor) : 1.0;
            $defender_loss_percentage = ($ratio < 1) ? pow($ratio, $Mfactor) : 1.0;
        }
        
        $finalResult['winner'] = ($attacker_loss_percentage < $defender_loss_percentage) ? 'attacker' : 'defender';

        $finalResult['hero_outcomes']['attacker']['damage'] = round(100 * $attacker_loss_percentage);
        $finalResult['hero_outcomes']['defender']['own']['damage'] = round(100 * $defender_loss_percentage);

        foreach ($attacker_units_sent as $uid => $c){
           if ($uid == 'hero') {
                $finalResult['casualties']['attacker']['hero'] = 0;

                if ($c > 0) {
                    $hero_damage = $finalResult['hero_outcomes']['attacker']['damage'];
                    $heroHealth = $finalResult['hero_outcomes']['attacker']['health'];

                    error_log("Heroi Atacante: Health=" . $heroHealth . " | Damage=" . $hero_damage . ".");
                    if (isset($heroHealth) && ($heroHealth - $hero_damage) <= 0) {
                        $finalResult['casualties']['attacker']['hero'] = 1;
                        error_log("Heroi Atacante Morreu -> Health-Damage = " . ($heroHealth - $hero_damage) . ".");
                    }
                }

           }else{
                $finalResult['casualties']['attacker'][$uid] = round($c * $attacker_loss_percentage);
           }
        } 
        foreach ($def_points_data['def_units_sent']['own'] as $uid => $c) {
            if ($uid == 'hero') {
                $finalResult['casualties']['defender']['own']['hero'] = 0;

                if ($c > 0) {
                    $hero_damage = $finalResult['hero_outcomes']['defender']['own']['damage'];
                    $heroHealth = $finalResult['hero_outcomes']['defender']['own']['health'];

                    error_log("Heroi Defensor: Health=" . $heroHealth . " | Damage=" . $hero_damage . ".");
                    if (isset($heroHealth) && ($heroHealth - $hero_damage) <= 0) {
                        $finalResult['casualties']['defender']['own']['hero'] = 1;
                        error_log("Heroi Defensor Morreu -> Health-Damage = " . ($heroHealth - $hero_damage) . ".");
                    }
                }

            }else{
                $finalResult['casualties']['defender']['own'][$uid] = round($c * $defender_loss_percentage);
            }
            
        }
        foreach ($def_points_data['def_units_sent']['reinforcements'] as $rid => $units) {
            foreach($units as $uid => $c) {
                if ($uid == 'hero') {
                    $finalResult['casualties']['defender']['reinforcements'][$rid]['hero'] = 0;

                    if ($c > 0) {
                        $hero_damage = $finalResult['hero_outcomes']['defender']['own']['damage']; //Mesmo dano aplicado a todos herois de defesa
                        $heroHealth = $finalResult['hero_outcomes']['defender']['reinforcements'][$rid]['health'];

                        error_log("Heroi Reforçando Defensor: Health=" . $heroHealth . " | Damage=" . $hero_damage . ".");
                        if (isset($heroHealth) && ($heroHealth - $hero_damage) <= 0) {
                            $finalResult['casualties']['defender']['reinforcements'][$rid]['hero'] = 1;
                            error_log("Heroi Reforçando Defensor Morreu -> Health-Damage = " . ($heroHealth - $hero_damage) . ".");
                        }
                    }

                }else{
                    $finalResult['casualties']['defender']['reinforcements'][$rid][$uid] = round($c * $defender_loss_percentage);
                }
                
            }
        }

        // --- 6. CÁLCULO DE RESULTADOS DE HERÓIS E SAQUE (BOUNTY) ---
        

        $bounty = 0;
        foreach($attacker_units_sent as $uid => $c) {
            if($uid == 'hero') continue;
            global ${'u'.$uid}; $survivors = $c - $finalResult['casualties']['attacker'][$uid];
            $bounty += $survivors * ${'u'.$uid}['cap'];
        }
        $finalResult['bounty'] = $bounty;

        $attacker_xp_gained = 0;
        $defender_xp_gained = 0;

        // XP do Atacante = Soma da população das tropas defensoras mortas
        foreach ($finalResult['casualties']['defender']['own'] as $unit_id => $count) {
            if ($count <= 0) continue;
           if ($unit_id === 'hero') {
                $attacker_xp_gained += $count * 6;
            } else {
                global ${'u'.$unit_id};
                if(isset(${'u'.$unit_id})) {
                    $attacker_xp_gained += $count * ${'u'.$unit_id}['pop'];
                }
            }
        }
        foreach ($finalResult['casualties']['defender']['reinforcements'] as $reinf_id => $casualties) {
            foreach ($casualties as $unit_id => $count) {
                if ($count <= 0) continue;
                if ($unit_id === 'hero') {
                    $attacker_xp_gained += $count * 6;
                } else {
                    global ${'u'.$unit_id};
                    if(isset(${'u'.$unit_id})) {
                        $attacker_xp_gained += $count * ${'u'.$unit_id}['pop'];
                    }
                }
            }
        }

        // XP do Defensor = Soma da população das tropas atacantes mortas
        foreach ($finalResult['casualties']['attacker'] as $unit_id => $count) {
            if ($count <= 0) continue;
            if ($unit_id === 'hero') {
                $defender_xp_gained += $count * 6;
            } else {
                global ${'u'.$unit_id};
                if(isset(${'u'.$unit_id})) {
                    $defender_xp_gained += $count * ${'u'.$unit_id}['pop'];
                }
            }
        }
        
        $finalResult['xp_gained'] = [
            'attacker' => $attacker_xp_gained,
            'defender' => $defender_xp_gained
        ];

        // --- 7. CÁLCULO DE DANO DE CATAPULTAS ---
        $catapult_count = 0;
        if(isset($attacker_units_sent[8])) { $catapult_count += ($attacker_units_sent[8] - $finalResult['casualties']['attacker'][8]); }
        if(isset($attacker_units_sent[18])) { $catapult_count += ($attacker_units_sent[18] - $finalResult['casualties']['attacker'][18]); }
        if(isset($attacker_units_sent[28])) { $catapult_count += ($attacker_units_sent[28] - $finalResult['casualties']['attacker'][28]); }
        if(isset($attacker_units_sent[48])) { $catapult_count += ($attacker_units_sent[48] - $finalResult['casualties']['attacker'][48]); }
        if(isset($attacker_units_sent[58])) { $catapult_count += ($attacker_units_sent[58] - $finalResult['casualties']['attacker'][58]); }
        if(isset($attacker_units_sent[68])) { $catapult_count += ($attacker_units_sent[68] - $finalResult['casualties']['attacker'][68]); }
        if(isset($attacker_units_sent[78])) { $catapult_count += ($attacker_units_sent[78] - $finalResult['casualties']['attacker'][78]); }
        if(isset($attacker_units_sent[88])) { $catapult_count += ($attacker_units_sent[88] - $finalResult['casualties']['attacker'][88]); }

        if ($catapult_count > 0 && $attackData['attack_type'] == 3) {
            $bdo = $database->getResourceLevel($defender['info']['wref'], false);
            $catapult_upgrades = pow(1.0205, $att_ab['b8']);
            $stonemason = $this->getTypeLevel(34, $defender['info']['wref']);
            $durability = $stonemason > 0 ? $GLOBALS['bid34'][$stonemason]['attri'] / 100 : 1;
            
            $catapult_targets = [$attackData['ctar1']];
            if (isset($attackData['ctar2']) && $attackData['ctar2'] != 0) {
                $catapult_targets[] = $attackData['ctar2'];
            }
            $shots_per_target = floor($catapult_count / count($catapult_targets));
            
            foreach ($catapult_targets as $target_gid) {
                $fID = $this->determineCatapultTarget($bdo, $target_gid);
                if ($fID) {
                    $old_level = $bdo['f' . $fID];
                    $damage = $this->calculateCatapultsDamage($shots_per_target, $catapult_upgrades, $durability, $ratio, 1, $moralbonus);
                    $new_level = $this->calculateNewBuildingLevel($old_level, $damage);
                    
                    $finalResult['siege_results']['destroyed_buildings'][] = ['fID' => $fID, 'old' => $old_level, 'new' => $new_level, 'gid' => $bdo['f' . $fID . 't']];
                    $bdo['f' . $fID] = $new_level; // Atualiza o BDO local para o próximo tiro, se houver
                }
            }
        }

        // --- BLOCO FINAL: CÁLCULO DOS TOTAIS PARA O RELATÓRIO ---
        $total_attacker_sent = array_sum($attacker_units_sent);
        $total_attacker_casualties = array_sum($finalResult['casualties']['attacker']);

        $total_defender_sent = array_sum($def_points_data['def_units_sent']['own']);
        $total_defender_casualties = array_sum($finalResult['casualties']['defender']['own']);
        foreach($def_points_data['def_units_sent']['reinforcements'] as $reinf_units) {
            $total_defender_sent += array_sum($reinf_units);
        }
        foreach($finalResult['casualties']['defender']['reinforcements'] as $reinf_cas) {
            $total_defender_casualties += array_sum($reinf_cas);
        }

        $finalResult['units']['defender_sent'] = $def_points_data['def_units_sent'];
        $finalResult['units']['defender_sent_hero'] = $def_points_data['total_heroes'];
        
        $dead_def_hero_count = 0;
        if (isset($finalResult['casualties']['defender']['own']['hero'])) {
            $dead_def_hero_count += $finalResult['casualties']['defender']['own']['hero'];
        }
        if (isset($finalResult['casualties']['defender']['reinforcements'])) {
            foreach($finalResult['casualties']['defender']['reinforcements'] as $reinf_cas) {
                if(isset($reinf_cas['hero'])) {
                    $dead_def_hero_count += $reinf_cas['hero'];
                }
            }
        }
        $finalResult['casualties']['defender_hero'] = $dead_def_hero_count;

        // Calcula o total de espiões defensores para a lógica de notificação
        $total_defending_spies = 0;
        $spy_units_ids = [4, 14, 23, 44, 53, 64, 72, 84];
        foreach ($spy_units_ids as $spy_id) {
            if (isset($def_points_data['def_units_sent']['own'][$spy_id])) {
                $total_defending_spies += $def_points_data['def_units_sent']['own'][$spy_id];
            }
            foreach ($def_points_data['def_units_sent']['reinforcements'] as $reinf_units) {
                if (isset($reinf_units[$spy_id])) {
                    $total_defending_spies += $reinf_units[$spy_id];
                }
            }
        }

        $finalResult['totals'] = [
            'attacker' => [
                'sent' => $total_attacker_sent,
                'casualties' => $total_attacker_casualties,
                'survivors' => $total_attacker_sent - $total_attacker_casualties,
            ],
            'defender' => [
                'sent' => $total_defender_sent,
                'casualties' => $total_defender_casualties,
                'survivors' => $total_defender_sent - $total_defender_casualties,
                'spy' => $total_defending_spies,
            ],
        ];
        
        return $finalResult;
    }


    private function calculate_def_points_new($context, $wallLevelOverride, &$finalResult, $isScout = false) {
        global $database;
        $wall_level = ($wallLevelOverride !== -1) ? $wallLevelOverride : $context['defender']['forces']['wallLevel'];
        $dp = 0; $cdp = 0; $total_def_units = 0; 
        $def_units_sent = ['own' => [], 'reinforcements' => []];
        $total_heroes = 0;
        $spy_unit_ids = [4, 14, 23, 44, 53, 64, 72, 84]; // IDs das unidades de espionagem
        $detected = false;

        $own_abTech = $context['defender']['forces']['abTech'];
        if(isset($context['defender']['forces']['own_units'])) {
            foreach ($context['defender']['forces']['own_units'] as $unit_id_str => $count) {
                if (substr($unit_id_str, 0, 1) !== 'u' && $unit_id_str !== 'hero') continue; // Pula chaves como 'vref'
                if ($count <= 0) continue;

                $is_hero_unit = ($unit_id_str === 'hero');
                if ($is_hero_unit) {
                    $def_units_sent['own']['hero'] = $count;
                }else{
                    $unit_id = (int) substr($unit_id_str, 1);
                    $def_units_sent['own'][$unit_id] = $count;
                }

                if ($isScout) {
                    if (isset($unit_id) && in_array($unit_id, $spy_unit_ids)) {
                        global ${'u' . $unit_id}; $unit_data = ${'u' . $unit_id};
                        $ab_level = isset($own_abTech['a' . (($unit_id - 1) % 10 + 1)]) ? $own_abTech['a' . (($unit_id - 1) % 10 + 1)] : 0;
                        $unit_defense = 20 + ($ab_level > 0 ? (20 + 300 * $unit_data['pop'] / 7) * (pow(1.007, $ab_level) - 1) : 0);
                        $dp += $count * $unit_defense;
                        $detected = true;
                    }
                } else {

                    if ($is_hero_unit) { // Herói próprio
                        $total_heroes += $count;
                        $defhero = $this->getBattleHero($context['defender']['info']['id']);
                        $dp += $defhero['di'];
                        $cdp += $defhero['dc'];
                        $finalResult['hero_outcomes']['defender']['own']['id'] = $defhero['heroid'];
                        $finalResult['hero_outcomes']['defender']['own']['health'] = $defhero['health'];
                        continue;
                    }

                    global ${'u' . $unit_id}; $unit_data = ${'u' . $unit_id};
                    $total_def_units += $count;
                    $ab_level = isset($own_abTech['a' . (($unit_id - 1) % 10 + 1)]) ? $own_abTech['a' . (($unit_id - 1) % 10 + 1)] : 0;
                    $unit_di = $unit_data['di'] + ($ab_level > 0 ? ($unit_data['di'] + 300 * $unit_data['pop'] / 7) * (pow(1.007, $ab_level) - 1) : 0);
                    $unit_dc = $unit_data['dc'] + ($ab_level > 0 ? ($unit_data['dc'] + 300 * $unit_data['pop'] / 7) * (pow(1.007, $ab_level) - 1) : 0);
                    $dp += $count * $unit_di;
                    $cdp += $count * $unit_dc;
                }
            }
        }

        foreach ($context['defender']['forces']['reinforcements'] as $reinf) {
            $reinf_id = $reinf['id'];
            $def_units_sent['reinforcements'][$reinf_id] = [];
            $reinf_abTech = isset($context['defender']['forces']['reinforcement_abTech'][$reinf['from']]) ? $context['defender']['forces']['reinforcement_abTech'][$reinf['from']] : [];
            $reinf_owner = $database->getVillageField($reinf['from'], "owner");
            
            foreach ($reinf as $key => $count) {
                if ($count <= 0) continue;
                
                if ($key == 'hero') {
                    $total_heroes += $count;
                    $def_units_sent['reinforcements'][$reinf_id]['hero'] = $count;
                    $reinf_hero_data = $this->getBattleHero($reinf_owner);
                    $dp += $reinf_hero_data['di'];
                    $cdp += $reinf_hero_data['dc'];
                    $finalResult['hero_outcomes']['defender']['reinforcements'][$reinf_id] = ['id' => $reinf_hero_data['heroid'], 'health' => $reinf_hero_data['health'], 'owner' => $reinf_owner, 'damage' => 0];
                } elseif (ctype_digit(substr($key, 1))) {
                    $unit_id = (int) substr($key, 1);
                    global ${'u' . $unit_id}; $unit_data = ${'u' . $unit_id};
                    $total_def_units += $count;
                    $def_units_sent['reinforcements'][$reinf_id][$unit_id] = $count;
                    $ab_level = isset($reinf_abTech['a' . (($unit_id - 1) % 10 + 1)]) ? $reinf_abTech['a' . (($unit_id - 1) % 10 + 1)] : 0;
                    
                    $unit_di = $unit_data['di'] + ($ab_level > 0 ? ($unit_data['di'] + 300 * $unit_data['pop'] / 7) * (pow(1.007, $ab_level) - 1) : 0);
                    $unit_dc = $unit_data['dc'] + ($ab_level > 0 ? ($unit_data['dc'] + 300 * $unit_data['pop'] / 7) * (pow(1.007, $ab_level) - 1) : 0);
                    $dp += $count * $unit_di;
                    $cdp += $count * $unit_dc;
                }
            }
        }

        // Bônus
        if ($wall_level > 0) {
            switch ($context['defender']['info']['tribe']) {
                case 1:
                    $factor = 1.030;
                    break;
                case 2:
                case 8:
                    $factor = 1.020;
                    break;
                case 3:
                case 7:
                    $factor = 1.025;
                    break;
                case 6:
                case 9:
                default:
                    $factor = 1.015;
                    break;
            }

            switch ($context['defender']['info']['tribe']) {
                case 1:
                case 8:
                    $wall_base_def = 10;
                    break;
                case 2:
                case 6:
                case 9:
                    $wall_base_def = 6;
                    break;
                case 3:
                case 7:
                    $wall_base_def = 8;
                    break;
                default:
                    $wall_base_def = 10;
                    break;
            }

            $dp += $wall_base_def * $wall_level;
            $cdp += $wall_base_def * $wall_level;

            $dp *= pow($factor, $wall_level); $cdp *= pow($factor, $wall_level);
        }
        $dp += (2 * pow($context['defender']['forces']['residenceLevel'], 2));
        if (!$isScout) {
            $cdp += (2 * pow($context['defender']['forces']['residenceLevel'], 2));
        }
        
        return [
            'dp' => $dp, 'cdp' => $cdp, 'total_def_units' => $total_def_units, 
            'def_units_sent' => $def_units_sent, 'total_heroes' => $total_heroes,
            'detected' => $detected
        ];
    }

     /**
     * Determina o alvo final de uma onda de catapultas com base na mira do jogador (específica ou aleatória).
     *
     * @param array $bdo Os dados dos edifícios da aldeia ('fdata').
     * @param int $requestedTarget O GID do edifício alvo (ou 0/99 para aleatório).
     * @return int|null O ID do campo (fID) a ser atacado (ex: 22, 35, 99) ou null se não houver alvos.
     */
    private function determineCatapultTarget(array $bdo, int $requestedTarget) {
        $possibleTargets = [];
        // Se a mira for aleatória (0 ou 99 no original)
        if ($requestedTarget == 0 || $requestedTarget == 99) {
            for ($i = 1; $i <= 41; $i++) {
                if ($i == 41) $i = 99; // f99 para a Maravilha do Mundo
                
                // Adiciona à lista se o edifício/campo tiver nível > 0 e não for a muralha
                if (isset($bdo['f' . $i]) && $bdo['f' . $i] > 0 && $i != 40) {
                    $possibleTargets[] = $i;
                }
            }
        } else { // Se a mira for um tipo de edifício específico
            for ($i = 1; $i <= 41; $i++) {
                 if ($i == 41) $i = 99;

                 // Adiciona à lista se o tipo de edifício corresponder ao alvo e tiver nível > 0
                 if (isset($bdo['f' . $i . 't']) && $bdo['f' . $i . 't'] == $requestedTarget && isset($bdo['f' . $i]) && $bdo['f' . $i] > 0 && $i != 40) {
                     $possibleTargets[] = $i;
                 }
            }
        }

        // Se a mira específica não encontrou alvos, tenta um alvo aleatório como fallback
        if (empty($possibleTargets) && $requestedTarget != 0 && $requestedTarget != 99) {
            return $this->determineCatapultTarget($bdo, 0);
        }

        if (empty($possibleTargets)) {
            return null; // Nenhum alvo possível na aldeia
        }

        // Retorna um dos alvos possíveis de forma aleatória
        return $possibleTargets[array_rand($possibleTargets)];
    }
};

$battle = new Battle;
?>
