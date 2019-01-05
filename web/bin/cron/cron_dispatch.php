<?
	include_once($_SERVER['CONF_INC']);

	$APP = new App();
	$APP->init_scheme('sitebase_base', 'cron');
	$APP       = new App('cron');
	$type_cron = $_GET['type_cron'];

	$file_name = __DIR__ . '/cron_' . $type_cron . '.php';

	if (file_exists($file_name)) {
		include($file_name);
	};
