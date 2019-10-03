<?php

	if (!ini_get('date.timezone')) {
		date_default_timezone_set('Europe/Paris');
	}

	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

	include_once('bin/config/constants.php');
	include_once('bin/vendor/autoload.php');
	include_once('bin/config/auto_load.php');
	include_once('bin/functions/function.php');
	include_once('bin/functions/function_prod.php');

	global $LATTE;



	$session = new Session();

	session_set_save_handler(
		[$session, 'open'],
		[$session, 'close'],
		[$session, 'read'],
		[$session, 'write'],
		[$session, 'destroy'],
		[$session, 'gc']
	);

	session_start();
