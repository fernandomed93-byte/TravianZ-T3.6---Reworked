<?php
$artifactsSum = $database->getArtifactsSumByKind($session->uid, $village->wid, 6);
$GreatGranaryWarehouseBuildable = $artifactsSum['small'] > 0 || $artifactsSum['great'] > 0;

$mainbuilding = $building->getTypeLevel(15);
$cranny = $building->getTypeLevel(23);
$granary = $building->getTypeLevel(11);
$warehouse = $building->getTypeLevel(10);
$embassy = $building->getTypeLevel(18);
$wall = $village->resarray['f40'];
$rallypoint = $building->getTypeLevel(16);
$hero = $building->getTypeLevel(37);
$market = $building->getTypeLevel(17);
$barrack = $building->getTypeLevel(19);
$cropland = $building->getTypeLevel(4);
$grainmill = $building->getTypeLevel(8);
$residence = $building->getTypeLevel(25);
$academy = $building->getTypeLevel(22);
$armoury = $building->getTypeLevel(13);
$woodcutter = $building->getTypeLevel(1);
$palace = $building->getTypeLevel(26);
$claypit = $building->getTypeLevel(2);
$ironmine = $building->getTypeLevel(3);
$blacksmith = $building->getTypeLevel(12);
$stable = $building->getTypeLevel(20);
$trapper = $building->getTypeLevel(36);
$treasury = $building->getTypeLevel(27);
$sawmill = $building->getTypeLevel(5);
$brickyard = $building->getTypeLevel(6);
$ironfoundry = $building->getTypeLevel(7);
$workshop = $building->getTypeLevel(21);
$stonemasonslodge = $building->getTypeLevel(34);
$townhall = $building->getTypeLevel(24);
$tournamentsquare = $building->getTypeLevel(14);
$bakery = $building->getTypeLevel(9);
$tradeoffice = $building->getTypeLevel(28);
$greatbarracks = $building->getTypeLevel(29);
$greatstable = $building->getTypeLevel(30);
$brewery = $building->getTypeLevel(35);
$horsedrinkingtrough = $building->getTypeLevel(41);
$herosmansion = $building->getTypeLevel(37);
$greatwarehouse = $building->getTypeLevel(38);
$greatgranary = $building->getTypeLevel(39);
$greatworkshop = $building->getTypeLevel(42);
$stonewall = $building->getTypeLevel(42);
$makeshiftwall = $building->getTypeLevel(43);
$commandcenter = $building->getTypeLevel(44);
$waterworks = $building->getTypeLevel(45);
$hospital = $building->getTypeLevel(46);
$defensivewall = $building->getTypeLevel(47);
$bighospital = $building->getTypeLevel(48);
$barricade = $building->getTypeLevel(50);

$typesArray = [];

//tipos de edificios
for ($i = 1; $i <= 50; $i++) {
    $typesArray[] = $i;
}

global $typeCounts;
$typeCounts = $database->getBuildingByType2($village->wid, $typesArray);

function getTypeCount($id) {
    global $typeCounts;

    return (isset($typeCounts[$id]) ? $typeCounts[$id] : 0);
}

