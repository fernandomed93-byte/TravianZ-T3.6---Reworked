<?php 
$config = $database->getConfigs();
$timeOffset = (int)($config['time_offset'] ?? 0);

$time = time(); //The actual time
$startDate = strtotime(START_DATE); //When the server has started
$daysToDisplay = 432000 / SPEED; //5 days / SPEED of the server

// Define os tempos de spawn dos Natars, verificando se as constantes existem
$natarsSpawnTime = defined('NATARS_SPAWN_TIME') ? NATARS_SPAWN_TIME : 0;
$natarsWWSpawnTime = defined('NATARS_WW_SPAWN_TIME') ? NATARS_WW_SPAWN_TIME : 0;
$natarsWWPlansSpawnTime = defined('NATARS_WW_BUILDING_PLAN_SPAWN_TIME') ? NATARS_WW_BUILDING_PLAN_SPAWN_TIME : 0;

$spawnTimeArray = [
    "Artifacts" => ($startDate + ($natarsSpawnTime * 86400) + $timeOffset) - $time,
    "WW villages" => ($startDate + ($natarsWWSpawnTime * 86400) + $timeOffset) - $time,
    "WW building plans" => ($startDate + ($natarsWWPlansSpawnTime * 86400) + $timeOffset) - $time
];

foreach($spawnTimeArray as $text => $spawnTime){
	if($spawnTime <= $daysToDisplay && $spawnTime > 0){
?>
<br /><br />
<div>
	<span><b><?php echo $text; ?></b> will spawn in: </span>
	<span id="timer<?php echo ++$session->timer; ?>"><?php echo $generator->getTimeFormat($spawnTime); ?></span>
</div>
<?php }} ?>