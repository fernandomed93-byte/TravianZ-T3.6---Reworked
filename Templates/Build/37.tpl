<?php

/*-------------------------------------------------------*\
| ********* DO NOT REMOVE THIS COPYRIGHT NOTICE ********* |
+---------------------------------------------------------+
| Developed by:  Manni < manuel_mannhardt@web.de >        |
|                Dzoki < dzoki.travian@gmail.com >        |
| Copyright:     TravianX Project All rights reserved     |
\*-------------------------------------------------------*/

        $hero_info = $units->Hero($session->uid);
        $heroes = $units->Hero($session->uid, 1);
        $define['reset_level'] = 3; // Until which level you are able to reset your points
      
?>


 <div id="build" class="gid37">
        <a href="#" onclick="return Popup(37,4, 'gid');" class="build_logo"><img class="building g37" src="gpack/travian_default/img/x.gif" alt="Hero's mansion" title="<?php echo HEROSMANSION; ?>"></a>

        <h1><?php echo HEROSMANSION; ?> <span class="level"><?php echo LEVEL; ?> <?php echo $village->resarray['f' . $id]; ?></span></h1>

        <p class="build_desc"><?php echo HEROSMANSION_DESC; ?></p>

        
        <?php
        if ($hero_info) {
            $name = defined('U'.$hero_info['unit']) ? constant('U'.$hero_info['unit']) : $technology->getUnitName($hero_info['unit']);
            $name1 = $hero_info['name'];
        } else {
            $name = 'Mr. Nobody';
            $name1 = 'unknown';
        }

		if(isset($_GET['land'])) {
            include_once("37_land.tpl");
		} else {
            $include_training = true;
            $include_revive = false;
            if (isset($heroes) && is_array($heroes) && count($heroes)) {
                foreach ( $heroes as $hdata ) {
                    if ( $hdata['dead'] == 1 ) {
                        $include_revive = true;
                    }

                    if ( $hdata['inrevive'] == 1 ) {
                        $name1            = $hdata['name'];
                        $include_training = false;
                    }
                }
            }

            if($hero_info === false && $include_revive){
                include_once("37_revive.tpl");
            }

            if ($hero_info === false && $include_training) {
                include_once("37_train.tpl");
            } else if(is_array($hero_info) && $hero_info['intraining'] == 1) {

		    $timeleft = $generator->getTimeFormat($hero_info['trainingtime'] - time());
		?>
	<table id="distribution" cellpadding="1" cellspacing="1">
        <thead>
            <tr>
            <?php echo "<tr class='next'><th>".HERO_READY."<span id=timer".++$session->timer.">" . $timeleft . "</span></th></tr>"; ?>
            </tr>
        </thead>
            
            <tr>
			<?php
				   echo "<tr>
                <td class=\"desc\">
					<div class=\"tit\">
						<img class=\"unit u".$hero_info['unit']."\" src=\"gpack/travian_default/img/x.gif\" alt=\"".$name."\" title=\"".$name."\" />
						$name ($name1)
					</div>"
			?>
			
            </tr>
    </table>
		<?php
		}

        if($hero_info !== false AND $hero_info['dead'] == 0 AND $hero_info['trainingtime'] <= time() AND $hero_info['inrevive'] == 0 AND $hero_info['intraining'] == 0){
            include("37_hero.tpl");
        }
        }
        include ("upgrade.tpl"); ?>
        
    </div>
