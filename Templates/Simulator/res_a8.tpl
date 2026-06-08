<table class="results attacker" cellpadding="1" cellspacing="1">
				<thead>
					<tr>
						<td class="role">
							Attacker
						</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u71" title="Hoplite" alt="Hoplite" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u72" title="Sentinel" alt="Sentinel" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u73" title="Shieldsman" alt="Shieldsman" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u74" title="Twinsteel Therion" alt="Twinsteel Therion" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u75" title="Elpida Rider" alt="Elpida Rider" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u76" title="Corinthian Crusher" alt="Corinthian Crusher" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u77" title="Ram" alt="Ram" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u78" title="Ballista" alt="Ballista" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u79" title="Ephor" alt="Ephor" />
							</td><td>
								<img src="gpack/travian_default/img/x.gif" class="unit u80" title="Settler" alt="Settler" />
							</td></tr>
				</thead>
				<tbody>
					<tr>
						<th>
							Troops
						</th>
                        <td <?php if (!$form->getValue('a1_1')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a1_1');} ?></td>
                        <td <?php if (!$form->getValue('a1_2')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a1_2');} ?></td>
                        <td <?php if (!$form->getValue('a1_3')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a1_3');} ?></td>
                        <td <?php if (!$form->getValue('a1_4')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a1_4');} ?></td>
                        <td <?php if (!$form->getValue('a1_5')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a1_5');} ?></td>
                        <td <?php if (!$form->getValue('a1_6')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a1_6');} ?></td>
                        <td <?php if (!$form->getValue('a1_7')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a1_7');} ?></td>
                        <td <?php if (!$form->getValue('a1_8')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a1_8');} ?></td>
                        <td <?php if (!$form->getValue('a1_9')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a1_9');} ?></td>
                        <td <?php if (!$form->getValue('a1_10')) { echo "class=\"none\">0"; }else{ echo ">".$form->getValue('a1_10');} ?></td>
								</tr>
					<tr>
						<th>
							Casualties
						</th>
                        <td <?php if (!$troops = $form->getValue('a1_1')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][1]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a1_2')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][1]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a1_3')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][1]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a1_4')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][1]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a1_5')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][1]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a1_6')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][1]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a1_7')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][1]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a1_8')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][1]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a1_9')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][1]);} ?></td>
                        <td <?php if (!$troops = $form->getValue('a1_10')) { echo "class=\"none\">0"; }else{ echo ">".$dead = round($troops * $_POST['result'][1]);} ?></td>
                        </tr>
				</tbody>
			</table>

