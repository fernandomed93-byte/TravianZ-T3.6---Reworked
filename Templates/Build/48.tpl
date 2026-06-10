<div id="build" class="gid48"><h1><?php echo BIGHOSPITAL; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f'.$id]; ?></span></h1>
<p class="build_desc">
	<a href="#" onClick="return Popup(48,4, 'gid');" class="build_logo">
		<img class="building g48" src="gpack/travian_default/img/x.gif" alt="Big Hospital" title="<?php echo BIGHOSPITAL; ?>" />
	</a>
	<?php echo BIGHOSPITAL_DESC; ?></p>
<?php
include("48_train.tpl");
include("upgrade.tpl");
?>
</div>
