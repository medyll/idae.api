<?php

	include_once($_SERVER['CONF_INC']);

	use  Idae\Data\Scheme\Model\IdaeDataSchemeModel;
	use  Idae\Data\Scheme\Field\IdaeDataSchemeField;
	use  Idae\Data\Scheme\IdaeDataScheme;
	use  Idae\Data\IdaeData;
	use MongoDB\Client;
	use MongoDB\Model\BSONArray;

	echo $base = MDB_PREFIX . 'sitebase_base';
	$table             = 'agent';
	$connectionOptions = ['db'       => 'admin',
	                      'username' => MDB_USER,
	                      'password' => MDB_PASSWORD,
	                      'connect'  => true];

	$connection = new Client('mongodb://' . MDB_USER . ':' . MDB_PASSWORD . '@' . MDB_HOST, $connectionOptions);;

	$db         = $connection->selectDatabase($base);
	$collection = $db->selectCollection($table);

	$rs   = $collection->find();

	$data = $rs->toArray();

	/*vardump($data);
	die();*/
	foreach ($data as $index => $item) {
		/*echo get_class($item);
		$json = MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($item));
		var_dump($json);*/
		// var_dump($item);
		//var_dump($item['id']);
		/**
		 * $item \MongoDB\Model\BSONDocument
		 */
		//$item->
		echo $item['_id'];
		vardump($item);
		//die();
	}

	//die('end');
	// $IdaeDataSchemeModel = new IdaeDataSchemeModel('agent');
	// $IdaeDataSchemeModel = new IdaeDataSchemeField('agent');
	$IdaeData = new IdaeData();

	$IdaeDataScheme = new IdaeDataScheme('agent');
	$ListIdaeData   = $IdaeData->getSchemeList(['codeAppscheme_base' => 'sitebase_app'], ['codeAppscheme' => 1]);
die();
	// $FK = $IdaeDataScheme->getSchemeFields();

	$i = 0;
	foreach ($ListIdaeData as $key => $idaeDatu) {
		$i++;
		die();
		echo '<pre>';

		if (!empty($idaeDatu['codeAppscheme_base'])) {
			echo $idaeDatu['codeAppscheme_base'] . '.' . $idaeDatu['codeAppscheme'];
			$ListFields = $IdaeData->getSchemeFieldList($idaeDatu['codeAppscheme']);
			die();
			foreach (iterator_to_array($ListFields) as $keyField => $Field) {
				//if($i > 1 )continue;
				die();
				var_dump($Field['nomAppscheme_has_field']);
				if ($i === 2) die();
			}
		} else {
			//var_dump($idaeDatu);
			die('died');
		}
		if ($i === 1) die();
		echo '</pre>';
	}
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

	// print_r($FK);
	// vardump($SCHDATA);
	/*$parts = $IdaeDataSchemeModel->get_schemeParts('scheme_field_main');
	var_dump($parts);*/
	// var_dump($IdaeDataSchemeModel);

	/*$ClassApp = new App('agent');
	$ClassApp->make_classes_app();*/

	$Router = new Router();

	// $Router->setBasePath('/web/');
