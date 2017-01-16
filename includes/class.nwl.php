<?php
class clsNewsletter {
	var $db;
	var $mailer;
	
	function __construct(&$db, &$mailer) {
// 		echo "<!--",__CLASS__,":",__FUNCTION__,"-->\n";
		
		$this->db = $db;
		$this->mailer = $mailer;
	}
	
	function status2text($status, $send, $total) {
		switch ($status) {
			case NWL_CREATED: return 'vorbereitet';
			case NWL_SENDING: return 'gesendet bis '.$send.' von '.$total;
			case NWL_READY:   return 'erfolgreich gesendet';
			case NWL_ABORTED: return 'abgebrochen bei '.$send.' von '.$total;
		}
		return $status;
	}
	
	function create() {
		echo "<h1>Newsletter erstellen/bearbeiten</h1>";
		
		msg("Ein neuer Newsletter wird erstellt", "info");
		
		$sql = "INSERT INTO `letterit_send` (`LS_ID`, `HTML`, `Text`, `Betreff`, `BID`) VALUES (NULL, '<p>!!option1!!<br>!!option2!!<br>!!option3!!<br>!!option4!!</p><p>!!unsubmittext!!</p>', '!!unsubmittext!!', 'Newsletter ".date('d.m.Y H:i:s')."', '".BID."');";
		$this->db->query($sql);
		$new_lsid = $this->db->last_insert_id;
		redirect('index2.php?view=nwl-edit&lsid='.$new_lsid, 0);
	}
	
	function edit() {
		echo "<h1>Newsletter bearbeiten</h1>";
		
		$lsid = param_int('lsid');
// debugarr($_POST);
		
		if ((isset($_POST['save'])) || (isset($_POST['save-send']))) {
			$subject = param('subject');
			$html_body = param('html_body');
			$text_body = param('text_body');
			$autotext = param('autotext');
			
			$html_body = str_replace('../', $_SESSION['zones'][BID]['URL'], $html_body);
			
			if ($autotext == 'yes') {
				$replace['</h1>'] = "</h1>\n";
				$replace['</h2>'] = "</h2>\n";
				$replace['</h3>'] = "</h3>\n";
				$replace['</h4>'] = "</h4>\n";
				$replace['</h5>'] = "</h5>\n";
				$replace['<br />'] = "<br />\n";
				$replace['</p>'] = "</p>\n";
				$tmp_html = str_replace(array_keys($replace), array_values($replace), $html_body);
				$text_body = strip_tags($tmp_html);
			}
			
			$sql  = "UPDATE `letterit_send` SET";
			$sql .= " `Betreff` = '".remove_bad_chars($subject)."',";
			$sql .= " `HTML` = '".remove_bad_chars($html_body)."',";
			$sql .= " `Text` = '".remove_bad_chars($text_body)."'";
			$sql .= " WHERE `LS_ID` = ".$lsid.";";
			
			$this->db->query($sql);
			msg("Änderungen gespeichert", "success");
		}
		
		if (isset($_POST['save-send'])) {
			$preview = param('preview');
			
			if (check_mail($preview)) {
				$this->mailer->prepare_mail(BID, $preview, $subject, $html_body, $text_body);
				if ($this->mailer->send_single_mail($preview, true))
					msg("Eine Testmail wurde an ".$preview." geschickt", "success");
				else
					msg("Die Testmail an ".$preview." konnte nicht gesendet werden [".$this->mailer->errormsg."]", "error");
			}
		}
		
		if ($lsid > 0)
			$sql = "SELECT * FROM `letterit_send` WHERE `BID` = ".BID." AND `LS_ID` = ".$lsid;
		else
			$sql = "SELECT * FROM `letterit_send` WHERE `BID` = ".BID." AND `Status` = ".NWL_CREATED;
		$nwls = $this->db->fetch_assoc_array($sql);
		
		if ($this->db->num_rows == 0) {
			msg("Bitte erst einen Newsletter erstellen", "info");
			
// 			msg("Ein neuer Newsletter wird erstellt", "info");
// 			
// 			$sql = "INSERT INTO `letterit_send` (`LS_ID`, `HTML`, `Text`, `Betreff`, `BID`) VALUES (NULL, '<p>!!unsubmittext!!</p>', '!!unsubmittext!!', 'Newsletter ".date('d.m.Y H:i:s')."', '".BID."');";
// 			$this->db->query($sql);
// 			$new_lsid = $this->db->last_insert_id;
// 			redirect('index2.php?view=nwl-create', 0);
		}
		elseif ($this->db->num_rows > 1) {
			echo "<h4>Folgende Newsletter sind bereits vorbereitet</h4>";
			foreach ($nwls as $nwl) {
				echo "<a href='index2.php?view=nwl-edit&lsid=",$nwl['LS_ID'],"'>#",$nwl['LS_ID']," &ndash; ",$nwl['Betreff'],"</a><br>";
			}
		}
		else {
			$nwl = $nwls[0];
			
			if (empty($preview))
				$preview = $_SESSION['user']['Name'];
			
			echo "<form name='lfrm' action='index2.php?view=nwl-edit&ts=",time(),"' method='POST' accept-charset='utf-8'>";
			echo "<input type='hidden' name='lsid' value='",$nwl['LS_ID'],"'>";
			
			echo "<label for='subject'>Betreff</label> <input type='text' name='subject' value='",$nwl['Betreff'],"' style='width: 98%;' required><br>";
			echo "<label for='html_body'>HTML Inhalt</label> <textarea name='html_body' id='html_body' style='width: 98%; height: 200px;' required>",stripslashes($nwl['HTML']),"</textarea><br>";
			echo "<label for='text_body'>Text Inhalt</label> <textarea name='text_body' style='width: 98%; height: 200px;'>",stripslashes($nwl['Text']),"</textarea><br>";
			echo "Platzhalter: <strong>!!unsubmittext!! / !!unsubmitlink!! / !!email!! / !!option1!! / !!option2!! / !!option3!! / !!option4!!</strong>";
			echo "<br>";
			echo "<br>";
			echo "<input type='submit' name='save' value='Speichern' class='button'>";
			echo " <input type='checkbox' name='autotext' value='yes'> Text-Inhalt automatisch aus HTML-Inhalt erstellen";
			echo "<br>";
			echo "<hr>";
			echo "<label for='preview'>Vorschau an</label> <input type='text' name='preview' value='",$preview,"' style='width: 28%;'> ";
			echo "<input type='submit' name='save-send' value='Testsenden' class='button'>";
			echo "<hr>";
			
			echo "</form>\n";
			
			echo "<script src='editor/tinymce.min.js'></script>\n";
			echo "<script>tinymce.init({ selector: '#html_body',
				menubar: false, toolbar: 'styleselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link | code',
				plugins: ['link code textcolor'] });</script>\n";
			
