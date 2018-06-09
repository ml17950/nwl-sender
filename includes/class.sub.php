<?php
class clsSubscriber {
	var $db;
	
	function __construct(&$db) {
		//echo "<!--",__CLASS__,":",__FUNCTION__,"-->\n";
		$this->db = $db;
	}
	
	function status2text($status, $abmzp = 0) {
		switch ($status) {
			case ABO_ACTIVE: return 'Angemeldet';
			case ABO_INACTIVE: return 'Abgem. am '.date('d.m.Y', $abmzp);
			case ABO_VALIDATE: return 'Validierung offen';
			case ABO_REMOVED: return 'Deaktiviert';
		}
		return $status;
	}
	
	function list_all() {
		echo "<h1>Abonnenten anzeigen</h1>";

		$show = param_int('show', ABO_ACTIVE);
		$sort = param('sort', 'date');
		
		echo "<div class='tab-container'>";
		echo "<div class='tab-header'>";
		
		if ($show == ABO_ACTIVE)
			echo "<a href='index.php?view=sub-list&amp;show=",ABO_ACTIVE,"' class='tab-item tab-item-active'>Angemeldet</a>";
		else
			echo "<a href='index.php?view=sub-list&amp;show=",ABO_ACTIVE,"' class='tab-item'>Angemeldet</a>";
		
		if ($show == ABO_VALIDATE)
			echo "<a href='index.php?view=sub-list&amp;show=",ABO_VALIDATE,"' class='tab-item tab-item-active'>Validierung offen</a>";
		else
			echo "<a href='index.php?view=sub-list&amp;show=",ABO_VALIDATE,"' class='tab-item'>Validierung offen</a>";
		
		if ($show == ABO_INACTIVE)
			echo "<a href='index.php?view=sub-list&amp;show=",ABO_INACTIVE,"' class='tab-item tab-item-active'>Abgemeldet</a>";
		else
			echo "<a href='index.php?view=sub-list&amp;show=",ABO_INACTIVE,"' class='tab-item'>Abgemeldet</a>";
		
		if ($show == ABO_REMOVED)
			echo "<a href='index.php?view=sub-list&amp;show=",ABO_REMOVED,"' class='tab-item tab-item-active'>Deaktiviert</a>";
		else
			echo "<a href='index.php?view=sub-list&amp;show=",ABO_REMOVED,"' class='tab-item'>Deaktiviert</a>";
		
		echo "</div>"; // .tab-header
		echo "<div class='tab-content'>";
		
		$sql  = "SELECT * FROM `letterit_abonnenten` WHERE `BID` = ".BID;
		$sql .= " AND `Status` = ".$show;
		if ($sort == 'status')
			$sql .= " ORDER BY `Status` DESC, `OptOutDT` DESC;";
		elseif ($sort == 'date')
			$sql .= " ORDER BY `RegisterDT` DESC;";
		else
			$sql .= " ORDER BY `Email` ASC;";
		$subs = $this->db->fetch_assoc_array($sql);
		
		if ($this->db->num_rows > 0) {
			echo "<table border='0' width='100%' cellpadding='2' cellspacing='0'>";
			echo "<tr>";
			echo "<th class='t-left'><a href='index.php?view=sub-list&amp;show=",$show,"&amp;sort=mail'>Email</a></th>";
			echo "<th class='t-left'>Option1</th>";
			echo "<th class='t-left' width='135'><a href='index.php?view=sub-list&amp;show=",$show,"&amp;sort=date'>Anmeldung</a></th>";
			echo "<th class='t-left' width='180'><a href='index.php?view=sub-list&amp;show=",$show,"&amp;sort=status'>Status</a></th>";
			echo "<th class='t-center' width='50'>&nbsp;</th>";
			echo "</tr>";
			
			foreach ($subs as $sub) {
				echo "<tr>";
				echo "<td>",$sub['Email'],"</td>";
				echo "<td>",$sub['Option1'],"&nbsp;</td>";
				if ($show == ABO_VALIDATE)
					echo "<td>",date('d.m.Y H:i', $sub['RegisterDT']),"</td>";
				else
					echo "<td>",date('d.m.Y H:i', $sub['OptInDT']),"</td>";
				echo "<td>",$this->status2text($sub['Status'], $sub['OptOutDT']),"</td>";
				echo "<td class='t-center'>";
				if (($sub['Status'] == ABO_VALIDATE) || ($sub['Status'] == ABO_INACTIVE))
					echo " <a href='index.php?view=sub-list&amp;set=",$sub['Email'],"&amp;status=",ABO_ACTIVE,"&amp;sort=",$sort,"' title='Anmelden'>&#10004;</a>";
				if (($sub['Status'] == ABO_VALIDATE) || ($sub['Status'] == ABO_ACTIVE))
					echo " <a href='index.php?view=sub-list&amp;set=",$sub['Email'],"&amp;status=",ABO_INACTIVE,"&amp;sort=",$sort,"' title='Abmelden'>&#10008;</a>";
				echo "</td>";
				echo "</tr>";
			}
			
			echo "</table>";
		}
		else
			msg("Keine Abonnenten gefunden", "info");
		
		echo "</div>"; // .tab-content
		echo "</div>"; // .tab-container
	}
	
