<?php 
$rankSize = $ranking->getRankCount();
$search = 0;

if(!is_numeric($_SESSION['search'])) {
?>
	<center><font color=orange size=2><p class=\"error\">The user <b>"<?php echo $_SESSION['search']; ?>"</b> does not exist.</p></font></center>
<?php
} else {
    $search = (int)$_SESSION['search'];
}
?>
<table cellpadding="1" cellspacing="1" id="player_def" class="row_table_data">
			<thead>
				<tr>
					<th colspan="5">
						Top Defenders						
						<div id="submenu">
						<a title="Top 10" href="statistiken.php?id=7"><img class="btn_top10" src="gpack/travian_default/img/x.gif" alt="Top 10"></a>
						<a title="Defenders" href="statistiken.php?id=32"><img class="active btn_def" src="gpack/travian_default/img/x.gif" alt="Defenders"></a>
						<a title="Attackers" href="statistiken.php?id=31"><img class="btn_off" src="gpack/travian_default/img/x.gif" alt="Attackers"></a></div>
						
						<div id="submenu2">
						<a title="Romans" href="statistiken.php?id=11"><img class="btn_v1" src="gpack/travian_default/img/x.gif" alt="Romans"></a>
						<a title="Teutons" href="statistiken.php?id=12"><img class="btn_v2" src="gpack/travian_default/img/x.gif" alt="Teutons"></a>
						<a title="Gauls" href="statistiken.php?id=13"><img class="btn_v3" src="gpack/travian_default/img/x.gif" alt="Gauls"></a></div>
						</th>
				</tr>
		<tr><td></td><td>Player</td><td>Population</td><td>Villages</td><td>Points</td></tr>
		</thead><tbody>  
        <?php
        $rankArray = $ranking->getRank();
        if(count($rankArray) > 1 && $rankSize > 0) {
            foreach ($rankArray as $row) {
                if ($row == "pad" || !isset($row['username'])) continue;
                $isHl = ($row['rank_pos'] == $search);
                echo $isHl ? "<tr class=\"hl\"><td class=\"ra fc\" >" : "<tr><td class=\"ra \" >";
                echo $row['rank_pos'].".</td><td class=\"pla \" >";
                if($row['username'] == $_SESSION['username']){
                    echo"<u><a href=\"spieler.php?uid=".$row['id']."\">".$row['username']."</a></u>";
                } else {
                    echo"<a href=\"spieler.php?uid=".$row['id']."\">".$row['username']."</a>";
                }
                echo"</td><td class=\"pop \" >".$row['totalpop']."";
                echo "</td><td class=\"vil\">".$row['totalvillages']."</td><td class=\"po \" >".$row['dpall']."</td></tr>";
            }
        }
        else echo "<td class=\"none\" colspan=\"5\">No users found</td>"; 
        ?>
 </tbody>
</table>
<?php
include("ranksearch.tpl");
?>
