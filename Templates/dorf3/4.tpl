<?php
include('menu.tpl');
?>
<table id="culture_points" cellpadding="1" cellspacing="1">
<thead>
<tr><th colspan="5">Culture points</th></tr>
<tr><td>Village</td><td>CP/day</td><td>Celebrations</td><td>Troops</td><td>Slots</td></tr>
</thead>
<tbody>
<?php

$timer = 0;
$varray = $database->getProfileVillages($session->uid, 8);

$totRemainingSlots = 0;
foreach($varray as $vil){
	$vid = $vil['wref'];
	$cp = $database->getVillageField($vid, 'cp');
	$exp = 0;
	for($i=1;$i<=3;$i++) {
		${'slot'.$i} = $database->getVillageField($vid, 'exp'.$i);
		if(${'slot'.$i} != 0) { $exp++;	}
	}
	$lvlTH = $building->getTypeLevel(24,$vid);
	$lvlRes = $building->getTypeLevel(25,$vid);
	$lvlPal = $building->getTypeLevel(26,$vid);
	$maxslots = ($lvlRes>=10?floor($lvlRes/10):0)+($lvlPal>=10?floor(($lvlPal-5)/5):0);
	$hasCel = $database->getVillageField($vid,'celebration');
	$typeCel = "";
	if ($hasCel <> 0) { 
		$timer++; 
		if ($database->getVillageField($vid,'type') == 1){
			$typeCel = "(S) ";
		}else if ($database->getVillageField($vid,'type') == 2){
			$typeCel = "(G) ";
		}
	}

	if($vid == $village->wid){$class = 'hl';}else{$class = '';}

	echo '<tr class="'.$class.'"><td class="vil fc"><a href="dorf1.php?newdid='.$vid.'">'.$vil['name'].'</a></td>';
	echo '<td class="cps">'.$cp.'</td>';
	echo '<td class="cel">'.($lvlTH>0?'<a href="build.php?newdid='.$vid.'&amp;gid=24">'.($hasCel<>0? $typeCel . '<span id="timer'.$timer.'">'. $generator->getTimeFormat($hasCel-time()).'</span>':'●').'</a>':'&nbsp;').'</td>';
	echo '<td class="tro"><span class="">';
	$unit = $database->getUnit($vid);
	$tribe = $session->tribe;
	$siedler = $unit['u'.$tribe*10];
	$siedlerp = '<img src=img/un/u/'.($tribe*10).'.gif />';
	$senator = (isset($unit['u'.((($tribe-1)*10)+9)]) ? $unit['u'.((($tribe-1)*10)+9)] : 0);
	$senatorp = '<img src=img/un/u/'.(($tribe-1)*10+9).'.gif />';
	$i=1;
	while($i <=$siedler) {
		echo $siedlerp;
		$i++;
	}
	$s=1;
	while($s <=$senator) {
		echo $senatorp;
		$s++;
	}		
		
	echo '</span></td>';
	$remainingSlots = $maxslots - $exp;
	if ($maxslots == 0 || $maxslots < $exp) $remainingSlots = 0;
	$totRemainingSlots += $remainingSlots;
	echo '<td class="slo lc">' .$remainingSlots. '</td>';
	$gesexp = (isset($gesexp) ? $gesexp : 0) + $exp;
	$gesdorf = (isset($gesdorf) ? $gesdorf : 0) + $maxslots;
	$gescp = (isset($gescp) ? $gescp : 0) + $cp;
	$gessied = (isset($gessied) ? $gessied : 0) + $siedler;
	$gessen = (isset($gessen) ? $gessen : 0) + $senator;
	echo '</tr>';    
}
?>

<tr><td colspan="5" class="empty"></td></tr>

<tr class="sum">
	<th class="vil">Sum</th>
	<td class="cps"><?php echo $gescp;?></td>
	<td class="cel none">&nbsp;</td>

	<td class="tro">
	<?php 	
	echo $gessied . " ";
	echo $siedlerp;
	echo '&nbsp;';
	echo $gessen . " ";
	echo $senatorp;
	?></td>
	<td class="slo"><?php 
	//echo $gesexp;echo '/';echo $gesdorf;
	echo $totRemainingSlots;
	?></td>
</tr></tbody></table>