	function add_single_mail($bid, $email) {
		$sql = "SELECT `AID`,`Status`,`Email`,`OptInCode` FROM `letterit_abonnenten` WHERE `BID` = ".$bid." AND `Email` LIKE '".$email."';";
		$info = $this->db->query_assoc($sql);
		
		if (intval($info['AID']) > 0) {
// 			echo "already found";
			return true;
		}
		
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			// invalid mail adress
// 			echo "invalid mail adress";
		}
		else {
			$splitemail = explode('@', $email);
			$sql = "INSERT INTO `letterit_abonnenten` SET Email='".$email."',Domain='".$splitemail[1]."',BID='".$bid."',RegisterDT='".time()."',OptInDT='".time()."',Status='".ABO_ACTIVE."',OptInCode='';";
			
			if ($this->db->query($sql) === false) {
				// insert error
// 				echo "insert error";
			}
			else {
				$sql = "SELECT `registered` FROM `letterit_stats` WHERE `BID` = ".$bid." AND `month` = ".date('n')." AND `year` = ".date('Y').";";
				$stats = $this->db->query_assoc($sql);
				if (isset($stats['registered']))
					$sql = "UPDATE `letterit_stats` SET `registered` = `registered` + 1 WHERE `BID` = ".$bid." AND `month` = ".date('n')." AND `year` = ".date('Y').";";
				else
					$sql = "INSERT INTO `letterit_stats` (`BID`, `month`, `year`, `registered`, `deregistered`) VALUES ('".$bid."', '".date('n')."', '".date('Y')."', '1', '0');";
				$this->db->query($sql);
				
				return true;
			}
		}
		
