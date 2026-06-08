<table class="results defender" cellpadding="1" cellspacing="1">
				<thead>
					<tr>
						<td class="role">
							Defender
						</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u81" title="Thrall" alt="Thrall" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u82" title="Shield Maiden" alt="Shield Maiden" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u83" title="Berserker" alt="Berserker" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u84" title="Heimdall's Eye" alt="Heimdall's Eye" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u85" title="Huskarl Rider" alt="Huskarl Rider" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u86" title="Valkyrie's Blessing" alt="Valkyrie's Blessing" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u87" title="Ram" alt="Ram" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u88" title="Catapult" alt="Catapult" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u89" title="Jarl" alt="Jarl" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u90" title="Settler" alt="Settler" />

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

