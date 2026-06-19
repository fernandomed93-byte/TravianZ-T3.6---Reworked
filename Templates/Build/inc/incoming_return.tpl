<?php
$actionType = RETURNFROM;

$isoasis = $database->isVillageOases($mov['from']);
if ($isoasis == 0) {
    $from = $database->getMInfo($mov['from']);
} else {
    $from = $database->getOMInfo($mov['from']);
}

?>
<table class="troop_details" cellpadding="1" cellspacing="1">
    <thead>
        <tr>
            <td class="role"><a
                href="karte.php?d=<?php echo $village->wid . "&c=" . $generator->getMapCheck($village->wid); ?>"><?php echo $village->vname; ?></a></td>
            <td colspan="<?php echo $mov['t11'] > 0 ? 11 : 10 ?>"><a
                href="karte.php?d=<?php echo $from['wref'] . "&c=" . $generator->getMapCheck($from['wref']); ?>"><?php echo $actionType . " " . $from['name']; ?></a></td>
        </tr>
    </thead>
    <tbody class="units">
        <tr>
            <?php
        $tribe = $session->tribe;
        $start = ($tribe - 1) * 10 + 1;
        $end = ($tribe * 10);
        echo "<th>&nbsp;</th>";
        for ($i = $start; $i <= ($end); $i++) {
            echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" title=\"" . $technology->getUnitName($i) . "\" alt=\"" . $technology->getUnitName($i) . "\" /></td>";
        }
        if ($mov['t11'] != 0) {
            echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit uhero\" title=\"Hero\" alt=\"Hero\" /></td>";
        }
        ?>
            </tr>
        <tr>
            <th><?php echo TROOPS; ?></th>
            <?php
        for ($i = 1; $i < ($mov['t11'] != 0 ? 12 : 11); $i++) {
            if ($mov['t' . $i] == 0) echo "<td class=\"none\">0</td>";
            else {
                echo "<td>";
                echo $mov['t' . $i] . "</td>";
            }
        }
        ?>
           </tr>
    </tbody>
            <?php
        $totalres = $mov['wood'] + $mov['clay'] + $mov['iron'] + $mov['crop'];
        if ($mov['attack_type'] != 2 && $mov['attack_type'] != 1 && $totalres > 0) {
            ?>
            <tbody class="goods">
        <tr>
            <th><?php echo BOUNTY; ?></th>
            <td colspan="<?php echo $mov['t11'] == 0 ? 10 : 11; ?>">
            <?php
            $totalcarry = 0;
            for ($i = 0; $i <= 9; $i++) $totalcarry += $mov['t' . ($i + 1)] * ${'u' . ($start + $i)}['cap'];
            echo "<div class=\"res\"><img class=\"r1\" src=\"gpack/travian_default/img/x.gif\" alt=\"Lumber\" title=\"Lumber\" />" . $mov['wood'] . " | <img class=\"r2\" src=\"gpack/travian_default/img/x.gif\" alt=\"Clay\" title=\"Clay\" />" . $mov['clay'] . " | <img class=\"r3\" src=\"gpack/travian_default/img/x.gif\" alt=\"Iron\" title=\"Iron\" />" . $mov['iron'] . " | <img class=\"r4\" src=\"gpack/travian_default/img/x.gif\" alt=\"Crop\" title=\"Crop\" />" . $mov['crop'] . "</div>";
            echo "<div class=\"carry\"><img class=\"car\" src=\"gpack/travian_default/img/x.gif\" alt=\"carry\" title=\"carry\"/>" . $totalres . "/" . $totalcarry . "</div>";
            ?>
        </tr>
    </tbody>
           <?php } ?>

    <tbody class="infos">
        <tr>
            <th><?php echo ARRIVAL; ?></th>
            <td colspan="<?php echo $mov['t11'] == 0 ? 10 : 11 ?>">
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
