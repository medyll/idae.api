<?php

	include_once($_SERVER['CONF_INC']);

	$_POST = array_merge($_GET, $_POST);

	$APP       = new App();
	$APP_SCH   = new App('appscheme');
	$APP_FIELD = new App('appscheme_field');

	$vars = empty($_POST['vars']) ? array() : function_prod::cleanPostMongo($_POST['vars'], 1);

	$RS_ARR_FIELD = $APP_FIELD->find($vars);
	$COLLECT      = [];

	foreach ($RS_ARR_FIELD as $ARR_FIELD):
		unset($ARR_FIELD['_id']);

		$code = $ARR_FIELD['codeAppscheme_field'];
		$APP_TABLE[$code] = $ARR_FIELD;
		//
		 $COLLECT[$code] = $ARR_FIELD;
	endforeach;

	echo trim(json_encode($COLLECT));
