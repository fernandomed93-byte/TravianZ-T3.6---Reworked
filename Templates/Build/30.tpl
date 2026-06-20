<div id="build" class="gid30"><a href="#" onClick="return Popup(30,4);" class="build_logo">
    <img class="building g30" src="gpack/travian_default/img/x.gif" alt="Great Stables" title="<?php echo GREATSTABLE; ?>" />
</a>
<h1><?php echo GREATSTABLE; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f'.$id]; ?></span></h1>
<p class="build_desc"><?php echo GREATSTABLE_DESC; ?><br /></p>

<?php if ($building->getTypeLevel(30) > 0) { ?>
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
                    include("30_train.tpl");
                ?></table>
    <p><input type="image" id="btn_train" class="dynamic_img" value="ok" name="s1" src="gpack/travian_default/img/x.gif" alt="train" /></form></p>
    <?php
    } else {
        echo "<b>".TRAINING_COMMENCE_GREATSTABLE."</b><br>\n";
    }
    $trainlist = $technology->getTrainingList(6);
    include("trainingqueue.tpl");
include("upgrade.tpl");
?>
</p></div>