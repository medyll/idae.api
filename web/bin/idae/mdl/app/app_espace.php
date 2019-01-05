<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 09/12/2017
	 * Time: 23:24
	 */

	$vars    = empty($_POST['vars']) ? [ ] : $_POST['vars'];
	$table   = empty($_POST['table']) ? 'client' : $_POST['table'];
	$name_id = 'id' . $table;
	$Table   = ucfirst($table);

	$APP       = new App($table);
	$nomTable  = $APP->nomAppscheme;
	$iconTable = $APP->iconAppscheme;

	$idae_liste                      = new Idae($table);
	$table_ligne                     = $idae_liste->liste($vars);
	$table_ligne                     = $idae_liste->module('liste',http_build_query($_POST));

	echo $table_ligne;