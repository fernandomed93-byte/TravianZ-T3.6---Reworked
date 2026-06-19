<?php
$actionType = ($mov['attack_type'] == 3) ? ATTACK_ON : RAID_ON;

$reinfowner = $database->getVillageField($mov['from'], "owner");

if ($mov['t11'] > 0) {
    $hasTroops = false;
    for ($trop = 1; $trop < 11; $trop++) {
        if ($mov['t' . $trop] > 0) {
            $hasTroops = true;
            break;
        }
    }
    if (!$hasTroops) {
        $actionType = "Changing hero village to ";
    }
}

if ($mov['t11'] > 0 && $reinfowner == $session->uid) {
    $colspan = 11;
} else {
    $colspan = 10;
}

if ($mov['from'] != 0) {
    echo "<table class=\"troop_details\" cellpadding=\"1\" cellspacing=\"1\"><thead><tr><td class=\"role\">
        <a href=\"karte.php?d=" . $mov['from'] . "&c=" . $generator->getMapCheck($mov['from']) . "\">" . $database->getVillageField($mov['from'], "name") . "</a></td>
        <td colspan=\"$colspan\">";
    echo "<a href=\"karte.php?d=" . $mov['to'] . "&c=" . $generator->getMapCheck($mov['to']) . "\">" . $actionType . " " . $database->getVillageField($mov['to'], "name") . "</a>";
    echo "</td></tr></thead><tbody class=\"units\">";
    $tribe = $database->getUserField($database->getVillageField($mov['from'], "owner"), "tribe", 0);
    $start = ($tribe - 1) * 10 + 1;
    $end = ($tribe * 10);
    echo "<tr><th>&nbsp;</th>";
    for ($i = $start; $i <= ($end); $i++) {
        echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" title=\"" . $technology->getUnitName($i) . "\" alt=\"" . $technology->getUnitName($i) . "\" /></td>";
    }
    if ($mov['t11'] != 0 && $reinfowner == $session->uid) {
        echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit uhero\" title=\"Hero\" alt=\"Hero\" /></td>";
    }
    echo "</tr><tr><th>" . TROOPS . "</th>";
    for ($i = 1; $i <= $colspan; $i++) {
        if ($artifactsSum['totals'] == 0) {
            echo "<td class=\"none\">?</td>";
        } else {
            if ($mov['t' . $i] == 0) echo "<td class=\"none\">0</td>";
            else echo "<td>?</td>";
        }
    }
} else {
    $colspan = 10;
    echo "<table class=\"troop_details\" cellpadding=\"1\" cellspacing=\"1\"><thead><tr><td class=\"role\">
        <a>" . VILLAGE_OF_THE_ELDERS . "</a></td>
        <td colspan=\"10\">";
    echo "<a>" . VILLAGE_OF_THE_ELDERS_TROOPS . "</a>";
    echo "</td></tr></thead><tbody class=\"units\">";
    $tribe = 4;
    $start = ($tribe - 1) * 10 + 1;
    $end = ($tribe * 10);
    echo "<tr><th>&nbsp;</th>";
    for ($i = $start; $i <= ($end); $i++) {
        echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" title=\"" . $technology->getUnitName($i) . "\" alt=\"" . $technology->getUnitName($i) . "\" /></td>";
    }
    echo "</tr><tr><th>" . TROOPS . "</th>";
    for ($i = 1; $i <= 10; $i++) {
        echo "<td class=\"none\">?</td>";
    }
}
echo "</tr></tbody>";
echo '
<tbody class="infos">
    <tr>
        <th>' . ARRIVAL . '</th>
        <td colspan="' . $colspan . '">
            <div class="in small"><span id=timer' . $session->timer . '>' . $generator->getTimeFormat($mov['endtime'] - time()) . '</span> h</div>';
$datetime = $generator->procMtime($mov['endtime']);
echo "<div class=\"at small\">";
if ($mov['from'] == 0 && $datetime[0] != "today") echo "" . ON . " " . $datetime[0] . " ";
echo "" . AT . " " . $datetime[1] . "</div>
        </td>
    </tr>
</tbody>";
echo "</table>";
