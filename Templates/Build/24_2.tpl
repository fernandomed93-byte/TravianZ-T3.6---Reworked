<?php 
		$timeleft = $database->getVillageField($village->wid, 'celebration');
		$celebType = $database->getVillageField($village->wid, 'type');
		$ctype="";
		if ($celebType > 0){
			$ctype = "Small";
			if ($celebType == 2){$ctype= "Great";}
		}
		if($timeleft > time()){
			echo '</br>';
			echo '<table cellpadding="0" cellspacing="0" id="building_contract">';
			echo '<tr><td>';
            echo $ctype. ' celebration still needs:';
            echo "</td><td><span id=\"timer".++$session->timer."\">";
            echo $generator->getTimeFormat($timeleft - time());
            echo "</span> hrs.</td>";
            echo "<td>done at ".date('H:i', $timeleft)."</td></tr>";
			echo "</table>";
		}
?>