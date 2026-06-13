<?php
$base = __DIR__;
$artifactsSum = $database->getArtifactsSumByKind($session->uid, $village->wid, 6);
$GreatGranaryWarehouseBuildable = $artifactsSum['small'] > 0 || $artifactsSum['great'] > 0;

$wall = $village->resarray['f40'];
$wall1 = $database->getBuildingByField2($village->wid,40);
$shown = [];

$tiers = ['available' => [], 'soon' => [], 'all' => []];
foreach (range(1, 50) as $bgid) {
	$tier = $building->getBuildTier($bgid);
	if ($tier !== null) $tiers[$tier][] = $bgid;
}
?>
<div id="build" class="gid0"><h1><?php echo CONSTRUCT_NEW_BUILDING;?></h1>
<?php
// === MAIN BUILDING ===
if (in_array(15, $tiers['available'] ?? []) && $id != 39 && $id != 40) {
	include $base."/avaliable/15.tpl";
	$shown[15] = true;
}
// === CRANNY ===
if (in_array(23, $tiers['available'] ?? []) && $id != 39 && $id != 40 && !isset($shown[23])) {
	include $base."/avaliable/23.tpl";
	$shown[23] = true;
}
// === GRANARY / WAREHOUSE ===
foreach ([11, 10] as $bgid) {
	if (in_array($bgid, $tiers['available'] ?? []) && $id != 39 && $id != 40 && !isset($shown[$bgid])) {
		include $base."/avaliable/".$bgid.".tpl";
		$shown[$bgid] = true;
	}
}
// === WALLS ===
if ($wall == 0 && $wall1 == 0) {
	$wallGids = [31,32,33,42,43,47,50];
	foreach ($wallGids as $wgid) {
		if (in_array($wgid, $tiers['available'] ?? []) && $id == 40 && !isset($shown[$wgid])) {
			include $base."/avaliable/".$wgid.".tpl";
			$shown[$wgid] = true;
		}
	}
}
// === RALLY POINT ===
if (in_array(16, $tiers['available'] ?? []) && $id == 39 && !isset($shown[16])) {
	include $base."/avaliable/16.tpl";
	$shown[16] = true;
}
// === GREAT WAREHOUSE / GREAT GRANARY ===
foreach ([38, 39] as $bgid) {
	if (in_array($bgid, $tiers['available'] ?? []) && $id != 39 && $id != 40 && !isset($shown[$bgid])
		&& ($GreatGranaryWarehouseBuildable || $village->natar == 1)) {
		include $base."/avaliable/".$bgid.".tpl";
		$shown[$bgid] = true;
	}
}
// === OTHER AVAILABLE BUILDINGS ===
foreach ($tiers['available'] ?? [] as $bgid) {
	if (isset($shown[$bgid])) continue;
	if ($bgid == 16 || $bgid == 15 || $bgid == 23 || $bgid == 10 || $bgid == 11) continue;
	if (in_array($bgid, [31,32,33,42,43,47,50,38,39])) continue;
	if ($id == 39 || $id == 40) continue;
	$avFile = $base."/avaliable/".$bgid.".tpl";
	if (!file_exists($avFile)) continue;
	include $avFile;
	$shown[$bgid] = true;
}

// === SOON SECTION ===
$soonDir = $base."/soon";
$soonList = array_filter($tiers['soon'] ?? [], function($bgid) use ($shown, $id, $GreatGranaryWarehouseBuildable, $village, $soonDir) {
	if (isset($shown[$bgid])) return false;
	if (in_array($bgid, [31,32,33,42,43,47,50]) && $id != 40) return false;
	if ($bgid == 16 && $id != 39) return false;
	if (!in_array($bgid, [16,31,32,33,42,43,47,50]) && ($id == 39 || $id == 40)) return false;
	if (in_array($bgid, [38, 39]) && !$GreatGranaryWarehouseBuildable && $village->natar != 1) return false;
	return file_exists($soonDir."/".$bgid.".tpl");
});

$allList = array_filter($tiers['all'] ?? [], function($bgid) use ($shown, $id, $GreatGranaryWarehouseBuildable, $village, $soonDir) {
	if (isset($shown[$bgid])) return false;
	if (in_array($bgid, [31,32,33,42,43,47,50]) && $id != 40) return false;
	if ($bgid == 16 && $id != 39) return false;
	if (!in_array($bgid, [16,31,32,33,42,43,47,50]) && ($id == 39 || $id == 40)) return false;
	if (in_array($bgid, [38, 39]) && !$GreatGranaryWarehouseBuildable && $village->natar != 1) return false;
	return file_exists($soonDir."/".$bgid.".tpl");
});

if (!empty($soonList) && $id != 39 && $id != 40) { ?>
<p class="switch"><a id="soon_link" href="javascript:show_build_list('soon');"><?php echo SHOWSOON_AVAILABLE_BUILDINGS;?></a></p>
<div id="build_list_soon" class="hide">
<?php foreach ($soonList as $bgid) {
	include $soonDir."/".$bgid.".tpl";
} ?>
</div>
<?php } ?>

<?php if (!empty($allList) && $id != 39 && $id != 40) { ?>
<p class="switch"><a id="all_link" class="<?php echo empty($soonList) ? '' : 'hide'; ?>" href="javascript:show_build_list('all');"><?php echo SHOW_MORE;?></a></p>
<div id="build_list_all" class="hide">
<?php foreach ($allList as $bgid) {
	include_once $soonDir."/".$bgid.".tpl";
} ?>
</div>
<?php } ?>

<script type="text/javascript">
function show_build_list(list) {
	var build_list = document.getElementById('build_list_'+list);
	var link = document.getElementById(list+'_link');
	var all_link = document.getElementById('all_link');
	var soon_link = document.getElementById('soon_link');
	var build_list_all = document.getElementById('build_list_all');
	var build_list_soon = document.getElementById('build_list_soon');

	if (build_list.className == 'hide') {
		build_list.className = '';
		if (link == soon_link) {
			link.innerHTML = '<?php echo HIDESOON_AVAILABLE_BUILDINGS;?>';
			if (all_link !== null) all_link.className = '';
		} else {
			link.innerHTML = '<?php echo HIDE_MORE;?>';
		}
	} else {
		build_list.className = 'hide';
		if (link == soon_link) {
			link.innerHTML = '<?php echo SHOWSOON_AVAILABLE_BUILDINGS;?>';
			if (all_link !== null) {
				all_link.innerHTML = 'show more';
				all_link.className = 'hide';
				build_list_all.className = 'hide';
			}
		} else {
			link.innerHTML = '<?php echo SHOW_MORE;?>';
		}
	}
}
</script>
</div>
