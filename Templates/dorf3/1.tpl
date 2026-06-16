<?php
	include('menu.tpl');
?>
<table id="overview" cellpadding="1" cellspacing="1">
<thead>
<tr><th colspan="5">Overview</th></tr>
<tr><td>Village</td><td>Attacks</td><td>Building</td><td>Troops</td><td>Merchants</td></tr>
</thead>
<tbody>
<?php
	$varray = $database->getProfileVillages($session->uid, 8);
	foreach($varray as $vil){
		$vid = $vil['wref'];
		$vdata = $database->getVillage($vid);
		$jobs = $database->getJobs($vid);
		$units = $database->getTraining($vid);
		$unitsArray = [];
		foreach($units as $unit) {
			if (!isset($unitsArray[$unit['unit']])) $unitsArray[$unit['unit']] = 0;
			$unitsArray[$unit['unit']] += $unit['amt'];
		}
		$totalmerchants = $building->getTypeLevel(17,$vid);
		$availmerchants = $totalmerchants - $database->totalMerchantUsed($vid);
		$incoming_attacks = $database->getMovement(3,$vid,1);
		$bui = '';
		$tro = '';
		$att = '<span class="none">-</span>';

		if (count($incoming_attacks) > 0) {
			$inc_atts = count($incoming_attacks);
			for($i=0;$i<count($incoming_attacks);$i++){
			    if($incoming_attacks[$i]['attack_type'] == 1 || $incoming_attacks[$i]['attack_type'] == 2) {
					$inc_atts -= 1;
				}
			}
			if($inc_atts > 0) {
				$att = '<a href="build.php?newdid='.$vid.'&id=39"><img class="att1" src="gpack/travian_default/img/x.gif" title="'.count($incoming_attacks).' incoming attack'.(count($incoming_attacks)>1?'s':'').'" alt="'.count($incoming_attacks).' incoming attack'.(count($incoming_attacks)>1?'s':'').'"></a>';
			}
		}
		if ($jobs && count($jobs) > 0){
			foreach($jobs as $b){
				$bui .= '<a href="build.php?newdid='.$vid.'&id='.$b['field'].'"> <img class="bau" src="gpack/travian_default/img/x.gif" title="'.Building::procResType($b['type']).'" alt="'.Building::procResType($b['type']).'"></a>';
			}
		}else{
			$bui = '<span class="none">-</span>';
		}
		
		if ($unitsArray && count($unitsArray) > 0){
			foreach($unitsArray as $key => $c){
				if($key == 99) $key = 51;
				
				$calculated_gid = null; // Valor padrão

				// Adicionado $key > 0 para evitar problemas com key=0 se existir
				if ($key > 0 && isset($unitsbytype['infantry']) && is_array($unitsbytype['infantry']) && in_array($key, $unitsbytype['infantry'])) {
					$calculated_gid = 19;
				} elseif ($key > 0 && isset($unitsbytype['cavalry']) && is_array($unitsbytype['cavalry']) && in_array($key, $unitsbytype['cavalry'])) {
					$calculated_gid = 20;
				} elseif ($key > 0 && isset($unitsbytype['siege']) && is_array($unitsbytype['siege']) && in_array($key, $unitsbytype['siege'])) {
					$calculated_gid = 21;
				} elseif ($key > 1000 && isset($unitsbytype['infantry']) && is_array($unitsbytype['infantry']) && in_array(($key - 1000), $unitsbytype['infantry'])) {
					 $calculated_gid = 29;
				} elseif ($key > 1000 && isset($unitsbytype['cavalry']) && is_array($unitsbytype['cavalry']) && in_array(($key - 1000), $unitsbytype['cavalry'])) {
					 $calculated_gid = 30;
				} elseif ($key == 51) { // $key pode ter sido mudado de 99 para 51 antes
					 $calculated_gid = 36;
				} else {
					// Fallback - Verifica Palácio/Residência
					 if (is_object($building) && method_exists($building, 'getTypeLevel')) {
						if($building->getTypeLevel(26) > 0){
							 $calculated_gid = 26; // Palácio
						} else {
							 $calculated_gid = 25; // Residência
						}
					 } else {
						// Se $building for inválido, defina um GID padrão seguro, ex: 0 ou 25
						$calculated_gid = 25; // Ou 0, ou outro GID que não cause erro
						// Poderia adicionar um log de erro aqui
					 }
				}
				
				if($key > 1000 && $key < 2000) { $key -= 1000; }
				if($key > 2000) { $key -= 2000; }
				$tro .= '<a href="build.php?newdid='.$vid.'&gid='.$calculated_gid.'"> <img class="unit u'.($key > 90 ? 99 : $key).'" src="gpack/travian_default/img/x.gif" title="'.$c.'x '.$technology->getUnitName($key).'" alt="'.$c.'x '.$technology->getUnitName($key).'"></a>';
			}
		}else{
			$tro = '<span class="none">-</span>';
		}
		if($vid == $village->wid) { $class = 'hl'; } else {$class = ''; }

echo '
<tr class="'.$class.'">
<td class="vil fc"><a href="dorf1.php?newdid='.$vid.'">'.$vdata['name'].'</a></td>
<td class="att">'.$att.'</td>
<td class="bui">'.$bui.'</td>
<td class="tro">'.$tro.'</td>
<td class="tra lc">'.($totalmerchants>0?'<a href="build.php?newdid='.$vid.'&amp;gid=17">':'').$availmerchants.'/'.$totalmerchants.'</a></td>
</tr>';

	}
?>
</tbody></table>
