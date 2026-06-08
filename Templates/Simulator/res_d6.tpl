<table class="results defender" cellpadding="1" cellspacing="1">
				<thead>
					<tr>
						<td class="role">
							Defender
						</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u51" title="Mercenary" alt="Mercenary" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u52" title="Bowman" alt="Bowman" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u53" title="Spotter" alt="Spotter" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u54" title="Steppe Rider" alt="Steppe Rider" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u55" title="Marksman" alt="Marksman" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u56" title="Marauder" alt="Marauder" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u57" title="Ram" alt="Ram" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u58" title="Catapult" alt="Catapult" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u59" title="Logades" alt="Logades" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u60" title="Settler" alt="Settler" />

							</td></tr>
				</thead>
				<tbody>
					<tr>
						<th>
							Troops
						</th>
                                <td <?php if (!$form->getValue('a2_1')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a2_1');} ?></td>
                                <td <?php if (!$form->getValue('a2_2')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a2_2');} ?></td>
                                <td <?php if (!$form->getValue('a2_3')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a2_3');} ?></td>
                                <td <?php if (!$form->getValue('a2_4')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a2_4');} ?></td>
                                <td <?php if (!$form->getValue('a2_5')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a2_5');} ?></td>
                                <td <?php if (!$form->getValue('a2_6')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a2_6');} ?></td>
                                <td <?php if (!$form->getValue('a2_7')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a2_7');} ?></td>
                                <td <?php if (!$form->getValue('a2_8')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a2_8');} ?></td>
                                <td <?php if (!$form->getValue('a2_9')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a2_9');} ?></td>
                                <td <?php if (!$form->getValue('a2_10')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a2_10');} ?></td>
                  </tr>
					<tr>
						<th>
							Casualties
						</th>
                        <td <?php if (!$troops = $form->getValue('a2_1')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][2]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a2_2')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][2]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a2_3')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][2]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a2_4')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][2]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a2_5')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][2]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a2_6')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][2]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a2_7')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][2]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a2_8')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][2]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a2_9')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][2]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a2_10')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][2]);} ?></td>
                        </tr>
				</tbody>
			</table>

