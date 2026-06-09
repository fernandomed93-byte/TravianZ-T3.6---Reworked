<?php

$result      = mysqli_query($database->dblink,"SELECT * FROM ".TB_PREFIX."units WHERE `vref` = ".(int) $village->wid."");
$units_array = mysqli_fetch_array($result);

$count_hero = mysqli_fetch_array(mysqli_query($database->dblink,"SELECT Count(*) as Total FROM " . TB_PREFIX . "hero WHERE `uid` = " . $database->escape($session->uid) . ""), MYSQLI_ASSOC);
$count_hero = $count_hero['Total'];

if ($count_hero < 3) {

$heroMap = [
	1 => [1, 2, 3, 5, 6],
	2 => [11, 12, 13, 15, 16],
	3 => [21, 22, 24, 25, 26],
	6 => [51, 52, 54, 55, 56],
	7 => [61, 62, 63, 65, 66],
	8 => [71, 73, 74, 75, 76],
	9 => [81, 82, 83, 85, 86],
];

$output="<table cellpadding=1 cellspacing=1 class=\"build_details\">
	<thead>
		<tr>
			<th colspan=2>".TRAIN_HERO."</th>
		</tr>
	</thead>";

foreach ($heroMap[$session->tribe] ?? [] as $i => $unitId) {
	$isBase = ($i == 0);
	$researched = $isBase || $database->checkIfResearched($village->wid, 't'.$unitId);

	if (!$researched) continue;

	$u = ${'u'.$unitId};
	$unitName = $technology->getUnitName($unitId);
	$output.="<tr>
		<td class=\"desc\">
			<div class=\"tit\">
				<img class=\"unit u".$unitId."\" src=\"gpack/travian_default/img/x.gif\" alt=\"".$unitName."\" title=\"".$unitName."\" />
				".$unitName."
			</div>
			<div class=\"details\">
				<img class=\"r1\" src=\"gpack/travian_default/img/x.gif\" alt=\"Wood\" title=\"".LUMBER."\" />".$u['wood']."|
				<img class=\"r2\" src=\"gpack/travian_default/img/x.gif\" alt=\"Clay\" title=\"".CLAY."\" />".$u['clay']."|
				<img class=\"r3\" src=\"gpack/travian_default/img/x.gif\" alt=\"Iron\" title=\"".IRON."\" />".$u['iron']."|
				<img class=\"r4\" src=\"gpack/travian_default/img/x.gif\" alt=\"Crop\" title=\"".CROP."\" />".$u['crop']."|
				<img class=\"r5\" src=\"gpack/travian_default/img/x.gif\" alt=\"Crop consumption\" title=\"".CROP_COM."\" />6|
				<img class=\"clock\" src=\"gpack/travian_default/img/x.gif\" alt=\"Duration\" title=\"".DURATION."\" />".
				$generator->getTimeFormat($database->getArtifactsValueInfluence($session->uid, $village->wid, 5, $u['time'] / SPEED) * 3);

	$total_required = (int)($u['wood'] + $u['clay'] + $u['iron'] + $u['crop']);
	if($session->userinfo['gold'] >= 3 && $building->getTypeLevel(17) >= 1 && $village->atotal >= $total_required) {
		$output .= "|<a href=\"build.php?gid=17&t=3&r1=".$u['wood']."&r2=".$u['clay']."&r3=".$u['iron']."&r4=".$u['crop']."\" title=\"NPC trade\"><img class=\"npc\" src=\"gpack/travian_default/img/x.gif\" alt=\"NPC trade\" title=\"NPC trade\" /></a>";
	}

	$output.="</div>
		</td>
		<td class=\"val\" width=\"20%\"><center>";

	if($village->awood < $u['wood'] || $village->aclay < $u['clay'] || $village->airon < $u['iron'] || $village->acrop < $u['crop']) {
		$output.="<span class=\"none\">".NOT."".ENOUGH_RESOURCES."</span>";
	} elseif($units_array['u'.$unitId] == 0) {
		$output.="<span class=\"none\">".NOT_UNITS."</span>";
	} else {
		$output.="<a href=\"build.php?id=".$id."&train=".$unitId."\">".TRAIN."</a>";
	}

	$output.="</center></td>
	</tr>";
}

	//HERO TRAINING
	if (isset($_GET['train'])) {
		$validationArray = $heroMap[$session->tribe] ?? [];

		if (in_array($_GET['train'], $validationArray)) {
			if($count_hero < 3){
				$unitID = $_GET['train'];
				mysqli_query($database->dblink,"INSERT INTO ".TB_PREFIX."hero (`uid`, `wref`, `regeneration`, `unit`, `name`, `level`, `points`, `experience`, `dead`, `health`, `attack`, `defence`, `attackbonus`, `defencebonus`, `trainingtime`, `autoregen`, `intraining`) VALUES (".$database->escape($session->uid).", " . (int) $village->wid . ", 0, ".$unitID.", '".$database->escape($session->username)."', 0, 5, 0, 0, 100, 0, 0, 0, 0, ".round((time() + (${'u'.$unitID}['time'] / SPEED)*3)).", 50, 1)");
				mysqli_query($database->dblink,"UPDATE " . TB_PREFIX . "units SET `u$unitID` = `u$unitID` - 1 WHERE `vref` = " . (int) $village->wid);
				mysqli_query($database->dblink,"
					UPDATE " . TB_PREFIX . "vdata
						SET
							`wood` = `wood` - ".(int) ${'u'.$unitID}['wood'].",
							`clay` = `clay` - ".(int) ${'u'.$unitID}['clay'].",
							`iron` = `iron` - ".(int) ${'u'.$unitID}['iron'].",
							`crop` = `crop` - ".(int) ${'u'.$unitID}['crop']."
						WHERE
							`wref` = " . (int) $village->wid);
			}
			header("Location: build.php?id=".$id."");
			exit;
		}
	}

echo $output;
}
?>
</table>
