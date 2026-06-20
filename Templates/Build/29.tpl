<div id="build" class="gid29"><a href="#" onClick="return Popup(29,4);" class="build_logo">
    <img class="building g29" src="gpack/travian_default/img/x.gif" alt="Great Barracks" title="<?php echo GREATBARRACKS; ?>" />
</a>
<h1><?php echo GREATBARRACKS; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f'.$id]; ?></span></h1>
<p class="build_desc"><?php echo GREATBARRACKS_DESC; ?></p>

<?php if ($building->getTypeLevel(29) > 0) { ?>
<form method="POST" name="snd" action="build.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>" />
                <input type="hidden" name="ft" value="t3" />
                <table cellpadding="1" cellspacing="1" class="build_details">
                <thead><tr>
					<td><?php echo NAME; ?></td>
					<td><?php echo QUANTITY; ?></td>
					<td><?php echo MAX; ?></td>
                </tr></thead><tbody>
                <?php
                    include("29_train.tpl");
                ?></table>
    <p><input type="image" id="btn_train" class="dynamic_img" value="ok" name="s1" src="gpack/travian_default/img/x.gif" alt="train" /></form></p>
    <?php
    } else {
        echo "<b>".TRAINING_COMMENCE_GREATBARRACKS."</b><br>\n";
    }
    $trainlist = $technology->getTrainingList(5);
    include("trainingqueue.tpl");
include("upgrade.tpl");
?>
</p></div>