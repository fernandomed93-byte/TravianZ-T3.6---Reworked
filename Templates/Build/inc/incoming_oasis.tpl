<?php
$to = $database->getOMInfo($mov['to']);
if ($mov['attack_type'] == 2) $actionType = REINFORCEMENTFOR;
else if ($mov['attack_type'] == 3) $actionType = ATTACK_ON;
else if ($mov['attack_type'] == 4) $actionType = RAID_ON;

$reinfowner = $database->getVillageField($mov['from'], "owner");
if ($mov['t11'] != 0 && $reinfowner == $session->uid) {
    $colspan = 11;
} else {
    $colspan = 10;
}

echo "<table class=\"troop_details\" cellpadding=\"1\" cellspacing=\"1\"><thead><tr><td class=\"role\">
    <a href=\"karte.php?d=" . $mov['from'] . "&c=" . $generator->getMapCheck($mov['from']) . "\">" . $database->getVillageField($mov['from'], "name") . "</a></td>
    <td colspan=\"$colspan\">";
echo "<a href=\"karte.php?d=" . $mov['to'] . "&c=" . $generator->getMapCheck($mov['to']) . "\">" . $actionType . " " . $to['name'] . "</a>";
echo "</td></tr></thead><tbody class=\"units\">";
$tribe = $database->getUserField($reinfowner, "tribe", 0);
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
    $totalunits = $mov['t1'] + $mov['t2'] + $mov['t3'] + $mov['t4'] + $mov['t5'] + $mov['t6'] + $mov['t7'] + $mov['t8'] + $mov['t9'] + $mov['t10'] + $mov['t11'];
    if ($mov['attack_type'] == 2) {
        if ($reinfowner != $session->uid) echo "<td class=\"none\">?</td>";
        else {
            if ($mov['t' . $i] == 0) echo "<td class=\"none\">0</td>";
            else {
                echo "<td>";
                echo $mov['t' . $i] . "</td>";
            }
        }
    } else {
        if ($artifactsSum['totals'] == 0) echo "<td class=\"none\">?</td>";
        else {
            if ($mov['t' . $i] == 0) echo "<td class=\"none\">0</td>";
            else echo "<td>?</td>";
        }
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
echo "<div class=\"at\">";
if ($datetime[0] != "today") echo "" . ON . " " . $datetime[0] . " ";
echo "" . AT . " " . $datetime[1] . " " . HRS . "</div>
        </div>
        </td>
    </tr>
</tbody>";
echo "</table>";
