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
		
		// Verifica se os pontos atuais são suficientes para a próxima vila
		if($session->cp >= $cpRequired) {
			// Calcula quantas vilas extras ele pode fundar (loop simples)
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
if(isset($_GET['w'])) {
		for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : "&w=".$_GET['w']).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
else if(isset($_GET['r'])) {
		for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : "&r=".$_GET['r']).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
else if(isset($_GET['z'])) {
        for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
        <tr>
            <td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
            <td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : "&z=".$_GET['z']).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
            <td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
    }
}
else if(isset($_GET['o'])) {
        for($i=1;$i<=count($session->villages);++$i){
		
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
        <tr>
            <td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
            <td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : "&o=".$_GET['o']).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
            <td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
    }
}
else if(isset($_GET['s'])) {
		for($i=1;$i<=count($session->villages);++$i){
		
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : "&s=".$_GET['s']).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
else if(isset($_GET['c'])) {
		for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : (isset($_GET['d']) ? "&d=".$_GET['d'] : '')).(($id>=19) ? "&id=".$id : "&c=".$_GET['c']).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
else if(isset($_GET['t'])) {
		for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : "&t=".$_GET['t']).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
else if(isset($_GET['d'])) {
		for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : "&d=".$_GET['d']).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
else if(isset($_GET['uid'])) {
		for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : "&uid=".$_GET['uid']).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
else if(isset($_GET['vill']) && isset($_GET['id'])) {
		for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : "&id=".$_GET['id'])."&vill=".$_GET['vill'].'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
else if(isset($_GET['t']) && isset($_GET['id'])) {
		for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : "&id=".$_GET['id'])."&t=".$_GET['t'].'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
else if(isset($_GET['id'])) {
		
			
		for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.((isset($_SESSION['wid']) && $_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : (!empty($_GET['id']) ? "&id=".$_GET['id'] : '')).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if (isset($_SESSION['wid']) && $returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if (isset($_SESSION['wid']) && $returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref'] . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
else if(isset($_GET['aid'])) {
		for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.(($_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : "&aid=".$_GET['aid']).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if ($returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref']. ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
else{
		
			
		for($i=1;$i<=count($session->villages);++$i){
			
			$marketid = 0;
			for($mkt = 19; $mkt <= 40; $mkt++) {
				if($village->resarray['f'.$mkt.'t'] == 17) $marketid = $mkt;
			}
			
			echo'
		<tr>
			<td class="dot '.((isset($_SESSION['wid']) && $_SESSION['wid'] == $returnVillageArray[$i-1]['wref'] ) ? 'hl':'').'">●</td>
			<td class="link2"><div class="link2"><a href="?newdid='.$returnVillageArray[$i-1]['wref'].(($id>=19) ? "&id=".$id : (!empty($_GET['id']) ? "&id=".$_GET['id'] : '')).'">'.$returnVillageArray[$i-1]['name'].'</a></div></td>
			<td class="aligned_coords2"><div class="cox2">('.$returnVillageArray[$i-1]['x'].'|'.$returnVillageArray[$i-1]['y'].')</div></td>';
			if (isset($_SESSION['wid']) && $returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) if ($marketid > 0) echo '<td class="extras2"><div class="cox3"><a href=.\build.php?z=' .$returnVillageArray[$i-1]['wref']. '&id=' . $marketid . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'sresvillage\' /></a></div></td>';
			if (isset($_SESSION['wid']) && $returnVillageArray[$i-1]['wref'] <> $_SESSION['wid']) echo '<td class="extras2"><div class="cox3"><a href=.\a2b.php?s=2&c=2&z=' .$returnVillageArray[$i-1]['wref'] . ' \"><img src=\'gpack/travian_default/img/x.gif\' class=\'def1\' /></a></div></td></tr>';
			
	}
}
?>
	</tbody>
</table>
<?php
}
?>
