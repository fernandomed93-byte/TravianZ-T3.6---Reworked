<?php
include_once("GameEngine/Generator.php");
$start_timer = $generator->pageLoadTimeStart();

/*-------------------------------------------------------*\
| ********* DO NOT REMOVE THIS COPYRIGHT NOTICE ********* |
+---------------------------------------------------------+
| Developed by:  Manni < manuel_mannhardt@web.de >        |
|                Dzoki < dzoki.travian@gmail.com >        |
| Copyright:     TravianX Project All rights reserved     |
\*-------------------------------------------------------*/

   use App\Utils\AccessLogger;

   include_once("GameEngine/Village.php");
   AccessLogger::logRequest();

   if($session->goldclub == 0) {
	   header("Location: plus.php?id=3");
	   exit;
   }

   if ( !empty( $_POST['type'] ) ) {
     if ( $_POST['type'] == 15 ) {
       header( "Location: " . $_SERVER['PHP_SELF'] . "?s=1&x=" . preg_replace( "/[^a-zA-Z0-9_-]/", "", $_POST['x'] ) . '&y=' . preg_replace( "/[^a-zA-Z0-9_-]/", "", $_POST['y'] ) . '&b=' . preg_replace( "/[^a-zA-Z0-9_-]/", "", $_POST['bonus_getreide'] ) );
       exit;
     } elseif ( $_POST['type'] == 9 ) {
       header( "Location: " . $_SERVER['PHP_SELF'] . "?s=2&x=" . preg_replace( "/[^a-zA-Z0-9_-]/", "", $_POST['x'] ) . '&y=' . preg_replace( "/[^a-zA-Z0-9_-]/", "", $_POST['y'] ) );
       exit;
     } elseif ( $_POST['type'] == 'both' ) {
       header( "Location: " . $_SERVER['PHP_SELF'] . "?s=3&x=" . preg_replace( "/[^a-zA-Z0-9_-]/", "", $_POST['x'] ) . '&y=' . preg_replace( "/[^a-zA-Z0-9_-]/", "", $_POST['y'] ) );
       exit;
     }
   }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?php

   echo SERVER_NAME

?> - Crop Finder</title>
	<link rel="shortcut icon" href="favicon.ico"/>
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<script src="mt-full.js?0faab" type="text/javascript"></script>
	<script src="unx.js?f4b7h" type="text/javascript"></script>
	<script src="new.js?0faab" type="text/javascript"></script>
	<link href="<?php

   echo GP_LOCATE;

?>lang/en/lang.css?f4b7d" rel="stylesheet" type="text/css" />
	<link href="<?php

   echo GP_LOCATE;

?>lang/en/compact.css?f4b7i" rel="stylesheet" type="text/css" />
	<?php

   if($session->gpack == null || GP_ENABLE == false) {
   echo "
	<link href='".GP_LOCATE."travian.css?e21d2' rel='stylesheet' type='text/css' />
	<link href='".GP_LOCATE."lang/en/lang.css?e21d2' rel='stylesheet' type='text/css' />";
   }
   else {
   echo "
	<link href='".$session->gpack."travian.css?e21d2' rel='stylesheet' type='text/css' />
	<link href='".$session->gpack."lang/en/lang.css?e21d2' rel='stylesheet' type='text/css' />";
   }

?>
	<script type="text/javascript">

		window.addEvent('domready', start);
	</script>
</head>


<body class="v35 ie ie8">
<div class="wrapper">
<img style="filter:chroma();" src="gpack/travian_default/img/x.gif" id="msfilter" alt="" />
<div id="dynamic_header">
	</div>
<?php

   include ("Templates/header.tpl");

?>
<div id="mid">
<?php
include ("Templates/menu.tpl");
if( !empty( $_GET['x'] ) && !empty( $_GET['y'] ) && is_numeric($_GET['x']) && is_numeric($_GET['y']) ) {
	$coor2['x'] = preg_replace("/[^a-zA-Z0-9_-]/","",$_GET['x']);
	$coor2['y'] = preg_replace("/[^a-zA-Z0-9_-]/","",$_GET['y']);
} else {
	$wref2 = $village->wid;
	$coor2 = $database->getCoor($wref2);
}
?>
<div id="content" class="player">

