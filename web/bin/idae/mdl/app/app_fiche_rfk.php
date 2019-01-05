<?
	include_once($_SERVER['CONF_INC']);
	// ini_set('display_errors', 55);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$table       = $this->HTTP_VARS['table'];
	$Table       = ucfirst($table);
	$table_value = empty($this->HTTP_VARS['table_value']) ? '' : (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);

	$arr_allowed_c = droit_table($type_session, 'C', $table);
	$arr_allowed_r = droit_table($type_session, 'R', $table);
	$arr_allowed_u = droit_table($type_session, 'U', $table);
	$arr_allowed_d = droit_table($type_session, 'D', $table);
	$arr_allowed_l = droit_table($type_session, 'L', $table);

	$id = 'id' . $table;
	if (!empty($table_value)) $vars[$id] = (int)$table_value;

	$APP = new App($table);
	$ARR = $APP->findOne($vars);

	$ARR_RFK = $APP->get_table_rfk($table_value);

	if (is_array($arr_allowed_c) && is_array($ARR_RFK)) {
		$arr_allowed_c = droit_table($type_session, 'L');
		$ARR_RFK       = array_intersect($arr_allowed_c, $ARR_RFK);
	}

?>
<div class="grid-x grid-padding-x align-spaced"><?
		foreach ($ARR_RFK as $table_rfk):
		//	if (strpos($table_rfk, '_ligne') !== false) continue;
			$APP_TMP           = new App($table_rfk);
			$vars_rfk['vars']  = ['id' . $table => $table_value];
			$vars_rfk['table'] = $table_rfk;
			if ($APP_TMP->has_field_fk($type_session)) {
				//$vars_rfk['vars'][$name_idtype_session] = $idtype_session;
			}
			$html_list_link = http_build_query($vars_rfk);
			$ARR_RFK        = $APP_TMP->findOne(["id$table_rfk" => (int)$ARR["id$table_rfk"]]);
			$RS_TMP         = $APP_TMP->find($vars_rfk['vars']);
			$count          = $RS_TMP->count();
			//if (empty($count)) continue;
			?>
			<div class=" small-12 medium-12 large-12">
				<div class="margin  " style="overflow:hidden;">
					<a data-module_link="app_liste/app_liste_mini" data-vars="<?= $html_list_link ?>&nbRows=10"
					   data-target_flyout="true"
					   class="ellipsis buton_full  " data-link
					   data-table="<?= $table_rfk ?>"
					   data-vars="<?= http_build_query($vars_rfk); ?>">
						<i class="fa fa-<?= $APP_TMP->iconAppscheme ?> padding" style="color: <?= $APP_TMP->colorAppscheme ?>;"></i>
						<span data-count="data-count" data-table="<?= $table_rfk ?>" data-vars="<?= http_build_query($vars_rfk); ?>">
						<?= $count ?>
						</span>
						<?= $APP_TMP->nomAppscheme; ?>
					</a>
				</div>
			</div>
		<? endforeach; ?>
</div>