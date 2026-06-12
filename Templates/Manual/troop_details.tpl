<?php
if (!isset($id)) return;
$u = ${'u'.$id};
$name = constant('U'.$id);
$hours = floor($u['time'] / 3600);
$minutes = floor(($u['time'] % 3600) / 60);
$secs = $u['time'] % 60;
$timeStr = sprintf("%d:%02d:%02d", $hours, $minutes, $secs);
?>
<table id="troop_details" cellpadding="1" cellspacing="1">
<tbody><tr>
	<th>Velocity</th>
	<td><b><?php echo $u['speed']; ?></b> fields/hour</td>
</tr>
<tr>
	<th>Can carry</th>
	<td><b><?php echo $u['cap']; ?></b> resources</td>
</tr>
<tr>
	<th>Upkeep</th>
	<td><img class="r5" src="gpack/travian_default/img/x.gif" alt="Crop consumption" title="Crop consumption" /> <?php echo $u['pop']; ?></td>
</tr>
<tr>
	<th>Duration of training</th>
	<td><img class="clock" src="gpack/travian_default/img/x.gif" alt="duration" title="duration" /> <?php echo $timeStr; ?></td>
</tr></tbody>
</table>

<img id="big_unit" class="big_u<?php echo $id; ?>" src="gpack/travian_default/img/x.gif" alt="<?php echo $name; ?>" title="<?php echo $name; ?>" /><div id="t_desc"><?php echo constant('U'.$id.'DESC'); ?></div>
