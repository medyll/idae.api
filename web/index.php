<?php
	include_once($_SERVER['CONF_INC']);

	$fp =  fsockopen($DOMAIN, SOCKETIO_PORT, $errno, $errstr, 5);
	if (!$fp) {
		$output = shell_exec('/bin/bash auto_start.sh');
		echo "<pre>$output</pre>";
		exit;
	}


	$Router = new Router();

