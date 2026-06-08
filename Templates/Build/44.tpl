<div id="build" class="gid44"><a href="#" onClick="return Popup(44,4);" class="build_logo">
	<img class="building g44" src="gpack/travian_default/img/x.gif" alt="Command Center" title="<?php echo COMMANDCENTER; ?>" />
</a>
<h1><?php echo COMMANDCENTER; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f'.$id]; ?></span></h1>
<p class="build_desc"><?php echo COMMANDCENTER_DESC; ?></p>
	<table cellpadding="1" cellspacing="1" id="build_value">
		<tr>
			<th><?php echo CURRENT_BONUS; ?></th>
			<td><b><?php echo $village->resarray['f'.$id] > 0 ? $bid44[$village->resarray['f'.$id]]['attri'] : 0; ?></b></td>
		</tr>
		<tr>
		<?php 
        if(!$building->isMax($village->resarray['f'.$id.'t'],$id)) {
			$next = $village->resarray['f'.$id] + 1 + $loopsame + $doublebuild + $master;
		if($next <= 20){
        ?>
			<th><?php echo BONUS_LEVEL; ?> <?php echo $next; ?>:</th>
			<td><b><?php echo $bid44[$next]['attri']; ?></b></td>
            <?php
            }else{
        ?>
			<th><?php echo BONUS_LEVEL; ?> 20:</th>
			<td><b><?php echo $bid44[20]['attri']; ?></b></td>
            <?php
			}}
            ?>
		</tr>
	</table>
<?php 
include("upgrade.tpl");
?>
</p></div>