		return false;
	}
	
	function import() {
		echo "<h1>Abonnenten importieren</h1>";
		
		if (!empty($_POST['emails'])) {
			switch ($_POST['sep']) {
				case 'nlwin': $sep = "\r\n"; break;
				case 'nltux': $sep = "\n"; break;
				case 'nwmac': $sep = "\r"; break;
				case 'tab': $sep = "\t"; break;
				case 'komma': $sep = ","; break;
				case 'semi': $sep = ";"; break;
			}
			
			$emails = explode($sep, $_POST['emails']);
			
			if (count($emails) > 0) {
				$sql = "SELECT `Email` FROM `letterit_blacklist`";
				$blacklist = $this->db->fetch_assoc_key_array($sql, 'Email');
				if (!is_array($blacklist))
					$blacklist = array();
				
				$time = time();
				
				ini_set('max_execution_time', '0');
				ini_set('ignore_user_abort', '1');
				
				foreach ($emails as $email) {
					if (!empty($email)) {
						if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
							echo $email," &rarr; Adresse ungültig<br>";
						}
						else {
							$splitemail = explode('@', $email);
							
							if ((array_key_exists($email, $blacklist)) || (array_key_exists($splitemail[1], $blacklist))) {
								echo $email," &rarr; gesperrt durch Blacklist<br>";
							}
							else {
								$sqli  = "INSERT INTO `letterit_abonnenten` SET Email='".$email."',Domain='".$splitemail[1]."',BID='".BID."',RegisterDT='".$time."',OptInDT='".$time."',Status='".ABO_ACTIVE."';";
								$this->db->query($sqli);
								if ($this->db->last_errno == 1062)
									echo $email," &rarr; bereits eingetragen<br>";
								else
									echo $email," &rarr; ok<br>";
							}
						}
					}
				}
			}
		}
		else {
			echo "<form action='index.php?view=sub-import' method='POST' accept-charset='utf-8'>";
			
			echo "<label for='emails' class='label'>Emails <em>(1 Adresse pro Zeile)</em>:</label>";
			echo " <textarea name='emails' style='width: 98%; height: 350px;' required></textarea><br>";
			
			echo "<br>";
			echo "<label for='sep' class='label'>Trennzeichen Abonnenten</label>";
			echo "<select name='sep' size='1'>";
			echo "<option value='nlwin'>Neue Zeile WIN (\\r\\n)</option>";
			echo "<option value='nltux'>Neue Zeile UNIX (\\n))</option>";
			echo "<option value='nwmac'>Neue Zeile MAC (\\r)</option>";
			echo "<option value='tab'>Tabulator (\\t)</option>";
			echo "<option value='komma'>Komma (,)</option>";
			echo "<option value='semi'>Semikolon (;)</option>";
			echo "</select>";
			
			echo " <input type='submit' value='weiter' class='button'>";
			
			echo "</form>";
		}
	}
	
	function export() {
		echo "<h1>Abonnenten exportieren</h1>";
		
		$export = '';
		
		if (!empty($_POST['sep'])) {
			switch ($_POST['sep']) {
				case 'nlwin': $sep = "\r\n"; break;
				case 'nltux': $sep = "\n"; break;
				case 'nwmac': $sep = "\r"; break;
				case 'tab': $sep = "\t"; break;
				case 'komma': $sep = ","; break;
				case 'semi': $sep = ";"; break;
			}
			
			$status = param('status');
			
			if ($status == '*')
				$sql = "SELECT `Email`, `Status` FROM `letterit_abonnenten` WHERE `BID` = ".BID;
			else
				$sql = "SELECT `Email`, `Status` FROM `letterit_abonnenten` WHERE `BID` = ".BID." AND `Status` = ".$status;
			
			$abo = $this->db->fetch_assoc_array($sql);
			
			if (count($abo) > 0) {
				foreach ($abo as $a) {
					$export .= $a['Email'].$sep;
				}
			}
			
			msg($this->db->num_rows." Abonnenten gefunden", "info");
			echo "<br>";
		}
		
		echo "<form action='index.php?view=sub-export' method='POST' accept-charset='utf-8'>";
		
		echo " <label for='emails' class='label'>Emails <em>(1 Adresse pro Zeile)</em>:</label>";
		echo " <textarea name='emails' style='width: 98%; height: 350px;'>",$export,"</textarea><br>";
		
		echo "<br>";
		echo "<label for='status' class='label'>Status</label>";
		echo "<select name='status' size='1'>";
		echo "<option value='*'>Alle</option>";
		echo "<option value='",ABO_ACTIVE,"'>Aktive</option>";
		echo "<option value='",ABO_INACTIVE,"'>Inaktive</option>";
		echo "<option value='",ABO_VALIDATE,"'>Validierung</option>";
		echo "<option value='",ABO_REMOVED,"'>Deaktiviert</option>";
		echo "</select>";
		
		echo " <label for='sep' class='label'>Trennzeichen Abonnenten</label>";
		echo "<select name='sep' size='1'>";
		echo "<option value='nlwin'>Neue Zeile WIN (\\r\\n)</option>";
		echo "<option value='nltux'>Neue Zeile UNIX (\\n))</option>";
		echo "<option value='nwmac'>Neue Zeile MAC (\\r)</option>";
		echo "<option value='tab'>Tabulator (\\t)</option>";
		echo "<option value='komma'>Komma (,)</option>";
		echo "<option value='semi'>Semikolon (;)</option>";
		echo "</select>";
		
		echo " <input type='submit' value='weiter' class='button'>";
		
		echo "</form>";
	}
	
	function remove() {
		echo "<h1>Abonnenten deaktivieren</h1>";
		
		if (!empty($_POST['emails'])) {
			switch ($_POST['sep']) {
				case 'nlwin': $sep = "\r\n"; break;
				case 'nltux': $sep = "\n"; break;
				case 'nwmac': $sep = "\r"; break;
				case 'tab': $sep = "\t"; break;
				case 'komma': $sep = ","; break;
				case 'semi': $sep = ";"; break;
			}
			
			$emails = explode($sep, $_POST['emails']);
			
			if (count($emails) > 0) {
				ini_set('max_execution_time', '0');
				ini_set('ignore_user_abort', '1');
				
				foreach ($emails as $email) {
					if (!empty($email)) {
// 						if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
// 							echo $email," &rarr; Adresse ungültig<br>";
// 						}
// 						else {
							$sqli = "UPDATE `letterit_abonnenten` SET `Status` = '".ABO_REMOVED."' WHERE `BID` = ".BID." AND `Email` LIKE '".$email."' LIMIT 1;";
							$this->db->query($sqli);
							if ($this->db->num_rows == 0)
								echo $email," &rarr; nicht gefunden<br>";
							else
								echo $email," &rarr; ok<br>";
// 						}
					}
				}
			}
		}
		else {
			echo "<form action='index.php?view=sub-remove' method='POST' accept-charset='utf-8'>";
			
			echo "<label for='emails' class='label'>Emails <em>(1 Adresse pro Zeile)</em>:</label>";
			echo " <textarea name='emails' style='width: 98%; height: 350px;' required></textarea><br>";
			
			echo "<br>";
			echo "<label for='sep' class='label'>Trennzeichen Abonnenten</label>";
			echo "<select name='sep' size='1'>";
			echo "<option value='nlwin'>Neue Zeile WIN (\\r\\n)</option>";
			echo "<option value='nltux'>Neue Zeile UNIX (\\n))</option>";
			echo "<option value='nwmac'>Neue Zeile MAC (\\r)</option>";
			echo "<option value='tab'>Tabulator (\\t)</option>";
			echo "<option value='komma'>Komma (,)</option>";
			echo "<option value='semi'>Semikolon (;)</option>";
			echo "</select>";
			
			echo " <input type='submit' value='weiter' class='button'>";
			
			echo "</form>";
		}
	}
	
	function delete() {
		echo "<h1>Abonnenten löschen</h1>";
		
		if (!empty($_POST['emails'])) {
			switch ($_POST['sep']) {
				case 'nlwin': $sep = "\r\n"; break;
				case 'nltux': $sep = "\n"; break;
				case 'nwmac': $sep = "\r"; break;
				case 'tab': $sep = "\t"; break;
				case 'komma': $sep = ","; break;
				case 'semi': $sep = ";"; break;
			}
			
			$emails = explode($sep, $_POST['emails']);
			
			if (count($emails) > 0) {
				ini_set('max_execution_time', '0');
				ini_set('ignore_user_abort', '1');
				
				foreach ($emails as $email) {
					if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
						echo $email," &rarr; Adresse ungültig<br>";
					}
					else {
						$sqli  = "DELETE FROM `letterit_abonnenten` WHERE `BID` = ".BID." AND `Email` = '".$email."' LIMIT 1;";
						$this->db->query($sqli);
						if ($this->db->num_rows == 0)
							echo $email," &rarr; nicht gefunden<br>";
						else
							echo $email," &rarr; ok<br>";
					}
				}
			}
		}
		else {
			echo "<form action='index.php?view=sub-delete' method='POST' accept-charset='utf-8'>";
			
			echo "<label for='emails' class='label'>Emails <em>(1 Adresse pro Zeile)</em>:</label>";
			echo " <textarea name='emails' style='width: 98%; height: 350px;' required></textarea><br>";
			
			echo "<br>";
			echo "<label for='sep' class='label'>Trennzeichen Abonnenten</label>";
			echo "<select name='sep' size='1'>";
			echo "<option value='nlwin'>Neue Zeile WIN (\\r\\n)</option>";
			echo "<option value='nltux'>Neue Zeile UNIX (\\n))</option>";
			echo "<option value='nwmac'>Neue Zeile MAC (\\r)</option>";
			echo "<option value='tab'>Tabulator (\\t)</option>";
			echo "<option value='komma'>Komma (,)</option>";
			echo "<option value='semi'>Semikolon (;)</option>";
			echo "</select>";
			
			echo " <input type='submit' value='weiter' class='button'>";
			
			echo "</form>";
		}
	}
}
?>