<div id="build" class="gid20"><a href="#" onClick="return Popup(20,4);" class="build_logo">
<img class="building g20" src="gpack/travian_default/img/x.gif" alt="Stable" title="<?php echo STABLE; ?>" /> </a>

<h1><?php echo STABLE; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f'.$id]; ?></span></h1>
<p class="build_desc"><?php echo STABLE_DESC; ?><br /></p>

<?php if ($building->getTypeLevel(20) > 0) { ?>

		<form method="POST" name="snd" action="build.php">
			<input type="hidden" name="id" value="<?php echo $id; ?>" />
            <input type="hidden" name="ft" value="t1" />
			<table cellpadding="1" cellspacing="1" class="build_details">
				<thead>
					<tr>
						<td><?php echo NAME; ?></td>
						<td><?php echo QUANTITY; ?></td>
						<td><?php echo MAX; ?></td>
					</tr>
				</thead>
				<tbody>
                <?php
				if($session->tribe != 4){
                    include("20_train.tpl");
				}
                ?>
				</tbody>
			</table>
			<p>
				<input type="image" id="btn_train" class="dynamic_img" value="ok" name="s1" src="gpack/travian_default/img/x.gif" alt="train" />
			</p>

		</form>
<?php
	} else {
		echo "<b>".TRAINING_COMMENCE_STABLE."</b><br>\n";
	}
    $trainlist = $technology->getTrainingList(2);
    include("trainingqueue.tpl");
    ?>
	<?php 
include("upgrade.tpl");
?> </p></div>
