<table cellpadding="1" cellspacing="1" class="build_details">
<thead><tr>
	<td><?php echo ACADEMY; ?></td>
	<td><?php echo ACTION; ?></td>
</tr></thead>
<tbody>

<?php 
$fail = $success = $prereqCount = 0;
$acares = $technology->grabAcademyRes();
$start = ($session->tribe - 1) * 10 + 2;
$end = ($session->tribe * 10) - 1;
for($i=$start;$i<=$end;$i++) {
	if($technology->meetRRequirement($i) && !$technology->getTech($i) && !$technology->isResearch($i,1)) {
    	echo "<tr><td class=\"desc\">
					<div class=\"tit\">
						<img class=\"unit u".$i."\" src=\"gpack/travian_default/img/x.gif\" alt=\"".$technology->getUnitName($i)."\" title=\"".$technology->getUnitName($i)."\" />
						<a href=\"#\" onClick=\"return Popup(".$i.",1);\">".$technology->getUnitName($i)."</a>
					</div>
					<div class=\"details\"><img class=\"r1\" src=\"gpack/travian_default/img/x.gif\" alt=\"Lumber\" title=\"".LUMBER."\" />".${'r'.$i}['wood']."|<img class=\"r2\" src=\"gpack/travian_default/img/x.gif\" alt=\"Clay\" title=\"".CLAY."\" />".${'r'.$i}['clay']."|<img class=\"r3\" src=\"gpack/travian_default/img/x.gif\" alt=\"Iron\" title=\"".IRON."\" />".${'r'.$i}['iron']."|<img class=\"r4\" src=\"gpack/travian_default/img/x.gif\" alt=\"Crop\" title=\"".CROP."\" />".${'r'.$i}['crop']."|<img class=\"clock\" src=\"gpack/travian_default/img/x.gif\" alt=\"duration\" title=\"".DURATION."\" />";
                    echo $generator->getTimeFormat(round(${'r'.$i}['time'] * ($bid22[$village->resarray['f'.$id]]['attri'] / 100)/SPEED));
					
                   //-- If available resources combined are not enough, remove NPC button
                   $total_required = (int)(${'r'.$i}['wood'] + ${'r'.$i}['clay'] + ${'r'.$i}['iron'] + ${'r'.$i}['crop']);
                   if($session->userinfo['gold'] >= 3 && $building->getTypeLevel(17) >= 1 && $village->atotal >= $total_required) {
                   echo "|<a href=\"build.php?gid=17&t=3&r1=".${'r'.$i}['wood']."&r2=".${'r'.$i}['clay']."&r3=".${'r'.$i}['iron']."&r4=".${'r'.$i}['crop']."\" title=\"NPC trade\"><img class=\"npc\" src=\"gpack/travian_default/img/x.gif\" alt=\"NPC trade\" title=\"NPC trade\" /></a>";
                   }
                   if(${'r'.$i}['wood'] > $village->maxstore || ${'r'.$i}['clay'] > $village->maxstore || ${'r'.$i}['iron'] > $village->maxstore) {
                    echo "<br><span class=\"none\">".EXPAND_WAREHOUSE1."</span></div></td>";
                    echo "<td class=\"act\">
					<div class=\"none\">".EXPAND_WAREHOUSE."</div>
				</td></tr>";
                }
                else if(${'r'.$i}['crop'] > $village->maxcrop) {
                    echo "<br><span class=\"none\">".EXPAND_GRANARY1."</span></div></td>";
                    echo "<td class=\"act\">
					<div class=\"none\">".EXPAND_GRANARY."</div>
				</td></tr>";
                }
                else if(${'r'.$i}['wood'] > $village->awood || ${'r'.$i}['clay'] > $village->aclay || ${'r'.$i}['iron'] > $village->airon || ${'r'.$i}['crop'] > $village->acrop) {
					if($village->getProd("crop")>0){
						$time = $technology->calculateAvaliable(22,${'r'.$i});
						echo "<br><span class=\"none\">".ENOUGH_RESOURCES." ".$time[0]." at ".$time[1]."</span></div></td>";
					} else {
						echo "<br><span class=\"none\">".CROP_NEGATIVE."</span></div></td>";
					}
					echo "<td class=\"act\"><div class=\"none\">".TOO_FEW_RESOURCES."</div></td></tr>";
				}
				else if ( count($acares) > 0 ) {
					echo "</td>";
					echo "<td class=\"act\">
					".RESEARCH_IN_PROGRESS."</td></tr>";
                }
				else if($session->access != BANNED){
                echo "</td>";
                    echo "<td class=\"act\">
					<a class=\"research\" href=\"build.php?id=$id&amp;a=$i&amp;c=".$session->mchecker."\">".RESEARCH."</a></td></tr>";
                }else{
                echo "</td>";
                    echo "<td class=\"act\">
					<a class=\"research\" href=\"banned.php\">".RESEARCH."</a></td></tr>";
					}
                $success += 1;
    }
    else {
    $fail += 1;
    if(!$technology->meetRRequirement($i) && !$technology->getTech($i)) $prereqCount++;
    }
}
if($success == 0) {
echo "<td colspan=\"2\"><div class=\"none\" align=\"center\">".RESEARCH_AVAILABLE."</div></td>";
}
?>
</tbody>
            </table>
