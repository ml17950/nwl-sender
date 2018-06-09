<?php
class clsCore {
	var $db;
	var $ui;
	var $user;
	var $nwl;
	var $mailer;
	var $sub;
	var $zone;
	var $stats;
	
	function __construct() {
// 		echo "<!--",__CLASS__,":",__FUNCTION__,"-->\n";
		
		if (DB_HOST == 'xxxx')
			die(LNG_SYS_SETUP1);
		
		include_once('class.db.php');
		$this->db = new clsDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
		include_once('class.mailer.php');
		$this->mailer = new clsMailer($this->db);
		
		include_once('class.ui.php');
		$this->ui = new clsUI($this->db);
		
		include_once('class.user.php');
		$this->user = new clsUser($this->db);
		
		include_once('class.nwl.php');
		$this->nwl = new clsNewsletter($this->db, $this->mailer);
		
		include_once('class.sub.php');
		$this->sub = new clsSubscriber($this->db);
		
		include_once('class.zone.php');
		$this->zone = new clsZone($this->db, $this->mailer);
		
		include_once('class.stats.php');
		$this->stats = new clsStatistics($this->db);
	}
	
	function initialize() {
		if (!isset($_SESSION['current-zone']))
			$_SESSION['current-zone'] = 1;
		
		if (!isset($_SESSION['user']))
			$_SESSION['user'] = array();
		
		if (!isset($_SESSION['config'])) {
			$sql = "SELECT * FROM `letterit_mailer`";
			$_SESSION['config'] = $this->db->query_assoc($sql);
		}
		
		if (empty($_SESSION['config'])) {
			$this->create_empty_tables();
			unset($_SESSION['config']);
			echo "<br><br>",LNG_SYS_SETUP2;
			exit;
		}
		
		if (empty($_SESSION['config']['DB_VERS']))
			$this->update_database_from_null_to_180606();
		elseif ($_SESSION['config']['DB_VERS'] == '18.01.06')
			$this->update_database_from_180106_to_180606();
		elseif ($_SESSION['config']['DB_VERS'] == '18.06.06')
			$this->update_database_from_180606_to_180609();
		
		if (!isset($_SESSION['zones'])) {
			$sql = "SELECT * FROM `letterit_bereiche` ORDER BY `BID`";
			$_SESSION['zones'] = $this->db->fetch_assoc_key_array($sql, 'BID');
		}
		
		if (intval($_SESSION['config']['reload_send']) < 0)
			$_SESSION['config']['reload_send'] = 15;
		if (intval($_SESSION['config']['bounce_weiter']) < 0)
			$_SESSION['config']['bounce_weiter'] = 3;
	}
	
	function config() {
		echo "<h1>",LNG_SYS_CONFIG1,"</h1>";
		
		if (isset($_POST['reload_send'])) {
// debugarr($_POST);
			$reload_send	= param_int('reload_send', 15);
			$bounce_weiter	= param_int('bounce_weiter', 2);
			
			$sql = "UPDATE `letterit_mailer` SET `reload_send` = '".$reload_send."', `bounce_weiter` = '".$bounce_weiter."';";
			if ($this->db->query($sql)) {
				msg(LNG_SYS_CONFIG2, "success");
				
				// renew config in session
				$sql = "SELECT * FROM `letterit_mailer`";
				$_SESSION['config'] = $this->db->query_assoc($sql);
			}
			else
				msg(LNG_SYS_CONFIG3, "error");
		}
		
		echo "<form action='index.php?view=config' method='POST' accept-charset='utf-8'>";
		
		echo "<label for='reload_send' class='label'>",LNG_SYS_CONFIG4,":</label> <input type='text' name='reload_send' value='",intval($_SESSION['config']['reload_send']),"' required><br>";
		echo "<label for='bounce_weiter' class='label'>",LNG_SYS_CONFIG5,":</label> <input type='text' name='bounce_weiter' value='",intval($_SESSION['config']['bounce_weiter']),"' required><br>";
		
		echo "<br><input type='submit' value='",LNG_BTN_SAVE,"' class='button'>";
		
		echo "</form>";
	}
	
