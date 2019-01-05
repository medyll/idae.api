<?

	include_once($_SERVER['CONF_INC']);
	$APP     = new App();
	$_POST   = array_merge($_GET, $_POST);
	$COLLECT = ['PHPSESSID' => ''];

	if (empty($_COOKIE['PHPSESSID'])) {
		echo trim(json_encode($COLLECT));
		echo trim(json_encode($_SESSION));
		exit;
	}

	$COLLECT['session']                       = $_SESSION;
	$COLLECT['PHPSESSID']                     = $_COOKIE['PHPSESSID'];
	$COLLECT['type_session']                  = $_SESSION['type_session'];
	$COLLECT[$COLLECT['type_session']]        = $_SESSION[$_SESSION['type_session']];
	$COLLECT['id' . $COLLECT['type_session']] = $_SESSION['id' . $_SESSION['type_session']];

	echo trim(json_encode($COLLECT), JSON_FORCE_OBJECT);