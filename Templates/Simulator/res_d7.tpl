<table class="results defender" cellpadding="1" cellspacing="1">
				<thead>
					<tr>
						<td class="role">
							Defender
						</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u61" title="Slave Militia" alt="Slave Militia" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u62" title="Ash Warden" alt="Ash Warden" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u63" title="Khopesh Warrior" alt="Khopesh Warrior" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u64" title="Sopdu Explorer" alt="Sopdu Explorer" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u65" title="Anhur Guard" alt="Anhur Guard" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u66" title="Resheph Chariot" alt="Resheph Chariot" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u67" title="Ram" alt="Ram" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u68" title="Stone Catapult" alt="Stone Catapult" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u69" title="Nomarch" alt="Nomarch" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u70" title="Settler" alt="Settler" />

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