			$this->mailer->prepare_mail(BID, $_SESSION['user']['Name'], $nwl['Betreff'], $nwl['HTML'], $nwl['Text']);
			
			echo "HTML Vorschau<br>";
			echo "<div class='nwl-preview'>";
			echo $this->mailer->mail->Body;
			echo "</div>";
			
			echo "Text Vorschau<br>";
			echo "<div class='nwl-preview'>";
			echo nl2br($this->mailer->mail->AltBody);
			echo "</div>";
		}
		
// echo $this->db->num_rows,"<hr>";
// debugarr($_POST);
// debugarr($nwls);
	}
	
	function send() {
		echo "<h1>Newsletter senden</h1>";
		
		$lsid = param_int('lsid');
		$current_code = param('code');
		
		if ($lsid > 0)
			$sql = "SELECT * FROM `letterit_send` WHERE `BID` = ".BID." AND `LS_ID` = ".$lsid;
		else
			$sql = "SELECT * FROM `letterit_send` WHERE `BID` = ".BID." AND `Status` < ".NWL_READY;
		$nwls = $this->db->fetch_assoc_array($sql);
		
		if ($this->db->num_rows == 0) {
			msg("Bitte erst einen Newsletter erstellen", "info");
		}
		elseif ($this->db->num_rows > 1) {
			msg("Zu viele offene Newsletter gefunden", "error");
			
			echo "<h4>Folgende Newsletter sind bereits vorbereitet</h4>";
			foreach ($nwls as $nwl) {
// debugarr($nwl);
				
				if ($nwl['Status'] == NWL_CREATED)
					echo "<a href='index2.php?view=nwl-send&lsid=",$nwl['LS_ID'],"'>#",$nwl['LS_ID']," &ndash; ",$nwl['Betreff']," starten</a><br>";
				elseif ($nwl['Status'] == NWL_SENDING)
					echo "<a href='index2.php?view=nwl-send&lsid=",$nwl['LS_ID'],"&send-now=yes&code=",$nwl['Code'],"'>#",$nwl['LS_ID']," &ndash; ",$nwl['Betreff']," weiter senden</a><br>";
				else
					echo "xxx";
				
			}
		}
		else {
			$nwl = $nwls[0];
			
			$send_now = param('send-now', 'no');
			
			if (($send_now == 'yes') && ($lsid > 0)) {
				if ($nwl['Status'] == NWL_CREATED) {
					msg("Senden wird verbereitet...", "info");
					
					$sql = "SELECT COUNT(`Email`) AS `cnt` FROM `letterit_abonnenten` WHERE `BID` = ".BID." AND `Status` = ".ABO_ACTIVE.";";
					$abo = $this->db->query_assoc($sql);
					
					$newcode = base64_encode(time());
					
					$sql  = "UPDATE `letterit_send` SET";
					$sql .= " `Status` = '".NWL_SENDING."',";
					$sql .= " `Abonnenten` = '".$abo['cnt']."',";
					$sql .= " `Abo_send_time` = '".time()."',";
					$sql .= " `Code` = '".$newcode."',";
					$sql .= " `Start_time` = '".time()."'";
					$sql .= " WHERE `LS_ID` = ".$lsid.";";
					
					$this->db->query($sql);
					
					redirect('index2.php?view=nwl-send&lsid='.$lsid.'&send-now=yes&code='.$newcode, intval($_SESSION['config']['bounce_weiter']));
// 					echo "<a href='index2.php?view=nwl-send&lsid=",$lsid,"&send-now=yes&code=",$newcode,"'>weiter 1</a>";
				}
				elseif ($nwl['Status'] == NWL_SENDING) {
					msg("Wenn sie diese Seite verlassen, wird das Senden unterbrochen!", "warning");
					
					$start = param_int('start');
					$end = param_int('end');
					$oldcode = param('code');
					
					$sql = "SELECT * FROM `letterit_send` WHERE `BID` = ".BID." AND `LS_ID` = ".$lsid;
					$nwl = $this->db->query_assoc($sql);
					
					if ($current_code == $nwl['Code']) {
						$mails_total = $nwl['Abonnenten'];
						$mails_sent = $nwl['Abo_send'];
						$last_aid = $nwl['Start'];
						
						$sql = "SELECT `AID`,`Email` FROM `letterit_abonnenten` WHERE `BID` = ".$nwl['BID']." AND `Status` = ".ABO_ACTIVE." AND `AID` > ".$last_aid." ORDER BY `AID` ASC LIMIT ".$_SESSION['config']['reload_send'].";";
// 						$sql = "SELECT `AID`,`Email` FROM `letterit_abonnenten` WHERE `BID` = ".$nwl['BID']." AND `Status` = ".ABO_ACTIVE." AND `AID` > ".$last_aid." ORDER BY `AID` ASC LIMIT 1;";
// debugsql($sql);
						$mails = $this->db->fetch_assoc_key_array($sql, 'AID');
						
						if ($this->db->num_rows > 0) {
							foreach ($mails as $aid => $mail) {
								$this->mailer->prepare_mail(BID, $mail['Email'], $nwl['Betreff'], $nwl['HTML'], $nwl['Text']);
								
								if ($this->mailer->send_single_mail($mail['Email'], false)) {
									$last_aid = $aid;
									$last_mail = $mail['Email'].' alias '.$this->mailer->mail->ToMail;
									$mails_sent++;
								}
								else {
									$last_aid = -1; // send mail error > abort
								}
							}
							
							$percent = round(((100 / $mails_total) * $mails_sent), 0);
							
							echo "<br><br>";
							echo "<div class='loading radius' id='process1'>";
							echo "<div class='text'><span>",$percent,"%</span></div>";
							echo "<div class='process green' style='width: ",$percent,"%;'>&nbsp;</div>";
							echo "</div>";
							echo "<em>letzte Email ging an ",$last_mail," &ndash; ",$mails_sent,"/",$mails_total," ",$this->mailer->errormsg,"</em>";
							
// debugarr($mails);
							
							$newcode = base64_encode(time());
							
							$sql  = "UPDATE `letterit_send` SET";
							$sql .= " `Status` = '".NWL_SENDING."',";
							$sql .= " `Code` = '".$newcode."',";
							$sql .= " `Start` = '".$last_aid."',";
							$sql .= " `Abo_send` = '".$mails_sent."',";
							$sql .= " `Abo_send_time` = '".time()."'";
							$sql .= " WHERE `LS_ID` = ".$lsid.";";
// debugsql($sql);
							
							redirect('index2.php?view=nwl-send&lsid='.$lsid.'&send-now=yes&code='.$newcode, intval($_SESSION['config']['bounce_weiter']));
// 							echo "<br><br><a href='index2.php?view=nwl-send&lsid=",$lsid,"&send-now=yes&code=",$newcode,"'>weiter 2</a>";
						}
						else {
							$percent = 100;
							
							echo "<br><br>";
							echo "<div class='loading radius' id='process1'>";
							echo "<div class='text'><span>",$percent,"%</span></div>";
							echo "<div class='process green' style='width: ",$percent,"%;'>&nbsp;</div>";
							echo "</div>";
							
							$newcode = base64_encode(time());
							
							$sql  = "UPDATE `letterit_send` SET";
							$sql .= " `Status` = '".NWL_READY."',";
							$sql .= " `Code` = '".$newcode."',";
							$sql .= " `Abo_send_time` = '".time()."'";
							$sql .= " WHERE `LS_ID` = ".$lsid.";";
							
							redirect('index2.php?view=nwl-send&lsid='.$lsid.'&send-now=yes&code='.$newcode, intval($_SESSION['config']['bounce_weiter']));
// 							echo "<a href='index2.php?view=nwl-send&lsid=",$lsid,"&send-now=yes&code=",$newcode,"'>weiter 3</a>";
						}
						
						$this->db->query($sql);
					}
					else {
						echo "wrong code";
					}
				}
				elseif ($nwl['Status'] == NWL_READY) {
					msg("Der Newsletter wurde an alle Abonnenten verschickt", "success");
				}
				else
					echo "%ELSE%";
			}
			else {
				if ((!empty($nwl['Betreff'])) && (!empty($nwl['HTML'])) && (!empty($nwl['Text']))) {
					$sql = "SELECT COUNT(`Email`) AS `cnt` FROM `letterit_abonnenten` WHERE `BID` = ".BID." AND `Status` = ".ABO_ACTIVE.";";
					$abo = $this->db->query_assoc($sql);
					
					echo "<form name='lfrm' action='index2.php?view=nwl-send' method='POST' accept-charset='utf-8'>";
					echo "Newsletter #",$nwl['LS_ID']," - <strong>",$nwl['Betreff'],"</strong> - an ",$abo['cnt']," Abonnenten senden?";
					echo "<br><br>";
					if (($nwl['Start'] > 0) && (!empty($nwl['Code']))) {
						echo " <input type='hidden' name='lsid' value='",$nwl['LS_ID'],"'>";
						echo " <input type='hidden' name='code' value='",$nwl['Code'],"'>";
						echo " <input type='checkbox' name='send-now' value='yes'>";
						echo " <input type='submit' value='Weiter senden' class='button'>";
					}
					else {
						echo " <input type='hidden' name='lsid' value='",$nwl['LS_ID'],"'>";
						echo " <input type='checkbox' name='send-now' value='yes'>";
						echo " <input type='submit' value='Jetzt senden' class='button'>";
					}
					echo "</form>\n";
					
					echo "<br>";
					
// 					echo "HTML Vorschau<br>";
// 					echo "<div class='nwl-preview'>";
// 					echo $nwl['HTML'];
// 					echo "</div>";
// 					
// 					echo "Text Vorschau<br>";
// 					echo "<div class='nwl-preview'>";
// 					echo nl2br($nwl['Text']);
// 					echo "</div>";
					
					$this->mailer->prepare_mail(BID, $_SESSION['user']['Name'], $nwl['Betreff'], $nwl['HTML'], $nwl['Text']);
					
					echo "HTML Vorschau<br>";
					echo "<div class='nwl-preview'>";
					echo $this->mailer->mail->Body;
					echo "</div>";
					
					echo "Text Vorschau<br>";
					echo "<div class='nwl-preview'>";
					echo nl2br($this->mailer->mail->AltBody);
					echo "</div>";
				}
				else
					msg("Newsletter noch nicht komplett vorbereitet", "info");
			}
			
// 			echo "<br><hr><hr>";
// 			debugarr($_POST);
// 			debugarr($nwl);
		}
	}
	
	function history() {
		echo "<h1>gesendete Newsletter</h1>";
		
		//$sql = "SELECT * FROM `letterit_send` WHERE `BID` = ".BID." ORDER BY `Abo_send_time` DESC;";
		$sql = "SELECT * FROM `letterit_send` WHERE `BID` = ".BID." ORDER BY `LS_ID` DESC;";
		$nwls = $this->db->fetch_assoc_array($sql);
		
		if ($this->db->num_rows > 0) {
			echo "<table border='0' width='100%' cellpadding='2' cellspacing='0'>";
			echo "<tr>";
			echo "<th class='t-left'>Betreff</th>";
			echo "<th class='t-center' width='50'>Abon.</th>";
			echo "<th class='t-left' width='180'>Status</th>";
			echo "<th class='t-right' width='135'>Gesendet</th>";
			echo "<th class='t-right' width='70'>Dauer</th>";
			echo "<th class='t-right' width='70'>Ø</th>";
			echo "<th class='t-center' width='50'>&nbsp;</th>";
			echo "</tr>";
			
			foreach ($nwls as $nwl) {
// debugarr($nwl);
				if ($nwl['Abo_send_time'] == 0) {
					$nwl_link = 'index2.php?view=nwl-edit&amp;lsid='.$nwl['LS_ID'];
					$send_time = '-';
					$duration = '-';
					$average = '-';
				}
				else {
					$nwl_link = 'index2.php?view=nwl-preview&amp;id='.$nwl['LS_ID'];
					$send_time = date('D, d.m.Y H:i', $nwl['Abo_send_time']);
					$dtmp = $nwl['Abo_send_time'] - $nwl['Start_time'];
					
					if ($dtmp > 3600) {
						$d_std = floor($dtmp / 3600);
						$d_std_rest = $dtmp % 3600;
						$d_min = floor($d_std_rest / 60);
						$d_min_rest = ($d_std_rest % 60) * 60;
						$d_sec = $d_min_rest / 60;
						$duration = $d_std.'h '.$d_min.'m '.$d_sec.'s';
					}
					elseif ($dtmp > 60) {
						$d_min = floor($dtmp / 60);
						$d_min_rest = ($dtmp % 60) * 60;
						$d_sec = $d_min_rest / 60;
						$duration = $d_min.'m '.$d_sec.'s';
					}
					else
						$duration = $dtmp.'s';
					
					if ($nwl['Abonnenten'] > 0)
						$atmp = $dtmp / $nwl['Abonnenten'];
					else
						$atmp = 0;
					
					if ($atmp > 60) {
						$a_min = floor($atmp / 60);
						$a_min_rest = ($atmp % 60) * 60;
						$a_sec = $a_min_rest / 60;
						$average = $a_min."m ".$a_sec."s";
					}
					else
						$average = floor($atmp)."s";
				}
				
				echo "<tr>";
				echo "<td><a href='",$nwl_link,"'>",$nwl['Betreff'],"</a></td>";
				echo "<td class='t-center'>",$nwl['Abonnenten'],"</td>";
				echo "<td>",$this->status2text($nwl['Status'], $nwl['Abo_send'], $nwl['Abonnenten']),"</td>";
				echo "<td class='t-right'>",$send_time,"</td>";
				echo "<td class='t-right'>",$duration,"</td>";
				echo "<td class='t-right'>",$average,"</td>";
				echo "<td class='t-center'>";
				echo " <a href='index2.php?view=nwl-delete&amp;id=",$nwl['LS_ID'],"' title='löschen'>&#10008;</a>";
				echo " <a href='index2.php?view=nwl-copy&amp;id=",$nwl['LS_ID'],"' title='neu senden'>&#10140;</a>";
				echo " <a href='index2.php?view=nwl-reset&amp;id=",$nwl['LS_ID'],"' title='zurücksetzen'>&#10026;</a>";
				echo "</td>";
				echo "</tr>";
			}
			
			echo "</table>\n";
		}
		else
			msg("Es wurden noch keine Newsletter erstellt oder versendet", "info");
	}
	
	function delete($id) {
		if ($id > 0) {
			$sql = "SELECT `LS_ID` FROM `letterit_send` WHERE `BID` = ".BID." AND `LS_ID` = ".$id." LIMIT 1;";
			$this->db->query($sql);
			
			if ($this->db->num_rows == 1) {
				$sql = "DELETE FROM `letterit_send` WHERE `BID` = ".BID." AND `LS_ID` = ".$id." LIMIT 1;";
				$this->db->query($sql);
				msg("Newsletter gelöscht", "success");
			}
			else
				msg("Newsletter nicht gefunden", "error");
		}
		else
			msg("Newsletter nicht gefunden", "error");
		
		redirect('index2.php?view=nwl-history', 2);
	}
	
	function preview($id) {
		$sql = "SELECT * FROM `letterit_send` WHERE `BID` = ".BID." AND `LS_ID` = ".$id;
		$nwl = $this->db->query_assoc($sql);
// debugsql($sql);
// debugarr($nwl);
		
		echo "<h1>",$nwl['Betreff'],"</h1>";
		
		$this->mailer->prepare_mail(BID, $_SESSION['user']['Name'], $nwl['Betreff'], $nwl['HTML'], $nwl['Text']);
		
		echo "HTML Vorschau<br>";
		echo "<div class='nwl-preview'>";
		echo $this->mailer->mail->Body;
		echo "</div>";
		
		echo "Text Vorschau<br>";
		echo "<div class='nwl-preview'>";
		echo nl2br($this->mailer->mail->AltBody);
		echo "</div>";
	}
	
	function copy($id) {
		if ($id > 0) {
			$sql = "SELECT `Betreff`, `HTML`, `Text` FROM `letterit_send` WHERE `BID` = ".BID." AND `LS_ID` = ".$id." LIMIT 1;";
			$nwl = $this->db->query_assoc($sql);
			
			if ($this->db->num_rows == 1) {
				$sql = "INSERT INTO `letterit_send` (`LS_ID`, `HTML`, `Text`, `Betreff`, `BID`) VALUES (NULL, '".$nwl['HTML']."', '".$nwl['Text']."', '".$nwl['Betreff']." (".date('d.m.y').")', '".BID."');"; // date('d.m.Y H:i:s')
				$this->db->query($sql);
				$new_lsid = $this->db->last_insert_id;
				msg("Newsletter kopiert", "success");
				redirect('index2.php?view=nwl-create&lsid='.$new_lsid, 2);
			}
			else {
				msg("Newsletter nicht gefunden", "error");
				redirect('index2.php?view=nwl-history', 2);
			}
		}
		else {
			msg("Newsletter nicht gefunden", "error");
			redirect('index2.php?view=nwl-history', 2);
		}
	}
	
	function opt_in($bid, $email, $name = '') {
		$sql = "SELECT `AID`,`Email`,`Datum`,`Option1`,`Abmeldezeit`,`Status` FROM `letterit_abonnenten` WHERE `BID` = ".$bid." AND `Email` LIKE '".$email."';";
		$info = $this->db->query_assoc($sql);
		$code = base64_encode(date('DHis'));
		
		if (!empty($info['AID'])) {
			$sql = "UPDATE `letterit_abonnenten` SET `Datum` = '".time()."', `Abmeldezeit` = '0', `Status` = '".ABO_VALIDATE."', `IP` = '".$_SERVER['REMOTE_ADDR']."', `Code` = '".$code."' WHERE `BID` = ".$bid." AND `Email` LIKE '".$email."';";
		}
		else {
			$splitemail = explode('@', $email);
			$sql = "INSERT INTO `letterit_abonnenten` SET Email='".$email."',domain='".$splitemail[1]."',BID='".$bid."',Datum='".time()."',Status='".ABO_VALIDATE."',IP='".$_SERVER['REMOTE_ADDR']."',Code='".$code."';";
		}
		$this->db->query($sql);
		
		// =====================================
		
		$sql = "SELECT `URL`,`LetteritURL`,`Anmelde_Betreff`,`Anmelde_Text`,`Anmelde_HTML` FROM `letterit_bereiche` WHERE `BID` = ".$bid." LIMIT 1;";
		$onfo = $this->db->query_assoc($sql);
		
// debugarr($onfo);
		
		$validatelink = $onfo['LetteritURL'].'&code='.$code.'&ehpdo=validate';
		
		$this->mailer->prepare_mail($bid, $email, $onfo['Anmelde_Betreff'], $onfo['Anmelde_HTML'], $onfo['Anmelde_Text'], $validatelink);
		$this->mailer->send_single_mail($email, true);
	}
	
	function opt_validate($bid, $email, $code) {
		$sql = "SELECT `AID`,`Status`,`Email`,`Code` FROM `letterit_abonnenten` WHERE `BID` = ".$bid." AND `Email` LIKE '".$email."';";
		$info = $this->db->query_assoc($sql);
		
		if ($info['Status'] == ABO_ACTIVE)
			return true;
		
		if (($code == $info['Code']) || ($code == '!!adm!!')) {
			$sql  = "UPDATE `letterit_abonnenten` SET `Datum` = '".time()."', `Status` = '".ABO_ACTIVE."', `IP` = '".$_SERVER['REMOTE_ADDR']."'";
			$sql .= " WHERE `BID` = ".$bid." AND `Email` LIKE '".$email."' LIMIT 1;";
			$this->db->query($sql);
			
			$sql = "SELECT `Zugang` FROM `letterit_stats` WHERE `BID` = ".$bid." AND `Monat` = ".date('n')." AND `Jahr` = ".date('Y').";";
			$stats = $this->db->query_assoc($sql);
			if (isset($stats['Zugang']))
				$sql = "UPDATE `letterit_stats` SET `Zugang` = `Zugang` + 1 WHERE `BID` = ".$bid." AND `Monat` = ".date('n')." AND `Jahr` = ".date('Y').";";
			else
				$sql = "INSERT INTO `letterit_stats` (`BID`, `Monat`, `Jahr`, `Zugang`, `Abgang`) VALUES ('".$bid."', '".date('n')."', '".date('Y')."', '1', '0');";
			$this->db->query($sql);
			
			return true;
		}
		
		return false;
	}
	
	function opt_out($bid, $email, $send_opout_mail = true) {
		$sql = "SELECT `AID`,`Status`,`Email`,`Code` FROM `letterit_abonnenten` WHERE `BID` = ".$bid." AND `Email` LIKE '".$email."';";
		$info = $this->db->query_assoc($sql);
		
		if ($info['Status'] == ABO_INACTIVE)
			return true;
		
		$sql  = "UPDATE `letterit_abonnenten` SET `Abmeldezeit` = '".time()."', `Status` = '".ABO_INACTIVE."', `IP` = '".$_SERVER['REMOTE_ADDR']."'";
		$sql .= " WHERE `BID` = ".$bid." AND `Email` LIKE '".$email."' LIMIT 1;";
		$this->db->query($sql);
		
		$sql = "SELECT `Abgang` FROM `letterit_stats` WHERE `BID` = ".$bid." AND `Monat` = ".date('n')." AND `Jahr` = ".date('Y').";";
		$stats = $this->db->query_assoc($sql);
		if (isset($stats['Abgang']))
			$sql = "UPDATE `letterit_stats` SET `Abgang` = `Abgang` + 1 WHERE `BID` = ".$bid." AND `Monat` = ".date('n')." AND `Jahr` = ".date('Y').";";
		else
			$sql = "INSERT INTO `letterit_stats` (`BID`, `Monat`, `Jahr`, `Zugang`, `Abgang`) VALUES ('".$bid."', '".date('n')."', '".date('Y')."', '0', '1');";
		$this->db->query($sql);
		
		// =====================================
		
		if ($send_opout_mail) {
			$sql = "SELECT `URL`,`Abmelde_Betreff`,`Abmelde_Text`,`Abmelde_HTML` FROM `letterit_bereiche` WHERE `BID` = ".$bid." LIMIT 1;";
			$onfo = $this->db->query_assoc($sql);
			
			$this->mailer->prepare_mail($bid, $email, $onfo['Abmelde_Betreff'], $onfo['Abmelde_HTML'], $onfo['Abmelde_Text']);
			$this->mailer->send_single_mail($email, true);
		}
	}
}
?>