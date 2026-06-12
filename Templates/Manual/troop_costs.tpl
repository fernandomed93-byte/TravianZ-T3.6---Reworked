<?php
if (!isset($id)) return;
$u = ${'u'.$id};
?>
<table id="troop_info" cellpadding="1" cellspacing="1">
<thead><tr>
	<th><img class="att_all" src="gpack/travian_default/img/x.gif" alt="attack value" title="attack value" /></th>
	<th><img class="def_i" src="gpack/travian_default/img/x.gif" alt="defence against infantry" title="defence against infantry" /></th>
	<th><img class="def_c" src="gpack/travian_default/img/x.gif" alt="defence against cavalry" title="defence against cavalry" /></th>
	<th><img class="r1" src="gpack/travian_default/img/x.gif" alt="Lumber" title="Lumber" /></th>
	<th><img class="r2" src="gpack/travian_default/img/x.gif" alt="Clay" title="Clay" /></th>
	<th><img class="r3" src="gpack/travian_default/img/x.gif" alt="Iron" title="Iron" /></th>
	<th><img class="r4" src="gpack/travian_default/img/x.gif" alt="Crop" title="Crop" /></th>
</tr></thead>
<tbody><tr>
	<td><?php echo ($u['atk'] > 0) ? $u['atk'] : '&mdash;'; ?></td>
	<td><?php echo $u['di']; ?></td>
	<td><?php echo $u['dc']; ?></td>
	<td><?php echo $u['wood']; ?></td>
	<td><?php echo $u['clay']; ?></td>
	<td><?php echo $u['iron']; ?></td>
	<td><?php echo $u['crop']; ?></td>
</tr></tbody>
</table>
