<?php
include_once("GameEngine/Data/unitdata.php");
include_once("inc/movement_map.php");

$artifactsSum = $database->getArtifactsSumByKind($session->uid, $village->wid, 3);
$movements = $database->getAllInboundMovements($village->wid);

foreach ($movements as $mov) {
    $session->timer++;
    include MOVEMENT_TPL[$mov['movement_type']];
}
?>