<h1>Crop Finder</h1>
<div style="text-align: center">
<img width="200" src="gpack/travian_default/img/g/f6.jpg" />
<img width="200" src="gpack/travian_default/img/g/f1.jpg" />
</div>
<br /><br />
<form action="<?php echo $_SERVER['PHP_SELF']; ?>?s" method="post">
 <table>
  <tr>
   <td width="100">Cropper Type:</td>
   <td width="250">
	<input type="radio" class="radio" name="type" value="15" <?php if ( !empty( $_GET['s'] ) &&  $_GET['s'] == 1 ) echo 'checked="checked"'; ?> /> 15 crop
	<input type="radio" class="radio" name="type" value="9" <?php if ( !empty( $_GET['s'] ) && $_GET['s'] == 2 ) echo 'checked="checked"'; ?> /> 9 crop
	<input type="radio" class="radio" name="type" value="both" <?php if ( !empty( $_GET['s'] ) && $_GET['s'] == 3 ) echo 'checked="checked"'; ?> /> both<br />
   </td>
  </tr>
    <tr>
   <td width="100">Oasis Crop Bonus (at least):</td>
   <td>
	 <select class="dropdown" name="bonus_getreide">
			<option value="all" <?php if ( !empty( $_GET['b'] ) &&  $_GET['b'] == "all" ) echo 'selected="selected"'; ?>>either</option>
			<option value="25" <?php if ( !empty( $_GET['b'] ) &&  $_GET['b'] == "25" ) echo 'selected="selected"'; ?>>+25%</option>
			<option value="50" <?php if ( !empty( $_GET['b'] ) &&  $_GET['b'] == "50" ) echo 'selected="selected"'; ?>>+50%</option>
			<option value="75" <?php if ( !empty( $_GET['b'] ) &&  $_GET['b'] == "75" ) echo 'selected="selected"'; ?>>+75%</option>
			<option value="100" <?php if ( !empty( $_GET['b'] ) &&  $_GET['b'] == "100" ) echo 'selected="selected"'; ?>>+100%</option>
			<option value="125" <?php if ( !empty( $_GET['b'] ) &&  $_GET['b'] == "125" ) echo 'selected="selected"'; ?>>+125%</option>
			<option value="150" <?php if ( !empty( $_GET['b'] ) &&  $_GET['b'] == "150" ) echo 'selected="selected"'; ?>>+150%</option>
	 </select>
	</td>
  </tr>
  <tr>
   <td>Startposition:</td>
   <td>x: <input type="text" name="x" value="<?php print $coor2['x']; ?>" size="3" /> y: <input type="text" name="y" value="<?php print $coor2['y']; ?>"  size="3" /></td>
  </tr>
  <tr>
   <td colspan="2"><button type="submit" class="trav_buttons" value="Search">Search</button></td>
  </tr>
 </table>
</form>


<?php
if ( !empty( $_GET['x'] ) && is_numeric($_GET['x']) && !empty( $_GET['y'] ) && is_numeric($_GET['y'])) {
	$coor['x'] = $_GET['x'];
	$coor['y'] = $_GET['y'];
} else {
	$wref = $village->wid;
	$coor = $database->getCoor($wref);
}

// Função getDistance (para cálculo de distância toroidal)
if (!function_exists('getDistanceCropfinder')) { // Renomeado para evitar conflito se getDistance já existir globalmente
    function getDistanceCropfinder($coorx1, $coory1, $coorx2, $coory2) {
        if (!defined('WORLD_MAX')) {
            define('WORLD_MAX', 400); // Placeholder: Este valor DEVE ser o correto do seu config
        }
        $max = 2 * WORLD_MAX + 1;
        $x1 = intval($coorx1); $y1 = intval($coory1);
        $x2 = intval($coorx2); $y2 = intval($coory2);
        $distanceX = min(abs($x2 - $x1), abs($max - abs($x2 - $x1)));
        $distanceY = min(abs($y2 - $y1), abs($max - abs($y2 - $y1)));
        $dist = sqrt(pow($distanceX, 2) + pow($distanceY, 2));
        return round($dist, 1);
    }
}

$ref_x_sql = $coor['x'];
$ref_y_sql = $coor['y'];

