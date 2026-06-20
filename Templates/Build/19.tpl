<div id="build" class="gid19"><a href="#" onClick="return Popup(19,4);" class="build_logo">
	<img class="building g19" src="gpack/travian_default/img/x.gif" alt="Barracks" title="<?php echo BARRACKS; ?>" />
</a>
<h1><?php echo BARRACKS; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f'.$id]; ?></span></h1>
<p class="build_desc"><?php echo BARRACKS_DESC; ?> </p>

<?php if ($building->getTypeLevel(19) > 0) { ?>
<form method="POST" name="snd" action="build.php">
				<input type="hidden" name="id" value="<?php echo $id; ?>" />
				<input type="hidden" name="ft" value="t1" />
				<table cellpadding="1" cellspacing="1" class="build_details">
				<thead><tr>
					<td><?php echo NAME; ?></td>
					<td><?php echo QUANTITY; ?></td>
					<td><?php echo MAX; ?></td>
				</tr></thead><tbody>
                <?php
	                include("19_train.tpl");
                ?></table>
	<p><button id="btn_train" class="trav_buttons" value="ok" name="s1" alt="train" onclick="this.disabled=true;this.form.submit();"/> Train </button></form></p>
    <?php
	} else {
		echo "<b>".TRAINING_COMMENCE_BARRACKS."</b><br>\n";
	}
    $trainlist = $technology->getTrainingList(1);
    include("trainingqueue.tpl");
include("upgrade.tpl");
?>
</p></div>