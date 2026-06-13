<?php
include("next.tpl");
?>
<div id="build" class="gid42"><a href="#" onClick="return Popup(42,4);" class="build_logo">
    <img class="building g42" src="gpack/travian_default/img/x.gif" alt="Stone Wall" title="<?php echo STONEWALL; ?>" />
</a>
<h1><?php echo STONEWALL; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f'.$id]; ?></span></h1>
<p class="build_desc"><?php echo STONEWALL_DESC; ?></p>

<table cellpadding="1" cellspacing="1" id="build_value">
		<tr>
			<th><?php echo DEFENCE_NOW; ?></th>
			<td><b><?php echo $village->resarray['f'.$id] > 0 ? $bid42[$village->resarray['f'.$id]]['attri'] : 0; ?></b> <?php echo PERCENT; ?></td>
		</tr><tr>
        <?php 
        if(!$building->isMax($village->resarray['f'.$id.'t'],$id)) {
			$next = $village->resarray['f'.$id] + 1 + $loopsame + $doublebuild + $master;
		if($next <= 20){
        ?>
			<th><?php echo DEFENCE_LEVEL; ?> <?php echo $next; ?>:</th>

			<td><b><?php echo $bid42[$next]['attri']; ?></b> <?php echo PERCENT; ?></td>
            <?php
            }else{
		?>
		<th><?php echo DEFENCE_LEVEL; ?> 20:</th>
		<td><b><?php echo $bid42[20]['attri']; ?></b> <?php echo PERCENT; ?></td>
		<?php
			}
			}
            ?>
		</tr></table>
<?php 
include("upgrade.tpl");
?>
        </p>
         </div>