$fieldType = ( !empty( $_GET['s'] ) && $_GET['s'] == 1) ? "fieldtype = 6" : ( ( !empty( $_GET['s'] ) && $_GET['s'] == 2 ) ? "fieldtype = 1" : "fieldtype = 1 OR fieldtype = 6");
$type = mysqli_query(
$database->dblink,"
SELECT wdat.id, wdat.x, wdat.y, wdat.occupied, wdat.fieldtype
		FROM ".TB_PREFIX."wdata as wdat 
		WHERE $fieldType
        AND wdat.x > $ref_x_sql - 30 AND wdat.x < $ref_x_sql + 30
        AND wdat.y > $ref_y_sql - 30 AND wdat.y < $ref_y_sql + 30
		ORDER BY SQRT(POW(wdat.x - $ref_x_sql, 2) + POW(wdat.y - $ref_y_sql, 2)) ASC ");
$resultSql = $database->mysqli_fetch_all($type);

$oasis_map = [];
if (!empty($resultSql)) {
    // 1. Encontrar a área geral a ser verificada
    $min_x = 999; $max_x = -999;
    $min_y = 999; $max_y = -999;
    foreach ($resultSql as $row) {
        $min_x = min($min_x, $row['x'] - 3);
        $max_x = max($max_x, $row['x'] + 3);
        $min_y = min($min_y, $row['y'] - 3);
        $max_y = max($max_y, $row['y'] + 3);
    }

    // 2. Fazer UMA consulta para buscar todos os oásis na área
    $q_oasis = "SELECT x, y, oasistype FROM ".TB_PREFIX."wdata
                WHERE oasistype > 0
                  AND x BETWEEN $min_x AND $max_x
                  AND y BETWEEN $min_y AND $max_y";
    $oasis_result = mysqli_query($database->dblink, $q_oasis);

    // 3. Criar um mapa em memória
    while ($oasis_row = mysqli_fetch_assoc($oasis_result)) {
        $oasis_map[$oasis_row['x'] . '|' . $oasis_row['y']] = $oasis_row['oasistype'];
    }
    mysqli_free_result($oasis_result); // Liberar memória
}

if ( !empty( $_GET['s'] ) &&  $_GET['s'] >= 1 && $_GET['s'] <= 3 ) {
?>

<table id="member">
	<thead>
	<tr>
		<th colspan='6'>Crop Finder - 9c and 15c</th>
	</tr>
	<tr>
		<td>Type</td>
		<td>Coordinates</td>
		<td>Owner</td>
		<td>Occupied</td>
		<td>Distance</td>
		<td>Oasis</td>
	</tr>
	</thead><tbody>

<?php

$total_for = count($resultSql);
for($cropSql = 0; $cropSql < $total_for; $cropSql++){
    $row = $resultSql[$cropSql];
    $bonusCrop = $totBonus50 = $totBonus25 = 0;
    $xStart = $row['x'] - 3;
    $xEnd = $row['x'] + 3;
    $yStart = $row['y'] - 3;
    $yEnd = $row['y'] + 3;

    for($bonusX=$xStart; $bonusX<=($xEnd); $bonusX++) {
        for($bonusY=$yStart; $bonusY<=($yEnd); $bonusY++) {
            if ($totBonus50 < 3){
                // --- USA O MAPA EM VEZ DA QUERY ---
                $key = $bonusX . '|' . $bonusY;
                $oasistype = isset($oasis_map[$key]) ? $oasis_map[$key] : 0; // Pega do mapa ou 0 se não existir

                if ($oasistype == 12) $totBonus50 = $totBonus50 + 1;
                if ($oasistype == 3 || $oasistype == 6 || $oasistype == 9 || $oasistype == 10 || $oasistype == 11) $totBonus25 = $totBonus25 + 1;
                // --- FIM ---

                $sub = 3 - $totBonus50;
                if ($totBonus25 > $sub) $totBonus25 = $sub;
                $bonusCrop = $totBonus50 * 50 + $totBonus25 * 25;
            } else {
                $bonusCrop = 150;
            }
        }
    }
	
	if ( !empty( $_GET['b'] ) &&  $_GET['b'] != "all" && $bonusCrop < $_GET['b']) {
		continue;
	}
	
	$field = $row['fieldtype'] == 1 ? '9c' : '15c';
	
	echo "<tr><td>" . $field . "</td>";
	if($row['occupied'] == 0) {
		echo "<td><a href=\"karte.php?d=".$row['id']."&c=".$generator->getMapCheck($row['id'])."\">(".$row['x']."|".$row['y'].")</a></td>";
		echo "<td class=\"cropfu\"><div class=\"cropfu\">-</div></td>";
		echo "<td><b><font color=\"green\">".UNOCCUPIED."</b></font></td>";
	} else {
		echo "<td><a href=\"karte.php?d=".$row['id']."&c=".$generator->getMapCheck($row['id'])."\">(".$row['x']."|".$row['y'].")</a></td>";
		echo "<td class=\"cropfu\"><div class=\"cropfu\"><a href=\"spieler.php?uid=".$database->getVillageField($row['id'], "owner")."\">".$database->getUserField($database->getVillageField($row['id'], "owner"), "username", 0)."</a></div></td>";
		echo "<td><b><font color=\"red\">".OCCUPIED."</b></font></td>";
	}
	echo "<td><div style=\"text-align: center\">".$database->getDistance($coor['x'], $coor['y'], $row['x'], $row['y'])."</div></td>";
	echo "<td>" . $bonusCrop . "</td>";
}
?>

</tbody></table>

<?php

   }
?>
</div>
<br /><br /><br /><br /><div id="side_info">
<?php
include("Templates/multivillage.tpl");
include("Templates/quest.tpl");
include("Templates/news.tpl");
if(!NEW_FUNCTIONS_DISPLAY_LINKS) {
	echo "<br><br><br><br>";
	include("Templates/links.tpl");
}
?>
</div>
<div class="clear"></div>
</div>
<div class="footer-stopper"></div>
<div class="clear"></div>

<?php

   include ("Templates/footer.tpl");
   include ("Templates/res.tpl");

?>
<div id="stime">
<div id="ltime">
<div id="ltimeWrap">
<?php echo CALCULATED_IN;?> <b><?php
echo round(($generator->pageLoadTimeEnd()-$start_timer)*1000);
?></b> ms

<br /><?php echo SERVER_TIME;?> <span id="tp1" class="b"><?php echo date('H:i:s'); ?></span>
</div>
	</div>
</div>

<div id="ce"></div>
</body>
</html>
