<?

	include_once($_SERVER['CONF_INC']);

	$COLLECT['PHPSESSID']       = 'TOKEN_PHPSESSID';
	$COLLECT['SESSID']          = 'TOKEN_SESSID';
	$COLLECT['idagent']         = 1;
	$COLLECT['token']           = 1;
	$COLLECT['token_valid_for'] = 3600;
	$COLLECT['username'] = 'Mydde';

	echo trim(json_encode($COLLECT));