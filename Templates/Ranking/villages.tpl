<?php
$rankSize = $ranking->getCountVillages();

if(isset($_POST['rank']) && is_numeric($_POST['rank'])) {
    $targetRank = (int)$_POST['rank'];
} elseif(isset($_GET['rank']) && is_numeric($_GET['rank'])) {
    $targetRank = (int)$_GET['rank'];
}else {
    $targetRank = $ranking->getVillageRankPosition($village->wid);
}

// 2. Define variáveis para o ranksearch.tpl
$search = $targetRank; // Usado para highlight e preencher o input de texto


// 3. Paginação (calcula o topo da página, ex: rank 22 vira renderStart 21)
$page = ceil($targetRank / 20);
$renderStart = ($page - 1) * 20 + 1;
$sqlOffset = max(0, $renderStart - 1);
$start = $renderStart;  // Usado para os cálculos de "back" e "forward" no ranksearch.tpl

// 4. Busca os dados
$rankArrayProc = $ranking->getVRankPart($sqlOffset, 20);
// Criamos uma referência para o ranksearch.tpl não quebrar em outros rankings
$rankArray = $rankArrayProc;

// 4. Manter a lógica de busca/highlight
if(isset($_SESSION['search']) && !is_numeric($_SESSION['search'])) {
    echo "<center><font color=orange size=2><p class=\"error\">The village <b>\"".$_SESSION['search']."\"</b> does not exist.</p></font></center>";
    $search = 0;
}
?>
<table cellpadding="1" cellspacing="1" id="villages" class="row_table_data">
    <thead>
        <tr><th colspan="5">The largest villages</th></tr>
        <tr><td></td><td>Village</td><td>Player</td><td>Population</td><td>Coordinates</td></tr>
    </thead>
    <tbody>  
    <?php

    if(is_array($rankArrayProc) && count($rankArrayProc) > 0) {
        foreach($rankArrayProc as $index => $data) {
            if($data == "pad") continue;
            
            $currentRank = $renderStart + $index - 1;
            $cut = 0;
            if ($currentRank < 100){
                $cut = 17; 
            }elseif ($currentRank < 1000){
                $cut = 16; 
            }elseif ($currentRank < 10000){
                $cut = 15; 
            }elseif ($currentRank < 100000){
                $cut = 14; 
            }else{
                $cut = 13; 
            }

            $highlight = ($currentRank == $search) ? "class=\"hl\"" : "";
            $usernameExibicao = mb_strimwidth($data['user'], 0, $cut, "...");
            $vilnameExibicao = mb_strimwidth($data['name'], 0, $cut, "...");
            
            echo "<tr ".$highlight."><td class=\"ra fc\">".$currentRank.".</td>";
            echo "<td class=\"vil\"><a href=\"karte.php?d=".$data['wref']."&amp;c=".$generator->getMapCheck($data['wref'])."\">".$vilnameExibicao."</a></td>";
            echo "<td class=\"pla\"><a href=\"spieler.php?uid=".$data['owner']."\">".$usernameExibicao."</a></td>";
            echo "<td class=\"hab\">".$data['pop']."</td>";
            echo "<td class=\"aligned_coords\"><div class=\"cox\">(".$data['x']."</div><div class=\"pi\">|</div><div class=\"coy\">".$data['y'].")</div></td></tr>";
        }
    } else {
        echo "<td class=\"none\" colspan=\"5\">No villages found</td>";
    }
    ?>
    </tbody>
</table>
<?php
include("ranksearch.tpl");
?>
