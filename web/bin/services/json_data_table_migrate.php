<?
	header('Content-Type: application/json');
	// ob_end_clean();
	include_once($_SERVER['CONF_INC']);
	//ini_set('display_errors', 0);

	$DEBUG = false;

	$_POST = array_merge($_GET, $_POST);

	// keep url_data
	if (!empty($_POST['url_data'])) {
		parse_str($_POST['url_data'], $arr_data);
		if (empty($arr_data['vars'])) {
			$arr_data['vars'] = [];
		}
		if (!empty($arr_data['vars_search_fk'])) {
			$arr_data['vars_search_fk'] = array_filter($arr_data['vars_search_fk']);
		}
		unset($arr_data['stream_to'], $arr_data['PHPSESSID'], $arr_data['SESSID']);
		$url_data = http_build_query($arr_data);
	} else {
		$tppost = $_POST;
		unset($tppost['PHPSESSID'], $tppost['SESSID'], $tppost['stream_to'], $tppost['url_data']);
		$url_data = http_build_query($tppost);
	}
	//
	if (empty($_POST['table'])) {
		return;
	}
	if (!empty($_POST['stream_to'])) {
		if (!empty($_POST['url_data'])) {
			$_POST['url_data'] .= '&stream_to=' . $_POST['stream_to'];
		}
	}
	if (!empty($_POST['url_data'])) {
		parse_str($_POST['url_data'], $_POST);
	}
	$uniqid = uniqid();
	//
	$table = $_POST['table'];
	$Table = ucfirst($_POST['table']);
	//
	$APP  = new App($table);
	$IDB  = new IdaeDataDB($table);
	$IDBC = new IdaeDataSchemeConsolidate($table);
	//

	//
	$id  = 'id' . $table;
	$nom = 'nom' . ucfirst($table);

	//
	$vars            = empty($_POST['vars']) ? [] : function_prod::cleanPostMongo(array_filter($_POST['vars'], "my_array_filter_fn"), 1);
	$vars_search     = empty($_POST['vars_search']) ? [] : function_prod::cleanPostMongo(array_filter($_POST['vars_search'], "my_array_filter_fn"), 1);
	$vars_search_fk  = empty($_POST['vars_search_fk']) ? [] : function_prod::cleanPostMongo(array_filter($_POST['vars_search_fk'], "my_array_filter_fn"), 1);
	$vars_search_rfk = empty($_POST['vars_search_rfk']) ? [] : function_prod::cleanPostMongo(array_filter($_POST['vars_search_rfk'], "my_array_filter_fn"), 1);

	//
	$APP_TABLE     = $APP->app_table_one;
	$GRILLE_FK     = $APP->get_grille_fk();// $APP->get_fk_tables($table);
	$GRILLE_COUNT  = $APP->get_grille_count($table);
	$arrFields_all = $APP->get_field_list();
	//
	$groupBy       = empty($_POST['groupBy']) ? '' : $_POST['groupBy'];
	$sortBy        = empty($_POST['sortBy']) ? empty($APP_TABLE['sortFieldName']) ? $nom : $APP_TABLE['sortFieldName'] : $_POST['sortBy'];
	$sortDir       = empty($_POST['sortDir']) ? empty($APP_TABLE['sortFieldOrder']) ? 1 : (int)$APP_TABLE['sortFieldOrder'] : (int)$_POST['sortDir'];
	$sortBySecond  = empty($_POST['sortBySecond']) ? empty($APP_TABLE['sortFieldSecondName']) ? $nom : $APP_TABLE['sortFieldSecondName'] : $_POST['sortBySecond'];
	$sortDirSecond = empty($_POST['sortDirSecond']) ? empty($APP_TABLE['sortFieldSecondOrder']) ? 1 : (int)$APP_TABLE['sortFieldSecondOrder'] : (int)$_POST['sortDirSecond'];

	$page   = (!isset($_POST['page'])) ? 0 : $_POST['page'];
	$nbRows = (empty($_POST['nbRows'])) ? empty($settings_nbRows) ? 150 : (int)$settings_nbRows : $_POST['nbRows'];
	//
	$MDL              = empty($_POST['mdl']) ? '' : $_POST['mdl'];
	$strem_chunk_size = empty($MDL) ? 20 : 15;

	// $in => tableau
	foreach ($vars as $key_vars => $value_vars):
		if ($key_vars == 'ne') continue;
		if (is_array($value_vars) && $key_vars != 'gte' && $key_vars != 'lte' && $key_vars != 'ne' && sizeof(array_values($value_vars)) != 0) $vars[$key_vars] = ['$in' => array_values($value_vars)];
	endforeach;

	//  vars_date
	if (!empty($_POST['vars_date'])):
		array_walk_recursive($_POST, 'CleanStr', $_POST['vars_date']);
		foreach ($_POST['vars_date'] as $dt => $dv):
			$vars[$dt] = $dv;
		endforeach;
	endif;
	if (!empty($_POST['vars_in'])) {
		foreach ($_POST['vars_in'] as $key_vars => $value_vars):
			$value_vars['$in'] = json_decode($value_vars['$in']);
			$vars[$key_vars]   = $value_vars;
		endforeach;
	}
	//
	if (!empty($_POST['vars']['gte'])) {
		foreach ($_POST['vars']['gte'] as $key_vars => $value_vars):
			$vars[$key_vars]['$gte'] = is_int($value_vars) ? (int)$value_vars : $value_vars;
		endforeach;
		unset($vars['gte']);
	}
	if (!empty($_POST['vars']['lte'])) {
		foreach ($_POST['vars']['lte'] as $key_vars => $value_vars):
			$vars[$key_vars]['$lte'] = is_int($value_vars) ? (int)$value_vars : $value_vars;
		endforeach;
		unset($vars['lte']);
	}
	if (!empty($_POST['vars']['ne'])) {
		foreach ($_POST['vars']['ne'] as $key_vars => $value_vars):
			$vars[$key_vars]['$ne'] = is_int($value_vars) ? (int)$value_vars : $value_vars;
		endforeach;
		unset($vars['ne']);
	}


	$APP_SOCKET = $APP->plug('sitebase_sockets', 'stream_to');
	//

	//
	$where = [];

	# champ = 'null'

	foreach ($vars as $key_vars => $value_vars):
		if (strtolower($value_vars) == 'null') {
			unset($vars[$key_vars]);
			$where['$or'][]          = [$key_vars => ['$exists' => false]];
			$where[$key_vars]['$in'] = [null, ''];

		}

	endforeach;

	if (!empty($_POST['search'])) { // un champ de recherche unique
		$regexp = new MongoRegex("/" . $_POST['search'] . "/i");

		if (is_int($_POST['search'])) $where['$or'][] = [$id => (int)$_POST['search']];

		if ($APP->has_field('nom')) $where['$or'][] = ['nom' . $Table => $regexp];
		if ($APP->has_field('prenom')) $where['$or'][] = ['prenom' . $Table => $regexp];
		if ($APP->has_field('email')) $where['$or'][] = ['email' . $Table => $regexp];
		if ($APP->has_field('code')) $where['$or'][] = ['code' . $Table => $regexp];
		if ($APP->has_field('reference')) $where['$or'][] = ['reference' . $Table => $regexp];
		if ($APP->has_field('telephone')) $where['$or'][] = ['telephone' . $Table => new MongoRegex("/" . cleanTel($_POST['search']) . "/i")];

		// tourne ds fk
		if (sizeof($GRILLE_FK) != 0) {
			foreach ($GRILLE_FK as $field):
				$code_fk = 'nom' . ucfirst($field['codeAppscheme']);
				$nom_fk  = 'nom' . ucfirst($field['nomAppscheme']);
				//$regexp         = new MongoRegex("/" . $nom_fk . "/i");
				$where['$or'][] = [$code_fk => $regexp];
				$where['$or'][] = [$nom_fk => $regexp];
			endforeach;
		}

		// vardump($where);exit;
	}

	if (!empty($_POST['search_start'])) {
		$regexp = new MongoRegex("/^" . $_POST['search_start'] . "./i");
		// $where['$or'][] = [$nom => $regexp];
		if ($APP->has_field('nom')) {
			$vars[$nom] = $regexp;
		} else {
			$vars[$code] = $regexp;
		}
	}

	if (!empty($vars_search)) { // vars_search est un array, avec des noms de table
		foreach ($vars_search as $key_field => $field_value):
			if (empty($field_value)) continue;
			// tourne ds fields
			$regexp         = new MongoRegex("/" . $field_value . "/i");
			$where['$or'][] = [$key_field => $regexp];

		endforeach;
	}
	if (!empty($vars_search_fk)) { // vars_search est un array, avec des noms de table
		$vars_search_fk = array_filter($vars_search_fk);
		if ($DEBUG && droit('DEV')) {
			skelMdl::send_cmd('act_notify', ['msg' => '<pre> $vars_search_fk' . json_encode($vars_search_fk, JSON_PRETTY_PRINT) . '</pre>', 'options' => ['sticky' => 0]], session_id());
		}
		foreach ($vars_search_fk as $table_key => $val_search):
			if (empty($val_search)) continue;
			$testid = substr(trim($table_key), 0, 2);;
			if ($testid == 'id') {
				$table_key = substr($table_key, -strlen($table_key) + 2);

				if (is_array($val_search)) {
					$vars['id' . $table_key] = ['$in' => $val_search];
				} else {
					$vars['id' . $table_key] = (int)$val_search;
				}
				continue;
			}

			$where_fk = [];
			// => on devrait tourner dans tout les champs de la table vars_search
			$APPKEY           = new App($table_key);
			$APP_TABLE_SCHEME = $APPKEY->get_field_list();

			// tourne ds fields
			foreach ($APP_TABLE_SCHEME as $key_field => $field_scheme):
				$tmp_name          = $field_scheme['field_name'];
				$regexp            = new MongoRegex("/" . $val_search . "/i");
				$where_fk['$or'][] = [$tmp_name => $regexp];
			endforeach;
			// query distinct id
			$rs                      = $APPKEY->distinct($table_key, $where_fk, 200, 'no_full');
			$vars['id' . $table_key] = ['$in' => array_values($rs)];
		endforeach;
		if ($DEBUG && droit('DEV')) {
			skelMdl::send_cmd('act_notify', ['msg' => '$where_fk <pre>' . json_encode($where_fk, JSON_PRETTY_PRINT) . '</pre>', 'options' => ['sticky' => 0]], session_id());
		}
	}

	if (!empty($vars_search_rfk)) { // vars_search est un array, avec des noms de table

		foreach ($vars_search_rfk as $table_key => $val_search):
			$where_rfk = [];
			$testid    = substr(trim($table_key), 0, 2);;
			if ($testid == 'id') $table_key = substr($table_key, -strlen($table_key) + 2);
			// => on devrait tourner dans tout les champs de la table vars_search
			$APPKEY           = new App($table_key);
			$APP_TABLE_SCHEME = $APPKEY->get_display_fields($table_key);

			// tourne ds fields
			if ($testid == 'id'):
				$where_rfk['id' . $table_key] = (int)$val_search;
			endif;
			foreach ($APP_TABLE_SCHEME as $key_field => $field_scheme):
				if ($testid == 'id') continue;
				$tmp_name           = $field_scheme['nomAppscheme_field'];
				$tmp_name_raw       = $field_scheme['codeAppscheme_field'];
				$regexp             = new MongoRegex("/" . $val_search . "/i");
				$where_rfk['$or'][] = [$tmp_name => $regexp];
				if ($tmp_name_raw == 'adresse') {
					$where_rfk['$or'][] = ['adresse1' . ucfirst($table_key) => $regexp];
					$where_rfk['$or'][] = ['codePostal' . ucfirst($table_key) => $regexp];
					$where_rfk['$or'][] = ['ville' . ucfirst($table_key) => $regexp];
				}
			endforeach;
			// query distinct id

			$rs = $APPKEY->distinct($table, $where_rfk, 200, 'no_full');

			$vars['id' . $table] = ['$in' => array_values($rs)];

		endforeach;
	}

	if (!empty($_POST['console_mode']) && !empty($APP_TABLE['hasStatutScheme'])) {
		// statut END, last ?
		$APP_STATUT = new App($table . '_statut');
		$ARR_STATUT = $APP_STATUT->findOne(['code' . $Table . '_statut' => 'END']);
		if (empty($ARR_STATUT['id' . $table . '_statut'])) {
			$RS_STATUT  = $APP_STATUT->find()->sort(['ordre' . $Table . '_statut' => -1]);
			$ARR_STATUT = $RS_STATUT->getNext();
		}
		if (!empty($ARR_STATUT['id' . $table . '_statut'])) {
			echo $idnostatut = (int)$ARR_STATUT['id' . $table . '_statut'];
			// {dateDebutIntervention:'2015-11-23',idintervention_statut:{'$ne':4}}
			// {dateDebutIntervention:{'$ne':'2015-11-23'},idintervention_statut:4}

			$dist1 = $APP->distinct($table, ['dateDebut' . $Table => date('Y-m-d')], 30, 'nofull');
			$dist2 = $APP->distinct($table, ['dateDebut' . $Table => ['$ne' => date('Y-m-d')], 'id' . $table . '_statut' => ['$ne' => $idnostatut]], 30, 'nofull');

			$where[$id] = ['$in' => $dist1 + $dist2];
		}
	}

	$rs       = $APP->find($vars + $where)->sort([$sortBy => $sortDir, $sortBySecond => $sortDirSecond])->skip(($nbRows * $page))->limit($nbRows);
	$count    = $rs->count();
	$maxcount = $rs->count(false);
	$count    = ($count > $nbRows) ? $nbRows : $count;
	//
	if ($DEBUG && droit('DEV')) {
		//skelMdl::send_cmd('act_notify', ['msg' => '<pre> DATA QUERY <br> ' . $count . ' : ' . json_encode($vars + $where) . '/  ' . $sortBySecond . " => " . $sortDirSecond . ' / ' . $page . '</pre>', 'options' => ['sticky' => 1, 'id' => 'json_debug']], session_id());
	}
	// ACT COUNT
	if (!empty($_POST['piece']) && $_POST['piece'] == 'count'):
		$data_count = ['count_id' => $_POST['count_id'], 'count' => $rs->count(), 'maxcount' => $maxcount, 'table' => $table];
		//skelMdl::send_cmd('act_count', json_decode(json_encode($data_count, JSON_FORCE_OBJECT)), session_id());
	endif;

	if (!empty($_POST['verify'])):
		$iter = iterator_to_array($rs1, true);
		$key  = array_search($_POST['verify'], array_column($iter, 'id' . $table));
		echo ($key === false) ? 'NULL' : $key;

		return;
	endif;

	if (!empty($groupBy)):
		if (!empty($GRILLE_FK[$groupBy])) {
			$rs_dist        = $APP->distinct($groupBy, $vars + $where, 50);
			$dist_count     = $rs_dist->count();
			$dist_max_count = $rs_dist->count(true);
			$rs_dist        = iterator_to_array($rs_dist);
			$groupBy_field  = "id$groupBy";
			$groupBy_values = array_column($rs_dist, $groupBy_field);
			$groupBy_mode   = "grille";
			$APP_GROUP      = new App($groupBy);
			$APP_TGR        = $APP_GROUP->app_table_one;
			$RS_GROUP       = $APP_GROUP->find([$groupBy_field => ['$in' => $groupBy_values]])->sort([$APP_TGR['sortFieldName'] => $APP_TGR['sortFieldOrder']]);

		} else {
			$rs_dist        = array_filter($APP->distinct_all($groupBy, array_merge($vars, $where)));
			$dist_count     = (sizeof($rs_dist) > 50) ? 50 : sizeof($rs_dist);
			$dist_max_count = sizeof($rs_dist);
			$groupBy_field  = $groupBy;
			$groupBy_mode   = "field";
			// date - nombre -

		}
		/*if ($DEBUG && droit('DEV')) {
			skelMdl::send_cmd('act_notify', ['msg' => '<pre> groupBy <br> ' . $groupBy . '/' . $groupBy_field . ' : ' . $groupBy_mode . sizeof($rs_dist) . '</pre>', 'options' => ['sticky' => 1, 'id' => 'json_debug']], session_id());
		}*/
	endif;
	// MAIN_DATA

	if (!empty($groupBy) && !empty($rs_dist) && !empty($groupBy_field)):
		$data_main = [];

		$i = 0;
		switch ($groupBy_mode):
			case 'grille':
				$rs_dist = array_filter($rs_dist);
				break;
			case 'field':
				// date ? string ? int ?
				if (strpos($groupBy_field, 'date') !== false) {
					$rs_dist      = array_map(function ($value) {
						return substr($value, 0, 7);
					}, $rs_dist);
					$groupBy_mode = 'date';
				} else if (strpos($groupBy_field, 'email') !== false) {
					$rs_dist      = array_map(function ($value) {
						return end(explode('@', $value));
					}, $rs_dist);
					$groupBy_mode = 'email';
				} else if (is_string(reset($rs_dist))) {
					$rs_dist      = array_map(function ($value) {
						return substr($value, 0, 2);
					}, $rs_dist);
					$groupBy_mode = 'string';
				} else {
					$groupBy_mode = 'integer';
				}
				$rs_dist = array_filter($rs_dist);
				break;
		endswitch;

		foreach ($rs_dist as $key_dist => $arr_dist):
			$i++;
			$vars_groupBy = [];
			switch ($groupBy_mode):
				case 'grille':
					$vars_groupBy[$groupBy_field] = (int)$arr_dist[$groupBy_field]; // unset apres
					$table_value                  = $arr_dist[$groupBy_field];
					break;
				case 'date':
					$vars_groupBy[$groupBy_field] = new MongoRegex("/^" . $arr_dist . "/i");
					$table_value                  = $arr_dist;
					break;
				case 'email':
					$vars_groupBy[$groupBy_field] = new MongoRegex("/" . $arr_dist . "/i");;
					$table_value = $arr_dist;
					break;
				case 'field':
				case 'integer':
				case 'string':
					$vars_groupBy[$groupBy_field] = new MongoRegex("/^" . $arr_dist . "/i");;
					$table_value = $arr_dist;
					break;
			endswitch;
			$rs = $APP->find(array_merge($vars_groupBy, $vars, $where))->limit($nbRows / sizeof($rs_dist))->sort([$sortBy => $sortDir]);
			/*if ($DEBUG && droit('DEV')) {
				skelMdl::send_cmd('act_notify', ['msg'     => '<pre>Group <br> ' . $groupBy_mode . ' ' . $table_value . ' : ' . vardump(array_merge($vars_groupBy, $vars, $where), true) . ' total : ' . $rs->count() . '</pre>',
				                                 'options' => ['sticky' => 1, 'id' => 'json_debug']], session_id());
			}*/
			$count_groupBy = $rs->count();
			if ($count_groupBy == 0) continue;

			$vars_rfk['groupBy'] = $groupBy;
			switch ($groupBy_mode):
				case 'grille':
					$vars_rfk['vars']        = ['id' . $groupBy => $arr_dist['id' . $groupBy]];
					$vars_rfk['table_value'] = $arr_dist['id' . $groupBy];
					break;
				case 'date':
				case 'email':
				case 'field':
				case 'integer':
				case 'string':
					$vars_rfk['vars'] = [$groupBy => $arr_dist[$groupBy]];
					break;
			endswitch;

			switch ($groupBy_mode):
				case 'grille':
					$groupBy_key = 'groupby_' . $groupBy . '_' . $arr_dist['id' . $groupBy];
					$z           = skelMdl::cf_module('app/app/app_fiche_entete_group', ['groupBy' => $groupBy, 'vars' => $vars_rfk, 'table' => $groupBy, 'table_value' => $arr_dist['id' . $groupBy]]);

					break;
				case 'date':
				case 'email':
				case 'field':
				case 'integer':
				case 'string':
					$groupBy_key = 'groupby_' . $groupBy . '_idx_' . $i;
					$z           = "<div class='padding_more'>" . $table_value . ' ' . "$groupBy </div>";
					break;
			endswitch;

			$data_main[] = ['groupBy_key' => $groupBy_key, 'data' => $z, 'md5' => md5($z), 'order' => $i, 'table' => $groupBy, 'table_value' => $table_value, 'vars' => $vars_rfk, 'groupBy' => 1, 'maxcount' => $dist_max_count, 'count' => $dist_count];

			$count_frag      = 0;
			$count_frag_test = 1;

			while ($arr = $rs->getNext()) {
				if (empty($arr[$id])) continue;
				$APP->consolidate_scheme($arr[$id]);
				$count_frag++;
				$count_frag_test++;

				if (!empty($_POST['stream_to'])):
					$arr_allow_stream = $APP_SOCKET->findOne(['nomStream_to' => $_POST['stream_to']]);
					if (!empty($arr_allow_stream['stop'])):
						exit;
					endif;
				endif;
				$i++;

				$data_row    = dotr($table, $arr);
				$data_main[] = array_merge($data_row, ['order' => $i, 'chunk' => $count_frag_test, 'chunks' => ceil($count_groupBy / $count_frag_test), 'maxcount' => $dist_max_count, 'count' => $dist_count]);
				// stream
				if (($i % $strem_chunk_size) == 0 || !$rs->hasNext()) {
					if (!empty($_POST['stream_to'])):
						$arr_allow_stream = $APP_SOCKET->findOne(['nomStream_to' => $_POST['stream_to']]);
						if (!empty($arr_allow_stream['stop'])):
							exit;
						endif;

						$out_model = ['data_main' => $data_main, 'maxcount' => $dist_max_count, 'url_data' => $url_data, 'table' => $table];// 'columnModel' => $columnModel,
						$strm_vars = ['stream_to' => $_POST['stream_to'], 'data' => $out_model, 'data_size' => sizeof($data_main)];

					//	skelMdl::send_cmd('act_stream_to', json_decode(json_encode($strm_vars, JSON_FORCE_OBJECT)), session_id());
						unset($data_main);
						$count_frag_test++;
					endif;
				}
			}
			unset($vars['id' . $groupBy]);
			unset($vars[$groupBy]);
		endforeach;
		// pas dans distinc : sans groupBy
		$data_main    = $strm = [];
		$vars_rfk     = [];
		$rs_noGroupBy = $APP->find($vars + $where + ['id' . $groupBy => ['$exists' => false]])->sort([$sortBy => $sortDir])->skip($page)->limit($nbRows);
		if ($rs_noGroupBy->count() != 0):
			$z           = '<div class="flex_h"><div class="aligncenter padding margin  border4"  style="width:32px;"><i class="fa fa-' . $APP->iconAppscheme . '  fa-2x"></i ></div><div class="padding uppercase bold">Sans ' . $groupBy . '<br>' . $rs_noGroupBy->count() . '</div></div>';//skelMdl::cf_module('app/app/app_fiche_entete_group', ['groupBy' => $groupBy, 'vars' => $vars_rfk, 'table' => $groupBy, 'table_value' => '0']);
			$groupBy_key = 'groupby_' . $groupBy . '_idx_' . $i;
			$data_main[] = ['data' => $z, 'vars' => $vars_rfk, 'groupBy_key' => $groupBy_key, 'groupBy' => 1];
			$strm[]      = ['data' => $z, 'table' => $groupBy, 'groupBy_key' => $groupBy_key, 'table_value' => 'no_group', 'vars' => $vars_rfk, 'groupBy' => 1];

			if (!empty($_POST['stream_to'])):

				$out_model = ['data_main' => $strm, 'maxcount' => $maxcount, 'url_data' => $url_data, 'table' => $table]; // 'columnModel' => $columnModel,
				$strm_vars = ['stream_to' => $_POST['stream_to'], 'data' => $out_model, 'data_size' => sizeof($strm)];

				//skelMdl::send_cmd('act_stream_to', json_decode(json_encode($strm_vars, JSON_FORCE_OBJECT)), session_id());
				unset($strm);
			endif;
		endif;
		$rs    = $APP->find($vars + $where + ['id' . $groupBy => ['$exists' => false]])->sort([$sortBy => $sortDir])->skip($page)->limit($nbRows);
		$count = $rs->count();
		$count = ($count > $nbRows) ? $nbRows : $count;
		unset($groupBy);

	endif;
	// NORMAL
	if ($rs->count() == 0) {
		$z = " Pas de résultats";

		$strm_vars = ['stream_to' => $_POST['stream_to'],
		              'data_size' => sizeof($strm),
		              'table'     => $table,
		              'data'      => ['data_main' => ['data' => $z, 'table' => $table, 'vars' => $vars, 'groupBy' => 1],
		                              'maxcount'  => $maxcount,
		                              'url_data'  => $url_data,
		                              'table'     => $table]];

		//skelMdl::send_cmd('act_stream_to', $strm_vars, session_id());
	}
	if (empty($groupBy)):
		$data_main       = [];
		$i               = 0;
		$modulo          = 0;
		$count_frag      = 0;
		$count_frag_test = 1;
		$count_prog      = 0;
		// si demande stream_count
		if (!empty($_POST['stream_count'])):
			$stream_count[] = ['count' => $count, 'ids' => $trvars, 'value' => $arr[$id], 'table_value' => $arr[$id], 'table' => $table, 'maxcount' => $maxcount, 'count' => $count];
		endif;
		//

		while ($arr = $rs->getNext()) {
			if (!empty($_POST['count'])) {
				continue;
			}
			if (empty($arr[$id])) continue;
			//$APP->consolidate_scheme($arr[$id]);
			//$IDBC->consolidate_scheme($arr[$id]);
			$i++;
			$count_frag++;
			$count_prog++;

			$data_row = dotr($table, $arr);

			$data_main[$arr[$id]] = $data_row;//$data_row;//array_merge($data_row, ['order' => $i, 'chunk' => $count_frag_test, 'chunks' => ceil($count / $strem_chunk_size), 'maxcount' => $maxcount, 'count' => $count]);

			// PROGRESS
			if ($count_prog == 40 || !$rs->hasNext()) {
				$progress_vars = ['progress_name' => 'progress_' . $table, 'progress_value' => $i, 'progress_max' => $count];
				// AppSocket::send_cmd('act_progress', $progress_vars, session_id());
				$count_prog = 0;
			}

			if (!empty($_POST['stream_to'])):

				if (($count_frag % $strem_chunk_size) == 0 || !$rs->hasNext()) {
					$out_model = ['data_main' => $data_main,
					              'maxcount'  => $maxcount,
					              'url_data'  => $url_data,
					              'table'     => $table];
					//
					$strm_vars = ['stream_to' => $_POST['stream_to'],
					              'data_size' => sizeof($data_main),
					              'table'     => $table,
					              'data'      => $out_model];

					// AppSocket::send_cmd('act_stream_to', $strm_vars, session_id());

					unset($data_main);
					$count_frag = 0;
					$count_frag_test++;
				}
			endif;

			$modulo++;
		}

	endif;
	//
	$out_model = ['data_main' => $data_main, 'count' => $maxcount, 'pagecount' => $count];
	//
	if (empty($_POST['stream_to'])):
		echo trim(json_encode($out_model));
	endif;
	if (!empty($_POST['csv_export'])):
		//
		foreach ($data_main as $key => $value) {
			array_walk($value['data'], 'strip_tags');
			$csv_main[] = $value['data'];
		}
		//
		$db_tmp = $APP->plug('sitebase_tmp', 'tmp');
		$db_tmp->insert(['uniqid' => $uniqid, 'data' => $csv_main, 'table' => $table, 'vars' => $vars]);
		//
		//AppSocket::send_cmd('act_gui', ['title' => 'Télécharger le csv', 'mdl' => 'app/app_prod/app_prod_csv', 'vars' => 'uniqid=' . $uniqid], session_id());

	endif;
	//
	//
	function dotr($table, $arr) {

		global $APP, $APP_TABLE, $arrFields_all, $GRILLE_FK, $BASE_APP, $GRILLE_COUNT, $sortBy, $key_date, $MDL;
		$out = [];
		$id  = 'id' . $table;
		// variables pour le mdl_tr => vars
		$trvars['id' . $table] = $arr[$id];
		$trvars['_id']         = (string)$arr['_id'];
		$trvars['table']       = $table;
		$trvars['table_value'] = $arr[$id];
		$trvars['sortBy']      = $sortBy;
		$trvars['key_date']    = $key_date;
		//
		$out_more = ['icon' => $APP_TABLE['iconAppscheme'], 'value' => $arr[$id], 'table_value' => $arr[$id], 'table' => $table];

		foreach ($arrFields_all as $key_f => $value_f):
			$field_name               = $value_f['field_name'];
			$field_value              = is_array($arr[$field_name]) ? explode(',', $arr[$field_name]) : $arr[$field_name];
			$field_name_raw           = $value_f['field_name_raw'];
			$codeAppscheme_field_type = $value_f['codeAppscheme_field_type'];
			// if (is_array($arr[$field_name])) unset($arr[$field_name]);
			// Integralité des champs // cast
			$arr_cast                = ['field_name' => $field_name, 'field_name_raw' => $field_name_raw, 'field_value' => $field_value, 'codeAppscheme_field_type' => $codeAppscheme_field_type];
			$arr_cast['table']       = $table;
			$arr_cast['table_value'] = $arr[$id];
			$out[$field_name]        = $arr[$field_name];//$APP->cast_field($arr_cast);
			if ($codeAppscheme_field_type == 'bool') {
				$set_value        = empty($field_value) ? 1 : 0;
				$uri              = "table=$table&table_value=$arr[$id]&vars[$field_name]=";
				$out[$field_name] = "$field_value";
			}
		endforeach;
		foreach ($GRILLE_FK as $field):
			$code  = $BASE_APP . $table . $field['table_fk'] . $arr[$id];
			$id_fk = $field['idtable_fk'];
			//
			$arrq = $APP->plug($field['base_fk'], $field['table_fk'])->findOne([$field['idtable_fk'] => (int)$arr[$id_fk]]);
			unset($arrq['_id']);
			$dsp_name = $arrq['nom' . ucfirst($field['table_fk'])];
			// we need default fields here !!!
			$out['id' . $field['table_fk']]           = (int)$arr[$id_fk];
			$out['nom' . ucfirst($field['table_fk'])] = $dsp_name;
			$out['grilleFK'][$field['table_fk']]      = $arrq ?: [];
		endforeach;
		foreach ($GRILLE_COUNT as $key_count => $field):
			$APP_TMP  = new App($key_count);
			$RS_TMP   = $APP_TMP->find([$id => $arr[$id]], [$id => 1, "id$key_count" => 1]);
			$count_ct = $RS_TMP->count();
			//$link     = fonctionsJs::app_liste($key_count, '', ['vars' => [$id => $arr[$id]]]);
			/*if ($count_ct == 1) {
				$ARR_TMP = $RS_TMP->getNext();
				$link    = fonctionsJs::app_fiche($key_count, $ARR_TMP["id$key_count"], ['vars' => [$id => $arr[$id]]]);
			}*/
			$attr         = " data-count='data-count' data-table='$key_count' data-vars='vars[$id]=$arr[$id]' ";
			$count_grille = (empty($count_ct)) ? '' : $count_ct;
			//$out['count_' . $key_count] = '<a onclick="' . $link . '"  ' . $attr . ' >' . $count_grille . '</a>' . ((!empty($count_ct)) ? "<span class='count_title'> $key_count</span>" : '');
			$out['count_' . $key_count] = $count_grille;
		endforeach;

		if (!empty($key_date)):
			$out[$key_date] = '';
		endif;

		if (!empty($MDL)) $out_more['mdl'] = skelMdl::cf_module($MDL, ['table' => $table, 'table_value' => $arr[$id]]);;

		if (droit('DEV') && $table == 'ville') {
			if (empty($arr['nomVille']) && !empty($arr['codeVille'])) {
				skelMdl::send_cmd('act_notify', ['msg' => '<br>' . $APP->codeAppscheme . ' ' . $arr['codeVille'] . ' => ' . $arr['nomVille'], 'options' => ['sticky' => 1], 'id' => 'json_debug'], session_id());
				$APP->update(['id' . $APP->codeAppscheme => (int)$arr['id' . $APP->codeAppscheme]], ['nomVille' => ucfirst(strtolower($arr['codeVille']))]);

			}
		}

		return array_merge($out, $out_more); // array_merge(['data' => $out, 'md5' => md5(json_encode($out)), 'vars' => $trvars], $out_more);
	}
