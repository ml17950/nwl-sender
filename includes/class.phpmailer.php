<?php
class phpmailer {
	var $ErrorInfo;
	var $XMailer;
	var $Version;
	var $Hostname;
	var $Boundary;
	var $LE;
	var $Sender;
	var $CharSet;
	var $Priority;
	var $WordWrap;
	var $From;
	var $FromName;
	var $ReplyMail;
	var $ReplyName;
	var $ToMail;
	var $ToName;
	var $UnscribeLink;
	
	var $Subject;
	var $AltBody;
	var $Body;
	
	function __construct() {
		$this->LE = "\r\n";
		$this->Hostname = 'service2solution.de';
		$this->Priority = 3;
		$this->CharSet = 'iso-8859-1';
		$this->WordWrap = 70;
	}
	
	function __destruct() {
	}
	
	function IsMail() {
	}
	
	function AddReplyTo($mail, $name) {
		$this->ReplyMail = $mail;
		$this->ReplyName = $name;
	}
	
	function SetAddress($mail, $name) {
		$this->ToMail = $mail;
		$this->ToName = $name;
	}
	
	function AddHeaderLine($key, $val) {
		return $key.': '.$val.$this->LE;
	}
	
	function AddHeaderBoundary() {
// 		$this->Boundary = '----=_NextPart_000_0001_01D18F13.8A4EB6A0';
		$this->Boundary = '=boundary_'.uniqid('np').'=';
		
		$ret  = 'Content-Type: multipart/alternative;'.$this->LE;
		$ret .= ' boundary="'.$this->Boundary.'"'.$this->LE;
		return $ret;
	}
	
	function AddMessageBoundaryStart($type = 'text/plain', $charset = '', $encoding = '8bit') {
		if (empty($charset))
			$charset = $this->CharSet;
		
		$ret  = '--'.$this->Boundary.$this->LE;
		$ret .= 'Content-Type: '.$type.'; charset="'.$charset.'"'.$this->LE;
		$ret .= 'Content-Transfer-Encoding: '.$encoding.$this->LE.$this->LE;
		return $ret;
	}
	
	function AddMessageBoundaryEnd() {
		return '--'.$this->Boundary.'--'.$this->LE.$this->LE;
	}
	
	function BuildSubject() {
		return $this->Subject;
	}
	
	function BuildMessage() {
		$ret = '';
		
// 		$ret .= 'This is a multipart message in MIME format.'.$this->LE;
		$ret .= 'This is a MIME encoded message.'.$this->LE;
		$ret .= $this->LE;
		$ret .= $this->AddMessageBoundaryStart('text/plain', $this->CharSet, '8bit');
// 		$ret .= chunk_split($this->AltBody, $this->WordWrap, $this->LE);
		$ret .= wordwrap($this->AltBody, $this->WordWrap, $this->LE);
		$ret .= $this->LE;
		$ret .= $this->LE;
		$ret .= $this->AddMessageBoundaryStart('text/html', $this->CharSet, '8bit');
// 		$ret .= chunk_split($this->Body, $this->WordWrap, $this->LE);
		$ret .= wordwrap($this->Body, $this->WordWrap, $this->LE);
		$ret .= $this->LE;
		$ret .= $this->LE;
		$ret .= $this->LE;
		$ret .= $this->AddMessageBoundaryEnd();
		
		return $ret;
	}
	
	function BuildHeaders() {
		$ret = '';
		$uniq_id = md5(uniqid(time()));
		
		$ret .= $this->AddHeaderLine('Date', date('r'));
		if (empty($this->FromName)) {
			$ret .= $this->AddHeaderLine('From', $this->From.' <'.$this->From.'>');
			$ret .= $this->AddHeaderLine('Reply-to', $this->From.' <'.$this->From.'>');
		}
		else {
			$ret .= $this->AddHeaderLine('From', $this->FromName.' <'.$this->From.'>');
			$ret .= $this->AddHeaderLine('Reply-to', $this->FromName.' <'.$this->From.'>');
		}
		$ret .= $this->AddHeaderLine('Return-Path', $this->From);
		$ret .= $this->AddHeaderLine('Message-ID', '<'.$uniq_id.'@'.$this->Hostname.'>');
		$ret .= $this->AddHeaderLine('MIME-Version', '1.0');
		$ret .= $this->AddHeaderBoundary();
		if ($this->Priority != 3)
			$ret .= $this->AddHeaderLine('X-Priority', $this->Priority);
// 		$ret .= $this->AddHeaderLine('X-Mailer', $this->XMailer.' [version '.$this->Version.']');
		$ret .= $this->AddHeaderLine('X-Mailer', 'PHP/'.phpversion());
		if (!empty($this->UnscribeLink))
			$ret .= $this->AddHeaderLine('List-Unsubscribe', '<'.$this->UnscribeLink.'>');
		
		return $ret;
	}
	
	function Send() {
		$headers = $this->BuildHeaders();
		$subject = $this->BuildSubject();
		$message = $this->BuildMessage();
		
// 		debugarr($_SERVER);
		
// 		file_put_contents(ABS_PATH.'includes/xxx.head', $headers);
// 		file_put_contents(ABS_PATH.'includes/xxx.body', $message);
// 		file_put_contents(ABS_PATH.'includes/xxx.mail', $headers.$this->LE.$this->LE.$message);
		
// 		return true;
		return mail($this->ToMail, $subject, $message, $headers);
	}
}
?>