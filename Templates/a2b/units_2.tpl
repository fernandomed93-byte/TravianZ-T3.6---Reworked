<h1>Send Troops</h1>

<form method="POST" name="snd" action="a2b.php" id="s1trops"><input name="timestamp" value="1278280730" type="hidden"> <input name="timestamp_checksum" value="597fa8" type="hidden"> <input name="b" value="1" type="hidden">



		<table id="troops" cellpadding="1" cellspacing="1">
	<tbody><tr>
		<?php if(isset($_GET['t1'])){ 	$t1=$_GET['t1']; }else{ $t1=""; } ?>
		<?php if(isset($_GET['t2'])){ 	$t2=$_GET['t2']; }else{ $t2=""; } ?>
		<?php if(isset($_GET['t3'])){ 	$t3=$_GET['t3']; }else{ $t3=""; } ?>
		<?php if(isset($_GET['t4'])){ 	$t4=$_GET['t4']; }else{ $t4=""; } ?>
		<?php if(isset($_GET['t5'])){ 	$t5=$_GET['t5']; }else{ $t5=""; } ?>
		<?php if(isset($_GET['t6'])){ 	$t6=$_GET['t6']; }else{ $t6=""; } ?>
		<?php if(isset($_GET['t7'])){ 	$t7=$_GET['t7']; }else{ $t7=""; } ?>
		<?php if(isset($_GET['t8'])){ 	$t8=$_GET['t8']; }else{ $t8=""; } ?>
		<?php if(isset($_GET['t9'])){ 	$t9=$_GET['t9']; }else{ $t9=""; } ?>
		<?php if(isset($_GET['t10'])){ 	$t10=$_GET['t10']; }else{ $t10=""; } ?>

		<td class="line-first column-first large"><img class="unit u11" src="gpack/travian_default/img/x.gif" title="Clubswinger" onclick="document.snd.t1.value=''; return false;" alt="Clubswinger">
		<input class="text" <?php if ($village->unitarray['u11']<=0) {echo ' disabled="disabled"';}?> name="t1" value="<?php if ($village->unitarray['u11']<=$t1) {if ($village->unitarray['u11']<=0){echo "";}else{ echo $village->unitarray['u11'];}} else {echo htmlspecialchars($t1);} ?>" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u11']>0){
        	echo "<a href=\"#\" onclick=\"document.snd.t1.value=".$village->unitarray['u11']."; return false;\">(".$village->unitarray['u11'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		
        <td class="line-first large"><img class="unit u14" src="gpack/travian_default/img/x.gif" title="Scout" alt="Scout">
		<input class="text" <?php if ($village->unitarray['u14']<=0) {echo ' disabled="disabled"';}?> name="t4" value="<?php if ($village->unitarray['u14']<=$t4) {if ($village->unitarray['u14']<=0){echo "";}else{echo $village->unitarray['u14'];}} else {echo htmlspecialchars($t4);} ?>" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u14']>0){
        	echo "<a href=\"#\" onclick=\"document.snd.t4.value=".$village->unitarray['u14']."; return false;\">(".$village->unitarray['u14'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
        <td class="line-first regular"><img class="unit u17" src="gpack/travian_default/img/x.gif" title="Ram" alt="Ram">
		<input class="text" <?php if ($village->unitarray['u17']<=0) {echo ' disabled="disabled"';}?> name="t7" value="<?php if ($village->unitarray['u17']<=$t7) {if ($village->unitarray['u17']<=0){echo "";}else{echo $village->unitarray['u17'];}} else {echo htmlspecialchars($t7);} ?>" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u17']>0){
        	echo "<a href=\"#\" onclick=\"document.snd.t7.value=".$village->unitarray['u17']."; return false;\">(".$village->unitarray['u17'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>

		
        <td class="line-first column-last small"><img class="unit u19" src="gpack/travian_default/img/x.gif" title="Chief" alt="Chief">
		<input class="text" <?php if ($village->unitarray['u19']<=0) {echo ' disabled="disabled"';}?> name="t9" value="<?php if ($village->unitarray['u19']<=$t9) {if ($village->unitarray['u19']<=0){echo "";}else{echo $village->unitarray['u19'];}} else {echo htmlspecialchars($t9);} ?>" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u19']>0){
        	echo "<a href=\"#\" onclick=\"document.snd.t9.value=".$village->unitarray['u19']."; return false;\">(".$village->unitarray['u19'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
	</tr>
	<tr>
		<td class="column-first large"><img class="unit u12" src="gpack/travian_default/img/x.gif" title="Spearman" alt="Spearman">
		<input class="text" <?php if ($village->unitarray['u12']<=0) {echo ' disabled="disabled"';}?> name="t2" value="<?php if ($village->unitarray['u12']<=$t2) {if ($village->unitarray['u12']<=0){echo "";}else{echo $village->unitarray['u12'];}} else {echo htmlspecialchars($t2);} ?>" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u12']>0){
        	echo "<a href=\"#\" onclick=\"document.snd.t2.value=".$village->unitarray['u12']."; return false;\">(".$village->unitarray['u12'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>

		<td class="large"><img class="unit u15" src="gpack/travian_default/img/x.gif" title="Paladin" alt="Paladin">
		<input class="text" <?php if ($village->unitarray['u15']<=0) {echo ' disabled="disabled"';}?> name="t5" value="<?php if ($village->unitarray['u15']<=$t5) {if ($village->unitarray['u15']<=0){echo "";}else{echo $village->unitarray['u15'];}} else {echo htmlspecialchars($t5);} ?>" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u15']>0){
        	echo "<a href=\"#\" onclick=\"document.snd.t5.value=".$village->unitarray['u15']."; return false;\">(".$village->unitarray['u15'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="regular"><img class="unit u18" src="gpack/travian_default/img/x.gif" title="Catapult" alt="Catapult">
		<input class="text" <?php if ($village->unitarray['u18']<=0) {echo ' disabled="disabled"';}?> name="t8" value="<?php if ($village->unitarray['u18']<=$t8) {if ($village->unitarray['u18']<=0){echo "";}else{echo $village->unitarray['u18'];}} else {echo htmlspecialchars($t8);} ?>" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u18']>0){
        	echo "<a href=\"#\" onclick=\"document.snd.t8.value=".$village->unitarray['u18']."; return false;\">(".$village->unitarray['u18'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="column-last small"><img class="unit u20" src="gpack/travian_default/img/x.gif" title="Settler" alt="Settler">
		<input class="text" <?php if ($village->unitarray['u20']<=0) {echo ' disabled="disabled"';}?> name="t10" value="<?php if ($village->unitarray['u20']<=$t10) {if ($village->unitarray['u20']<=0){echo "";}else{echo $village->unitarray['u20'];}} else {echo htmlspecialchars($t10);} ?>" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u20']>0){
        	echo "<a href=\"#\" onclick=\"document.snd.t10.value=".$village->unitarray['u20']."; return false;\">(".$village->unitarray['u20'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
	</tr>
	<tr>
		<td class="line-last column-first large"><img class="unit u13" src="gpack/travian_default/img/x.gif" title="Axeman" alt="Axeman">
		<input class="text" <?php if ($village->unitarray['u13']<=0) {echo ' disabled="disabled"';}?> name="t3" value="<?php if ($village->unitarray['u13']<=$t3) {if ($village->unitarray['u13']<=0){echo "";}else{echo $village->unitarray['u13'];}} else {echo htmlspecialchars($t3);} ?>" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u13']>0){
        	echo "<a href=\"#\" onclick=\"document.snd.t3.value=".$village->unitarray['u13']."; return false;\">(".$village->unitarray['u13'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="line-last large"><img class="unit u16" src="gpack/travian_default/img/x.gif" title="Teutonic Knight" alt="Teutonic Knight">
		<input class="text" <?php if ($village->unitarray['u16']<=0) {echo ' disabled="disabled"';}?> name="t6" value="<?php if ($village->unitarray['u16']<=$t6) {if ($village->unitarray['u16']<=0){echo "";}else{echo $village->unitarray['u16'];}} else {echo htmlspecialchars($t6);} ?>" maxlength="6" type="text">
		<?php 
        if ($village->unitarray['u16']>0){
        	echo "<a href=\"#\" onclick=\"document.snd.t6.value=".$village->unitarray['u16']."; return false;\">(".$village->unitarray['u16'].")</a></td>";
        }else{ 
       		echo  "<span class=\"none\">(0)</span></td>";
		}
        ?>
		<td class="line-last regular"><?php 
        if ($village->unitarray['hero']>0){
        echo "<img class=\"unit uhero\" src=\"gpack/travian_default/img/x.gif\" title=\"Hero\" alt=\"Hero\"> <input class=\"text\" name=\"t11\" value=\"\" maxlength=\"6\" type=\"text\">   ";
            echo "<a href=\"#\" onclick=\"document.snd.t11.value=".$village->unitarray['hero']."; return false;\">(".$village->unitarray['hero'].")</a></td>";
        }
        ?></td>
			<td class="line-last column-last"></td>		</tr>
</tbody></table>

