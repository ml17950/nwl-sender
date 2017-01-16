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
		echo "<h1>Einstellungen</h1>";
		
		if (isset($_POST['reload_send'])) {
// debugarr($_POST);
			$reload_send	= param_int('reload_send', 15);
			$bounce_weiter	= param_int('bounce_weiter', 2);
			
			$sql = "UPDATE `letterit_mailer` SET `reload_send` = '".$reload_send."', `bounce_weiter` = '".$bounce_weiter."';";
			if ($this->db->query($sql)) {
				msg("Einstellungen gespeichert", "success");
				
				// renew config in session
				$sql = "SELECT * FROM `letterit_mailer`";
				$_SESSION['config'] = $this->db->query_assoc($sql);
			}
			else
				msg("Einstellungen nicht gespeichert", "error");
		}
		
		echo "<form action='index2.php?view=config' method='POST' accept-charset='utf-8'>";
		
		echo "<label for='reload_send' class='label'>Anzahl Mails pro Sendeblock (1-50 Stk):</label> <input type='text' name='reload_send' value='",intval($_SESSION['config']['reload_send']),"' required><br>";
		echo "<label for='bounce_weiter' class='label'>Pause zwischen Sendeblock (1-30 Sek):</label> <input type='text' name='bounce_weiter' value='",intval($_SESSION['config']['bounce_weiter']),"' required><br>";
		
		echo "<br><input type='submit' value='Speichern' class='button'>";
		
		echo "</form>";
	}
}
?>