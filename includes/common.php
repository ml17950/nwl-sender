<?php
	function param($key, $dflt = '') {
		if (isset($_GET[$key]) && ($_GET[$key] != ''))
			return trim($_GET[$key]);
		else {
			if (isset($_POST[$key]) && ($_POST[$key] != '')) {
				if (is_array($_POST[$key]))
					return $_POST[$key];
				else
					return trim($_POST[$key]);
			}
			else
				return $dflt;
		}
	}
	
	function param_int($key, $dflt = 0) {
		if (isset($_POST[$key]) && ($_POST[$key] != '')) {
			if (is_array($_POST[$key]))
				return $_POST[$key];
			else
				return intval(trim($_POST[$key]));
		}
		elseif (isset($_GET[$key]) && ($_GET[$key] != ''))
			return intval(trim($_GET[$key]));
		else
			return $dflt;
	}
	
	function debugarr(&$arr) {
		echo highlight_string(print_r($arr, true)),"<hr>";
	}
	
	function debugsql($sql) {
		echo "<hr>",$sql,"<hr>";
	}
	
	function msg($msg, $type = 'info') {
		echo "<div class='msgbox msg-",$type,"'>",$msg,"</div>";
	}
	
	function remove_bad_chars($text) {
		$text = str_replace("'", "\\'", $text);
		$text = addslashes($text);
		return $text;
	}
	
	function check_mail($mailadr) {
		// TODO
		return true;
	}
	
	function redirect($url, $seconds = 3) {
		echo "<meta http-equiv='Refresh' content='",$seconds,"; URL=",$url,"'>";
	}
	
	function zname($bid) {
		return $_SESSION['zones'][$bid]['Bereich_Name'];
	}
?>