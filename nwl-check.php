<?php
	session_start();
	date_default_timezone_set('Europe/Berlin');
	
	include_once('includes/defines.php');
	include_once('includes/common.php');
	include_once('includes/class.core.php');
	$core = new clsCore();
	
	// =========================================================================
	
	$core->ui->html_head();
	
	// =========================================================================
	
	$btntxt = 'Anmelden';
	$errmsg = '';
	
	// =========================================================================
	
	$bid = param_int('id');
	$email = param('email');
	
	echo "<form name='lfrm' action='nwl-check.php?id=",$bid,"' method='POST' accept-charset='utf-8'>";
// 	echo "<input type='hidden' name='id' value='",$bid,"'>";
	
	if (!empty($email)) {
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			echo "TODO: asaaaaa";
		}
		else {
			$name = param('ename');
			$ehpdo = param('ehpdo');
			
			switch ($ehpdo) {
				case '':
				case 'check':
					$sql = "SELECT `AID`,`Email`,`RegisterDT`,`Option1`,`OptInDT`,`OptOutDT`,`Status` FROM `letterit_abonnenten` WHERE `BID` = ".$bid." AND `Email` LIKE '".$email."';";
					$info = $core->db->query_assoc($sql);
					if (!empty($info['AID'])) {
						if ($info['Status'] == ABO_ACTIVE) {
							msg('Die Adresse '.$email.' ist bereits angemeldet', 'success');
							echo "<label for='email'>Email</label> <input type='text' name='email' value='",$email,"' style='width: 200px;' required><br>";
// 							echo "<label for='ename'>Name</label>  <input type='text' name='ename' value='",$name,"' style='width: 200px;' placeholder='Optional'><br>";
							echo "<input type='text' name='ehpdo' value='opt-out' style='width: 200px; display: none;' placeholder='Optional'><br>";
							$btntxt = 'Abmelden';
						}
						elseif ($info['Status'] == ABO_VALIDATE) {
							$core->nwl->opt_in($bid, $email, $name);
							msg('Eine Bestätigungsmail wurde erneut an '.$email.' geschickt', 'info');
							echo "<label for='email'>Email</label> <input type='text' name='email' value='",$email,"' style='width: 200px;' required><br>";
// 							echo "<label for='ename'>Name</label>  <input type='text' name='ename' value='",$name,"' style='width: 200px;' placeholder='Optional'><br>";
							echo "<input type='text' name='ehpdo' value='opt-in' style='width: 200px; display: none;' placeholder='Optional'><br>";
							$btntxt = 'Erneut senden';
						}
						else {
							msg('Die Adresse '.$email.' wurde am '.date('d.m.y', $info['OptOutDT']).' abgemeldet', 'info');
							echo "<label for='email'>Email</label> <input type='text' name='email' value='",$email,"' style='width: 200px;' required><br>";
// 							echo "<label for='ename'>Name</label>  <input type='text' name='ename' value='",$name,"' style='width: 200px;' placeholder='Optional'><br>";
							echo "<input type='text' name='ehpdo' value='opt-in' style='width: 200px; display: none;' placeholder='Optional'><br>";
							$btntxt = 'Erneut anmelden';
						}
					}
					else {
// 						$core->nwl->opt_in($bid, $email, $name);
// 						msg('Danke für die Anmeldung. Eine Bestätigungsmail wurde an '.$email.' geschickt', 'success');
						msg('Diese Adresse ist momentan nicht angemeldet', 'info');
						echo "<label for='email'>Email</label> <input type='text' name='email' value='",$email,"' style='width: 200px;' required><br>";
// 						echo "<label for='ename'>Name</label>  <input type='text' name='ename' value='",$name,"' style='width: 200px;' placeholder='Optional'><br>";
						echo "<input type='text' name='ehpdo' value='opt-in' style='width: 200px; display: none;' placeholder='Optional'><br>";
						$btntxt = 'Anmelden';
					}
					break;
				
				case 'opt-in':
					$core->nwl->opt_in($bid, $email, $name);
// 					msg('Eine Bestätigungsmail wurde an '.$email.' geschickt', 'info');
					msg('Danke für die Anmeldung. Eine Bestätigungsmail wurde an '.$email.' geschickt', 'success');
					echo "<label for='email'>Email</label> <input type='text' name='email' value='",$email,"' style='width: 200px;' required><br>";
// 					echo "<label for='ename'>Name</label>  <input type='text' name='ename' value='",$name,"' style='width: 200px;' placeholder='Optional'><br>";
					echo "<input type='text' name='ehpdo' value='opt-in' style='width: 200px; display: none;' placeholder='Optional'><br>";
					$btntxt = 'Erneut senden';
					break;
				
				case 'validate':
					$code = param('code');
					if ($core->nwl->opt_validate($bid, $email, $code)) {
						msg('Erfolgreich angemeldet', 'success');
						echo "<label for='email'>Email</label> <input type='text' name='email' value='",$email,"' style='width: 200px;' required><br>";
// 						echo "<label for='ename'>Name</label>  <input type='text' name='ename' value='",$name,"' style='width: 200px;' placeholder='Optional'><br>";
						echo "<input type='text' name='ehpdo' value='opt-out' style='width: 200px; display: none;' placeholder='Optional'><br>";
						$btntxt = 'Abmelden';
// 						redirect('/', 5);
					}
					else {
						msg('Anmeldung fehlgeschlagen', 'error');
						echo "<label for='email'>Email</label> <input type='text' name='email' value='",$email,"' style='width: 200px;' required><br>";
// 						echo "<label for='ename'>Name</label>  <input type='text' name='ename' value='",$name,"' style='width: 200px;' placeholder='Optional'><br>";
						echo "<input type='text' name='ehpdo' value='opt-in' style='width: 200px; display: none;' placeholder='Optional'><br>";
					}
					break;
				
				case 'opt-out':
					$core->nwl->opt_out($bid, $email);
					msg('Erfolgreich abgemeldet.', 'success');
// 					redirect('/', 5);
					$btntxt = '';
					break;
			}
		}
	}
	else {
		$name = '';
		$email = '';
		
		echo "<label for='email'>Email</label> <input type='text' name='email' value='",$email,"' style='width: 200px;' required><br>";
// 		echo "<label for='ename'>Name</label>  <input type='text' name='ename' value='",$name,"' style='width: 200px;' placeholder='Optional'><br>";
		echo "<input type='text' name='ehpdo' value='' style='width: 200px; display: none;' placeholder='Optional'><br>";
	}
	
	if (!empty($btntxt)) {
		echo "<br>";
		echo "<input type='submit' value='",$btntxt,"' class='button'>";
	}
	
	if (!empty($errmsg))
		echo "<hr>",$errmsg,"<hr>";
	
	echo "</form>\n";
	
	// =========================================================================
	
echo "<br><hr>";
debugarr($_POST);
	
	$core->ui->html_foot();
?>