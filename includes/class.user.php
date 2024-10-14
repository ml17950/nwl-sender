<?php
class clsUser {
	var $db;

	function __construct(&$db) {
		$this->db = $db;
	}

	function is_loggedin($view) {
		if ($view == 'login') {
			// debugarr($_POST);
			$usr = param('usrname');
			$pwd = param('usrpass');
			$this->login($usr, $pwd);
		}

		if (!isset($_SESSION['user']['UID']))
			$_SESSION['user']['UID'] = 0;

		if (intval($_SESSION['user']['UID']) > 0)
			return true;
		else
			return false;
	}

	function login_form() {
		echo "<div id='lbox'>";
		echo "	<form name='lfrm' action='index.php?view=login' method='POST' accept-charset='utf-8'>";

		echo "	<div id='lbox_head'>";
		echo "		NWL Login";
		echo "	</div>";
		echo "	<div id='lbox_body'>";
		echo "		<label for='usrname' class='lbox_label'>Benutzername:</label>";
		echo "		<input id='usrname' class='lbox_input' name='usrname' type='text' value=''>";
		echo "		<br>";
		echo "		<label for='usrpass' class='lbox_label'>Passwort:</label>";
		echo "		<input id='usrpass' class='lbox_input' name='usrpass' type='password'>";
		echo "	</div>";
		// if (!empty($loginbox_msg))
			// echo "<div id='lbox_msg'>",$loginbox_msg,"</div>";
		echo "	<div id='lbox_foot'>";
		echo "		<input type='submit' value='Anmelden' class='lbox_submit' tabindex='4'>";
		echo "		<div style='clear: both;'></div>";
		echo "	</div>";

		echo "	</form>";
		echo "</div>";
	}

	function login($username, $password) {
		$sql = "SELECT * FROM `letterit_user` WHERE `Login` = '".htmlspecialchars($username)."'";
		$user = $this->db->query_assoc($sql);

		if ($this->db->num_rows == 1) {
			if (md5($password) === $user['Passwort']) {
				$_SESSION['user'] = $user;
			}
		}
	}

	function logout() {
		echo "<h1>Abmelden</h1>";

		unset($_SESSION['user']);
		unset($_SESSION['config']);
		unset($_SESSION['zones']);
		// unset($_SESSION['user']);

		msg("Abmeldung erfolgreich - <a href='index.php'>erneut Anmelden</a>", "success");
	}

	function renew_session() {
		// unset($_SESSION['user']);
		unset($_SESSION['config']);
		unset($_SESSION['zones']);
	}

	function set_password() {
		echo "<h1>Passwort/Mail ändern</h1>";

		$pwd1 = param('pwd1');
		$pwd2 = param('pwd2');
		$mail = param('mail');

		if (!empty($pwd1)) {
			if ($pwd1 == $pwd2) {
				$sql = "UPDATE `letterit_user` SET `Passwort` = '".md5($pwd1)."', `Name` = '".$mail."' WHERE `UID` = ".$_SESSION['user']['UID'].";";
				$this->db->query($sql);

				if (md5($pwd1) == $_SESSION['user']['Passwort']) {
					$_SESSION['user']['Name'] = $mail;

					msg("Email erfolgreich geändert", "success");
				}
				else {
					msg("Passwort erfolgreich geändert", "success");

					$this->logout();
					redirect('index.php', 3);
					exit;
				}
			}
			else
				msg("Passwörter stimmen nicht überein", "error");
		}

		echo "<form action='index.php?view=password' method='POST' accept-charset='utf-8'>";

		echo "<label for='pwd1' class='label'>Passwort:</label>";
		echo "<input type='password' name='pwd1' value='' required>";
		echo "<br>";
		echo "<label for='pwd2' class='label'>Wiederholen:</label>";
		echo "<input type='password' name='pwd2' value='' required>";
		echo "<br>";
		echo "<label for='mail' class='label'>Email:</label>";
		echo "<input type='text' name='mail' value='",$_SESSION['user']['Name'],"'>";
		echo "<br>";
		echo "<br><input type='submit' value='Speichern' class='button'>";

		echo "</form>";
	}
}
?>