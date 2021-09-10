<?php

	header("Access-Control-Allow-Origin: *");
	header('Access-Control-Allow-Origin: *');
	header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
	header("Allow: GET, POST, OPTIONS, PUT, DELETE");

	if (!ini_get('date.timezone')) {
		date_default_timezone_set('Europe/Paris');
	}

	ini_set('xdebug.var_display_max_depth', '30');
	ini_set('xdebug.var_display_max_children', '256');
	ini_set('xdebug.var_display_max_data', '1024');
	// todo fix this
	// ini_set('error_reporting','~E_NOTICE');
	if (function_exists('xdebug_disable')) {
		xdebug_disable();
	}

	include_once('bin/vendor/autoload.php');
	include_once('bin/config/constants.php');
	include_once('bin/config/auto_load.php');
	include_once('bin/functions/function.php');
	include_once('bin/functions/function_prod.php');

	global $LATTE;



	/*$session = new Session();

	session_set_save_handler(
		[$session, 'open'],
		[$session, 'close'],
		[$session, 'read'],
		[$session, 'write'],
		[$session, 'destroy'],
		[$session, 'gc']
	);

	session_start();*/
