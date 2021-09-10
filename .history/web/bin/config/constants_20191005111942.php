<?php
	/**
	 * MongoDB : MDB_PASSWORD MDB_HOST MDB_USER MDB_PREFIX
	 * socket.io : SOCKETIO_PORT SOCKETIO_HOST
	 * ENVIRONEMENT : PROD PREPROD PREPROD_LAN
	 * smtp : SMTPDOMAIN
	 */
	$host = str_replace('www.', '', $_SERVER['HTTP_HOST']);

	switch ($host) {


		case "idae.api.lan":
		default:
			$DOMAIN    = "idae.api.lan";
			$DIRECTORY = "idae.api.lan";

			DEFINE('DIRECTORY', $DIRECTORY);
			DEFINE('ROOT_WWW', "");
			DEFINE('SOCKETIO_PORT', 3008);
			DEFINE('SOCKETIO_HOST', 'http://idae.api.lan');

			DEFINE("MDB_PASSWORD", "gwetme2011");
			DEFINE("MDB_PREFIX", "idaenext_"); // tactac_ // crfr_ // maw_ // idaenext_

			DEFINE('ENVIRONEMENT', 'PREPROD_LAN');


			break;
	}

	$HTTP_PREFIX = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'https://';
	DEFINE('BUSINESS', 'foodlivery');// DEFINE('BUSINESS', 'commercial');
	DEFINE('CUSTOMER', 'tactac');// DEFINE('BUSINESS', 'commercial');

	DEFINE('SITEPATH', ROOT_WWW . $DIRECTORY . '/web/');
	DEFINE('APPPATH', ROOT_WWW . $DIRECTORY . '/web/');
	DEFINE('CUSTOMERPATH', ROOT_WWW . $DIRECTORY . '/web/');

	DEFINE('HTTPSITE', $HTTP_PREFIX . $DOMAIN);

	DEFINE('APPNAME', 'tac-tac');
	DEFINE('CUSTOMERNAME', 'tactac');
	DEFINE('DOCUMENTDOMAIN', $DOMAIN);
	DEFINE('DOCUMENTDOMAINNOPORT', $DOMAIN);
	DEFINE('DOCUMENTDOMAINPORT', '');
	DEFINE('HTTPCUSTOMERSITE', $HTTP_PREFIX . $DOMAIN . '/');
	//
	DEFINE('HTTPAPP', $HTTP_PREFIX . $DOMAIN . '/');
	//
	DEFINE('FLATTENIMGDIR', CUSTOMERPATH . 'images_base/' . CUSTOMERNAME . '/');
	DEFINE('FLATTENIMGHTTP', HTTPCUSTOMERSITE . '/images_base/' . CUSTOMERNAME . '/');
	//
	/*DEFINE('SOCKETIO_PORT', 3007);
	DEFINE('SOCKETIO_HOST', 'https://tac-tac.shop.mydde.fr');*/
	//
	DEFINE('APP_CONFIG_DIR', APPPATH . 'bin/config/');
	DEFINE('APPMDL', APPPATH . 'bin/idae/mdl/');
	DEFINE('APPLESS', 'appcss/');
	DEFINE('APPTPL', APPPATH . 'bin/templates/');
	DEFINE('PATHTMP', APPPATH . '/tmp/');
	DEFINE('ADODBDIR', APPPATH . '/adodb/');
	DEFINE('REPFONCTIONS_APP', APPPATH . 'bin/functions/');
	DEFINE('XMLDIR', APPPATH . 'xmlfiles/');
	//
	DEFINE("APPCLASSES", APPPATH . "bin/classes/");
	DEFINE("APPCLASSES_ENGINE", APPPATH . "bin/engine/");
	DEFINE("APPCLASSES_VIEWS", APPPATH . "bin/views/");
	DEFINE("APPCLASSES_TOOLS", APPPATH . "bin/tools/");
	DEFINE("APPCLASSES_APP", APPPATH . "bin/classes_app/");
	DEFINE("OLDAPPCLASSES", APPPATH . "classes/");
	DEFINE('REPFONCTIONS', APPPATH . 'bin/functions/');
	//
	DEFINE('HTTPHOST', $HTTP_PREFIX . DOCUMENTDOMAIN);
	DEFINE('HTTPHOSTNOPORT', $HTTP_PREFIX . DOCUMENTDOMAINNOPORT);
	//
	DEFINE("SQL_HOST", "localhost");
	DEFINE("SQL_BDD", "crm_general_new");
	DEFINE("SQL_USER", "root");
	DEFINE("SQL_PASSWORD", "redPoi654pied");
	//
	DEFINE("MDB_HOST", "127.0.0.1");
	DEFINE("MDB_USER", "admin");
	//

	//
	DEFINE('SMTPHOSTGED', 'mail.mydde.fr');
	DEFINE('SMTPUSERGED', 'ged.idae@mydde.fr'); //
	DEFINE('SMTPEMAILGED', 'ged.idae@mydde.fr');
	DEFINE('SMTPPASSGED', 'malaterre654');

	global $buildArr;
	global $IMG_SIZE_ARR;
	/** @deprecated   $IMG_SIZE_ARR */
	$IMG_SIZE_ARR = !empty($IMG_SIZE_ARR) ? $IMG_SIZE_ARR : ['tiny'   => ['150', '70'],
	                                                         'square' => ['150', '150'],
	                                                         'small'  => ['300', '200'],
	                                                         'long'   => ['1100', '100'],
	                                                         'large'  => ['1100', '350']];
	$buildArr     = !empty($buildArr) ? $buildArr : ['tinyy'      => [50, 25],
	                                                 'tiny'       => [150, 70],
	                                                 'smally'     => [68, 68],
	                                                 'squary'     => [70, 70],
	                                                 'largy'      => [325, 215],
	                                                 'largey'     => [325, 215],
	                                                 'wallpapery' => [100, 25]
	];
