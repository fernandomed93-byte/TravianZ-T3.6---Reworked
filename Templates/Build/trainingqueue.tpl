<?php
if (!isset($queueHeader)) $queueHeader = TRAINING;

if (count($trainlist) > 0) {
	$trainlist = $technology->groupTrainingList($trainlist);
	echo '
<table cellpadding="1" cellspacing="1" class="under_progress">
	<thead><tr>
		<td>'.$queueHeader.'</td>
		<td>'.DURATION.'</td>
		<td>'.FINISHED.'</td>
	</tr></thead>
	<tbody>';
	$TrainCount = 0;
	foreach($trainlist as $train) {
		$TrainCount++;
		echo '<tr><td class="desc">';
		echo '<img class="unit u'.$train['unit'].'" src="gpack/travian_default/img/x.gif" alt="'.$train['name'].'" title="'.$train['name'].'" />';
		echo $train['amt'].' '.$train['name'].'</td><td class="dur">';
		if ($TrainCount == 1) {
			if (count($trainlist) == 1) {
				$NextFinished = $generator->getTimeFormat($train['timestamp2']-time());
				echo '<span id=timer'.++$session->timer.'>'.$generator->getTimeFormat($train['lastTimestamp']-time()).'</span>';
			} else {
				$NextFinished = $generator->getTimeFormat($train['timestamp2']-time());
				echo '<span id=timer'.++$session->timer.'>'.$generator->getTimeFormat($train['timestamp']-time()).'</span>';
			}
		} else {
			echo $generator->getTimeFormat($train['totalTime']);
		}
		echo '</td><td class="fin">';
		$time = $generator->procMTime($TrainCount == 1 && count($trainlist) == 1 ? $train['lastTimestamp'] : $train['timestamp']);
		if($time[0] != "today") {
			echo "on ".$time[0]." at ";
		}
		echo $time[1];
		echo '</td></tr>';
	}
	echo '
	</tr><tr class="next"><td colspan="3">'.UNIT_FINISHED.' <span id="timer'.++$session->timer.'">'.$NextFinished.'</span></td></tr>
	</tbody></table>';
}
?>
