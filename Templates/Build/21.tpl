<div id="build" class="gid21"><a href="#" onClick="return Popup(21,4, 'gid');" class="build_logo"> 
<img class="building g21" src="gpack/travian_default/img/x.gif" alt="Workshop" title="<?php echo WORKSHOP; ?>" /> </a>

<h1><?php echo WORKSHOP; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f'.$id]; ?></span></h1>
<p class="build_desc"><?php echo WORKSHOP_DESC; ?></p>
<?php if ($building->getTypeLevel(21) > 0) { ?>

		<form method="POST" name="snd" action="build.php">
			<input type="hidden" name="id" value="<?php echo $id; ?>" />
            <input type="hidden" name="ft" value="t1" />
			<table cellpadding="1" cellspacing="1" class="build_details">
			<thead>
					<tr>
						<td><?php echo NAME; ?></td>
						<td><?php echo QUANTITY; ?></td>
						<td><?php echo MAX; ?></td>
					</tr>
				</thead>
				<tbody>
             <?php
            $success = 0;
            $start = ($session->tribe == 1)? 7 : (($session->tribe == 2)? 17 : 27);
            if ($session->tribe == 1){
            $start = 7;
            }else if ($session->tribe == 2){
            $start = 17;
            }else if ($session->tribe == 3){
            $start = 27;
			}else if ($session->tribe == 5){
            $start = 47;
            }else if ($session->tribe == 6){
            $start = 57;
            }else if ($session->tribe == 7){
            $start = 67;
            }else if ($session->tribe == 8){
            $start = 77;
            }else if ($session->tribe == 9){
            $start = 87;
            }
			if($session->tribe != 4){
            for($i=$start;$i<=($start+1);$i++) {
                if($technology->getTech($i)) {
                echo "<tr><td class=\"desc\"><div class=\"tit\"><img class=\"unit u$i\" src=\"gpack/travian_default/img/x.gif\" alt=\"".$technology->getUnitName($i)."\" title=\"".$technology->getUnitName($i)."\" />
                    <a href=\"#\" onClick=\"return Popup($i,1);\">".$technology->getUnitName($i)."</a> <span class=\"info\">(".AVAILABLE.": ".$village->unitarray['u'.$i].")</span></div>";
                    echo "<div class=\"details\">
                                        <img class=\"r1\" src=\"gpack/travian_default/img/x.gif\" alt=\"Wood\" title=\"".LUMBER."\" />".${'u'.$i}['wood']."|<img class=\"r2\" src=\"gpack/travian_default/img/x.gif\" alt=\"Clay\" title=\"".CLAY."\" />".${'u'.$i}['clay']."|<img class=\"r3\" src=\"gpack/travian_default/img/x.gif\" alt=\"Iron\" title=\"".IRON."\" />".${'u'.$i}['iron']."|<img class=\"r4\" src=\"gpack/travian_default/img/x.gif\" alt=\"Crop\" title=\"".CROP."\" />".${'u'.$i}['crop']."|<img class=\"r5\" src=\"gpack/travian_default/img/x.gif\" alt=\"Crop consumption\" title=\"".CROP_COM."\" />".${'u'.$i}['pop']."|<img class=\"clock\" src=\"gpack/travian_default/img/x.gif\" alt=\"Duration\" title=\"".DURATION."\" />";
                    $dur = $database->getArtifactsValueInfluence($session->uid, $village->wid, 5, round(${'u'.$i}['time'] * ($bid21[$village->resarray['f'.$id]]['attri'] / 100) / SPEED));
					echo $generator->getTimeFormat($dur);
					
                 //-- If available resources combined are not enough, remove NPC button
                 $total_required = (int)(${'u'.$i}['wood'] + ${'u'.$i}['clay'] + ${'u'.$i}['iron'] + ${'u'.$i}['crop']);

                 if($session->userinfo['gold'] >= 3 && $building->getTypeLevel(17) >= 1 && $village->atotal >= $total_required) {
                   echo "|<a href=\"build.php?gid=17&t=3&r1=".((${'u'.$i}['wood'])*$technology->maxUnitPlus($i))."&r2=".((${'u'.$i}['clay'])*$technology->maxUnitPlus($i))."&r3=".((${'u'.$i}['iron'])*$technology->maxUnitPlus($i))."&r4=".((${'u'.$i}['crop'])*$technology->maxUnitPlus($i))."\" title=\"NPC trade\"><img class=\"npc\" src=\"gpack/travian_default/img/x.gif\" alt=\"NPC trade\" title=\"NPC trade\" /></a>";
                 }  
                    echo "</div></td>
                                <td class=\"val\">
                                    <input type=\"text\" class=\"text\" name=\"t$i\" value=\"0\" maxlength=\"10\">
                                </td>
            
                                <td class=\"max\">
                                    <a href=\"#\" onClick=\"document.snd.t$i.value=".$technology->maxUnit($i)."; return false;\">(".$technology->maxUnit($i).")</a>
                                </td>
                            </tr>";
                      $success += 1;
                }
            }
            if($success == 0) {
                echo "<tr><td class=\"none\" colspan=\"3\">".AVAILABLE_ACADEMY."</td></tr>";
            }
			}
            ?>
				</tbody>
			</table>
			<p>
				<input type="image" id="btn_train" class="dynamic_img" value="ok" name="s1" src="gpack/travian_default/img/x.gif" alt="train" />

			</p>
		</form>
<?php
	    } else {
			echo "<b>".TRAINING_COMMENCE_WORKSHOP."</b><br>\n";
		}

    $trainlist = $technology->getTrainingList(3);
    include("trainingqueue.tpl");
include("upgrade.tpl");
?>  
    </p></div>


