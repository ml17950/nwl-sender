<?php
class clsZone {
	var $mailer;
	
	function __construct(&$db, &$mailer) {
// 		echo "<!--",__CLASS__,":",__FUNCTION__,"-->\n";
		
		$this->db = $db;
		$this->mailer = $mailer;
	}
	
	function select($id) {
		if ($id > 0) {
			$sql = "SELECT `Bereich_Name` FROM `letterit_bereiche` WHERE `BID` = ".$id.";";
			$zone = $this->db->query_assoc($sql);
			
			if ($this->db->num_rows == 1) {
				$_SESSION['current-zone'] = $id;
				msg("Bereich ausgewählt", "success");
				redirect('index2.php', 1);
			}
			else {
				msg("Bereich nicht gefunden", "error");
				redirect('index2.php?view=zone-list', 2);
			}
		}
		else {
			msg("Bereich nicht gefunden", "error");
			redirect('index2.php?view=zone-list', 2);
		}
	}
	
	function list_all() {
		echo "<h1>Bereich wählen</h1>";
		
		$sql = "SELECT `BID`, COUNT(`Email`) AS `cnt` FROM `letterit_abonnenten` WHERE `Status` = ".ABO_ACTIVE." GROUP BY `BID`;";
		$subs = $this->db->fetch_assoc_key_array($sql, 'BID');
		
		$sql = "SELECT `BID`, COUNT(`LS_ID`) AS `cnt` FROM `letterit_send` WHERE `Status` >= 0 GROUP BY `BID`;";
		$nwls = $this->db->fetch_assoc_key_array($sql, 'BID');
		
		$sql = "SELECT * FROM `letterit_bereiche` ORDER BY `BID` ASC;";
		$zones = $this->db->fetch_assoc_array($sql);
		
		if ($this->db->num_rows > 0) {
			echo "<table border='0' width='100%' cellpadding='2' cellspacing='0'>";
			echo "<tr>";
			echo "<th class='t-left'>Bereich</th>";
			echo "<th class='t-left'>Absender</th>";
			echo "<th class='t-center'>Newsletter</th>";
			echo "<th class='t-center'>Abon.</th>";
			echo "<th class='t-center' width='50'>&nbsp;</th>";
			echo "</tr>";
			
			foreach ($zones as $zone) {
				echo "<tr>";
				echo "<td><a href='index2.php?view=zone-select&amp;id=",$zone['BID'],"' title='wählen'>",$zone['Bereich_Name'],"</a></td>";
				echo "<td>",$zone['Absender_Name']," &lt;",$zone['Absender_Email'],"&gt;</td>";
				echo "<td class='t-center'>",intval($nwls[$zone['BID']]['cnt']),"</td>";
				echo "<td class='t-center'>",intval($subs[$zone['BID']]['cnt']),"</td>";
				echo "<td class='t-center'><a href='index2.php?view=zone-delete&amp;id=",$zone['BID'],"' title='löschen'>&#10008;</a></td>";
				echo "</tr>";
			}
			
			echo "</table>";
		}
	}
	
