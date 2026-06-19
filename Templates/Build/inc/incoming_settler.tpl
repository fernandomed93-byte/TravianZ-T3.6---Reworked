<?php
$to = $database->getMInfo($mov['to']);
$actionType = RETURNTO;
?>
<table class="troop_details" cellpadding="1" cellspacing="1">
    <thead>
        <tr>
            <td class="role"><a
                href="karte.php?d=<?php echo $village->wid . "&c=" . $generator->getMapCheck($village->wid); ?>"><?php echo $village->vname; ?></a></td>
            <td colspan="10"><a
                href="karte.php?d=<?php echo $to['wref'] . "&c=" . $generator->getMapCheck($to['wref']); ?>"><?php echo $actionType . " " . $to['name']; ?></a></td>
        </tr>
    </thead>
    <tbody class="units">
            <?php
    $tribe = $session->tribe;
    $start = ($tribe - 1) * 10 + 1;
    $end = ($tribe * 10);
    echo "<tr><th>&nbsp;</th>";
    for ($i = $start; $i <= ($end); $i++) {
        echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" title=\"" . $technology->getUnitName($i) . "\" alt=\"" . $technology->getUnitName($i) . "\" /></td>";
    }
    ?>
            </tr>
        <tr>
            <th><?php echo TROOPS; ?></th>
            <?php
    for ($z = 1; $z <= 9; $z++) $mov['t' . $z] = 0;
    $mov['t10'] = 3;

    for ($i = 1; $i < 11; $i++) {
        if ($mov['t' . $i] == 0) echo "<td class=\"none\">0</td>";
        else {
            echo "<td>";
            echo $mov['t' . $i] . "</td>";
        }
    }
    ?>
           </tr>


    <tbody class="infos">
        <tr>
            <th><?php echo ARRIVAL; ?></th>
            <td colspan="10">
                <?php
    echo "<div class=\"in small\"><span id=timer" . $session->timer . ">" . $generator->getTimeFormat($mov['endtime'] - time()) . "</span> h</div>";
    $datetime = $generator->procMtime($mov['endtime']);
    echo "<div class=\"at\">";
    if ($datetime[0] != "today") echo "" . ON . " " . $datetime[0] . " ";
    echo "" . AT . " " . $datetime[1] . "</div>";
    ?>
                </div>
            </td>
        </tr>
    </tbody>
</table>
