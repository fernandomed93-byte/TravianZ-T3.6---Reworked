<?php
?>
<table class="troop_details" cellpadding="1" cellspacing="1">
    <thead>
        <tr>
            <td class="role"><a href="karte.php?d=<?php echo $village->wid . "&c=" . $generator->getMapCheck($village->wid); ?>"><?php echo $village->vname; ?></a></td>
            <td colspan="10"><a href="karte.php?d=<?php echo $mov['to'] . "&c=" . $generator->getMapCheck($mov['to']); ?>"><?php echo FOUNDNEWVILLAGE; ?></a></td>
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
 <tr><th><?php echo TROOPS; ?></th>
            <?php
            $mov['t11'] = 0;
            for ($i = 1; $i <= 10; $i++) {
                if ($i < 10) $mov['t' . $i] = 0;
                else $mov['t10'] = 3;

                if ($mov['t' . $i] == 0) {
                    echo "<td class=\"none\">0</td>";
                } else {
                echo "<td>";
                echo $mov['t' . $i] . "</td>";
                }
            }
            ?>
           </tr></tbody>
        <tbody class="infos">
            <tr>
                <th><?php echo ARRIVAL; ?></th>
                <td colspan="10">
                <?php
                echo "<div class=\"in small\"><span id=timer$session->timer>" . $generator->getTimeFormat($mov['endtime'] - time()) . "</span> h</div>";
                    $datetime = $generator->procMtime($mov['endtime']);
                    echo "<div class=\"at small\">";
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
