<?php
class clsUI {
	var $db;
	
	function __construct(&$db) {
		$this->db = $db;
	}
	
	function html_head($title = 'Newsletter') {
		echo "<!DOCTYPE html>\n";
		echo "<html>\n";
		echo "<head>\n";
		echo "	<meta charset='UTF-8'>\n";
		echo "	<meta http-equiv='Content-Type' content='text/html; charset=utf8'>\n";
		echo "	<meta name='generator' content='PHP/PsPad'>\n";
		echo "	<meta name='robots' content='noarchive'>\n";
// 		echo "	<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1'>\n";
// 		echo "	<meta name='apple-mobile-web-app-capable' content='yes'>\n";
// 		echo "	<meta name='mobile-web-app-capable' content='yes'>\n";
		echo "	<title>",$title,"</title>\n";
		echo "	<link rel='stylesheet' type='text/css' href='nwl.css'>\n";
// 		echo "	<script type='text/javascript' src='res/js/jquery-1.12.0.min.js'></script>\n";
// 		echo "	<script type='text/javascript' src='res/js/common.js'></script>\n";
// 		echo "	<meta http-equiv='refresh' content='300'>\n";
		echo "</head>\n";
		echo "<body>\n";
	}
	
	function html_foot() {
		echo "</body>\n";
		echo "</html>";
	}
	
	function header() {
		$sql = "SELECT COUNT(`Email`) AS `cnt` FROM `letterit_abonnenten` WHERE `BID` = ".BID." AND `Status` = ".ABO_ACTIVE.";";
		$abo = $this->db->query_assoc($sql);
		
		echo "<div class='header'>";
		echo "Aktueller Bereich: ",$_SESSION['zones'][BID]['Bereich_Name'];
		echo "<br>";
		echo "Abonnenten: ",$abo['cnt'];
		echo "<br>";
		echo "Version: ",VERSION;
		echo "</div>\n"; // .header
		
		echo "<div class='main'>";
	}
	
	function sidebar() {
		echo "<div class='sidebar'>";
		
		echo "<span>Home</span>";
		echo "<ul>";
		echo "<li><a href='index.php'>Übersicht</a></li>";
		echo "</ul>";
		
		echo "<span>Newsletterbereich</span>";
		echo "<ul>";
		echo "<li><a href='index.php?view=zone-list'>wählen</a></li>";
		echo "<li><a href='index.php?view=zone-edit'>bearbeiten</a></li>";
		echo "<li><a href='index.php?view=zone-create'>erstellen</a></li>";
// 		echo "<li><a href='index.php?view=zone-delete'>löschen</a></li>";
		echo "</ul>";
		
		echo "<span>Newsletter</span>";
		echo "<ul>";
		echo "<li><a href='index.php?view=nwl-create'>erstellen</a></li>";
		echo "<li><a href='index.php?view=nwl-edit'>bearbeiten</a></li>";
		echo "<li><a href='index.php?view=nwl-send'>senden</a></li>";
		echo "<li><a href='index.php?view=nwl-history'>gesendet</a></li>";
		echo "</ul>";
		
		echo "<span>Abonnenten</span>";
		echo "<ul>";
		echo "<li><a href='index.php?view=sub-list'>anzeigen</a></li>";
		echo "<li><a href='index.php?view=sub-import'>importieren</a></li>";
		echo "<li><a href='index.php?view=sub-export'>exportieren</a></li>";
		echo "<li><a href='index.php?view=sub-remove'>deaktivieren</a></li>";
		echo "<li><a href='index.php?view=sub-delete'>löschen</a></li>";
		echo "</ul>";
		
		echo "<span>Statistik</span>";
		echo "<ul>";
		echo "<li><a href='index.php?view=stats-all'>alle Bereiche</a></li>";
		echo "<li><a href='index.php?view=stats-cur'>aktueller Bereich</a></li>";
// 		echo "<li><a href='index.php?view=stats-page'>Seitenaufrufe</a></li>";
		echo "</ul>";
		
		echo "<span>Global</span>";
		echo "<ul>";
		echo "<li><a href='index.php?view=config'>Einstellungen</a></li>";
// 		echo "<li><a href='index.php?view=blacklist'>Blacklist</a></li>";
		echo "<li><a href='index.php?view=password'>Passwort/Mail</a></li>";
		echo "<li><a href='index.php?view=logout'>Abmelden</a></li>";
		echo "<li><a href='index.php?view=debug'>Renew Session</a></li>";
		echo "</ul>";
		
		echo "</div>\n"; // .sidebar
	}
	
