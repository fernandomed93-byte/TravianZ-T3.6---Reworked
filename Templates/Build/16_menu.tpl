<div id="textmenu">
	<a href="build.php?id=<?php echo $id; ?>" <?php if(!isset($_GET['t']) || (isset($_GET['t']) && $_GET['t'] == 99 && !$session->goldclub)) echo "class=\"selected\""; ?> ><?php echo OVERVIEW;?></a> |
    <a href="a2b.php"><?php echo SEND_TROOPS;?></a> |
    <a href="warsim.php"><?php echo Q20_RESP1;?></a> 
    <?php if($session->goldclub == 1){ ?>|
    <a href="build.php?id=<?php echo $id; ?>&amp;t=99" <?php if(isset($_GET['t']) && $_GET['t'] == 99) echo "class=\"selected\""; ?> >Gold Club</a>
    <?php } ?>
</div><br>

<?php 
$tribe = $session->tribe;
$village->unitarray2 = $database->getUnit($village->wid, false);
$unitsonRoute = $database->getUnitonRoute($village->wid, false);
$unitsonVacation = $database->getUnitonVacation($village->wid, $tribe, false);
$village->unitarray2['hero'] = $village->unitarray2['hero'] + $unitsonRoute['t11'];
?>

<table class="troop_details" cellpadding="1" cellspacing="1">
		<thead>
			<tr>
				<td class="role"><a
					href="karte.php?d=<?php echo $village->wid."&c=".$generator->getMapCheck($village->wid); ?>"><?php echo $village->vname; ?></a></td>
				<td
					colspan="<?php echo $village->unitarray2['hero'] == 0 ? 10 : 11; ?>">
					<a href="spieler.php?uid=<?php echo $session->uid; ?>"><?php echo OWN_TROOPS_TOTAL;?></a>
				</td>
			</tr>
		</thead>
		<tbody class="units">

			<?php
				
                  $start = ($tribe-1)*10+1;
                  $end = ($tribe*10);
                  echo "<tr><th>&nbsp;</th>";
                  for($i=$start;$i<=($end);$i++) {
                  	echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" title=\"".$technology->getUnitName($i)."\" alt=\"".$technology->getUnitName($i)."\" /></td>";	
                  }
				 
				  if($village->unitarray2['hero'] != 0) {
                  echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit uhero\" title=\"Hero\" alt=\"Hero\" /></td>";    
				  }
                  
			?>
			</tr><tr><th><?php echo TROOPS;?></th>
            <?php
			
			
            for($i=$start;$i<=$end;$i++) {
			$village->unitarray2['u'.$i] = $village->unitarray2['u'.$i] + $unitsonRoute['t'.$i - ($tribe-1)*10]+ $unitsonVacation['u'.$i];
			
			
            	if($village->unitarray2['u'.$i] == 0) {
                	echo "<td class=\"none\">";
                }
                else {
                echo "<td>";
                }
                echo $village->unitarray2['u'.$i]."</td>";
				
            }

                if($village->unitarray2['hero'] != 0) {
                echo "<td>";
				echo $village->unitarray2['hero']."</td>";
                }
                
            ?>
           </tr>
		   
		   <?php
			$units = $database->getMovement(34, $village->wid, 1);
			$total_for = count($units);
			$totalcarry = $totalres = $totwood = $totiron = $totclay = $totcrop = 0;
			
			for($y = 0; $y < $total_for; $y++){
				$tribe = $session->tribe;
				$start = ($tribe - 1) * 10 + 1;
				$end = ($tribe * 10);
				
				$totalres = $totalres + $units[$y]['wood'] + $units[$y]['clay'] + $units[$y]['iron'] + $units[$y]['crop'];
				$totwood = $totwood + $units[$y]['wood'];
				$totclay = $totclay + $units[$y]['clay'];
				$totiron = $totiron + $units[$y]['iron'];
				$totcrop = $totcrop + $units[$y]['crop'];
				
				if($units[$y]['sort_type'] == 4){
					$totalatk = 0;
					for($i = 0; $i <= 9; $i++) {
						$totalthis = $units[$y]['t'.($i + 1)] * ${'u'.($start + $i)}['cap'];
						$totalatk = $totalatk + $totalthis;
					}
					$totalcarry = $totalcarry + $totalatk;
				}
				
			}
			
			?>
		
		   </tbody>
            <tbody class="infos"><tr><th><?php echo UPKEEP;?></th>
            <td colspan="<?php if($village->unitarray2['hero'] == 0) {echo"10";}else{echo"11";}?>"><?php echo $technology->getUpkeep($village->unitarray2,0); ?><img class="r4" src="gpack/travian_default/img/x.gif" title="Crop" alt="Crop" /><?php echo PER_HR;?></td></tr>
			
		
</tbody>
	</table>
	
	
	<table class="troop_details" cellpadding="1" cellspacing="1">
	<thead>
	<tr>
			<th rowspan = "2"><?php echo BOUNTY_TOTAL;?></th>
			
			<td style="text-align:center;">
			<?php
				echo "<img class=\"r1\" src=\"gpack/travian_default/img/x.gif\" alt=\"Lumber\" title=\"Lumber\" />";
				?>	
				</td>
				
				<td style="text-align:center;">
				<?php
				echo "<img class=\"r2\" src=\"gpack/travian_default/img/x.gif\" alt=\"Clay\" title=\"Clay\" />";
				?>	
				</td>
				
				<td style="text-align:center;">
				<?php
				echo "<img class=\"r3\" src=\"gpack/travian_default/img/x.gif\" alt=\"Iron\" title=\"Iron\" />";
				?>	
				</td>
				
				<td style="text-align:center;">
				<?php
				echo "<img class=\"r4\" src=\"gpack/travian_default/img/x.gif\" alt=\"Crop\" title=\"Crop\" />";
				?>	
				</td>
				
				<td style="text-align:center;">
				<?php
				echo "<img class=\"carry\" src=\"gpack/travian_default/img/x.gif\" alt=\"Carry\" title=\"Carry\" /> ";
				?>	
				</td>
				</tr>
				
				<tr>
				<td><?php echo $totwood;?></td>
				<td><?php echo $totclay;?></td>
				<td><?php echo $totiron;?></td>
				<td><?php echo $totcrop;?></td>
				<td><?php echo $totalres;?></td>
				</tr>
		
		</thead>
	</table>