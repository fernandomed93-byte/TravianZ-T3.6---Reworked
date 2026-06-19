<?php
$gid = 46;
$bidName = 'bid46';

$woundData = $database->getWoundedData($village->wid);

$hasWounded = false;
$rows = [];
$hospitalLevel = $building->getTypeLevel($gid);
if ($hospitalLevel > 0) {
	global $$bidName;
	for ($pos = 1; $pos <= 6; $pos++) {
		$wounded = $woundData['wounded'][$pos];
		if ($wounded <= 0) continue;
		$globalId = ($session->tribe - 1) * 10 + $pos;
		$hasWounded = true;
		$rows[] = ['pos' => $pos, 'wounded' => $wounded, 'inQueue' => $woundData['inQueue'][$pos], 'globalId' => $globalId];
	}
}

$healList = $database->query("SELECT * FROM ".TB_PREFIX."training WHERE vref = ".(int)$village->wid." AND unit > 2000 ORDER BY timestamp ASC");
?>

<?php if ($hasWounded) { ?>
<form method="POST" name="snd" action="build.php">
<input type="hidden" name="id" value="<?php echo $id; ?>" />
<input type="hidden" name="ft" value="t4" />

<table cellpadding="1" cellspacing="1" class="build_details">
<thead>
<tr>
	<td><?php echo WOUNDED; ?></td>
	<td><?php echo QUANTITY; ?></td>
	<td><?php echo MAX; ?></td>
</tr>
</thead>
<tbody>
<?php
foreach ($rows as $row) {
	$u = ${'u'.$row['globalId']};
	$unitName = $technology->getUnitName($row['globalId']);
	$available = $row['wounded'];
	$maxCanHeal = min($available, $technology->maxUnit($row['globalId']));

	echo "<tr><td class=\"desc\">
	<div class=\"tit\">
		<img class=\"unit u".$row['globalId']."\" src=\"gpack/travian_default/img/x.gif\" alt=\"".$unitName."\" title=\"".$unitName."\" />
		<a href=\"#\" onClick=\"return Popup(".$row['globalId'].",1);\"> ".$unitName."</a>
		<span class=\"info\">(".AVAILABLE.": ".$village->unitarray['u'.$row['globalId']]." | ".WOUNDED.": ".$row['wounded'].")</span>
	</div>
	<div class=\"details\">
		<img class=\"r1\" src=\"gpack/travian_default/img/x.gif\" alt=\"Wood\" title=\"".LUMBER."\" />".$u['wood']."|
		<img class=\"r2\" src=\"gpack/travian_default/img/x.gif\" alt=\"Clay\" title=\"".CLAY."\" />".$u['clay']."|
		<img class=\"r3\" src=\"gpack/travian_default/img/x.gif\" alt=\"Iron\" title=\"".IRON."\" />".$u['iron']."|
		<img class=\"r4\" src=\"gpack/travian_default/img/x.gif\" alt=\"Crop\" title=\"".CROP."\" />".$u['crop']."|
		<img class=\"r5\" src=\"gpack/travian_default/img/x.gif\" alt=\"Crop consumption\" title=\"".CROP_COM."\" />".$u['pop']."|
		<img class=\"clock\" src=\"gpack/travian_default/img/x.gif\" alt=\"Duration\" title=\"".DURATION."\" />";
	$dur = $database->getArtifactsValueInfluence($session->uid, $village->wid, 5, round((${$bidName}[$hospitalLevel]['attri'] / 100) * $u['time'] / 2 / SPEED));
	echo $generator->getTimeFormat($dur);

	$total_required = (int)($u['wood'] + $u['clay'] + $u['iron'] + $u['crop']);
	if($session->userinfo['gold'] >= 3 && $building->getTypeLevel(17) >= 1 && $village->atotal >= $total_required) {
		echo "|<a href=\"build.php?gid=17&t=3&r1=".(($u['wood'])*$technology->maxUnitPlus($row['globalId']))."&r2=".(($u['clay'])*$technology->maxUnitPlus($row['globalId']))."&r3=".(($u['iron'])*$technology->maxUnitPlus($row['globalId']))."&r4=".(($u['crop'])*$technology->maxUnitPlus($row['globalId']))."\" title=\"NPC trade\"><img class=\"npc\" src=\"gpack/travian_default/img/x.gif\" alt=\"NPC trade\" title=\"NPC trade\" /></a>";
	}

	echo "</div>
	</td>
	<td class=\"val\"><input type=\"text\" class=\"text\" name=\"t".$row['pos']."\" value=\"0\" maxlength=\"10\"></td>
	<td class=\"max\"><a href=\"#\" onClick=\"document.snd.t".$row['pos'].".value=".$maxCanHeal."; return false;\">(".$maxCanHeal.")</a></td></tr>";
}
?>
</tbody>
</table>
<p>
<input type="image" id="btn_train" class="dynamic_img" value="ok" name="s1" src="gpack/travian_default/img/x.gif" alt="<?php echo HEAL; ?>" />
</p>
</form>
<?php } else { ?>
<div class="c"><?php echo NO_WOUNDED; ?></div>
<?php } ?>

<?php if (mysqli_num_rows($healList) > 0) { ?>
<table cellpadding="1" cellspacing="1" class="under_progress">
<thead><tr>
	<td><?php echo HEALING_IN_PROGRESS; ?></td>
	<td><?php echo DURATION; ?></td>
	<td><?php echo FINISHED; ?></td>
</tr></thead>
<tbody>
<?php
$counter = 0;
while ($heal = mysqli_fetch_assoc($healList)) {
	$counter++;
	$realUnit = $heal['unit'] - 2000;
	$unitName = $technology->getUnitName($realUnit);
	echo "<tr><td class=\"desc\">
		<img class=\"unit u".$realUnit."\" src=\"gpack/travian_default/img/x.gif\" alt=\"".$unitName."\" title=\"".$unitName."\" />
		".$heal['amt']." ".$unitName."</td>
		<td class=\"dur\">";
	if ($counter == 1) {
		$NextFinished = $generator->getTimeFormat($heal['timestamp2'] - time());
		echo "<span id=timer".++$session->timer.">".$generator->getTimeFormat($heal['timestamp'] - time())."</span>";
	} else {
		echo $generator->getTimeFormat($heal['eachtime'] * $heal['amt']);
	}
	echo "</td>
		<td class=\"fin\">";
	$time = $generator->procMtime($heal['timestamp']);
	if ($time[0] != "today") echo "on ".$time[0]." at ";
	echo $time[1];
	echo "</td></tr>";
}
?><tr class="next"><td colspan="3"><?php echo UNIT_FINISHED; ?> <span id="timer<?php echo ++$session->timer?>"><?php echo $NextFinished; ?></span></td></tr>
</tbody></table>
<?php } ?>
