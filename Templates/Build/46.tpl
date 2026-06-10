<div id="build" class="gid46"><h1><?php echo HOSPITAL; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f'.$id]; ?></span></h1>
<p class="build_desc">
	<a href="#" onClick="return Popup(46,4, 'gid');" class="build_logo">
		<img class="building g46" src="gpack/travian_default/img/x.gif" alt="Hospital" title="<?php echo HOSPITAL; ?>" />
	</a>
	<?php echo HOSPITAL_DESC; ?></p>
<?php
include("46_train.tpl");
include("upgrade.tpl");
?>
</div>