	function edit() {
		echo "<h1>Bereichs-Einstellungen bearbeiten</h1>";
		
		if (isset($_POST['save'])) {
			unset($_POST['save']);
			
			$sql = "UPDATE `letterit_bereiche` SET";
			foreach ($_POST as $key => $val) {
// 				if (substr($key, 0, 6) == 'Option') {
// 					$sql .= $sep." `".$key."` = '".$val."'";
// 				}
// 				else
					$sql .= $sep." `".$key."` = '".remove_bad_chars($val)."'";
				$sep = ',';
			}
			$sql .= " WHERE `BID` = ".BID." LIMIT 1;";

// 			echo "<hr>A] ",$_POST['Option1'];
// 			echo "<hr>B] ",htmlentities($_POST['Option1']);
// 			echo "<hr>C] ",remove_bad_chars($_POST['Option1']);
// 			echo "<hr>D] ",addslashes(remove_bad_chars($_POST['Option1']));
// 			echo "<hr>E] ",$_SESSION['zones'][BID]['Option1'];
			
// 			echo $sql,"<hr>";
// 			debugarr($_POST);
// 			exit;
			
			$this->db->query($sql);
			msg("Daten gespeichert", "success");
			
			$sql = "SELECT * FROM `letterit_bereiche` ORDER BY `BID`";
			$_SESSION['zones'] = $this->db->fetch_assoc_key_array($sql, 'BID');
		}
		
		$sql = "SELECT * FROM `letterit_bereiche` WHERE `BID` = ".BID." LIMIT 1;";
		$znfo = $this->db->query_assoc($sql);
		
		echo "<form name='lfrm' action='index2.php?view=zone-edit' method='POST' accept-charset='utf-8'>";
		
		echo "<fieldset><legend>Allgemeines</legend>";
		echo "<label for='Bereich_Name'>Bereichs Name</label> <input type='text' name='Bereich_Name' value='",$znfo['Bereich_Name'],"' style='width: 98%;' required><br>";
		echo "<label for='Absender_Name'>Absender Name</label> <input type='text' name='Absender_Name' value='",$znfo['Absender_Name'],"' style='width: 98%;' required><br>";
		echo "<label for='Absender_Email'>Absender Email</label> <input type='text' name='Absender_Email' value='",$znfo['Absender_Email'],"' style='width: 98%;' required><br>";
		echo "<label for='URL'>Homepage URL</label> <input type='text' name='URL' value='",$znfo['URL'],"' style='width: 98%;' required><br>";
		echo "<label for='LetteritURL'>Unsubmit URL (<strong>!!unsubmitlink!!</strong>)</label> <input type='text' name='LetteritURL' value='",$znfo['LetteritURL'],"' style='width: 98%;' placeholder='http://domain/nwl-check.php?id=2&email=!!email!!' required><br>";
		echo "<label for='Zeichensatz'>Zeichensatz</label> <input type='text' name='Zeichensatz' value='",$znfo['Zeichensatz'],"' style='width: 98%;' required><br>";

		echo "<label for='Abmeldelink_Text'>Text Abmeldelink</label> <textarea name='Abmeldelink_Text' style='width: 98%; height: 80px;' required>",stripslashes($znfo['Abmeldelink_Text']),"</textarea><br>";
		echo "<label for='Abmeldelink_HTML'>HTML Abmeldelink</label> <textarea name='Abmeldelink_HTML' style='width: 98%; height: 80px;'>",stripslashes($znfo['Abmeldelink_HTML']),"</textarea><br>";
		echo "</fieldset>";
		
		echo "<br>";
		echo "<fieldset><legend>Anmeldebestätigung</legend>";
		echo "<label for='Anmelde_Betreff'>Anmelde_Betreff</label> <input type='text' name='Anmelde_Betreff' value='",$znfo['Anmelde_Betreff'],"' style='width: 98%;' required><br>";
		echo "<label for='Anmelde_Text'>Text Inhalt</label> <textarea name='Anmelde_Text' style='width: 98%; height: 100px;' required>",stripslashes($znfo['Anmelde_Text']),"</textarea><br>";
		echo "<label for='Anmelde_HTML'>HTML Inhalt</label> <textarea name='Anmelde_HTML' style='width: 98%; height: 100px;'>",stripslashes($znfo['Anmelde_HTML']),"</textarea><br>";
		echo "Platzhalter: <strong>!!validatelink!! / !!email!! / !!option1!! / !!option2!! / !!option3!! / !!option4!!</strong>";
		
		echo "<br><br>";
		
		$code = base64_encode(date('DHis'));
		//$validatelink = $znfo['URL'].'nwl-check.php?id='.BID.'&email='.$znfo['Absender_Email'].'&code='.$code.'&ehpdo=validate';
		$validatelink = $znfo['LetteritURL'].'&code='.$code.'&ehpdo=validate';
		$this->mailer->prepare_mail(BID, $znfo['Absender_Email'], $znfo['Anmelde_Betreff'], $znfo['Anmelde_HTML'], $znfo['Anmelde_Text'], $validatelink);
		
		echo "HTML Vorschau<br>";
		echo "<div class='nwl-preview'>";
		echo stripslashes($this->mailer->mail->Body);
		echo "</div>";
		
		echo "Text Vorschau<br>";
		echo "<div class='nwl-preview'>";
		echo nl2br(stripslashes($this->mailer->mail->AltBody));
		echo "</div>";
		echo "</fieldset>";
		
		echo "<br>";
		echo "<fieldset><legend>Abmeldebestätigung</legend>";
		echo "<label for='Abmelde_Betreff'>Abmelde_Betreff</label> <input type='text' name='Abmelde_Betreff' value='",$znfo['Abmelde_Betreff'],"' style='width: 98%;' required><br>";
		echo "<label for='Abmelde_Text'>Abmelde_Text</label> <textarea name='Abmelde_Text' style='width: 98%; height: 100px;' required>",stripslashes($znfo['Abmelde_Text']),"</textarea><br>";
		echo "<label for='Abmelde_HTML'>Abmelde_HTML</label> <textarea name='Abmelde_HTML' style='width: 98%; height: 100px;'>",stripslashes($znfo['Abmelde_HTML']),"</textarea><br>";
		echo "Platzhalter: <strong>!!email!! / !!option1!! / !!option2!! / !!option3!! / !!option4!!</strong>";
		
		echo "<br><br>";
		
		$this->mailer->prepare_mail(BID, $znfo['Absender_Email'], $znfo['Abmelde_Betreff'], $znfo['Abmelde_HTML'], $znfo['Abmelde_Text']);
		
		echo "HTML Vorschau<br>";
		echo "<div class='nwl-preview'>";
		echo stripslashes($this->mailer->mail->Body);
		echo "</div>";
		
		echo "Text Vorschau<br>";
		echo "<div class='nwl-preview'>";
		echo nl2br(stripslashes($this->mailer->mail->AltBody));
		echo "</div>";
		echo "</fieldset>";
		
		echo "<br>";
		echo "<fieldset><legend>Optionen</legend>";
		echo "<label for='Option1'>Option1</label> <input type='text' name='Option1' value='",$znfo['Option1'],"' style='width: 98%;'><br>";
		echo "<label for='Option2'>Option2</label> <input type='text' name='Option2' value='",$znfo['Option2'],"' style='width: 98%;'><br>";
		echo "<label for='Option3'>Option3</label> <input type='text' name='Option3' value='",$znfo['Option3'],"' style='width: 98%;'><br>";
		echo "<label for='Option4'>Option4</label> <input type='text' name='Option4' value='",$znfo['Option4'],"' style='width: 98%;'><br>";
		echo "</fieldset>";
		
		echo "<br>";
		echo "<input type='submit' name='save' value='Speichern' class='button'>";
		
		echo "</form>\n";
		
// 		echo "<h2>Anmeldebestätigung</h2>";
// 		
// 		$code = base64_encode(date('DHis'));
// 		$validatelink = $znfo['URL'].'nwl-check.php?id='.BID.'&email='.$znfo['Absender_Email'].'&code='.$code.'&ehpdo=validate';
// 		$validatelink = $znfo['LetteritURL'].'&code='.$code.'&ehpdo=validate';
// 		$this->mailer->prepare_mail(BID, $znfo['Absender_Email'], $znfo['Anmelde_Betreff'], $znfo['Anmelde_HTML'], $znfo['Anmelde_Text'], $validatelink);
// 		
// 		echo "HTML Vorschau<br>";
// 		echo "<div class='nwl-preview'>";
// 		echo stripslashes($this->mailer->mail->Body);
// 		echo "</div>";
// 		
// 		echo "Text Vorschau<br>";
// 		echo "<div class='nwl-preview'>";
// 		echo nl2br(stripslashes($this->mailer->mail->AltBody));
// 		echo "</div>";
// 		
// 		echo "<h2>Abmeldebestätigung</h2>";
// 		
// 		$this->mailer->prepare_mail(BID, $znfo['Absender_Email'], $znfo['Abmelde_Betreff'], $znfo['Abmelde_HTML'], $znfo['Abmelde_Text']);
// 		
// 		echo "HTML Vorschau<br>";
// 		echo "<div class='nwl-preview'>";
// 		echo stripslashes($this->mailer->mail->Body);
// 		echo "</div>";
// 		
// 		echo "Text Vorschau<br>";
// 		echo "<div class='nwl-preview'>";
// 		echo nl2br(stripslashes($this->mailer->mail->AltBody));
// 		echo "</div>";

	}
	
