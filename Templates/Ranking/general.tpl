<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       general.tpl                                                 ##
##  Developed by:  Dzoki                                                       ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2011. All rights reserved.                ##
##  Enhanced:      saulyzas                                                    ##
##  Optimized:     Consolidated 20+ queries into 4 (2024)                      ##
#################################################################################

// Query 1: All tribe counts in a single GROUP BY (replaces 7 separate queries)
$tribeCounts = [];
$result = mysqli_query($database->dblink, "SELECT tribe, COUNT(*) as Total FROM ".TB_PREFIX."users WHERE tribe IN (1,2,3,6,7,8,9) GROUP BY tribe");
while ($row = mysqli_fetch_assoc($result)) {
    $tribeCounts[(int)$row['tribe']] = (int)$row['Total'];
}
$tribes = [
    $tribeCounts[1] ?? 0,
    $tribeCounts[2] ?? 0,
    $tribeCounts[3] ?? 0,
    $tribeCounts[6] ?? 0,
    $tribeCounts[7] ?? 0,
    $tribeCounts[8] ?? 0,
    $tribeCounts[9] ?? 0,
];

// Total registered players (from tribe GROUP BY sum is cheaper than another query)
$users = array_sum($tribes);
?>
<table cellpadding="1" cellspacing="1" id="world_player" class="world">
        <thead>
            <tr>
                <th colspan="2">World Stats</th>
            </tr>
            <tr>
                <td>Total Villages</td>
                <td>Total Population</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
<?php
// Query 2: Village totals (single query, was already fine but keeping it)
$result2 = mysqli_fetch_array(mysqli_query($database->dblink,"SELECT Count(*) as Total, SUM(pop) AS sumofpop FROM ".TB_PREFIX."vdata"), MYSQLI_ASSOC);
echo $result2['Total'];
?></td>
                <td>
<?php
echo $result2['sumofpop'];
?></td>
</tr>
</tbody>
</table>
<br />

    <table cellpadding="1" cellspacing="1" id="world_player" class="world">
        <thead>
            <tr>
                <th colspan="2">Players</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <th>Registered players</th>
                <td><?php echo $users; ?></td>
            </tr>

            <tr>
                <th>Active players</th>
                <td><?php
                   // Query 3: Active players (using COUNT instead of SELECT *)
                   $activeResult = mysqli_query($database->dblink,"SELECT COUNT(*) as Total FROM ".TB_PREFIX."users WHERE timestamp > ".(time() - 86400)." AND tribe!=0 AND tribe!=4 AND tribe!=5");
                   echo mysqli_fetch_assoc($activeResult)['Total'];
                   ?></td>
            </tr>

            <tr>
                <th>Players online</th>
                <td><?php
                    $online = mysqli_query($database->dblink,"SELECT Count(*) as Total FROM ".TB_PREFIX."users WHERE timestamp > ".(time() - 600)." AND tribe!=0 AND tribe!=4 AND tribe!=5");
                    if (!empty($online)) {
                        echo mysqli_fetch_assoc($online)['Total'];
                    } else {
                        echo 0;
                    }
                   ?></td>
            </tr>
        </tbody>
    </table>

    <table cellpadding="1" cellspacing="1" id="world_tribes" class="world">
        <thead>
            <tr>
                <th colspan="3">Tribes</th>
            </tr>

            <tr>
                <td>Tribe</td>
                <td>Registered</td>
                <td>Percent</td>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>Romans</td>
                <td><?php echo $tribes[0]; ?></td>
                <td><?php echo ($users > 0) ? ($percents[0] = round(100 * ($tribes[0] / $users), 2))."%" : '---'; ?></td>
            </tr>
            <tr>
                <td>Teutons</td>
                <td><?php echo $tribes[1]; ?></td>
                <td><?php echo ($users > 0) ? ($percents[1] = round(100 * ($tribes[1] / $users), 2))."%" : "---"; ?></td>
            </tr>
            <tr>
                <td>Gauls</td>
                <td><?php echo $tribes[2]; ?></td>
                <td><?php echo ($users > 0) ? ($percents[2] = round(100 * ($tribes[2] / $users), 2))."%" : "---"; ?></td>
            </tr>
            <tr>
                <td>Huns</td>
                <td><?php echo $tribes[3]; ?></td>
                <td><?php echo ($users > 0) ? ($percents[3] = round(100 * ($tribes[3] / $users), 2))."%" : "---"; ?></td>
            </tr>
            <tr>
                <td>Egyptians</td>
                <td><?php echo $tribes[4]; ?></td>
                <td><?php echo ($users > 0) ? ($percents[4] = round(100 * ($tribes[4] / $users), 2))."%" : "---"; ?></td>
            </tr>
            <tr>
                <td>Spartans</td>
                <td><?php echo $tribes[5]; ?></td>
                <td><?php echo ($users > 0) ? ($percents[5] = round(100 * ($tribes[5] / $users), 2))."%" : "---"; ?></td>
            </tr>
            <tr>
                <td>Vikings</td>
                <td><?php echo $tribes[6]; ?></td>
                <td><?php echo ($users > 0) ? ($percents[6] = round(100 * ($tribes[6] / $users), 2))."%" : "---"; ?></td>
            </tr>
        </tbody>
    </table>
        <table cellpadding="1" cellspacing="1" id="world_tribes" class="world">
        <thead>
            <tr>
                <th colspan="3">Miscellaneous</th>
            </tr>

            <tr>
                <td>Attacks</td>
                <td>Casualties</td>
                <td>Date</td>
            </tr>
        </thead>

        <tbody>
<?php
// Query 4: Weekly attack stats in a single aggregated query (replaces 14 queries + 14 PHP loops)
$weeklyStats = $database->getWeeklyAttackStats();
for ($d = 0; $d < 7; $d++) {
    $dayTs = time() - (86400 * $d);
    $dayKey = date("Y-m-d", $dayTs);
    $attacks = isset($weeklyStats[$dayKey]) ? $weeklyStats[$dayKey]['attack_count'] : 0;
    $casualties = isset($weeklyStats[$dayKey]) ? $weeklyStats[$dayKey]['total_casualties'] : 0;
    echo "<tr>
        <td>$attacks</td>
        <td>$casualties</td>
        <td>".date("j. M", $dayTs)."</td>
    </tr>";
}
?>
        </tbody>
    </table>
    <?php  ?>

<table cellpadding="1" cellspacing="1" id="search_navi"> <?php //fix the problem with footer.php, don't change or remove it ?>
