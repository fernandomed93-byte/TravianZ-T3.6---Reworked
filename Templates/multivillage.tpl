<?php 
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       multivillage.tpl                                            ##
##  Developed by:  Dzoki                                                       ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2025. All rights reserved.                ##
##                                                                             ##
#################################################################################
 ?>

<?php
if (!isset($id)) {
	$id = '';
}

$count = count($session->villages);
if($count > 1){
?>
<table id="vlist" cellpadding="1" cellspacing="3">
   <thead><tr><td colspan="3">
   <a href="dorf3.php" accesskey="9"><?php echo VILLAGES; ?>:</a>
   <br>
   <?php 
		$mode = CP; 
		$numVillages = $count;
		$cpRequired = ${'cp'.$mode}[$numVillages + 1];
		
		if($session->cp >= $cpRequired) {
			$canFound = 0;
			$tempTotal = $numVillages;
			while($session->cp >= ${'cp'.$mode}[$tempTotal + 1]) {
				$canFound++;
				$tempTotal++;
			}
			echo " <span style='color:#006400; font-size:10px;'>(Can found or conquer: ".$canFound.")</span>";
		} else {
			echo " <span style='color:#707070; font-size:10px;'>(CP: ". $session->cp ."/". $cpRequired .")</span>";
		}
	?>
   </td></tr></thead>
	<tbody><?php
		$returnVillageArray = $database->getArrayMemberVillage($session->uid);
		
		$queryParams = $_GET;
		unset($queryParams['newdid']);
		$queryString = http_build_query($queryParams);
		$linkSuffix = $queryString ? "&".$queryString : "";

		for($i = 1; $i <= count($session->villages); ++$i) {
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}

			$isActive = (isset($_SESSION['wid']) && $_SESSION['wid'] == $returnVillageArray[$i-1]['wref']);
			$isOther = (isset($_SESSION['wid']) && $returnVillageArray[$i-1]['wref'] != $_SESSION['wid']);

			echo '
		<tr>
			<td class="dot '.($isActive ? 'hl' : '').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].$linkSuffix.'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($isOther && $marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z='.$returnVillageArray[$i-1]['wref'].'&id='.$marketid.'><img src="gpack/travian_default/img/x.gif" class="sresvillage" /></a></div></td>';
			if ($isOther) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z='.$returnVillageArray[$i-1]['wref'].'"><img src="gpack/travian_default/img/x.gif" class="def1" /></a></div></td></tr>';
		}
	?>
	</tbody>
</table>
<?php
}
?>
