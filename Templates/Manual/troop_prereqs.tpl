<?php
if (!isset($id)) return;
?>
<div id="prereqs"><b>Prerequisites</b><br />
<?php
if (isset($GLOBALS['unitRequirements'][$id]) && !empty($GLOBALS['unitRequirements'][$id])) {
    $reqs = $GLOBALS['unitRequirements'][$id];
    $reqLinks = [];
    foreach ($reqs as $gid => $level) {
        $bldConst = $GLOBALS['buildingNames'][$gid] ?? '';
        $bldName = ($bldConst && defined($bldConst)) ? constant($bldConst) : 'Building';
        $reqLinks[] = '<a href="manual.php?typ=4&amp;gid='.$gid.'">'.$bldName.'</a> Level '.$level;
    }
    echo implode(', ', $reqLinks);
} else {
    echo NOPREREQUISITES;
}
?>
</div>
