<?php
if (!isset($id)) return;
$tribeId = (int)(($id - 1) / 10) + 1;
$firstInTribe = ($id % 10 == 1);
$lastInTribe = ($id % 10 == 0);
?>
<map id="nav" name="nav">
    <area href="manual.php?<?php echo $firstInTribe ? 'typ=2&amp;s='.$tribeId : 'typ=1&amp;s='.($id-1); ?>" title="back" coords="0,0,45,18" shape="rect" alt="" />
    <area href="manual.php?typ=2&amp;s=<?php echo $tribeId; ?>" title="Overview" coords="46,0,70,18" shape="rect" alt="" />
    <area href="manual.php?<?php echo $lastInTribe ? 'typ=2&amp;s='.$tribeId : 'typ=1&amp;s='.($id+1); ?>" title="forward" coords="71,0,116,18" shape="rect" alt="" />
</map>
<img usemap="#nav" src="gpack/travian_default/img/x.gif" class="navi" alt="" />