	function create_empty_tables() {
		echo LNG_SYS_SETUP3,"<br>";
		
		$sql = "CREATE TABLE IF NOT EXISTS `letterit_abonnenten` (`AID` int(11) NOT NULL AUTO_INCREMENT,`BID` int(3) NOT NULL DEFAULT '0',`Email` varchar(80) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`domain` varchar(60) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Datum` int(11) NOT NULL DEFAULT '0',`Option1` varchar(50) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Option2` varchar(50) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Option3` varchar(50) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Option4` varchar(50) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Code` varchar(15) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Abmeldezeit` int(11) NOT NULL DEFAULT '0',`Status` int(1) NOT NULL DEFAULT '0',`IP` varchar(40) COLLATE latin1_german1_ci NOT NULL DEFAULT '',PRIMARY KEY (`AID`),UNIQUE KEY `BID` (`BID`,`Email`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1;";
		$this->db->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS `letterit_bereiche` (`BID` int(3) NOT NULL AUTO_INCREMENT,`Bereich_Name` varchar(100) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Absender_Name` varchar(60) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Absender_Email` varchar(70) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Art` varchar(10) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`URL` varchar(150) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`LetteritURL` varchar(150) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Best_Betreff` varchar(150) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Best_Art` varchar(4) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Best_Text` text COLLATE latin1_german1_ci NOT NULL,`Best_HTML` text COLLATE latin1_german1_ci NOT NULL,`Best_HTML_Bilder` int(1) NOT NULL DEFAULT '0',`Anmeldebest` int(1) NOT NULL DEFAULT '0',`Anmelde_Betreff` varchar(150) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Anmelde_Art` varchar(4) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Anmelde_Text` text COLLATE latin1_german1_ci NOT NULL,`Anmelde_HTML` text COLLATE latin1_german1_ci NOT NULL,`Anmelde_HTML_Bilder` int(1) NOT NULL DEFAULT '0',`Abmeldebest` int(1) NOT NULL DEFAULT '0',`Abmelde_Betreff` varchar(150) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Abmelde_Art` varchar(4) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Abmelde_Text` text COLLATE latin1_german1_ci NOT NULL,`Abmelde_HTML` text COLLATE latin1_german1_ci NOT NULL,`Abmelde_HTML_Bilder` int(1) NOT NULL DEFAULT '0',`Abmeldelink_Text` text COLLATE latin1_german1_ci NOT NULL,`Abmeldelink_HTML` text COLLATE latin1_german1_ci NOT NULL,`Option1` text COLLATE latin1_german1_ci NOT NULL,`Option2` text COLLATE latin1_german1_ci NOT NULL,`Option3` text COLLATE latin1_german1_ci NOT NULL,`Option4` text COLLATE latin1_german1_ci NOT NULL,`Fullpage` int(1) NOT NULL DEFAULT '0',`Zeichensatz` varchar(10) COLLATE latin1_german1_ci NOT NULL,PRIMARY KEY (`BID`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1;";
		$this->db->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS `letterit_blacklist` (`Bl_ID` int(5) NOT NULL AUTO_INCREMENT,`Email` varchar(80) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Zeit` int(11) NOT NULL DEFAULT '0',PRIMARY KEY (`Bl_ID`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1;";
		$this->db->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS `letterit_clicks` (`LS_ID` int(3) NOT NULL DEFAULT '0',`Seite` varchar(150) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Hits` int(5) NOT NULL DEFAULT '0',PRIMARY KEY (`LS_ID`,`Seite`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;";
		$this->db->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS `letterit_mailer` (`Typ` varchar(10) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`sendmail_pfad` varchar(150) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`sendmail_delivery` varchar(30) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`qmail_pfad` varchar(150) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`mailroot_directory` varchar(150) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`smtp_server` varchar(50) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`smtp_user` varchar(25) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`smtp_password` varchar(25) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`smtp_port` int(5) NOT NULL DEFAULT '0',`emailcheck` int(1) NOT NULL DEFAULT '0',`PHP_Pfad` varchar(150) COLLATE latin1_german1_ci DEFAULT NULL,`Letzter_Check` int(11) NOT NULL DEFAULT '0',`update_verfuegbar` int(1) NOT NULL DEFAULT '0',`default_language` varchar(30) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Version` int(7) NOT NULL DEFAULT '0',`Max_Anhang` int(5) NOT NULL DEFAULT '0',`bounce_user` varchar(50) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`bounce_pass` varchar(50) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`bounce_port` int(5) NOT NULL DEFAULT '0',`bounce_host` varchar(150) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`bounce_email` varchar(70) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`bounce_anzahl` int(2) NOT NULL DEFAULT '0',`bounce_weiter` varchar(70) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`reload_send` int(2) NOT NULL DEFAULT '0') ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;";
		$this->db->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS `letterit_send` (`LS_ID` int(5) NOT NULL AUTO_INCREMENT,`HTML` text COLLATE latin1_german1_ci NOT NULL,`Text` text COLLATE latin1_german1_ci NOT NULL,`Betreff` varchar(150) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`BID` int(3) NOT NULL DEFAULT '0',`Status` int(1) NOT NULL DEFAULT '0',`Abo_send` int(10) NOT NULL DEFAULT '0',`Abo_send_time` int(11) NOT NULL DEFAULT '0',`Abonnenten` int(10) NOT NULL DEFAULT '0',`Start_time` int(11) NOT NULL DEFAULT '0',`Art` varchar(4) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Start` int(10) NOT NULL DEFAULT '0',`Code` varchar(20) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`URL_Stat` int(1) NOT NULL DEFAULT '0',`Bilder_einbinden` int(1) NOT NULL DEFAULT '0',PRIMARY KEY (`LS_ID`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1;";
		$this->db->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS `letterit_stats` (`BID` int(3) NOT NULL DEFAULT '0',`Monat` int(2) NOT NULL DEFAULT '0',`Jahr` int(4) NOT NULL DEFAULT '0',`Zugang` int(5) NOT NULL DEFAULT '0',`Abgang` int(5) NOT NULL DEFAULT '0',PRIMARY KEY (`BID`,`Monat`,`Jahr`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;";
		$this->db->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS `letterit_user` (`UID` int(3) NOT NULL AUTO_INCREMENT,`Login` varchar(30) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Passwort` varchar(32) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Sprache` varchar(100) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Status` int(1) NOT NULL DEFAULT '0',`Name` varchar(100) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`Loginzeit` int(11) NOT NULL DEFAULT '0',PRIMARY KEY (`UID`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1;";
		$this->db->query($sql);
		$sql = "CREATE TABLE IF NOT EXISTS `letterit_vorlagen` (`VO_ID` int(3) NOT NULL AUTO_INCREMENT,`BID` int(3) NOT NULL DEFAULT '0',`Name` varchar(60) COLLATE latin1_german1_ci NOT NULL DEFAULT '',`HTML` text COLLATE latin1_german1_ci NOT NULL,`Text` text COLLATE latin1_german1_ci NOT NULL, PRIMARY KEY (`VO_ID`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1;";
		$this->db->query($sql);
		
		echo LNG_SYS_SETUP4,"<br>";
		
		$sql  = "INSERT INTO `letterit_mailer` (`Typ`, `sendmail_pfad`, `sendmail_delivery`, `qmail_pfad`, `mailroot_directory`, `smtp_server`, `smtp_user`, `smtp_password`, `smtp_port`, `emailcheck`, `PHP_Pfad`, `Letzter_Check`, `update_verfuegbar`, `default_language`, `Version`, `Max_Anhang`, `bounce_user`, `bounce_pass`, `bounce_port`, `bounce_host`, `bounce_email`, `bounce_anzahl`, `bounce_weiter`, `reload_send`) VALUES";
		$sql .= " ('PHP', '/usr/lib/sendmail', 'SENDMAIL_DELIVERY_DEFAULT', '/var/qmail/bin', '', '', 'user', 'pass', 25, 0, '', 1460458321, 0, 'german', 2070726, 0, '', '', 0, '', '', 0, '3', 15);";
		$this->db->query($sql);
		
		$sql  = "INSERT INTO `letterit_user` (`UID`, `Login`, `Passwort`, `Sprache`, `Status`, `Name`, `Loginzeit`) VALUES";
		$sql .= " (1, 'admin', '".md5('admin')."', 'german', 1, 'me@domain.de', ".time().");";
		$this->db->query($sql);
		
		$sql  = "INSERT INTO `letterit_bereiche` (`BID`, `Bereich_Name`, `Absender_Name`, `Absender_Email`, `Art`, `URL`, `LetteritURL`, `Best_Betreff`, `Best_Art`, `Best_Text`, `Best_HTML`, `Best_HTML_Bilder`, `Anmeldebest`, `Anmelde_Betreff`, `Anmelde_Art`, `Anmelde_Text`, `Anmelde_HTML`, `Anmelde_HTML_Bilder`, `Abmeldebest`, `Abmelde_Betreff`, `Abmelde_Art`, `Abmelde_Text`, `Abmelde_HTML`, `Abmelde_HTML_Bilder`, `Abmeldelink_Text`, `Abmeldelink_HTML`, `Option1`, `Option2`, `Option3`, `Option4`, `Fullpage`, `Zeichensatz`) VALUES";
		$sql .= " ('1', 'Demo', 'Demo Name', 'demo@mail.com', '', '', '', '', '', '', '', '0', '0', '', '', '', '', '0', '0', '', '', '', '', '0', '', '', '', '', '', '', '0', '');";
		$this->db->query($sql);
		
		echo LNG_SYS_SETUP5,"<br>";
	}
	
	function update_database_from_null_to_180606() {
		echo LNG_SYS_SETUP6,"<br>";

		$sql = "ALTER TABLE `letterit_mailer` ADD `DB_VERS` VARCHAR(8) NOT NULL AFTER `reload_send`;";
		if ($this->db->query($sql)) {
			$sql = "UPDATE `letterit_mailer` SET `DB_VERS` = '18.01.06';";
			if ($this->db->query($sql)) {
				msg(LNG_SYS_CONFIG2, "success");
				unset($_SESSION['config']);
			}
			else
				msg(LNG_SYS_SETUP6, "error");
		}
		else
			msg(LNG_SYS_SETUP6, "error");

		echo "<br><br>",LNG_SYS_SETUP2;
		exit;
	}

	function update_database_from_180106_to_180606() {
		$sql = "DROP TABLE `letterit_anhang`;";
		$this->db->query($sql);
		$sql = "DROP TABLE `letterit_bounce`;";
		$this->db->query($sql);
		$sql = "DROP TABLE `letterit_clicks`;";
		$this->db->query($sql);
		$sql = "DROP TABLE `letterit_rechte`;";
		$this->db->query($sql);
		$sql = "DROP TABLE `letterit_logincheck`;";
		$this->db->query($sql);

		$sql = "ALTER TABLE `letterit_stats` DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";
		$this->db->query($sql);
		$sql = "ALTER TABLE `letterit_stats` CHANGE `Monat` `month` INT(2) NOT NULL DEFAULT '0';";
		$this->db->query($sql);
		$sql = "ALTER TABLE `letterit_stats` CHANGE `Jahr` `year` INT(2) NOT NULL DEFAULT '0';";
		$this->db->query($sql);
		$sql = "ALTER TABLE `letterit_stats` CHANGE `Zugang` `registered` INT(2) NOT NULL DEFAULT '0';";
		$this->db->query($sql);
		$sql = "ALTER TABLE `letterit_stats` CHANGE `Abgang` `deregistered` INT(2) NOT NULL DEFAULT '0';";
		$this->db->query($sql);

		$sql = "CREATE TABLE `letterit_views` ( `BID` INT NOT NULL ,  `LS_ID` INT NOT NULL ,  `VIEWS` INT NOT NULL ,  `LAST_VIEW` INT NOT NULL ,  `CLICKS` INT NOT NULL ,  `LAST_CLICK` INT NOT NULL ) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci;";
		$this->db->query($sql);

		$sql = "UPDATE `letterit_mailer` SET `DB_VERS` = '18.06.06';";
		if ($this->db->query($sql)) {
			msg(LNG_SYS_SETUP6, "success");
			unset($_SESSION['config']);
		}
		else
			msg(LNG_SYS_SETUP6, "error");

		echo "<br><br>",LNG_SYS_SETUP2;
		exit;
	}

	function update_database_from_180606_to_180609() {
		$sql = "ALTER TABLE `letterit_abonnenten` DROP `IP`;";
		$this->db->query($sql);
		$sql = "ALTER TABLE `letterit_abonnenten` CHANGE `Abmeldezeit` `OptOutDT` INT(11) NOT NULL DEFAULT '0'";
		$this->db->query($sql);
		$sql = "ALTER TABLE `letterit_abonnenten` ADD `OptInDT` INT NOT NULL AFTER `Code` ";
		$this->db->query($sql);
		$sql = "ALTER TABLE `letterit_abonnenten` CHANGE `Datum` `RegisterDT` INT( 11 ) NOT NULL DEFAULT '0'";
		$this->db->query($sql);
		$sql = "ALTER TABLE `letterit_abonnenten` CHANGE `domain` `Domain` VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
		$this->db->query($sql);
		$sql = "ALTER TABLE `letterit_abonnenten` CHANGE `Code` `OptInCode` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
		$this->db->query($sql);
		$sql = "UPDATE `letterit_abonnenten` SET `OptInDT` = `RegisterDT` WHERE `OptInDT` = 0;";
		$this->db->query($sql);

		$sql = "UPDATE `letterit_mailer` SET `DB_VERS` = '18.06.09';";
		if ($this->db->query($sql)) {
			msg(LNG_SYS_SETUP6, "success");
			unset($_SESSION['config']);
		}
		else
			msg(LNG_SYS_SETUP6, "error");

		echo "<br><br>",LNG_SYS_SETUP2;
		exit;
	}
}
?>