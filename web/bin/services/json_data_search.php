<?php

	include_once($_SERVER['CONF_INC']);



	$_POST = array_merge($_GET, $_POST);

	if (empty($_POST['search'])) {
		return;
	}
	if (!empty($_POST['stream_to'])) {
		$stream_to = $_POST['stream_to'];
		if (!empty($_POST['url_data'])) {
			$_POST['url_data'] .= '&stream_to=' . $_POST['stream_to'];
		}
	}
	if (!empty($_POST['url_data'])) {
		parse_str($_POST['url_data'], $_POST);
	}

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$arr_allowed_c = droit_table($type_session, 'C');
	$arr_allowed_r = droit_table($type_session, 'R');
	$arr_allowed_l = droit_table($type_session, 'L');

	if ($_SESSION[$type_session]) {
		$APP_SHOP = new App($type_session);
		$arr_shop = $APP_SHOP->findOne(['private_key' => $_SESSION[$type_session]], ['_id' => 0]);
		$_POST['vars'][$name_idtype_session] = $arr_shop[$name_idtype_session];
	}
	//
	$uniqid = uniqid();
	//
	$APP = new App('appscheme');
	//
	$SEARCH_SCH_QY = !empty($_POST['table']) ? ['codeAppscheme' => $_POST['table']] : [];
	$RSSCHEME      = $APP->find($SEARCH_SCH_QY); // 'codeAppscheme_base'=>'sitebase_base'
	//
	$vars    = empty($_POST['vars']) ? [] : function_prod::cleanPostMongo(array_filter($_POST['vars']), 1);
	$groupBy = empty($_POST['groupBy']) ? '' : $_POST['groupBy'];
	$sortBy  = empty($_POST['sortBy']) ? empty($settings_sortBy) ? $nom : $settings_sortBy : $_POST['sortBy'];
	$sortDir = empty($_POST['sortDir']) ? empty($settings_sortDir) ? 1 : (int)$settings_sortDir : (int)$_POST['sortDir'];
	$page    = (!isset($_POST['page'])) ? 0 : $_POST['page'];
	$nbRows  = 25; //(empty($_POST['nbRows'])) ? empty($settings_nbRows) ? 250 : (int)$settings_nbRows : $_POST['nbRows'];
	//  vars_date
	if (!empty($_POST['vars_date'])):
		$key_date        = $_POST['vars_date'] ['name_key'];
		$vars[$key_date] = $_POST['vars_date'][$key_date];
	endif;
	//
	$arrFieldsBool   = $APP->get_array_field_bool();
	//

	// MAIN_DATA

	$data_main = [];
	$strm      = [];

	$i         = 0;
	$nb_res    = 0;
	$SEARCH    = trim(strip_tags($_POST['search']));
	$SEARCH_QY = !empty($_POST['table']) ? trim($_POST['table']) : new MongoRegex("/$SEARCH/i");

	$RSSCHEME_SEARCH = $APP->find(['codeAppscheme' => $SEARCH_QY]);
	// $RSSCHEME_SEARCH = $APP->find(['codeAppscheme' =>  new MongoRegex("/$SEARCH/i")] );
	$maxcount = $RSSCHEME_SEARCH->count();
	$count    = $RSSCHEME_SEARCH->count(true);

	//
	if ($RSSCHEME_SEARCH->count() != 0) {
		//	$data_main[] = ['groupBy' => 'appscheme', 'html' => '<i class="fa fa-link"></i> Espaces'];

	}

	while ($ARR_SCh = $RSSCHEME_SEARCH->getNext()) {
		continue;
		$table = $ARR_SCh['codeAppscheme'];
		$nom   = $ARR_SCh['nomAppscheme'];
		$icon  = $ARR_SCh['iconAppscheme'];
		$color = $ARR_SCh['colorAppscheme'];

		/*if (!droit_table($_SESSION['idagent'], 'L', $table)) continue;
		if (!droit_table($_SESSION['idagent'], 'R', $table)) continue;*/

		$in = '<div><a class="textgrisfonce">Espace ' . $nom . '</a></div>';
		//if (droit_table($_SESSION['idagent'], 'C', $table)) $in .= '<div><a class="textgris" onclick="' . fonctionsJs::app_create($table) . '">Créer ' . $nom . '</a></div>';

		$data_out['nom']    = '<div onclick="" class="shadowbox hide_gui_pane cursor applink flex_h marginb padding edededhover"><div class="aligncenter padding margin ededed border4" style="width:46px;border-color: ' . $color . '!important;"><i class="textbold fa fa-' . $icon . ' fa-2x"></i></div><div><span class="titre1 borderb padding" >' . $nom . '</span>' . $in . '</div></div>';
		$data_out['nom_fk'] = '';

		$data_main[] = ['html' => $data_out, 'value' => '$arr[$id]', 'name_id' => '$id', 'table' => 'appscheme'];

		$out_model = ['data_main' => $data_main, 'maxcount' => $maxcount];
		$strm_vars = ['stream_to' => $stream_to, 'data' => $out_model, 'data_size' => sizeof($data_main)];
		AppSocket::send_cmd('act_stream_to', json_decode(json_encode($strm_vars)), session_id());
		$data_main = [];

	}

	foreach ($RSSCHEME as $arr_dist):
		//
		$table = $arr_dist['codeAppscheme'];
		//if ($APP->get_settings($_SESSION['idagent'], 'app_search_' . $table) != 'true') continue;
		//if (!droit_table($_SESSION['idagent'], 'R', $table)) continue;
		//if(!droit_table($type_session,'R',$table)) continue;
		$out   = [];
		$APPSC = new App($table);
		$where = [];
		if ($APPSC->has_field_fk($type_session)) {
			$where[$name_idtype_session] = $idtype_session;
		};
		$GRILLE_FK = $APPSC->get_grille_fk();
		$APP_TABLE = $APPSC->app_table_one;
		$i++;
		//
		$color     = $APPSC->colorAppscheme;
		$Table     = ucfirst($table);
		$id        = 'id' . $table;
		$nom       = 'nom' . $Table;
		$prenom    = 'prenom' . $Table;
		$email     = 'email' . $Table;
		$code      = 'code' . $Table;
		$telephone = 'telephone' . $Table;
		$icon      = $arr_dist['iconAppscheme'];
		$icon_css  = '<i class="padding textgris fa fa-' . $icon . '" style="color:' . $color . '!important;"></i>'; //

		if (!empty($_POST['search'])) {
			if (!is_int($_POST['search'])):
				$regexp        = new MongoRegex("/$SEARCH/i");
				$tmp_or        = [];
				$where['$and'] = [];
				foreach (['nom', 'prenom', 'email', 'code', 'telephone', 'ville'] as $in_key => $val_key) {
					$tmp_or['$or'][] = [$val_key . $Table => $regexp];
					//$where['$and'][] = ['$or'];
					if ($APPSC->has_field($val_key)) {
						//$where['$or'][] = [$val_key . $Table => $regexp];
						$where['$and'][] = $tmp_or;
					}
					// if ($APPSC->has_field($val_key)) $where['$or'][] = [$val_key . $Table => $regexp];
				}
			/*	if ($APPSC->has_field('nom')) $where['$and']['$or'][] = [$nom => $regexp];
				if ($APPSC->has_field('prenom')) $where['$and']['$or'][] = [$prenom => $regexp];
				if ($APPSC->has_field('email')) $where['$and']['$or'][] = [$email => $regexp];
				if ($APPSC->has_field('code')) $where['$and']['$or'][] = [$code => $regexp];
				if ($APPSC->has_field('telephone')) $where['$and']['$or'][] = [$telephone => $regexp];*/
			/*$tmp_or['$or'][] = [$id => (int)$_POST['search']];;
			$where['$and'][] = $tmp_or;
			$where['$or'][] = [$id => (int)$_POST['search']];;*/
			else :
				$where[$id] = (int)$_POST['search'];
			endif;
		}

		//vardump($where);
		/*if ($APPSC->has_agent() && !droit_table($_SESSION['idagent'], 'CONF', $table)) {
			$where['idagent'] = (int)$_SESSION['idagent'];
		};*/

		$rssc       = $APPSC->query([$id => ['$ne' => 0]] + $vars + $where)->sort([$nom => 1])->limit($nbRows)->skip($page * $nbRows);
		$rssc_count = $rssc->count();

		if ($rssc->count() != 0):
			$rss_html = "<div   class='flex_h flex_align_middle padding_more   borderb flex_padding borderb margin'>$icon_css<div class='flex_main'><span class='bold padding'> " . ucfirst($arr_dist['nomAppscheme']) . " - $rssc_count</span></div></div>";

			$data_main[] = $strm[] = ['groupBy' => $table, 'html' => '$rss_html'];
		endif;

		while ($arr = $rssc->getNext()) {
			// sleep(1);
			$i++;
			$nb_res++;
			$data_out = [];
			//
			$data_out['chk'] = '<input type = "checkbox" value = "' . $arr[$id] . '" name = "id[]" />';
			$data_out[$id]   = $arr[$id];

			$name = ucfirst(strtolower($arr[$nom] . ' ' . $arr[$prenom]));

			$data_out['nom_fk']       = [];
			$data_out['nom_fk_large'] = [];

			foreach ($GRILLE_FK as $field):
				$code  = $BASE_APP . $table . $field['table_fk'] . $arr[$id];
				$id_fk = $field['idtable_fk'];
				//
				$arrq     = $APP->plug($field['base_fk'], $field['table_fk'])->findOne([$field['idtable_fk'] => (int)$arr[$id_fk]], [$field['idtable_fk'] => 1, $field['nomtable_fk'] => 1]);
				$dsp_name = $arrq['nom' . ucfirst($field['table_fk'])];
				//
				if (!empty($dsp_name)) {
					$data_out['nom_fk'][]       = "<a class='textgris'>" . strtolower($dsp_name) . "</a>";
					$data_out['nom_fk_large'][] = "<i class='textgris   fa fa-" . $field['iconAppscheme'] . "'></i><div class='flex_main'><div ><span class='textgrisfonce'>" . ucfirst($field['nomAppscheme']) . "</span></div><div>" . $dsp_name . "</div></div>";
				}
			endforeach;

			$onclick = '';//fonctionsJs::app_fiche($table, $arr[$id]);

			if ($rssc_count == 1) {
				$in              = '<div class="textbold" >' . $table . '</div>';
				$Idae            = new Idae($table);
				$data_out['nom'] = //$Idae->module('fiche_mini', http_build_query(['table' => $table, 'table_value' => $arr[$id]]));
				$data_out['nom_fk'] = '';
			} elseif ($rssc_count <= 3) {
				$Idae            = new Idae($table);
				$data_out['nom'] =// $Idae->module('fiche_mini', http_build_query(['table' => $table, 'table_value' => $arr[$id]]));

				//$data_out['nom']    = $arr[$nom];
				$data_out['nom_fk'] = '';
			} else {
				$Idae               = new Idae($table);
				$data_out['nom']    = // $Idae->module('fiche_mini', http_build_query(['table' => $table, 'table_value' => $arr[$id]]));
				$data_out['nom_fk'] = '';//implode(', ', $data_out['nom_fk']);
			}

			if ($rssc_count == 1) {
			} else {

			}

			$data_main[] = ['html' => '$data_out', 'value' => $arr[$id],'table_value' => $arr[$id], 'name_id' => $id, 'table' => $table] + $arr ;
			$strm[]      = ['html' => '$data_out', 'value' => $arr[$id],'table_value' => $arr[$id], 'name_id' => $id, 'table' => $table] + $arr ;

			// stream
			if ($i == 1 || ($i % 2) == 0 || !$rssc->hasNext()) {
				if (!empty($stream_to)):
					$out_model = ['data_main' => $strm, 'maxcount' => $maxcount];
					$strm_vars = ['stream_to' => $stream_to, 'data' => $out_model, 'data_size' => sizeof($strm)];
					AppSocket::send_cmd('act_stream_to', json_decode(json_encode($strm_vars)), session_id());
					$strm = [];
				endif;
			}

		}
		//
	endforeach;

	if ($nb_res == 0):
		$data_main[] = ['groupBy' => 'fin ...', 'html' => '<div class="text-center padding_more ededed border4">
															<br>Aucun résultat pour "' . $_POST['search'] . '"</div>'];

		$out_model = ['data_main' => $data_main, 'maxcount' => $maxcount];
		$strm_vars = ['stream_to' => $stream_to, 'data' => $out_model, 'data_size' => sizeof($strm)];
		AppSocket::send_cmd('act_stream_to', json_decode(json_encode($strm_vars)), session_id());

	endif;
	if ($nb_res != 0):
		$APP_SA            = new APP('agent_recherche');
		$idagent_recherche = $APP_SA->create_update(['quantiteAgent_recherche' => $nb_res, 'codeAgent_recherche' => $_POST['search']], ['dateCreationAgent_recherche' => date('Y-m-d'), 'heureCreationAgent_recherche' => date('H:i:s'), 'timeAgent_recherche' => time(), 'nomAgent_recherche' => $_POST['search'], 'idagent' => (int)$_SESSION['idagent']]);
		$APP_SA->update_inc(['idagent_recherche' => $idagent_recherche], 'valeurAgent_recherche');
		AppSocket::reloadModule('app/app_gui/app_gui_start_search_last', $_SESSION['idagent']);
	endif;
	//
	$out_model = ['data_main' => $data_main, 'maxcount' => $maxcount];
	//
	if (empty($stream_to)):
		echo trim(json_encode($out_model));
	endif;

	function dotr($arr) {

	}