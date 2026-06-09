<div id="build" class="gid44"><h1><?php echo COMMANDCENTER; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f'.$id]; ?></span></h1>
<p class="build_desc">
	<a href="#" onClick="return Popup(44,4, 'gid');"
		class="build_logo"> <img
		class="building g44"
		src="gpack/travian_default/img/x.gif" alt="Command Center"
		title="<?php echo COMMANDCENTER; ?>" /> </a>
		<?php echo COMMANDCENTER_DESC; ?></p>


<?php include("44_menu.tpl"); ?>

<?php echo RESIDENCE_LOYALTY_DESC; ?> <b><?php echo floor($database->getVillageField($village->wid,'loyalty')); ?></b> <?php echo PERCENT; ?>.</div>