	function home_overview() {
		echo "<h1>Übersicht</h1>";
		
		echo "<div style='margin: 40px;'>";
		
		$sql = "SELECT COUNT(`BID`) AS `cnt` FROM `letterit_bereiche`";
		$bnfo = $this->db->query_assoc($sql);
		
		$sql = "SELECT `Status`, COUNT(`Email`) AS `cnt` FROM `letterit_abonnenten` GROUP BY `Status`;";
		$anfo = $this->db->fetch_assoc_key_array($sql, 'Status');
		
		$sql = "SELECT `BID`, `Email`, `Datum` FROM `letterit_abonnenten` ORDER BY `Datum` DESC LIMIT 1;";
		$enfo = $this->db->query_assoc($sql);
		
		$sql = "SELECT SUM(`registered`) AS `cntZ`, SUM(`deregistered`) AS `cntA` FROM `letterit_stats` WHERE `month` = '".date('n')."' AND `year` = '".date('Y')."'";
		$snfo = $this->db->query_assoc($sql);
		
		echo "<h2>Alle Bereiche</h2>";
		echo "<table border='0' cellpadding='2' cellspacing='0'>";
		echo "<tr><td width='200'>Anzahl Newsletterbereiche:</td><td>",intval($bnfo['cnt']),"</td></tr>";
		echo "<tr><td>Anzahl aktive Abonnenten:</td><td>",intval($anfo[ABO_ACTIVE]['cnt']),"</td></tr>";
		echo "<tr><td>Anzahl inaktive Abonnenten:</td><td>",intval($anfo[ABO_INACTIVE]['cnt']),"</td></tr>";
		echo "<tr><td>Anzahl fehlende Validierungen:</td><td>",intval($anfo[ABO_VALIDATE]['cnt']),"</td></tr>";
		echo "<tr><td>Neuster Abonnent:</td><td>",$enfo['Email']," - ",date('d.m.y H:i', $enfo['Datum'])," - ",zname($enfo['BID']),"</td></tr>";
		echo "<tr><td>Anmeldungen diesen Monat:</td><td>",intval($snfo['cntZ']),"</td></tr>";
		echo "<tr><td>Abmeldungen diesen Monat:</td><td>",intval($snfo['cntA']),"</td></tr>";
		echo "</table>";
		
		
		$sql = "SELECT `Status`, COUNT(`Email`) AS `cnt` FROM `letterit_abonnenten` WHERE `BID` = ".BID." GROUP BY `Status`;";
		$anfo = $this->db->fetch_assoc_key_array($sql, 'Status');
		
		$sql = "SELECT `Email`, `Datum` FROM `letterit_abonnenten` WHERE `BID` = ".BID." ORDER BY `Datum` DESC LIMIT 1;";
		$enfo = $this->db->query_assoc($sql);
		
		$sql = "SELECT SUM(`registered`) AS `cntZ`, SUM(`deregistered`) AS `cntA` FROM `letterit_stats` WHERE `BID` = ".BID." AND `month` = '".date('n')."' AND `year` = '".date('Y')."'";
		$snfo = $this->db->query_assoc($sql);
		
		echo "<h2>Aktueller Bereich (",$_SESSION['zones'][BID]['Bereich_Name'],")</h2>";
		echo "<table border='0' cellpadding='2' cellspacing='0'>";
		echo "<tr><td width='200'>Anzahl aktive Abonnenten:</td><td>",intval($anfo[ABO_ACTIVE]['cnt']),"</td></tr>";
		echo "<tr><td>Anzahl inaktive Abonnenten:</td><td>",intval($anfo[ABO_INACTIVE]['cnt']),"</td></tr>";
		echo "<tr><td>Anzahl fehlende Validierungen:</td><td>",intval($anfo[ABO_VALIDATE]['cnt']),"</td></tr>";
		echo "<tr><td>Neuster Abonnent:</td><td>",$enfo['Email']," - ",date('d.m.y H:i', $enfo['Datum']),"</td></tr>";
		echo "<tr><td>Anmeldungen diesen Monat:</td><td>",intval($snfo['cntZ']),"</td></tr>";
		echo "<tr><td>Abmeldungen diesen Monat:</td><td>",intval($snfo['cntA']),"</td></tr>";
		echo "</table>";
		
		echo "</div>\n";
	}
	
	function footer() {
		echo "<!--",__CLASS__,":",__FUNCTION__,"-->\n";
		
		echo "</div>\n"; // .main
		
		echo "<div class='footer'>";
		echo "&copy; 2016-2018 andev.de / M. Lindner";
		echo "</div>\n"; // .footer
		
// 		debugarr($_SESSION);
// 		debugarr($_SESSION['zones'][BID]);
	}
}
?>