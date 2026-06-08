<div id="build" class="gid46"><a href="#" onClick="return Popup(46,4);" class="build_logo">
	<img class="building g46" src="gpack/travian_default/img/x.gif" alt="Hospital" title="<?php echo HOSPITAL; ?>" />
</a>
<h1><?php echo HOSPITAL; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f'.$id]; ?></span></h1>
<p class="build_desc"><?php echo HOSPITAL_DESC; ?></p>
	<table cellpadding="1" cellspacing="1" id="build_value">
		<tr>
			<th><?php echo CURRENT_BONUS; ?></th>
			<td><b><?php echo $village->resarray['f'.$id] > 0 ? $bid46[$village->resarray['f'.$id]]['attri'] * 100 : 0; ?></b> <?php echo PERCENT; ?></td>
		</tr>
		<tr>
		<?php 
        if(!$building->isMax($village->resarray['f'.$id.'t'],$id)) {
			$next = $village->resarray['f'.$id] + 1 + $loopsame + $doublebuild + $master;
		if($next <= 20){
        ?>
			<th><?php echo BONUS_LEVEL; ?> <?php echo $next; ?>:</th>
			<td><b><?php echo $bid46[$next]['attri'] * 100; ?></b> <?php echo PERCENT; ?></td>
            <?php
            }else{
        ?>
			<th><?php echo BONUS_LEVEL; ?> 20:</th>
			<td><b><?php echo $bid46[20]['attri'] * 100; ?></b> <?php echo PERCENT; ?></td>
            <?php
			}}
            ?>
		</tr>
	</table>
<?php 
include("upgrade.tpl");
?>
</p></div>
