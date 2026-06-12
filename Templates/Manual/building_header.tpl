<?php
if (!isset($gid)) return;
$constName = $GLOBALS['buildingNameMap'][$gid] ?? '';
if (!$constName) return;
$name = defined($constName) ? constant($constName) : 'Building';
$desc = defined($constName.'_DESC') ? constant($constName.'_DESC') : '';
?>
<h1><img class="unit ugeb" src="gpack/travian_default/img/x.gif"> <?php echo $name; ?></h1>
<img class="building g<?php echo $gid; ?>" src="gpack/travian_default/img/x.gif" alt="<?php echo $name; ?>" title="<?php echo $name; ?>" />
<?php echo $desc; ?>
