<?php
if (!isset($gid)) return;
$costs = $GLOBALS['bid'.$gid][1] ?? null;
if (!$costs) return;
$hours = floor($costs['time'] / 3600);
$mins = floor(($costs['time'] % 3600) / 60);
$secs = $costs['time'] % 60;
$timeStr = sprintf("%d:%02d:%02d", $hours, $mins, $secs);
?>
<p><b>Costs</b> and <b>construction time</b> for level 1:<br />
<img class="r1" src="gpack/travian_default/img/x.gif" alt="Lumber" title="Lumber" /><?php echo $costs['wood']; ?> |
<img class="r2" src="gpack/travian_default/img/x.gif" alt="Clay" title="Clay" /><?php echo $costs['clay']; ?> |
<img class="r3" src="gpack/travian_default/img/x.gif" alt="Iron" title="Iron" /><?php echo $costs['iron']; ?> |
<img class="r4" src="gpack/travian_default/img/x.gif" alt="Crop" title="Crop" /><?php echo $costs['crop']; ?> |
<img class="r5" src="gpack/travian_default/img/x.gif" alt="Crop consumption" title="Crop consumption" /><?php echo $costs['pop']; ?> |
<span class="dur"><img class="clock" alt="duration" title="duration" src="gpack/travian_default/img/x.gif" /><?php echo $timeStr; ?></span></p>
