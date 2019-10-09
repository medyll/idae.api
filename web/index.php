<?php

	include_once($_SERVER['CONF_INC']);

	use  Idae\Data\Scheme\Model\IdaeDataSchemeModel;
	use  Idae\Data\Scheme\Field\IdaeDataSchemeField;
	use  Idae\Data\Scheme\IdaeDataScheme;
	use  Idae\Data\IdaeData;
	use  Idae\Generator\IdaeGeneratorAppClass;
	use MongoDB\Client;
	use MongoDB\Model\BSONArray;

	$generator = new IdaeGeneratorAppClass();
	$generator->init();
	$generator->travel();



	//die('end');
	// $IdaeDataSchemeModel = new IdaeDataSchemeModel('agent');
	// $IdaeDataSchemeModel = new IdaeDataSchemeField('agent');
	$IdaeData = new IdaeData();

	$IdaeDataScheme = new IdaeDataScheme('agent');
	$ListIdaeData   = $IdaeData->getSchemeList(['codeAppscheme_base' => 'sitebase_base'], ['codeAppscheme' => 1]);

	  $FK = $IdaeDataScheme->getSchemeFields();

	$i = 0;

	foreach ($FK as $index => $chemeField) {
		$code = $chemeField['codeAppscheme_has_field'];
		$str  = "public get" . ucfirst($code) . "(){";
		$str  .= '<br>return $this->table_id<br>';
		$str  .= '}<br>--------------------------------------<br>';
		$str  .= "public set" . ucfirst($code) . "(){";
		$str  .= '$this->table_id = $table_id;';

		$str .= 'return $this;';

		echo $str;
		// vardump($chemeField['codeAppscheme_has_field']);
		//if ($i===7) die('died');
	}

	/*$ClassApp = new App('agent');
	$ClassApp->make_classes_app();*/

	$Router = new Router();

	// $Router->setBasePath('/web/');
