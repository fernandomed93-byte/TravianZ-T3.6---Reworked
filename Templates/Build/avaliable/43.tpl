<h2><?php echo MAKESHIFTWALL ?></h2>

<table class="new_building" cellpadding="1" cellspacing="1">
	<tbody><tr>
			<td class="desc"><?php echo MAKESHIFTWALL_DESC ?></td>
				<td rowspan="3" class="bimg">
				<a href="#" onClick="return Popup(43,4);">
				<img class="building g43" src="gpack/travian_default/img/x.gif" alt="Makeshift Wall" title="Makeshift Wall" /></a>
			</td>
	</tr>
	<tr>
		<?php
        $_GET['bid'] = 43;
        include("availupgrade.tpl");
        ?>
	</tr>
</table>
