<?php
if (!isset($gid)) return;
$prereqs = $GLOBALS['buildingPrereqs'][$gid] ?? null;
?>
<p><b>Prerequisites</b><br />
<?php
if ($prereqs && (isset($prereqs['structure']) || isset($prereqs['conflicts']) || isset($prereqs['tribes']) || isset($prereqs['capital']))) {
    $items = [];

    if (isset($prereqs['structure'])) {
        foreach ($prereqs['structure'] as $req) {
            $bldName = constant($GLOBALS['buildingNameMap'][$req[0]]);
            $items[] = '<a href="manual.php?typ=4&amp;gid='.$req[0].'">'.$bldName.'</a> Level '.$req[1];
        }
    }

    if (isset($prereqs['conflicts'])) {
        foreach ($prereqs['conflicts'] as $cgid) {
            $bldName = constant($GLOBALS['buildingNameMap'][$cgid]);
            $items[] = '<strike><a href="manual.php?typ=4&amp;gid='.$cgid.'">'.$bldName.'</a></strike>';
        }
    }

    if (isset($prereqs['tribes'])) {
        if ($prereqs['tribes'] === 0) {
            // all tribes - no restriction
        } elseif (is_string($prereqs['tribes']) && strpos($prereqs['tribes'], ',') !== false) {
            $tids = explode(',', $prereqs['tribes']);
            $tnames = [];
            foreach ($tids as $tid) {
                $tConst = 'TRIBE'.trim($tid);
                $tnames[] = defined($tConst) ? constant($tConst) : 'Tribe '.trim($tid);
            }
            $items[] = implode(' or ', $tnames).' only';
        } else {
            $tConst = 'TRIBE'.$prereqs['tribes'];
            $items[] = (defined($tConst) ? constant($tConst) : 'Tribe '.$prereqs['tribes']).' only';
        }
    }

    if (isset($prereqs['capital'])) {
        if ($prereqs['capital'] == 1) {
            $items[] = '(capital only)';
        } elseif ($prereqs['capital'] == -1) {
            $items[] = '(cannot be built in capital)';
        }
    }

    echo implode(', ', $items);
} else {
    echo 'none';
}
?>
</p>
