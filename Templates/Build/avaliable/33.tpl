<h2><?php echo PALISADE ?></h2>

<table class="new_building" cellpadding="1" cellspacing="1">
	<tbody><tr>
				<td class="desc"><?php echo PALISADE_DESC ?></td>
				<td rowspan="3" class="bimg">
				<a href="#" onClick="return Popup(33,4);">
				<img class="building g33" src="gpack/travian_default/img/x.gif" alt="Palisade" title="Palisade" /></a>
			</td>
	</tr>
	<tr>
		<?php
        $_GET['bid'] = 33;
        include("availupgrade.tpl");
        ?>
	</tr>
</table>
