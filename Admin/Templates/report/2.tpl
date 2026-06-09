<?php
$dataarray = explode(",",$rep['data']);
?>
<table cellpadding="1" cellspacing="1" id="report_surround">
	<thead>
		<tr>
			<th>Subject:</th>
			<th><?php echo $rep['topic']; ?></th>
		</tr>
		<tr>
			<?php
                $date = date('d:m:y H:i:s', $rep['time']);
			?>
			<td class="sent">Sent:</td>
			<td>on <?php echo $date; ?></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="2" class="empty"></td>
		</tr>
		<tr>
			<td colspan="2" class="report_content">
				<table cellpadding="1" cellspacing="1" id="attacker">
					<thead>
						<tr>
							<td class="role">Attacker</td>
							<td colspan="10"><a href="admin.php?p=player&uid=<?php echo $database->getUserField($dataarray[0],"id",0); ?>"><?php echo $database->getUserField($dataarray[0],"username",0); ?></a> from the village <a href="admin.php?p=village&did=<?php echo $dataarray[1] ?>"><?php echo $database->getVillageField($dataarray[1],"name"); ?></a></td>
						</tr>
					</thead>
					<tbody class="units">
						<tr>
						<td>&nbsp;</td>
						<?php
							$start = ($dataarray[2] - 1) * 10 + 1;
							for($i=$start;$i<=($start+9);$i++)
							{
								echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" /></td>";
							}
							echo "</tr><tr><th>Troops</th>";
							for($i=3;$i<=12;$i++)
							{
								if($dataarray[$i] == 0)
								{
									echo "<td class=\"none\">0</td>";
								}
								else
								{
									echo "<td>".$dataarray[$i]."</td>";
								}
							}
							echo "<tr><th>Casualties</th>";
							for($i=13;$i<=22;$i++)
							{
								if($dataarray[$i] == 0)
								{
									echo "<td class=\"none\">0</td>";
								}
								else
								{
									echo "<td>".$dataarray[$i]."</td>";
								}
							}
							echo "</tr></tbody>";
							if ($dataarray[139]!='' and $dataarray[140]!='')
							{ //ram ?>
								<tbody class="goods">
									<tr>
										<th>Information</th>
										<td colspan="10">
											<img class="unit u<?php echo $dataarray[139]; ?>" src="gpack/travian_default/img/x.gif" alt="Ram" title="Ram" />
											<?php
												echo $dataarray[140];
											?>
										</td>
									</tr>
								</tbody><?php 
							} 
							if ($dataarray[141]!='' and $dataarray[142]!='')
							{ //cata ?>
								<tbody class="goods">
									<tr>
										<th>Information</th>
										<td colspan="10">
											<img class="unit u<?php echo $dataarray[141]; ?>" src="gpack/travian_default/img/x.gif" alt="Catapult" title="Catapult" />
											<?php
												echo $dataarray[142];
											?>
										</td>
									</tr>
								</tbody><?php
							}
							if ($dataarray[143]!='' and $dataarray[144]!='')
							{ //chief ?>
								<tbody class="goods">
									<tr>
										<th>Information</th>
										<td colspan="10">
											<img class="unit u<?php echo $dataarray[143]; ?>" src="gpack/travian_default/img/x.gif" alt="Chief" title="Chief" />
											<?php
												echo $dataarray[144];
											?>
										</td>
									</tr>
								</tbody><?php 
							} 
							if ($dataarray[145]!='' and $dataarray[146]!='')
							{ //spy ?>
								<tbody class="goods">
									<tr>
										<th>Information</th>
										<td colspan="10">
											<?php
												echo $dataarray[146];
											?>
										</td>
									</tr>
								</tbody><?php
							} 
						?>
					<tbody class="goods">
						<tr>
							<th>Bounty</th>
								<td colspan="10">
								<div class="res">
									<img class="r1" src="gpack/travian_default/img/x.gif" alt="Lumber" title="Lumber" /><?php echo $dataarray[23]; ?> | <img class="r2" src="gpack/travian_default/img/x.gif" alt="Clay" title="Clay" /><?php echo $dataarray[24]; ?> | <img class="r3" src="gpack/travian_default/img/x.gif" alt="Iron" title="Iron" /><?php echo $dataarray[25]; ?> | <img class="r4" src="gpack/travian_default/img/x.gif" alt="Crop" title="Crop" /><?php echo $dataarray[26]; ?></div><div class="carry"><img class="car" src="gpack/travian_default/img/x.gif" alt="carry" title="carry" /><?php echo ($dataarray[23]+$dataarray[24]+$dataarray[25]+$dataarray[26])."/".$dataarray[27]; ?>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<?php
					$targettribe=$dataarray['31'];
					if ($dataarray[34]=='1')
					{
						$start=1; ?>	
						<table cellpadding="1" cellspacing="1" class="defender">
							<thead>
								<tr>
									<td class="role">Defender</th>
									<td colspan="10"><?php if($targettribe=='1'){ echo'<a href="admin.php?p=player&uid='.$database->getUserField($dataarray[28],"id",0).'">'.$database->getUserField($dataarray[28],"username",0).'</a> from the village <a href="admin.php?p=village$did='.$dataarray[29].'"'.stripslashes($dataarray[30]).'</a>'; } else { echo"Reinforcement"; } ?></td>
								</tr>
							</thead>
							<tbody class="units">
								<tr>
									<td>&nbsp;</td>
									<?php
										for($i=$start;$i<=($start+9);$i++)
										{
											echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" /></td>";
										}
										echo "</tr><tr><th>Troops</th>";
										for($i=35;$i<=44;$i++)
										{
											if($dataarray[$i] == 0)
											{
												echo "<td class=\"none\">0</td>";
											}
											else
											{
												echo "<td>".$dataarray[$i]."</td>";
											}
										}
										echo "<tr><th>Casualties</th>";
										for($i=45;$i<=54;$i++)
										{
											if($dataarray[$i] == 0)
											{
												echo "<td class=\"none\">0</td>";
											}
											else
											{
												echo "<td>".$dataarray[$i]."</td>";
											}
										}
									?>
								</tr>
							</tbody>
						</table><?php 
					} 
					if ($dataarray[55]=='1')
					{ 
						$start=11; ?>	
						<table cellpadding="1" cellspacing="1" class="defender">
							<thead>
								<tr>
									<td class="role">Defender</th>
									<td colspan="10"><?php if($targettribe=='2'){ echo'<a href="admin.php?p=player&uid='.$database->getUserField($dataarray[28],"id",0).'">'.$database->getUserField($dataarray[28],"username",0).'</a> from the village <a href="admin.php?p=village?did='.$dataarray[29].'">'.stripslashes($dataarray[30]).'</a>'; } else { echo"Reinforcement"; } ?></td>
								</tr>
							</thead>
							<tbody class="units">
								<tr>
									<td>&nbsp;</td>
									<?php
										for($i=$start;$i<=($start+9);$i++)
										{
											echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" /></td>";
										}
										echo "</tr><tr><th>Troops</th>";
										for($i=56;$i<=65;$i++)
										{
											if($dataarray[$i] == 0)
											{
												echo "<td class=\"none\">0</td>";
											}
											else
											{
												echo "<td>".$dataarray[$i]."</td>";
											}
										}
										echo "<tr><th>Casualties</th>";
										for($i=66;$i<=75;$i++)
										{
											if($dataarray[$i] == 0)
											{
												echo "<td class=\"none\">0</td>";
											}
											else
											{
												echo "<td>".$dataarray[$i]."</td>";
											}
										}
									?>
								</tr>
							</tbody>
						</table><?php 
					}
						if ($dataarray[76]=='1')
						{
							$start=21; ?>	
							<table cellpadding="1" cellspacing="1" class="defender">
								<thead>
									<tr>
										<td class="role">Defender</th>
										<td colspan="10"><?php if($targettribe=='3'){ echo'<a href="admin.php?p=player&uid='.$database->getUserField($dataarray[28],"id",0).'">'.$database->getUserField($dataarray[28],"username",0).'</a> from the village <a href="admin.php?p=village&did='.$dataarray[29].'">'.stripslashes($dataarray[30]).'</a>'; } else { echo"Reinforcement"; } ?></td>
									</tr>
								</thead>
								<tbody class="units">
									<tr>
										<td>&nbsp;</td>
										<?php
											for($i=$start;$i<=($start+9);$i++)
											{
												echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" /></td>";
											}
											echo "</tr><tr><th>Troops</th>";
											for($i=77;$i<=86;$i++)
											{
												if($dataarray[$i] == 0)
												{
													echo "<td class=\"none\">0</td>";
												}
												else 
												{
													echo "<td>".$dataarray[$i]."</td>";
												}
											}
											echo "<tr><th>Casualties</th>";
											for($i=87;$i<=96;$i++)
											{
												if($dataarray[$i] == 0)
												{
													echo "<td class=\"none\">0</td>";
												}
												else
												{
													echo "<td>".$dataarray[$i]."</td>";
												}
											}
										?>
									</tr>
								</tbody>
							</table><?php 
						} 
						if ($dataarray[97]=='1')
						{ 
							$start=31; ?>	
							<table cellpadding="1" cellspacing="1" class="defender">
								<thead>
									<tr>
										<td class="role">Defender</th>
										<td colspan="10"><?php if($targettribe=='4'){ echo'<a href="admin.php?p=player&uid='.$database->getUserField($dataarray[28],"id",0).'">'.$database->getUserField($dataarray[28],"username",0).'</a> from the village <a href="admin.php?p=player&did='.$dataarray[29].'&amp;c='.$generator->getMapCheck($dataarray[29]).'">'.stripslashes($dataarray[30]).'</a>'; } else { echo"Reinforcement"; } ?></td>
									</tr>
								</thead>
								<tbody class="units">
									<tr>
										<td>&nbsp;</td>
										<?php
											for($i=$start;$i<=($start+9);$i++)
											{
												echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" /></td>";
											}
											echo "</tr><tr><th>Troops</th>";
											for($i=98;$i<=107;$i++)
											{
												if($dataarray[$i] == 0)
												{
													echo "<td class=\"none\">0</td>";
												}
												else
												{
													echo "<td>".$dataarray[$i]."</td>";
												}
											}
											echo "<tr><th>Casualties</th>";
											for($i=108;$i<=117;$i++)
											{
												if($dataarray[$i] == 0)
												{
													echo "<td class=\"none\">0</td>";
												}
												else
												{
													echo "<td>".$dataarray[$i]."</td>";
												}
											}
										?>
									</tr>
								</tbody>
							</table><?php 
						}
							if ($dataarray[118]=='1')
							{
								$start=41; ?>	
								<table cellpadding="1" cellspacing="1" class="defender">
									<thead>
										<tr>
											<td class="role">Defender</th>
											<td colspan="10"><?php if($targettribe=='5'){ echo'<a href="spieler.php?uid='.$database->getUserField($dataarray[28],"id",0).'">'.$database->getUserField($dataarray[28],"username",0).'</a> from the village <a href="karte.php?d='.$dataarray[29].'&amp;c='.$generator->getMapCheck($dataarray[29]).'">'.stripslashes($dataarray[30]).'</a>'; } else { echo"Reinforcement"; } ?></td>
										</tr>
									</thead>
									<tbody class="units">
										<tr>
											<td>&nbsp;</td>
											<?php
												for($i=$start;$i<=($start+9);$i++)
												{
													echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" /></td>";
												}
												echo "</tr><tr><th>Troops</th>";
												for($i=119;$i<=128;$i++)
												{
													if($dataarray[$i] == 0)
													{
														echo "<td class=\"none\">0</td>";
													}
													else
													{
														echo "<td>".$dataarray[$i]."</td>";
													}
												}
												echo "<tr><th>Casualties</th>";
												for($i=129;$i<=138;$i++)
												{
													if($dataarray[$i] == 0)
													{
														echo "<td class=\"none\">0</td>";
													}
													else
													{
														echo "<td>".$dataarray[$i]."</td>";
													}
												}
											?>
										</tr>
									</tbody>
								</table><?php 
							} 
							if ($dataarray[147]=='1')
							{ 
								$start=51; ?>	
								<table cellpadding="1" cellspacing="1" class="defender">
									<thead>
										<tr>
											<td class="role">Defender</th>
											<td colspan="10"><?php if($targettribe=='6'){ echo'<a href="admin.php?p=player&uid='.$database->getUserField($dataarray[28],"id",0).'">'.$database->getUserField($dataarray[28],"username",0).'</a> from the village <a href="admin.php?p=village$did='.$dataarray[29].'"'.stripslashes($dataarray[30]).'</a>'; } else { echo"Reinforcement"; } ?></td>
										</tr>
									</thead>
									<tbody class="units">
										<tr>
											<td>&nbsp;</td>
											<?php
												for($i=$start;$i<=($start+9);$i++)
												{
													echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" /></td>";
												}
												echo "</tr><tr><th>Troops</th>";
												for($i=148;$i<=157;$i++)
												{
													if($dataarray[$i] == 0)
													{
														echo "<td class=\"none\">0</td>";
													}
													else
													{
														echo "<td>".$dataarray[$i]."</td>";
													}
												}
												echo "<tr><th>Casualties</th>";
												for($i=158;$i<=167;$i++)
												{
													if($dataarray[$i] == 0)
													{
														echo "<td class=\"none\">0</td>";
													}
													else
													{
														echo "<td>".$dataarray[$i]."</td>";
													}
												}
											?>
										</tr>
									</tbody>
								</table><?php 
							} 
							if ($dataarray[168]=='1')
							{ 
								$start=61; ?>	
								<table cellpadding="1" cellspacing="1" class="defender">
									<thead>
										<tr>
											<td class="role">Defender</th>
											<td colspan="10"><?php if($targettribe=='7'){ echo'<a href="admin.php?p=player&uid='.$database->getUserField($dataarray[28],"id",0).'">'.$database->getUserField($dataarray[28],"username",0).'</a> from the village <a href="admin.php?p=village&did='.$dataarray[29].'">'.stripslashes($dataarray[30]).'</a>'; } else { echo"Reinforcement"; } ?></td>
										</tr>
									</thead>
									<tbody class="units">
										<tr>
											<td>&nbsp;</td>
											<?php
												for($i=$start;$i<=($start+9);$i++)
												{
													echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" /></td>";
												}
												echo "</tr><tr><th>Troops</th>";
												for($i=169;$i<=178;$i++)
												{
													if($dataarray[$i] == 0)
													{
														echo "<td class=\"none\">0</td>";
													}
													else
													{
														echo "<td>".$dataarray[$i]."</td>";
													}
												}
												echo "<tr><th>Casualties</th>";
												for($i=179;$i<=188;$i++)
												{
													if($dataarray[$i] == 0)
													{
														echo "<td class=\"none\">0</td>";
													}
													else
													{
														echo "<td>".$dataarray[$i]."</td>";
													}
												}
											?>
										</tr>
									</tbody>
								</table><?php 
							} 
							if ($dataarray[189]=='1')
							{ 
								$start=71; ?>	
								<table cellpadding="1" cellspacing="1" class="defender">
									<thead>
										<tr>
											<td class="role">Defender</th>
											<td colspan="10"><?php if($targettribe=='8'){ echo'<a href="admin.php?p=player&uid='.$database->getUserField($dataarray[28],"id",0).'">'.$database->getUserField($dataarray[28],"username",0).'</a> from the village <a href="admin.php?p=village&did='.$dataarray[29].'">'.stripslashes($dataarray[30]).'</a>'; } else { echo"Reinforcement"; } ?></td>
										</tr>
									</thead>
									<tbody class="units">
										<tr>
											<td>&nbsp;</td>
											<?php
												for($i=$start;$i<=($start+9);$i++)
												{
													echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" /></td>";
												}
												echo "</tr><tr><th>Troops</th>";
												for($i=190;$i<=199;$i++)
												{
													if($dataarray[$i] == 0)
													{
														echo "<td class=\"none\">0</td>";
													}
													else
													{
														echo "<td>".$dataarray[$i]."</td>";
													}
												}
												echo "<tr><th>Casualties</th>";
												for($i=200;$i<=209;$i++)
												{
													if($dataarray[$i] == 0)
													{
														echo "<td class=\"none\">0</td>";
													}
													else
													{
														echo "<td>".$dataarray[$i]."</td>";
													}
												}
											?>
										</tr>
									</tbody>
								</table><?php 
							} 
							if ($dataarray[210]=='1')
							{ 
								$start=81; ?>	
								<table cellpadding="1" cellspacing="1" class="defender">
									<thead>
										<tr>
											<td class="role">Defender</th>
											<td colspan="10"><?php if($targettribe=='9'){ echo'<a href="spieler.php?uid='.$database->getUserField($dataarray[28],"id",0).'">'.$database->getUserField($dataarray[28],"username",0).'</a> from the village <a href="karte.php?d='.$dataarray[29].'&amp;c='.$generator->getMapCheck($dataarray[29]).'">'.stripslashes($dataarray[30]).'</a>'; } else { echo"Reinforcement"; } ?></td>
										</tr>
									</thead>
									<tbody class="units">
										<tr>
											<td>&nbsp;</td>
											<?php
												for($i=$start;$i<=($start+9);$i++)
												{
													echo "<td><img src=\"gpack/travian_default/img/x.gif\" class=\"unit u$i\" /></td>";
												}
												echo "</tr><tr><th>Troops</th>";
												for($i=211;$i<=220;$i++)
												{
													if($dataarray[$i] == 0)
													{
														echo "<td class=\"none\">0</td>";
													}
													else
													{
														echo "<td>".$dataarray[$i]."</td>";
													}
												}
												echo "<tr><th>Casualties</th>";
												for($i=221;$i<=230;$i++)
												{
													if($dataarray[$i] == 0)
													{
														echo "<td class=\"none\">0</td>";
													}
													else
													{
														echo "<td>".$dataarray[$i]."</td>";
													}
												}
											?>
										</tr>
									</tbody>
								</table><?php 
							} 
						?>
					</td>
				</tr>
			</tbody>
		</table>