<?php
$dataarray = explode(",",$message->readingNotice['data']);
$colspan = (isset($dataarray[270]) && $dataarray[270] > 0) ? 11 : 10;
$spy = !empty($dataarray[269]) && !empty($dataarray[268]) && empty($dataarray[283]);

if(!isset($isAdmin)){
    $mapUrl = "karte.php?d=";
    $playerUrl = "spieler.php?uid=";
}elseif($isAdmin){
    $mapUrl = "admin.php?p=village&did=";
    $playerUrl = "admin.php?p=player&uid=";
}

//Attacker
if ($database->getUserField($dataarray[0], 'username', 0) != "[?]") {
    $user_url="<a href=\"".$playerUrl.$database->getUserField($dataarray[0], 'id', 0)."\">".$database->getUserField($dataarray[0], 'username', 0)."</a>";
}
else $user_url="<font color=\"grey\"><b>[?]</b></font>";
    

if($database->getVillageField($dataarray[1], 'name') != "[?]") {
    $from_url="<a href=\"".$mapUrl.$dataarray[1]."&c=".$generator->getMapCheck($dataarray[1])."\">".$database->getVillageField($dataarray[1], 'name')."</a>";
}else $from_url="<font color=\"grey\"><b>[?]</b></font>";

//defender
if ($database->getUserField($dataarray[28], 'username', 0) != "[?]") {
    $defuser_url="<a href=\"".$playerUrl.$database->getUserField($dataarray[28], 'id', 0)."\">".$database->getUserField($dataarray[28], 'username', 0)."</a>";
}
else $defuser_url="<font color=\"grey\"><b>[?]</b></font>";

if($database->isVillageOases($dataarray[29])){
    $deffrom_url="<a href=\"".$mapUrl.$dataarray[29]."&c=".$generator->getMapCheck($dataarray[29])."\">".$dataarray[30]."</a>";
}elseif($database->getVillageField($dataarray[29],'name') != "[?]") {
    $deffrom_url="<a href=\"".$mapUrl.$dataarray[29]."&c=".$generator->getMapCheck($dataarray[29])."\">".$database->getVillageField($dataarray[29], 'name')."</a>";
}
else $deffrom_url="<font color=\"grey\"><b>[?]</b></font>";
    

?>
<table cellpadding="1" cellspacing="1" id="report_surround">
            <thead>
                <tr>
                    <th>Subject:</th>
                    <th><?php echo $message->readingNotice['topic']; ?></th>
                </tr>
 
                <tr>
                    <?php
                $date = $generator->procMtime($message->readingNotice['time']); ?>
                    <td class="sent">Sent:</td>
                    <td>on <span><?php echo $date[0]." at ".$date[1]; ?></span> <span>hour</span></td>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="2" class="empty"></td></tr>
                <tr><td colspan="2" class="report_content">
        <table cellpadding="1" cellspacing="1" id="attacker"><thead>
<tr>
<td class="role">Attacker</td>
<td colspan="<?php echo $colspan ?>"><?php echo ($user_url ? $user_url : 'Natar Counterforce'); ?> <?php echo ($from_url ? 'from the village '.$from_url : '');?></td>
</tr>
</thead>
<tbody class="units">
<tr>
<td>&nbsp;</td>
<?php
$tribe = $dataarray[2] ? $dataarray[2] : 5;
$start = ($tribe - 1) * 10 + 1;
for($i = $start; $i <= ($start + 9); $i++) {
    echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" title=\"".$technology->getUnitName($i)."\" alt=\"".$technology->getUnitName($i)."\" /></td>";
}
if (isset($dataarray[270]) && $dataarray[270] > 0){
    echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit uhero\" title=\"Hero\" alt=\"Hero\" /></td>";
}
echo "</tr><tr><th>Troops</th>";

for($i = 3; $i <= 12; $i++) {
    if($dataarray[$i] == 0) echo "<td class=\"none\">0</td>"; 
    else echo "<td>".$dataarray[$i]."</td>";  
}

if (isset($dataarray[270]) && $dataarray[270] > 0){
    echo "<td>$dataarray[270]</td>";
}
echo "<tr><th>Casualties</th>";

