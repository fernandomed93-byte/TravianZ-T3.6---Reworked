<?php
$noticeClass = ["Scout Report", "Won as attacker without losses", "Won as attacker with losses", "Lost as attacker with losses", "Won as defender without losses", "Won as defender with losses", "Lost as defender with losses", "Lost as defender without losses", "Reinforcement arrived", "",
                "Wood Delivered", "Clay Delivered", "Iron Delivered", "Crop Delivered", "", "Won as defender without losses", "Won as defender with losses", "Lost as defender with losses", "Won scouting as attacker", "Lost scouting as attacker", "Won scouting as defender", "Lost scouting as defender",
                "Scout Report"];
?>
<form method="post" action="berichte.php" name="msg">
                           <table cellpadding="1" cellspacing="1" id="overview"
                                   class="row_table_data">
                                               <thead>
                                               <tr>
                                               <th colspan="2">Subject:</th>
                                                       <th class="sent">
                                                                   <a href="berichte.php?o=1<?php echo (isset($_GET['t']) ? '&amp;t='.$_GET['t'] : ''); ?>">Sent</a></th>
                                                                           </tr>
                                                                           </thead><tfoot>
                                                                           <tr><th><?php
                                                                               $MyGold = mysqli_query($database->dblink,"SELECT plus FROM ".TB_PREFIX."users WHERE `id`='".(int) $session->uid."'") or die(mysqli_error($database->dblink));
$golds = mysqli_fetch_array($MyGold);
$date2=strtotime("NOW");
if ($golds['plus'] <= $date2) {
    ?>
    <?php
}
else {
    ?>
    <input class="check" type="checkbox" id="s10" name="s10" onclick="Allmsg(this.form);" />
                                                <?php
        } ?></th>
                                        <th class="buttons"><input name="del" type="image" id="btn_delete" class="dynamic_img" src="gpack/travian_default/img/x.gif" value="delete" alt="delete" />
            <?php if($session->plus) {
            if(isset($_GET['t']) && $_GET['t'] == 5) {
                echo "<input name=\"start\" type=\"image\" value=\"back\" alt=\"back\" id=\"btn_back\" class=\"dynamic_img\" src=\"gpack/travian_default/img/x.gif\" />";
            }
            else {
                echo "<input name=\"archive\" type=\"image\" value=\"Archive\" alt=\"Archive\" id=\"btn_archiv\" class=\"dynamic_img\" src=\"gpack/travian_default/img/x.gif\" />";
            }
        }?>
</th>
<th class=navi>
              <?php
        $totalReports = $message->totalNotice;
                $reportsPerPage = $message->reportsPerPage;
                $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                $totalPages = ceil($totalReports / $reportsPerPage);

                // Manter os parâmetros 't' e 'o' nos links
                $params = [];
                if (isset($_GET['t'])) $params['t'] = 't=' . $_GET['t'];
                if (isset($_GET['o'])) $params['o'] = 'o=' . $_GET['o'];
                if (isset($_GET['s'])) $params['s'] = 's=' . $_GET['s']; // Novo filtro de sentido
                if (isset($_GET['r'])) $params['r'] = 'r=' . $_GET['r']; // Novo filtro de resultado
                if (isset($_GET['h'])) $params['h'] = 'h=' . $_GET['h']; // Adicione esta linha junto aos outros params
                
                $queryString = empty($params) ? '' : '&amp;' . implode('&amp;', $params);

                // Determina se há página anterior
                $hasPrevious = $currentPage > 1;
                // Determina se há página seguinte
                $hasNext = $currentPage < $totalPages;

                // Mostra o link 'Anterior' (&laquo;)
                if ($hasPrevious) {
                    echo "<a href=\"berichte.php?page=" . ($currentPage - 1) . $queryString . "\">&laquo;</a>";
                } else {
                    echo "&laquo;"; // Sem link se for a primeira página
                }

                // Mostra o link 'Próximo' (&raquo;)
                if ($hasNext) {
                    echo "<a href=\"berichte.php?page=" . ($currentPage + 1) . $queryString . "\">&raquo;</a>";
                } else {
                    echo "&raquo;"; // Sem link se for a última página
                }
?>
</th>
</tr>
</tfoot>
	<tbody>
        <?php
        $name = 1; // Contador para o nome do checkbox
        // --- LOOP USANDO FOREACH ---
        if (!empty($message->noticearray)) {
            foreach ($message->noticearray as $report) {
                echo "<tr><td class=\"sel\"><input class=\"check\" type=\"checkbox\" name=\"n" . $name . "\" value=\"" . $report['id'] . "\" /></td>";
                echo "<td class=\"sub\">";

                // Determina o tipo e a imagem
                $type = (isset($_GET['t']) && $_GET['t'] == 5) ? $report['archive'] : $report['ntype']; // [cite: 14]
                if($type == 23) $type = 22; // [cite: 15]

                $imgClass = '';
                $imgSrc = '';
                if($type >= 15 && $type <= 17){
                    $type -= 11;
                    $imgClass = "iReport iReport$type";
                    $imgSrc = "gpack/travian_default/img/x.gif"; // [cite: 16]
                } else if($type >= 18 && $type <= 22){
                    $imgSrc = "gpack/travian_default/img/scouts/$type.gif"; // [cite: 17]
                } else {
                    $imgClass = "iReport iReport$type";
                    $imgSrc = "gpack/travian_default/img/x.gif"; // [cite: 18]
                }
                echo "<img src=\"$imgSrc\" " . ($imgClass ? "class=\"$imgClass\"" : "") . " alt=\"" . $noticeClass[$type] . "\" title=\"" . $noticeClass[$type] . "\" />"; // [cite: 16, 17, 18]

                // Mostra o tópico e "(novo)"
                echo "<div><a href=\"berichte.php?id=" . $report['id'] . "\">" . $report['topic'] . "</a> "; // [cite: 19]
                if ($report['viewed'] == 0) {
                    echo "(novo)"; // [cite: 20]
                }

                // Mostra a data
                $date = $generator->procMtime($report['time']);
                echo "</div></td><td class=\"dat\">" . $date[0] . " " . $date[1] . "</td></tr>"; // [cite: 21]

                $name++;
            }
        } else {
            // Mensagem se não houver relatórios
            echo "<tr><td colspan=\"3\" class=\"none\">There are no reports available.</td></tr>";
        }
        ?>
    </tbody>

</table>