$mainbuilding1 = getTypeCount(15);
$cranny1 = getTypeCount(23);
$granary1 = getTypeCount(11);
$warehouse1 = getTypeCount(10);
$embassy1 = getTypeCount(18);
$wall1 = $database->getBuildingByField2($village->wid,40);
$rallypoint1 = getTypeCount(16);
$hero1 = getTypeCount(37);
$market1 = getTypeCount(17);
$barrack1 = getTypeCount(19);
$cropland1 = getTypeCount(4);
$grainmill1 = getTypeCount(8);
$residence1 = getTypeCount(25);
$academy1 = getTypeCount(22);
$armoury1 = getTypeCount(13);
$woodcutter1 = getTypeCount(1);
$palace1 = getTypeCount(26);
$claypit1 = getTypeCount(2);
$ironmine1 = getTypeCount(3);
$blacksmith1 = getTypeCount(12);
$stable1 = getTypeCount(20);
$trapper1 = getTypeCount(36);
$treasury1 = getTypeCount(27);
$sawmill1 = getTypeCount(5);
$brickyard1 = getTypeCount(6);
$ironfoundry1 = getTypeCount(7);
$workshop1 = getTypeCount(21);
$stonemasonslodge1 = getTypeCount(34);
$townhall1 = getTypeCount(24);
$tournamentsquare1 = getTypeCount(14);
$bakery1 = getTypeCount(9);
$tradeoffice1 = getTypeCount(28);
$greatbarracks1 = getTypeCount(29);
$greatstable1 = getTypeCount(30);
$brewery1 = getTypeCount(35);
$horsedrinkingtrough1 = getTypeCount(41);
$herosmansion1 = getTypeCount(37);
$greatwarehouse1 = getTypeCount(38);
$greatgranary1 = getTypeCount(39);
$greatworkshop1 = getTypeCount(42);
$stonewall1 = getTypeCount(42);
$makeshiftwall1 = getTypeCount(43);
$commandcenter1 = getTypeCount(44);
$waterworks1 = getTypeCount(45);
$hospital1 = getTypeCount(46);
$defensivewall1 = getTypeCount(47);
$bighospital1 = getTypeCount(48);
$barricade1 = getTypeCount(50);
$shown = [];
?>
<div id="build" class="gid0"><h1><?php echo CONSTRUCT_NEW_BUILDING;?></h1>
<?php
if($mainbuilding == 0 && $mainbuilding1 == 0 && $id != 39 && $id != 40) {
    include("avaliable/mainbuilding.tpl");
    $shown['mainbuilding'] = true;
}
if((($cranny == 0 && $cranny1 == 0) || $cranny == 10) && $mainbuilding >= 1 && $id != 39 && $id != 40) {
    include("avaliable/cranny.tpl");
    $shown['cranny'] = true;
}
if((($granary == 0 && $granary1 == 0) || $granary == 20) && $mainbuilding >= 1 && $id != 39 && $id != 40 ) {
    include("avaliable/granary.tpl");
    $shown['granary'] = true;
}
if($wall == 0 && $wall1 == 0) {
    if($session->tribe == 1 && $id == 40) {
        include("avaliable/citywall.tpl");
        $shown['citywall'] = true;
    }
    if($session->tribe == 2 && $id == 40) {
        include("avaliable/earthwall.tpl");
        $shown['earthwall'] = true;
    }
    if($session->tribe == 3 && $id == 40) {
        include("avaliable/palisade.tpl");
        $shown['palisade'] = true;
    }
    if($session->tribe == 4 && $id == 40) {
        include("avaliable/earthwall.tpl");
        $shown['earthwall'] = true;
    }
    if($session->tribe == 5 && $id == 40) {
        include("avaliable/citywall.tpl");
        $shown['citywall'] = true;
    }
    if($session->tribe == 6 && $id == 40) {
        include("avaliable/makeshiftwall.tpl");
        $shown['makeshiftwall'] = true;
    }
    if($session->tribe == 7 && $id == 40) {
        include("avaliable/stonewall.tpl");
        $shown['stonewall'] = true;
    }
    if($session->tribe == 8 && $id == 40) {
        include("avaliable/defensivewall.tpl");
        $shown['defensivewall'] = true;
    }
    if($session->tribe == 9 && $id == 40) {
        include("avaliable/barricade.tpl");
        $shown['barricade'] = true;
    }
}
if((($warehouse == 0 && $warehouse1 == 0) || $warehouse == 20) && $mainbuilding >= 1 && $id != 39 && $id != 40) {
    include("avaliable/warehouse.tpl");
    $shown['warehouse'] = true;
}
if((($greatwarehouse == 0 && $greatwarehouse1 == 0) || $greatwarehouse == 20) && $mainbuilding >= 10 && ($GreatGranaryWarehouseBuildable || $village->natar == 1) && ($id != 39 && $id != 40)) {
    include("avaliable/greatwarehouse.tpl");
    $shown['greatwarehouse'] = true;
}
if((($greatgranary == 0 && $greatgranary1 == 0) || $greatgranary == 20) && $mainbuilding >= 10 && ($GreatGranaryWarehouseBuildable  || $village->natar == 1) && ($id != 39 && $id != 40)) {
    include("avaliable/greatgranary.tpl");
    $shown['greatgranary'] = true;
}
if((($trapper == 0 && $trapper1 == 0) || $trapper == 20) && $rallypoint >= 1 && $session->tribe == 3 && $id != 39 && $id != 40) {
    include("avaliable/trapper.tpl");
    $shown['trapper'] = true;
}
if($rallypoint == 0 && $rallypoint1 == 0 && $id == 39) {
    include("avaliable/rallypoint.tpl");
    $shown['rallypoint'] = true;
}
if($embassy == 0 && $embassy1 == 0 && $id != 39 && $id != 40 && $mainbuilding >= 1) {
    include("avaliable/embassy.tpl");
    $shown['embassy'] = true;
}
//fix hero
if($hero == 0 && $hero1 == 0 && $mainbuilding >= 3 && $rallypoint >= 1 && $id != 39 && $id != 40) {
    include("avaliable/hero.tpl");
    $shown['hero'] = true;
}
//fix barracks
if($rallypoint >= 1 && $mainbuilding >= 3 && $barrack == 0 && $barrack1 == 0 && $id != 39 && $id != 40) {
    include("avaliable/barracks.tpl");
    $shown['barracks'] = true;
}
if($mainbuilding >= 3 && $academy >= 1 && $armoury == 0 && $armoury1 == 0 && $id != 39 && $id != 40) {
    include("avaliable/armoury.tpl");
    $shown['armoury'] = true;
}
if($cropland >= 5 && $grainmill == 0 && $grainmill1 == 0 && $id != 39 && $id != 40) {
    include("avaliable/grainmill.tpl");
    $shown['grainmill'] = true;
}
//fix marketplace
if($granary >= 1 && $warehouse >= 1 && $mainbuilding >= 3 && $market == 0 && $market1 == 0 && $id != 39 && $id != 40) {
    include("avaliable/marketplace.tpl");
    $shown['marketplace'] = true;
}
if($mainbuilding >= 5 && $residence == 0 && $residence1 == 0 && $id != 39 && $id != 40 && $palace == 0 && $palace1 == 0 && $commandcenter == 0 && $commandcenter1 == 0) {
    include("avaliable/residence.tpl");
    $shown['residence'] = true;
}
if($academy == 0 && $academy1 == 0 && $mainbuilding >= 3 && $barrack >= 3 && $id != 39 && $id != 40) {
    include("avaliable/academy.tpl");
    $shown['academy'] = true;
}
if($palace == 0 && $palace1 == 0 && !$building->isCastleBuilt() && $village->natar == 0 && $embassy >= 1 && $mainbuilding >= 5 && $id != 39 && $id != 40 && $residence == 0 && $residence1 == 0 && $commandcenter == 0 && $commandcenter1 == 0) {
    include("avaliable/palace.tpl");
    $shown['palace'] = true;
}
if($blacksmith == 0 && $blacksmith1 == 0 && $academy >= 3 && $mainbuilding >= 3 && $id != 39 && $id != 40) {
    include("avaliable/blacksmith.tpl");
    $shown['blacksmith'] = true;
}
if($stonemasonslodge == 0 && $stonemasonslodge1 == 0 && $palace >= 3 && $mainbuilding >= 5 && $id != 39 && $id != 40) {
    include("avaliable/stonemason.tpl");
    $shown['stonemason'] = true;
}
if($stable == 0 && $stable1 == 0 && $blacksmith >= 3 && $academy >= 5 && $id != 39 && $id != 40) {
    include("avaliable/stable.tpl");
    $shown['stable'] = true;
}
if($treasury == 0 && $treasury1 == 0 && $village->natar == 0 && $mainbuilding >= 10 && $id != 39 && $id != 40) {
    include("avaliable/treasury.tpl");
    $shown['treasury'] = true;
}
if($brickyard == 0 && $brickyard1 == 0 && $claypit >= 10 && $mainbuilding >= 5 && $id != 39 && $id != 40 ) {
    include("avaliable/brickyard.tpl");
    $shown['brickyard'] = true;
}
if($sawmill == 0 && $sawmill1 == 0 && $woodcutter >= 10 && $mainbuilding >= 5 && $id != 39 && $id != 40) {
    include("avaliable/sawmill.tpl");
    $shown['sawmill'] = true;
}
if($ironfoundry == 0 && $ironfoundry1 == 0 && $ironmine >= 10 && $mainbuilding >= 5 && $id != 39 && $id != 40) {
    include("avaliable/ironfoundry.tpl");
    $shown['ironfoundry'] = true;
}
if($workshop == 0 && $workshop1 == 0 && $academy >= 10 && $mainbuilding >= 5 && $id != 39 && $id != 40) {
    include("avaliable/workshop.tpl");
    $shown['workshop'] = true;
}
if($tournamentsquare == 0 && $tournamentsquare1 == 0 && $rallypoint >= 15 && $id != 39 && $id != 40) {
    include("avaliable/tsquare.tpl");
    $shown['tsquare'] = true;
}
if($bakery == 0 && $bakery1 == 0 && $grainmill >= 5 && $cropland >= 10 && $mainbuilding >= 5 && $id != 39 && $id != 40) {
    include("avaliable/bakery.tpl");
    $shown['bakery'] = true;
}
if($townhall == 0 && $townhall1 == 0 && $mainbuilding >= 10 && $academy >= 10 && $id != 39 && $id != 40) {
    include("avaliable/townhall.tpl");
    $shown['townhall'] = true;
}
if($tradeoffice == 0 && $tradeoffice1 == 0 && $market == 20 && $stable >= 10 && $id != 39 && $id != 40) {
    include("avaliable/tradeoffice.tpl");
    $shown['tradeoffice'] = true;
}
if($session->tribe == 1 && $horsedrinkingtrough == 0 && $horsedrinkingtrough1 == 0 && $rallypoint >= 10 && $stable == 20 && $id != 39 && $id != 40) {
    include("avaliable/horsedrinking.tpl");
    $shown['horsedrinking'] = true;
}
if($session->tribe == 2 && $village->capital == 1 && $brewery == 0 && $brewery1 == 0 && $rallypoint >= 10 && $granary == 20 && $id != 39 && $id != 40) {
    include("avaliable/brewery.tpl");
    $shown['brewery'] = true;
}
if($greatbarracks == 0 && $greatbarracks1 == 0 && $barrack == 20 && $village->capital == 0 && $id != 39 && $id != 40) {
    include("avaliable/greatbarracks.tpl");
    $shown['greatbarracks'] = true;
}
if($greatstable == 0 && $greatstable1 == 0 && $stable == 20 && $village->capital == 0 && $id != 39 && $id != 40) {
    include("avaliable/greatstable.tpl");
    $shown['greatstable'] = true;
}
if($session->tribe == 6 && $commandcenter == 0 && $commandcenter1 == 0 && $mainbuilding >= 5 && !$building->isCastleBuilt() && $id != 39 && $id != 40 && $residence == 0 && $residence1 == 0 && $palace == 0 && $palace1 == 0) {
    include("avaliable/commandcenter.tpl");
    $shown['commandcenter'] = true;
}
if($session->tribe == 7 && $waterworks == 0 && $waterworks1 == 0 && $building->getTypeLevel(37) >= 10 && $id != 39 && $id != 40) {
    include("avaliable/waterworks.tpl");
    $shown['waterworks'] = true;
}
if($hospital == 0 && $hospital1 == 0 && $mainbuilding >= 10 && $academy >= 15 && $id != 39 && $id != 40 && $bighospital == 0 && $bighospital1 == 0) {
    include("avaliable/hospital.tpl");
    $shown['hospital'] = true;
}
if($bighospital == 0 && $bighospital1 == 0 && $rallypoint >= 10 && $stable == 20 && ($session->tribe == 8 || $session->tribe == 9) && $id != 39 && $id != 40 && $hospital == 0 && $hospital1 == 0) {
    include("avaliable/bighospital.tpl");
    $shown['bighospital'] = true;
}
if($greatworkshop == 0 && $greatworkshop1 == 0 && $workshop == 20 && $village->capital == 0 && $id != 39 && $id != 40 && GREAT_WKS) {
    include("avaliable/greatworkshop.tpl");
    $shown['greatworkshop'] = true;
}
if($id != 39 && $id != 40) {

$soon_count = 0;
if($rallypoint == 0 && $session->tribe == 3 && $trapper == 0 && !($shown['trapper'] ?? false)) $soon_count++;
if($mainbuilding < 10 && $warehouse < 10 && $village->capital == 0 && $GreatGranaryWarehouseBuildable && !($shown['greatwarehouse'] ?? false)) $soon_count++;
if($mainbuilding < 10 && $granary < 10 && $village->capital == 0 && $GreatGranaryWarehouseBuildable && !($shown['greatgranary'] ?? false)) $soon_count++;
if($hero == 0 && ($mainbuilding <= 2 || $rallypoint == 0) && !($shown['hero'] ?? false)) $soon_count++;
if($barrack == 0 && ($rallypoint == 0 || $mainbuilding <= 2) && !($shown['barracks'] ?? false)) $soon_count++;
if($armoury == 0 && ($mainbuilding <= 2 || $academy == 0) && !($shown['armoury'] ?? false)) $soon_count++;
if($cropland <= 4 && !($shown['grainmill'] ?? false)) $soon_count++;
if($market == 0 && ($mainbuilding <= 2 || $granary <= 0 || $warehouse <= 0) && !($shown['marketplace'] ?? false)) $soon_count++;
if($residence == 0 && $mainbuilding <= 4 && !($shown['residence'] ?? false)) $soon_count++;
if($academy == 0 && ($mainbuilding <= 2 || $barrack <= 2) && !($mainbuilding == 1 || $barrack == 0) && !($shown['academy'] ?? false)) $soon_count++;
if(($embassy == 0 || $mainbuilding >= 2 && $mainbuilding <= 4 && !$building->isCastleBuilt() && $village->natar == 0) && !(($embassy == 0 || $mainbuilding <= 2) && $village->natar == 0) && !($shown['palace'] ?? false)) $soon_count++;
if($blacksmith == 0 && ($academy <= 2 || $mainbuilding <= 2) && !($academy == 0 || $mainbuilding == 1) && !($shown['blacksmith'] ?? false)) $soon_count++;
if($stonemasonslodge == 0 && $palace <= 2 && $palace != 0 && $mainbuilding >= 2 && $mainbuilding <= 4 && $residence == 0 && $village->capital == 1 && !(($palace == 0 || $mainbuilding <= 2) && $residence == 0) && !($shown['stonemason'] ?? false)) $soon_count++;
if($stable == 0 && (($blacksmith <= 2 && $blacksmith != 0) || ($academy >= 2 && $academy <= 4)) && !($blacksmith == 0 || $academy <= 2) && !($shown['stable'] ?? false)) $soon_count++;
if($treasury == 0 && $mainbuilding <= 9 && $mainbuilding >= 5 && $village->natar == 0 && !($mainbuilding <= 5) && !($shown['treasury'] ?? false)) $soon_count++;
if($brickyard == 0 && ( ($claypit <= 9 && $claypit >= 5) || $claypit == 10 ) && $mainbuilding >= 2 && !($claypit <= 5 || $mainbuilding <= 2) && !($shown['brickyard'] ?? false)) $soon_count++;
if($sawmill == 0 && ( ( $woodcutter <= 9 && $woodcutter >= 5 ) || $woodcutter == 10 ) && $mainbuilding >= 2 && !($woodcutter <= 5 || $mainbuilding <= 2) && !($shown['sawmill'] ?? false)) $soon_count++;
if($ironfoundry == 0 && ( ( $ironmine <= 9 && $ironmine >= 5 ) || $ironmine == 10 ) && $mainbuilding >= 2 && !($ironmine <= 5 || $mainbuilding <= 2) && !($shown['ironfoundry'] ?? false)) $soon_count++;
if($workshop == 0 && $academy <= 9 && $academy >= 5 && $mainbuilding >= 2 && !($academy <= 5 || $mainbuilding <= 2) && !($shown['workshop'] ?? false)) $soon_count++;
if($tournamentsquare == 0 && $rallypoint <= 14 && $rallypoint >= 7 && !($rallypoint <= 7) && !($shown['tsquare'] ?? false)) $soon_count++;
if($bakery == 0 && ($grainmill >= 1 && $cropland >= 5 || $mainbuilding >= 2 && ($grainmill >= 1 || $cropland >= 5)) && !($grainmill == 0 || $cropland <= 5 || $mainbuilding <= 2) && !($shown['bakery'] ?? false)) $soon_count++;
if(($townhall == 0 && ($mainbuilding <= 9 && $mainbuilding >= 5) || ($academy >= 5 && $academy <= 9)) && !($mainbuilding <= 5 || $academy <= 5) && !($shown['townhall'] ?? false)) $soon_count++;
if(($tradeoffice == 0 && $market <= 19 && $market >= 10 || $stable >= 5 && $stable <= 9) && !($market <= 10 || $stable <= 5) && !($shown['tradeoffice'] ?? false)) $soon_count++;
if(($session->tribe == 1 && $horsedrinkingtrough == 0 && $rallypoint <= 9 && $rallypoint >= 5 || $stable <= 19 && $stable >= 10 && $session->tribe == 1) && !($rallypoint <= 5 || $stable <= 10) && !($shown['horsedrinking'] ?? false)) $soon_count++;
if(($brewery == 0 && $village->capital == 1 && $rallypoint <= 9 && $rallypoint >= 5 || $granary <= 19 && $granary >= 10 && $session->tribe == 2) && !($rallypoint <= 5 || $granary <= 10) && !($shown['brewery'] ?? false)) $soon_count++;
if($greatbarracks == 0 && $barrack >= 18 && $village->capital == 0 && !($barrack >= 15) && !($shown['greatbarracks'] ?? false)) $soon_count++;
if($greatstable == 0 && $stable >= 18 && $village->capital == 0 && !($stable >= 15) && !($shown['greatstable'] ?? false)) $soon_count++;
if($greatworkshop == 0 && $workshop >= 18 && $village->capital == 0 && GREAT_WKS && !($workshop >= 15) && !($shown['greatworkshop'] ?? false)) $soon_count++;
if($session->tribe == 6 && $commandcenter == 0 && $mainbuilding >= 3 && $mainbuilding <= 4 && !$building->isCastleBuilt() && $residence == 0 && $palace == 0 && !($shown['commandcenter'] ?? false)) $soon_count++;
if($session->tribe == 7 && $waterworks == 0 && $hero >= 7 && $hero <= 9 && !($shown['waterworks'] ?? false)) $soon_count++;
if($hospital == 0 && $mainbuilding >= 5 && $academy >= 8 && !($mainbuilding >= 10 && $academy >= 15) && $bighospital == 0 && !($shown['hospital'] ?? false)) $soon_count++;
if($bighospital == 0 && $rallypoint >= 5 && $stable >= 15 && ($session->tribe == 8 || $session->tribe == 9) && !($rallypoint >= 10 && $stable == 20) && $hospital == 0 && !($shown['bighospital'] ?? false)) $soon_count++;

$all_count = 0;
if($academy == 0 && ($mainbuilding == 1 || $barrack == 0) && !($shown['academy'] ?? false)) $all_count++;
if($palace == 0 && ($embassy == 0 || $mainbuilding <= 2) && $village->natar == 0 && !($shown['palace'] ?? false)) $all_count++;
if($blacksmith == 0 && ($academy == 0 || $mainbuilding == 1) && !($shown['blacksmith'] ?? false)) $all_count++;
if($stonemasonslodge == 0 && ($palace == 0 || $mainbuilding <= 2) && $residence == 0 && !($shown['stonemason'] ?? false)) $all_count++;
if($stable == 0 && ($blacksmith == 0 || $academy <= 2) && !($shown['stable'] ?? false)) $all_count++;
if($treasury == 0 && $mainbuilding <= 5 && !($shown['treasury'] ?? false)) $all_count++;
if($brickyard == 0 && ($claypit <= 5 || $mainbuilding <= 2) && !($shown['brickyard'] ?? false)) $all_count++;
if($sawmill == 0 && ($woodcutter <= 5 || $mainbuilding <= 2) && !($shown['sawmill'] ?? false)) $all_count++;
if($ironfoundry == 0 && ($ironmine <= 5 || $mainbuilding <= 2) && !($shown['ironfoundry'] ?? false)) $all_count++;
if($workshop == 0 && ($academy <= 5 || $mainbuilding <= 2) && !($shown['workshop'] ?? false)) $all_count++;
if($tournamentsquare == 0 && $rallypoint <= 7 && !($shown['tsquare'] ?? false)) $all_count++;
if($bakery == 0 && ($grainmill == 0 || $cropland <= 5 || $mainbuilding <= 2) && !($shown['bakery'] ?? false)) $all_count++;
if($townhall == 0 && ($mainbuilding <= 5 || $academy <= 5) && !($shown['townhall'] ?? false)) $all_count++;
if($tradeoffice == 0 && ($market <= 10 || $stable <= 5) && !($shown['tradeoffice'] ?? false)) $all_count++;
if($session->tribe == 1 && $horsedrinkingtrough == 0 && ($rallypoint <= 5 || $stable <= 10) && !($shown['horsedrinking'] ?? false)) $all_count++;
if($brewery == 0 && ($rallypoint <= 5 || $granary <= 10) && $session->tribe == 2 && $village->capital == 1 && !($shown['brewery'] ?? false)) $all_count++;
if($greatbarracks == 0 && $barrack >= 15 && $village->capital == 0 && !($shown['greatbarracks'] ?? false)) $all_count++;
if($greatstable == 0 && $stable >= 15 && $village->capital == 0 && !($shown['greatstable'] ?? false)) $all_count++;
if($greatworkshop == 0 && $workshop >= 15 && $village->capital == 0 && GREAT_WKS && !($shown['greatworkshop'] ?? false)) $all_count++;
if($session->tribe == 6 && $commandcenter == 0 && $mainbuilding <= 2 && !($shown['commandcenter'] ?? false)) $all_count++;
if($session->tribe == 7 && $waterworks == 0 && $hero <= 6 && !($shown['waterworks'] ?? false)) $all_count++;
if($hospital == 0 && ($mainbuilding < 5 || $academy < 8) && $bighospital == 0 && !($shown['hospital'] ?? false)) $all_count++;
if($bighospital == 0 && ($rallypoint < 5 || $stable < 15) && ($session->tribe == 8 || $session->tribe == 9) && $hospital == 0 && !($shown['bighospital'] ?? false)) $all_count++;

if($soon_count > 0) {
?>
<p class="switch"><a id="soon_link" href="javascript:show_build_list('soon');"><?php echo SHOWSOON_AVAILABLE_BUILDINGS;?></a></p>

<div id="build_list_soon" class="hide">
<?php
if($rallypoint == 0 && $session->tribe == 3 && $trapper == 0 && !($shown['trapper'] ?? false)) {
include("soon/trapper.tpl");
}
if($mainbuilding < 10 && $warehouse < 10 && $village->capital == 0 && $GreatGranaryWarehouseBuildable && !($shown['greatwarehouse'] ?? false)) {
    include("soon/greatwarehouse.tpl");
}
if($mainbuilding < 10 && $granary < 10 && $village->capital == 0 && $GreatGranaryWarehouseBuildable && !($shown['greatgranary'] ?? false)) {
    include("soon/greatgranary.tpl");
}
if($hero == 0 && ($mainbuilding <= 2 || $rallypoint == 0) && !($shown['hero'] ?? false)){
    include("soon/hero.tpl");
}
if($barrack == 0 && ($rallypoint == 0 || $mainbuilding <= 2) && !($shown['barracks'] ?? false)) {
    include("soon/barracks.tpl");
}
if($armoury == 0 && ($mainbuilding <= 2 || $academy == 0) && !($shown['armoury'] ?? false)) {
    include("soon/armoury.tpl");
}
if($cropland <= 4 && !($shown['grainmill'] ?? false)) {
    include("soon/grainmill.tpl");
}
if($market == 0 && ($mainbuilding <= 2 || $granary <= 0 || $warehouse <= 0) && !($shown['marketplace'] ?? false)) {
    include("soon/marketplace.tpl");
}
if($residence == 0 && $mainbuilding <= 4 && !($shown['residence'] ?? false)) {
    include("soon/residence.tpl");
}
if($academy == 0 && ($mainbuilding <= 2 || $barrack <= 2) && !($mainbuilding == 1 || $barrack == 0) && !($shown['academy'] ?? false)) {
    include("soon/academy.tpl");
}
if(($embassy == 0 || $mainbuilding >= 2 && $mainbuilding <= 4 && !$building->isCastleBuilt() && $village->natar == 0) && !(($embassy == 0 || $mainbuilding <= 2) && $village->natar == 0) && !($shown['palace'] ?? false)) {
    include("soon/palace.tpl");
}
if($blacksmith == 0 && ($academy <= 2 || $mainbuilding <= 2) && !($academy == 0 || $mainbuilding == 1) && !($shown['blacksmith'] ?? false)) {
    include("soon/blacksmith.tpl");
}
if($stonemasonslodge == 0 && $palace <= 2 && $palace != 0 && $mainbuilding >= 2 && $mainbuilding <= 4 && $residence == 0 && $village->capital == 1 && !(($palace == 0 || $mainbuilding <= 2) && $residence == 0) && !($shown['stonemason'] ?? false)) {
    include("soon/stonemason.tpl");
}
if($stable == 0 && (($blacksmith <= 2 && $blacksmith != 0) || ($academy >= 2 && $academy <= 4)) && !($blacksmith == 0 || $academy <= 2) && !($shown['stable'] ?? false)) {
    include("soon/stable.tpl");
}
if($treasury == 0 && $mainbuilding <= 9 && $mainbuilding >= 5 && $village->natar == 0 && !($mainbuilding <= 5) && !($shown['treasury'] ?? false)) {
    include("soon/treasury.tpl");
}
if($brickyard == 0 && ( ($claypit <= 9 && $claypit >= 5) || $claypit == 10 ) && $mainbuilding >= 2 && !($claypit <= 5 || $mainbuilding <= 2) && !($shown['brickyard'] ?? false)) {
    include("soon/brickyard.tpl");
}
if($sawmill == 0 && ( ( $woodcutter <= 9 && $woodcutter >= 5 ) || $woodcutter == 10 ) && $mainbuilding >= 2 && !($woodcutter <= 5 || $mainbuilding <= 2) && !($shown['sawmill'] ?? false)) {
    include("soon/sawmill.tpl");
}
if($ironfoundry == 0 && ( ( $ironmine <= 9 && $ironmine >= 5 ) || $ironmine == 10 ) && $mainbuilding >= 2 && !($ironmine <= 5 || $mainbuilding <= 2) && !($shown['ironfoundry'] ?? false)) {
    include("soon/ironfoundry.tpl");
}
if($workshop == 0 && $academy <= 9 && $academy >= 5 && $mainbuilding >= 2 && !($academy <= 5 || $mainbuilding <= 2) && !($shown['workshop'] ?? false)) {
    include("soon/workshop.tpl");
}
if($tournamentsquare == 0 && $rallypoint <= 14 && $rallypoint >= 7 && !($rallypoint <= 7) && !($shown['tsquare'] ?? false)) {
    include("soon/tsquare.tpl");
}
if($bakery == 0 && ($grainmill >= 1 && $cropland >= 5 || $mainbuilding >= 2 && ($grainmill >= 1 || $cropland >= 5)) && !($grainmill == 0 || $cropland <= 5 || $mainbuilding <= 2) && !($shown['bakery'] ?? false)) {
    include("soon/bakery.tpl");
}
if(($townhall == 0 && ($mainbuilding <= 9 && $mainbuilding >= 5) || ($academy >= 5 && $academy <= 9)) && !($mainbuilding <= 5 || $academy <= 5) && !($shown['townhall'] ?? false)) {
    include("soon/townhall.tpl");
}
if(($tradeoffice == 0 && $market <= 19 && $market >= 10 || $stable >= 5 && $stable <= 9) && !($market <= 10 || $stable <= 5) && !($shown['tradeoffice'] ?? false)) {
    include("soon/tradeoffice.tpl");
}
if(($session->tribe == 1 && $horsedrinkingtrough == 0 && $rallypoint <= 9 && $rallypoint >= 5 || $stable <= 19 && $stable >= 10 && $session->tribe == 1) && !($rallypoint <= 5 || $stable <= 10) && !($shown['horsedrinking'] ?? false)) {
    include("soon/horsedrinking.tpl");
}
if(($brewery == 0 && $village->capital == 1 && $rallypoint <= 9 && $rallypoint >= 5 || $granary <= 19 && $granary >= 10 && $session->tribe == 2) && !($rallypoint <= 5 || $granary <= 10) && !($shown['brewery'] ?? false)) {
    include("soon/brewery.tpl");
}
if($greatbarracks == 0 && $barrack >= 18 && $village->capital == 0 && !($barrack >= 15) && !($shown['greatbarracks'] ?? false)) {
    include("soon/greatbarracks.tpl");
}
if($greatstable == 0 && $stable >= 18 && $village->capital == 0 && !($stable >= 15) && !($shown['greatstable'] ?? false)) {
    include("soon/greatstable.tpl");
}
if($greatworkshop == 0 && $workshop >= 18 && $village->capital == 0 && GREAT_WKS && !($workshop >= 15) && !($shown['greatworkshop'] ?? false)) {
    include("soon/greatworkshop.tpl");
}
if($session->tribe == 6 && $commandcenter == 0 && $mainbuilding >= 3 && $mainbuilding <= 4 && !$building->isCastleBuilt() && $residence == 0 && $palace == 0 && !($shown['commandcenter'] ?? false)) {
    include("soon/commandcenter.tpl");
}
if($session->tribe == 7 && $waterworks == 0 && $hero >= 7 && $hero <= 9 && !($shown['waterworks'] ?? false)) {
    include("soon/waterworks.tpl");
}
if($hospital == 0 && $mainbuilding >= 5 && $academy >= 8 && !($mainbuilding >= 10 && $academy >= 15) && $bighospital == 0 && !($shown['hospital'] ?? false)) {
    include("soon/hospital.tpl");
}
if($bighospital == 0 && $rallypoint >= 5 && $stable >= 15 && ($session->tribe == 8 || $session->tribe == 9) && !($rallypoint >= 10 && $stable == 20) && $hospital == 0 && !($shown['bighospital'] ?? false)) {
    include("soon/bighospital.tpl");
}
?>
</div>
<?php } ?>
<?php if($all_count > 0) { ?>
<p class="switch"><a id="all_link" class="hide"
href="javascript:show_build_list('all');"><?php echo SHOW_MORE;?></a></p>
<div id="build_list_all" class="hide">
<?php
if($academy == 0 && ($mainbuilding == 1 || $barrack == 0) && !($shown['academy'] ?? false)) {
    include_once("soon/academy.tpl");
}
if($palace == 0 && ($embassy == 0 || $mainbuilding <= 2) && $village->natar == 0 && !($shown['palace'] ?? false)) {
    include_once("soon/palace.tpl");
}
if($blacksmith == 0 && ($academy == 0 || $mainbuilding == 1) && !($shown['blacksmith'] ?? false)) {
    include_once("soon/blacksmith.tpl");
}
if($stonemasonslodge == 0 && ($palace == 0 || $mainbuilding <= 2) && $residence == 0 && !($shown['stonemason'] ?? false)) {
    include_once("soon/stonemason.tpl");
}
if($stable == 0 && ($blacksmith == 0 || $academy <= 2) && !($shown['stable'] ?? false)) {
    include_once("soon/stable.tpl");
}
if($treasury == 0 && $mainbuilding <= 5 && !($shown['treasury'] ?? false)) {
    include_once("soon/treasury.tpl");
}
if($brickyard == 0 && ($claypit <= 5 || $mainbuilding <= 2) && !($shown['brickyard'] ?? false)) {
    include_once("soon/brickyard.tpl");
}
if($sawmill == 0 && ($woodcutter <= 5 || $mainbuilding <= 2) && !($shown['sawmill'] ?? false)) {
    include_once("soon/sawmill.tpl");
}
if($ironfoundry == 0 && ($ironmine <= 5 || $mainbuilding <= 2) && !($shown['ironfoundry'] ?? false)) {
    include_once("soon/ironfoundry.tpl");
}
if($workshop == 0 && ($academy <= 5 || $mainbuilding <= 2) && !($shown['workshop'] ?? false)) {
    include_once("soon/workshop.tpl");
}
if($tournamentsquare == 0 && $rallypoint <= 7 && !($shown['tsquare'] ?? false)) {
    include_once("soon/tsquare.tpl");
}
if($bakery == 0 && ($grainmill == 0 || $cropland <= 5 || $mainbuilding <= 2) && !($shown['bakery'] ?? false)) {
    include_once("soon/bakery.tpl");
}
if($townhall == 0 && ($mainbuilding <= 5 || $academy <= 5) && !($shown['townhall'] ?? false)) {
    include_once("soon/townhall.tpl");
}
if($tradeoffice == 0 && ($market <= 10 || $stable <= 5) && !($shown['tradeoffice'] ?? false)) {
    include_once("soon/tradeoffice.tpl");
}
if($session->tribe == 1 && $horsedrinkingtrough == 0 && ($rallypoint <= 5 || $stable <= 10) && !($shown['horsedrinking'] ?? false)) {
    include_once("soon/horsedrinking.tpl");
}
if($brewery == 0 && ($rallypoint <= 5 || $granary <= 10) && $session->tribe == 2 && $village->capital == 1 && !($shown['brewery'] ?? false)) {
    include_once("soon/brewery.tpl");
}
if($greatbarracks == 0 && $barrack >= 15 && $village->capital == 0 && !($shown['greatbarracks'] ?? false)) {
    include_once("soon/greatbarracks.tpl");
}
if($greatstable == 0 && $stable >= 15 && $village->capital == 0 && !($shown['greatstable'] ?? false)) {
    include_once("soon/greatstable.tpl");
}
if($greatworkshop == 0 && $workshop >= 15 && $village->capital == 0 && GREAT_WKS && !($shown['greatworkshop'] ?? false)) {
    include_once("soon/greatworkshop.tpl");
}
if($session->tribe == 6 && $commandcenter == 0 && $mainbuilding <= 2 && !($shown['commandcenter'] ?? false)) {
    include_once("soon/commandcenter.tpl");
}
if($session->tribe == 7 && $waterworks == 0 && $hero <= 6 && !($shown['waterworks'] ?? false)) {
    include_once("soon/waterworks.tpl");
}
if($hospital == 0 && ($mainbuilding < 5 || $academy < 8) && $bighospital == 0 && !($shown['hospital'] ?? false)) {
    include_once("soon/hospital.tpl");
}
if($bighospital == 0 && ($rallypoint < 5 || $stable < 15) && ($session->tribe == 8 || $session->tribe == 9) && $hospital == 0 && !($shown['bighospital'] ?? false)) {
    include_once("soon/bighospital.tpl");
}
?>
</div>
<?php } ?>
<script type="text/javascript">
function show_build_list(list) {
    // aktuelle liste, aktueller link
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
            if (all_link !== null) {
                all_link.className = '';
            }
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
<?php
}
?>
</div>


