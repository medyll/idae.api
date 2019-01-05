<?php
	/** @deprecated  */
	include_once($_SERVER['CONF_INC']);

	if (!empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']!= '/' ) {


		$arr_qs = explode('/', trim($_SERVER['REQUEST_URI']));

		if (empty($arr_qs[0])) unset ($arr_qs[0]);
		$arr_qs = array_values(array_filter($arr_qs));

		$type_page = $arr_qs[0];
		$vars=[];
		switch ($type_page) {

			default:
				$page = "404";
				break;
		}


		include_once('bin/pages/' . $page . '.php');

	}else{


		include_once('bin/pages/index.php');
	}



