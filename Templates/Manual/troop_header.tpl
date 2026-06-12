<?php
if (!isset($id)) return;
$tribeId = (int)(($id - 1) / 10) + 1;
$name = constant('U'.$id);
$tribe = constant('TRIBE'.$tribeId);
?>
<h1><img class="unit u<?php echo $id; ?>" src="gpack/travian_default/img/x.gif" alt="<?php echo $name; ?>" title="<?php echo $name; ?>" /> <?php echo $name; ?> <span class="tribe">(<?php echo $tribe; ?>)</span></h1>