	function create() {
		echo "<h1>Bereich erstellen</h1>";
		
		msg("TODO");
	}
	
	function delete($id) {
		echo "<h1>Bereich löschen</h1>";
		
		$del_now = param('del-now', 'no');
		
		if (($del_now == 'yes') && ($id > 0)) {
			$sql = "DELETE FROM `letterit_stats` WHERE `BID` = ".$id;
			if ($this->db->query($sql))
				msg("Statistiken erfolgreich gelöscht", "success");
			else {
				msg("Statistiken nicht gelöscht", "error");
				return;
			}
			
			$sql = "DELETE FROM `letterit_send` WHERE `BID` = ".$id;
			if ($this->db->query($sql))
				msg("Statistiken erfolgreich gelöscht", "success");
			else {
				msg("Statistiken nicht gelöscht", "error");
				return;
			}
			
			$sql = "DELETE FROM `letterit_abonnenten` WHERE `BID` = ".$id;
			if ($this->db->query($sql))
				msg("Abonnenten erfolgreich gelöscht", "success");
			else {
				msg("Abonnenten nicht gelöscht", "error");
				return;
			}
			
			$sql = "DELETE FROM `letterit_bereiche` WHERE `BID` = ".$id;
			if ($this->db->query($sql))
				msg("Bereich erfolgreich gelöscht", "success");
			else {
				msg("Bereich nicht gelöscht", "error");
				return;
			}
			
			$_SESSION['current-zone'] = 1;
			redirect('index2.php?view=zone-list', 3);
		}
		else {
			echo "<form name='lfrm' action='index2.php?view=zone-delete&id=",$id,"' method='POST' accept-charset='utf-8'>";
			echo "Gewählter Bereich - <strong>",zname($id),"</strong>";
			echo "<br><br>";
			echo " <input type='checkbox' name='del-now' value='yes'>";
			echo " <input type='submit' value='Löschen' class='button'>";
			echo "</form>\n";
		}
	}
}
?>