for($i = 13; $i <= 22; $i++) {
    if($dataarray[$i] == 0) echo "<td class=\"none\">0</td>";
    else echo "<td>".$dataarray[$i]."</td>";
}

if(isset($dataarray[270]) && $dataarray[270] > 0){
    if ($dataarray[271] == 0) $tdclass='class="none"'; else $tdclass='';
    echo "<td $tdclass>$dataarray[271]</td>";
}
if(!$spy && array_sum(array_slice($dataarray, 274, 11)) > 0){
echo "</tr><tr><th>Prisoners</th>";
for($i = 274; $i <= 284; $i++) {
    if($dataarray[$i] == 0) echo "<td class=\"none\">0</td>";
    else echo "<td>".$dataarray[$i]."</td>";
}
if(isset($dataarray[270]) && $dataarray[270] > 0){
    if ($dataarray[284] == 0) $tdclass='class="none"'; else $tdclass='';
    echo "<td $tdclass>$dataarray[284]</td>";
}
}  
echo "</tr></tbody>";
if (!empty($dataarray[262]) && !empty($dataarray[263])){ //ram
?>
    <tbody class="goods"><tr><th>Information</th><td colspan="<?php echo $colspan; ?>">
    <img class="unit u<?php echo $dataarray[262]; ?>" src="gpack/travian_default/img/x.gif" alt="Ram" title="Ram" />
    <?php echo $dataarray[263]; ?>
    </td></tr></tbody>
<?php } 
if (!empty($dataarray[264]) && !empty($dataarray[265])){ //cata
?>
    <tbody class="goods"><tr><th>Information</th><td colspan="<?php echo $colspan; ?>">
    <img class="unit u<?php echo $dataarray[264]; ?>" src="gpack/travian_default/img/x.gif" alt="Catapult" title="Catapult" />
    <?php echo $dataarray[265]; ?>
    </td></tr></tbody>
<?php }
if (!empty($dataarray[266]) && !empty($dataarray[267])){ //chief
?>
    <tbody class="goods"><tr><th>Information</th><td colspan="<?php echo $colspan; ?>">
    <img class="unit u<?php echo $dataarray[266]; ?>" src="gpack/travian_default/img/x.gif" alt="Chief" title="Chief" />
    <?php echo $dataarray[267]; ?>
    </td></tr></tbody>
<?php }
if ($spy){ //spy
?>
    <tbody class="goods"><tr><th>Information</th><td colspan="<?php echo $colspan; ?>">
    
    <?php echo $dataarray[269]; ?>
    </td></tr></tbody>
<?php } 
if (!empty($dataarray[285])){ //release prisoners
?>
    <tbody class="goods"><tr><th>Information</th><td colspan="<?php echo $colspan; ?>">
    
    <?php echo $dataarray[285]; ?>
    </td></tr></tbody>
<?php } 
if (!empty($dataarray[288]) && !empty($dataarray[289])){ //hero
?>
    <tbody class="goods"><tr><th>Information</th><td colspan="<?php echo $colspan; ?>">
    <img class="unit u<?php echo $dataarray[288]; ?>" src="gpack/travian_default/img/x.gif" alt="Hero" title="Hero" />
    <?php echo $dataarray[289]; ?>
    </td></tr></tbody>
<?php }
if(isset($dataarray[283]) && !empty($dataarray[283])){ //No troops returned
?>	
	<tbody class="goods"><tr><th>Information</th><td colspan="<?php echo $colspan; ?>">
	<?php echo $dataarray[283]; ?>
    </td></tr></tbody>
<?php }elseif(empty($dataarray[268]) && empty($dataarray[269])){?>
     <tbody class="goods"><tr><th>Bounty</th><td colspan="<?php echo $colspan; ?>">
    <div class="res"><img class="r1" src="gpack/travian_default/img/x.gif" alt="Lumber" title="Lumber" /><?php echo $dataarray[23]; ?> | <img class="r2" src="gpack/travian_default/img/x.gif" alt="Clay" title="Clay" /><?php echo $dataarray[24]; ?> | <img class="r3" src="gpack/travian_default/img/x.gif" alt="Iron" title="Iron" /><?php echo $dataarray[25]; ?> | <img class="r4" src="gpack/travian_default/img/x.gif" alt="Crop" title="Crop" /><?php echo $dataarray[26]; ?></div><div class="carry"><img class="car" src="gpack/travian_default/img/x.gif" alt="carry" title="carry" /><?php echo ($dataarray[23]+$dataarray[24]+$dataarray[25]+$dataarray[26])."/".$dataarray[27]; ?></div>
    </td></tr></tbody></table>
<?php } //Defender(s)
$defArray = [1, $dataarray[55], $dataarray[76], $dataarray[97], $dataarray[118], $dataarray[139], $dataarray[160], $dataarray[181], $dataarray[202], $dataarray[223]];
$targetTribe = $dataarray[34];
foreach($defArray as $index => $value){
    if($value == 0) continue;
    $heroIndex = ($index == 0 ? 272 : 244 + ($index - 1));
    $heroDeadIndex = ($index == 0 ? 1 : 9); 
    
    $target = ($index == 0 ? $targetTribe : $index) - 1;
    $start = $target * 10 + 1;
    $troopsStart = $index * 21 + 35;
?>    
    <table cellpadding="1" cellspacing="1" class="defender">
    <thead>
    <tr>
    <td class="role">Defender</td>
	<td colspan="<?php echo $dataarray[$heroIndex] > 0 ? 11 : 10; ?>"><?php echo ($index == 0) ? $defuser_url." from the village ".$deffrom_url : "Reinforcement"; ?></td>	
    </tr></thead>
    <tbody class="units">
    <tr>
    <td><?php
        
        if ($index == 0 && ($targetTribe == 4 || $targetTribe == 5)) {
            $sim_url = "warsim.php?target=" . $targetTribe;
            // O loop deve ir de 0 a 9 para cobrir as 10 unidades da tribo
            for ($k = 0; $k < 10; $k++) {
                $unit_id_for_sim = $start + $k; // $start já tem o ID da primeira unidade da tribo atual
                $troop_count_index = $troopsStart + $k; // Índice da contagem de tropas no $dataarray
                
                if (isset($dataarray[$troop_count_index]) && $dataarray[$troop_count_index] > 0) {
                    $sim_url .= "&u" . $unit_id_for_sim . "=" . $dataarray[$troop_count_index];
                }
            }
			echo '<a href="' . htmlspecialchars($sim_url) . '">Simulator</a>';
			}
        ?></td>
    
<?php
for($i = $start; $i <= ($start + 9); $i++) {
    echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" title=\"".$technology->getUnitName($i)."\" alt=\"".$technology->getUnitName($i)."\" /></td>";
}
if(isset($dataarray[$heroIndex]) && $dataarray[$heroIndex] > 0){
	echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit uhero\" title=\"Hero\" alt=\"Hero\" /></td>";
}
echo "</tr><tr><th>Troops</th>";

for($i = $troopsStart; $i <= $troopsStart + 9; $i++) {
    if($dataarray[$i] == 0) echo "<td class=\"none\">0</td>";
    else echo "<td>".$dataarray[$i]."</td>";
}

if(isset($dataarray[$heroIndex]) && $dataarray[$heroIndex] > 0){
    echo "<td>".$dataarray[$heroIndex]."</td>";
}
echo "<tr><th>Casualties</th>";

for($i = $troopsStart + 10; $i <= $troopsStart + 19; $i++) {
    if($dataarray[$i] == 0) echo "<td class=\"none\">0</td>";
    else echo "<td>".$dataarray[$i]."</td>";
}

if(isset($dataarray[$heroIndex]) && $dataarray[$heroIndex] > 0){
    if ($dataarray[$heroIndex + $heroDeadIndex] == 0) $tdclass1 = 'class="none"';
    echo "<td $tdclass1>".$dataarray[$heroIndex + $heroDeadIndex]."</td>";
}
}

$woundFlagIdx = count($dataarray) - 1;

?>
</tr></tbody></table>
</td></tr>

<?php
if (isset($dataarray[$woundFlagIdx]) && $dataarray[$woundFlagIdx] > 0) {
    echo "<tr><td colspan=2 >Some units were wounded in the battle.</td></tr>";
}
?>
</tbody></table>