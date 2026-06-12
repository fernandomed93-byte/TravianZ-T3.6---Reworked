<?php if (!isset($gid)) return;
$name = constant($GLOBALS['buildingNameMap'][$gid]);
$desc = constant($GLOBALS['buildingNameMap'][$gid].'_DESC');
$prereqs = $GLOBALS['buildingPrereqs'][$gid] ?? null;
?>
<h2><?php echo $name; ?></h2>
<table class="new_building" cellpadding="1" cellspacing="1">
	<tbody><tr>
		<td class="desc"><?php echo $desc; ?></td>
		<td rowspan="3" class="bimg">
			<a href="#" onClick="return Popup(<?php echo $gid; ?>,4);">
			<img class="building g<?php echo $gid; ?>" src="gpack/travian_default/img/x.gif" alt="<?php echo $name; ?>" title="<?php echo $name; ?>" /></a>
		</td>
	</tr>
	<tr>
		<td class="requ"><?php echo PREREQUISITES; ?></td>
	</tr>
	<tr>
		<td><?php
		$parts = [];
		if ($prereqs && isset($prereqs['structure'])) {
			foreach ($prereqs['structure'] as $req) {
				$bldName = constant($GLOBALS['buildingNameMap'][$req[0]]);
				$parts[] = '<a href="#" onClick="return Popup('.$req[0].',4);">'.$bldName.'</a> <span title="+'.$req[1].'">'.LEVEL.' '.$req[1].'</span>';
			}
		}
		$gates = [10 => 20, 11 => 20, 23 => 10, 36 => 20, 38 => 20, 39 => 20];
		if (isset($gates[$gid]) && $building->getTypeField($gid)) {
			$cur = $building->getTypeLevel($gid);
			$max = $gates[$gid];
			if ($cur > 0 && $cur < $max) {
				$bldName = constant($GLOBALS['buildingNameMap'][$gid]);
				$parts[] = '<a href="#" onClick="return Popup('.$gid.',4);">'.$bldName.'</a> <span title="+'.$max.'">'.LEVEL.' '.$max.'</span>';
			}
		}
		if ($prereqs && isset($prereqs['conflicts'])) {
			foreach ($prereqs['conflicts'] as $cgid) {
				$cname = constant($GLOBALS['buildingNameMap'][$cgid]);
				$parts[] = '<s><a href="manual.php?typ=4&amp;gid='.$cgid.'">'.$cname.'</a></s>';
			}
		}
		echo implode(', ', $parts);
		?></td>
	</tr></tbody>
</table>