<?php if($prereqCount > 0) { 
	echo "<p class=\"switch\"><a id=\"researchFutureLink\" href=\"#\" onclick=\"return $('researchFuture').toggle();\">".SHOW_MORE."</a></p>
		<table id=\"researchFuture\" class=\"build_details hide\" cellspacing=\"1\" cellpadding=\"1\">
			<thead><tr><td colspan=\"2\">".PREREQUISITES."</td></tr><tbody>";
      for($i=$start;$i<=$end;$i++) {
          if(!$technology->meetRRequirement($i) && !$technology->getTech($i) && isset($unitRequirements[$i])) {
              echo "<tr><td class=\"desc\"><div class=\"tit\"><img class=\"unit u".$i."\" alt=\"".$technology->getUnitName($i)."\" src=\"gpack/travian_default/img/x.gif\"/>
              <a onclick=\"return Popup(".$i.", 1);\" href=\"#\">".$technology->getUnitName($i)."</a></div></td><td class=\"cond\">";
              $first = true;
              foreach ($unitRequirements[$i] as $gid => $level) {
                  if (!$first) echo "<br />";
                  $first = false;
                  $name = defined($buildingNames[$gid]) ? constant($buildingNames[$gid]) : $buildingNames[$gid];
                  echo "<a href=\"#\" onclick=\"return Popup(".$gid.", 4);\">".$name."</a><span title=\"+".$level."\">&nbsp;".LEVEL." ".$level."</span>";
              }
              echo "</td></tr>";
          }
      }
      echo "<script type=\"text/javascript\">
		//<![CDATA[
			$(\"researchFuture\").toggle = (function()
			{
				this.toggleClass(\"hide\");

				$(\"researchFutureLink\").set(\"text\",
					this.hasClass(\"hide\")
					?	\"".SHOW_MORE."\"
					:	\"".HIDE_MORE."\"
				);

				return false;
			}).bind($(\"researchFuture\"));
		//]]>
		</script>";
     echo "</tbody></table>";
}
if(count($acares) > 0) {
	echo "<table cellpadding=\"1\" cellspacing=\"1\" class=\"under_progress\"><thead><tr><td>".RESEARCHING."</td><td>".DURATION."</td><td>".COMPLETE."</td></tr>
	</thead><tbody>";
	foreach($acares as $aca) {
		$unit = substr($aca['tech'],1,2);
		echo "<tr><td class=\"desc\"><img class=\"unit u$unit\" src=\"gpack/travian_default/img/x.gif\" alt=\"".$technology->getUnitName($unit)."\" title=\"".$technology->getUnitName($unit)."\" />".$technology->getUnitName($unit)."</td>";
		echo "<td class=\"dur\"><span id=\"timer".++$session->timer."\">".$generator->getTimeFormat($aca['timestamp']-time())."</span></td>";
		$date = $generator->procMtime($aca['timestamp']);
		echo "<td class=\"fin\"><span>".$date[1]."</span><span> hrs</span></td>";
		echo "</tr>";
	}
	echo "</tbody></table>";
}
?>
