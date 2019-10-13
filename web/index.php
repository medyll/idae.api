<?php
	include_once($_SERVER['CONF_INC']);

	$Router = new Router();



	use Idae\App\IdaeAppBase;

	$data       = new IdaeAppBase();
	$schemeList = $data->getSchemeBaseList();

	foreach ($schemeList as $list) {
		// var_dump($list['nomAppscheme_base']);
	}

	// $dataScheme;
	use  Idae\Generator\IdaeGeneratorAppClass;

	/*$generator = new IdaeGeneratorAppClass();
	$generator->init();
	$generator->travel();*/

