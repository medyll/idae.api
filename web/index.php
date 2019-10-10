<?php

	include_once($_SERVER['CONF_INC']);

	$Router = new Router();

	use Idae\Data\Scheme\IdaeDataScheme;

	$dataScheme = new IdaeDataScheme('produit');
	$grilleFK   = $dataScheme->getGrilleFK();

	var_dump($grilleFK);
	// $dataScheme;
	// use  Idae\Generator\IdaeGeneratorAppClass;

	/*$generator = new IdaeGeneratorAppClass();
	$generator->init();
	$generator->travel();*/

	// $Router->setBasePath('/web/');
