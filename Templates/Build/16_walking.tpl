<?php
include_once("inc/movement_map.php");

$movements = $database->getAllOutboundMovements($village->wid);

foreach ($movements as $mov) {
    $session->timer++;
    include MOVEMENT_TPL[$mov['movement_type']];
}
?>
