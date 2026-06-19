<?php
if ($mov['attack_type'] == 2) {
    if ($mov['t11'] > 0) {
        $hasTroops = false;
        for ($trop = 1; $trop < 11; $trop++) {
            if ($mov['t' . $trop] > 0) {
                $hasTroops = true;
                break;
            }
        }
        if (!$hasTroops) {
            $attack_type = "Changing hero village to ";
        } else {
            $attack_type = REINFORCEMENTFOR;
        }
    } else {
        $attack_type = REINFORCEMENTFOR;
    }
}
if ($mov['attack_type'] == 1) {
    $attack_type = SCOUTING;
}
if ($mov['attack_type'] == 3) {
    $attack_type = ATTACK_ON;
}
if ($mov['attack_type'] == 4) {
    $attack_type = RAID_ON;
}
$isoasis = $database->isVillageOases($mov['to']);
if ($isoasis == 0) {
    $to = $database->getMInfo($mov['to']);
} else {
    $to = $database->getOMInfo($mov['to']);
}
?>
<table class="troop_details" cellpadding="1" cellspacing="1">
    <thead>
        <tr>
            <td class="role"><a href="karte.php?d=<?php echo $village->wid . "&c=" . $generator->getMapCheck($village->wid); ?>"><?php echo $village->vname; ?></a></td>
            <td colspan="<?php if ($mov['t11'] == 0) { echo "10"; } else { echo "11"; } ?>"><a href="karte.php?d=<?php echo $to['wref'] . "&c=" . $generator->getMapCheck($to['wref']); ?>"><?php echo $attack_type . " " . $to['name']; ?></a></td>
        </tr>
    </thead>
    <tbody class="units">
            <?php
                  echo "<tr><th>&nbsp;</th>";
                  for ($i = ($session->tribe - 1) * 10 + 1; $i <= $session->tribe * 10; $i++) {
                      echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" title=\"" . $technology->getUnitName($i) . "\" alt=\"" . $technology->getUnitName($i) . "\" /></td>";
                  }
                  if ($mov['t11'] != 0) {
                   echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit uhero\" title=\"Hero\" alt=\"Hero\" /></td>";
                  }
            ?>
            </tr>
 <tr><th><?php echo TROOPS; ?></th>
            <?php
            if ($mov['t11'] != 0) {
                $end = 12;
            } else {
                $end = 11;
            }
            for ($i = 1; $i < $end; $i++) {
                if ($mov['t' . $i] == 0) {
                    echo "<td class=\"none\">";
                } else {
                echo "<td>";
                }
                echo $mov['t' . $i] . "</td>";
            }
            ?>
           </tr></tbody>
        <?php if (NEW_FUNCTIONS_DISPLAY_CATAPULT_TARGET) {
            if ($mov['t8'] > 0 && $mov['attack_type'] == 3 && !$database->isVillageOases($mov['to'])) { ?>
        <tbody>
            <tr>
                <th><?php echo CATAPULT_TARGET; ?></th>
                <td style="text-align: center" colspan="5">
                    <?php echo $mov['ctar1'] == 0 ? "Random" : Building::procResType($mov['ctar1']); ?>
                </td>
                <td style="text-align: center" colspan="<?php if ($mov['t11'] == 0) { echo "5"; } else { echo "6"; } ?>">
                    <?php echo $mov['ctar2'] == 99 ? "Random" : ($mov['ctar2'] == 0 ? "-" : Building::procResType($mov['ctar2'])); ?>
                </td>
            </tr>
        </tbody>
            <?php }
        } ?>
        <tbody class="infos">
            <tr>
                <th><?php echo ARRIVAL; ?></th>
                <td colspan="<?php if ($mov['t11'] == 0) { echo "10"; } else { echo "11"; } ?>">
                <?php
                echo "<div class=\"in small\"><span id=timer$session->timer>" . $generator->getTimeFormat($mov['endtime'] - time()) . "</span> h</div>";
                    $datetime = $generator->procMtime($mov['endtime']);
                    echo "<div class=\"at\">";
                    if ($datetime[0] != "today") {
                    echo "" . ON . " " . $datetime[0] . " ";
                    }
                    echo "" . AT . " " . $datetime[1] . "</div>";
                    if (($mov['starttime'] + 90) > time()) {
                ?>
                    <div class="abort"><a href="build.php?id=<?php echo $_GET['id'] . "&mode=troops&cancel=1&moveid=" . $mov['moveid']; ?>"><img src="gpack/travian_default/img/x.gif" class="del" /></a></div>
                    <?php } ?>
                    </div>
                </td>
            </tr>
        </tbody>
</table>
