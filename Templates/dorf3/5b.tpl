<?php
	include('menu.tpl');
?>
<style>#submenu { margin-bottom: 8px; }</style>
<div id="submenu">
	<a href="dorf3.php?s=5&amp;t=1" class="<?php if(!isset($_GET['t']) || $_GET['t'] == 1){echo 'selected';}?>">Total Troops</a>
	| <a href="dorf3.php?s=5&amp;t=2" class="<?php if(isset($_GET['t']) && $_GET['t'] == 2){echo 'selected';}?>">Troops in Village</a>
</div>
<table id="troops" cellpadding="1" cellspacing="1">
<thead><tr><th colspan="12">Troops in <?php echo $village->vname; ?></th></tr><tr>
<?php
	$varray = $database->getProfileVillages($session->uid, 8);
?>
<td>Village</td>
<?php
	for ($i=($session->tribe-1)*10+1; $i<=($session->tribe)*10; $i++) {
		echo '<td><img class="unit u'.$i.'" src="gpack/travian_default/img/x.gif"></td>';
		$unit_total['u'.$i] = 0;
	}
	echo '<td><img class="unit uhero" src="gpack/travian_default/img/x.gif"></td>';
?>
</tr></thead><tbody>
<?php
	foreach($varray as $vil) {
		$vid = $vil['wref'];
		if($vid == $village->wid){$class = 'hl';}else{$class = '';}

		$units = $database->getUnit($vid);

		echo '<tr class="'.$class.'"><td class="vil fc"><a href="dorf1.php?newdid='.$vid.'">'.$vil['name'].'</a></td>';
		for ($i=($session->tribe-1)*10+1; $i<=($session->tribe)*10; $i++) {
			$cnt = isset($units['u'.$i]) ? $units['u'.$i] : 0;
			$unit_total['u'.$i] += $cnt;
			if($cnt != 0){$cl = '';}else{$cl = 'none';}
			echo '<td class="'.$cl.'">'.$cnt.'</td>';
		}
		$hero = isset($units['hero']) ? $units['hero'] : 0;
		if (!isset($unit_total['hero'])) {
			$unit_total['hero'] = 0;
		}
		$unit_total['hero'] += $hero;
		if($hero != 0){$cl = '';}else{$cl = 'none';}
		echo '<td class="'.$cl.'">'.$hero.'</td>';
		echo '</tr>';
	}
?>

<tr>
<th>Sum</th>
<?php
	for ($i=($session->tribe-1)*10+1; $i<=($session->tribe)*10; $i++) {
		if($unit_total['u'.$i] != 0){$cl = '';}else{$cl = 'none';}
		echo '<td class="'.$cl.'">'.$unit_total['u'.$i].'</td>';
	}
	if($unit_total['hero'] != 0){$cl = '';}else{$cl = 'none';}
	echo '<td class="'.$cl.'">'.$unit_total['hero'].'</td>';
?>
</tr></tbody></table>
