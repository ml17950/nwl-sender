<?php
// 	error_reporting(E_ERROR | E_WARNING | E_PARSE);
// 	ini_set('display_errors', 1);

	session_start();
	date_default_timezone_set('Europe/Berlin');

	if (file_exists('../newsletter/config/config.php'))
		include_once('../newsletter/config/config.php');
	else
		exit("../newsletter/config/config.php not found");
	include_once('../newsletter/includes/defines.php');
	include_once('../newsletter/includes/common.php');
	include_once('../newsletter/includes/class.core.php');
	$core = new clsCore();
	$core->initialize();

	$bid = param_int('id');
	$lsid = param_int('ls');

	$core->stats->add_click($bid, $lsid);

	header('Location: https://service2solution.de/');
	exit;
?>