<?php
class clsMailer {
	var $db;
	var $mail;
	var $errormsg;
	var $preview_text;
	var $preview_html;
	
	function __construct(&$db) {
// 		echo "<!--",__CLASS__,":",__FUNCTION__,"-->\n";
		
		$this->db = $db;
		
		include_once('class.phpmailer.php');
// 		include_once('mail/class.phpmailer.php');
		$this->mail = new phpmailer();
// 		$this->mail->XMailer = 'NWL-Sender';
		$this->mail->XMailer = 'info by service2solution.de';
		$this->mail->Version = VERSION;
		
		define('SMTP_HOST', 'smtp.service2solution.de');
		define('SMTP_USER', 'service2solution.de12');
		define('SMTP_PASS', 'i8tU3mLf');
		
		$this->mail->IsMail();				// per PHP Mail verschicken
		$this->mail->LE = "\n";
		
//         $this->mail->IsSMTP();				// per SMTP verschicken
//         $this->mail->Host     = SMTP_HOST;	// SMTP-Server
//         $this->mail->SMTPAuth = false;		// SMTP mit Authentifizierung benutzen
//         $this->mail->Username = SMTP_USER;	// SMTP-Benutzernames
//         $this->mail->Password = SMTP_PASS;	// SMTP-Passwort
//         
// // 		$this->mail->SMTPKeepAlive = true;
// 		$this->mail->SMTPDebug = true;
	}
	
	function prepare_mail($bid, $email, $subject, $html, $text, $validatelink = '') {
		$replace['!!validatelink!!'] = $validatelink;
		$replace['!!unsubmittext!!'] = $_SESSION['zones'][$bid]['Abmeldelink_Text'];
		$replace['!!unsubmitlink!!'] = $_SESSION['zones'][$bid]['LetteritURL'];
		$replace['!!email!!'] = $email;
		$replace['!!bid!!'] = $bid;
		$replace['!!option1!!'] = $_SESSION['zones'][$bid]['Option1'];
		$replace['!!option2!!'] = $_SESSION['zones'][$bid]['Option2'];
		$replace['!!option3!!'] = $_SESSION['zones'][$bid]['Option3'];
		$replace['!!option4!!'] = $_SESSION['zones'][$bid]['Option4'];
		
		$what = array_keys($replace);
		$with = array_values($replace);
		$replaced_text = str_replace($what, $with, $text);
		
		$replaced_subject = str_replace($what, $with, $subject);
		
// file_put_contents('debug.temp', $email);
// 		debugarr($replace);
// 		debugarr($what);
// 		debugarr($with);
		
		$replace['!!unsubmittext!!'] = $_SESSION['zones'][$bid]['Abmeldelink_HTML'];
		$what = array_keys($replace);
		$with = array_values($replace);
		$replaced_html = str_replace($what, $with, $html);
		
// 		debugarr($replace);
// 		debugarr($what);
// 		debugarr($with);
		
		$replaced_UnscribeLink = str_replace($what, $with, $_SESSION['zones'][$bid]['LetteritURL']);
		$this->mail->UnscribeLink	= $replaced_UnscribeLink;
		
		if (!empty($_SESSION['config']['bounce_email']))
			$this->mail->Sender		= $_SESSION['config']['bounce_email'];
		else
			$this->mail->Sender		= $_SESSION['zones'][$bid]['Absender_Email'];
		
		$this->mail->From		= $_SESSION['zones'][$bid]['Absender_Email'];
    	$this->mail->FromName	= $_SESSION['zones'][$bid]['Absender_Name'];
		$this->mail->AddReplyTo($_SESSION['zones'][$bid]['Absender_Email'], $_SESSION['zones'][$bid]['Absender_Name']);
		
		$this->mail->CharSet	= $_SESSION['zones'][$bid]['Zeichensatz'];
		
		$this->mail->WordWrap	= 80;
		$this->mail->Subject	= $replaced_subject;
		
		$pre_body  = "<html>\r\n<head>\r\n<meta http-equiv='content-type' content='text/html; charset=".$_SESSION['zones'][$bid]['Zeichensatz']."'>\r\n</head>\r\n<body bgcolor='#FFFFFF' text='#000000' style='font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.5em;'>\r\n";
		$post_body = "\r\n</body>\r\n</html>";
		
		$this->mail->AltBody	= stripslashes($replaced_text);
		$this->mail->Body		= $pre_body.stripslashes($replaced_html).$post_body;
		
		$this->preview_text		= stripslashes($replaced_text);
		$this->preview_html		= stripslashes($replaced_html);
	}
	
	function send_single_mail($to_mail, $preview = false) {
		$ret = true;
		
		if ($preview) {
			$this->mail->SetAddress($to_mail, $to_mail);
			$ret = $this->mail->Send();
			if (!$ret)
				$this->errormsg = $this->mail->ErrorInfo;
		}
		else {
// 			$tmp = explode('@', $to_mail);
// 			$to_mail = $tmp[0].'@andev.de';
			$this->mail->SetAddress($to_mail, $to_mail);
			$ret = $this->mail->Send();
			if (!$ret)
				$this->errormsg = $this->mail->ErrorInfo;
		}
		
		return $ret;
	}
}
?>