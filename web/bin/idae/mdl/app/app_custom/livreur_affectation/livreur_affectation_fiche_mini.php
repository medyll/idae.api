<?
	include_once($_SERVER['CONF_INC']);

	$table       = $this->HTTP_VARS['table'];
	$Table       = ucfirst($table);
	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$arr_allowed_c = droit_table($type_session, 'C', $table);
	$arr_allowed_r = droit_table($type_session, 'R', $table);
	$arr_allowed_u = droit_table($type_session, 'U', $table);
	$arr_allowed_d = droit_table($type_session, 'D', $table);
	$arr_allowed_l = droit_table($type_session, 'L', $table);

	$APP           = new App($table);
	$Idae          = new Idae($table);
	$APPOBJ        = $APP->appobj($table_value, $vars);
	$ARR           = $APPOBJ->ARR;
	$iconAppscheme = $APP->iconAppscheme;

	$query_link = 'table=' . $table . '&table_value=' . $table_value;

	$Idae        = new Idae($table);
	$module_link = in_array($table, ['livreur_affectation']) ? 'update' : 'fiche';

	$ARR_COLLECT = $Idae->get_table_fields($table_value, ['codeAppscheme_field' => ['code']]);
?>
<div class="animated fadeIn  ">
	<div style="margin:0" class=" blanc   "  data-table="<?= $table ?>" data-table_value="<?= $table_value ?>" data-module_link="update" data-target_flyout="true"  data-vars="<?= $query_link ?>">
		<div class="padding">
			<div class="padding borderb flex_h flex_align_middle">
				<div class="padding_more   boxshadowr"><?= $APP->cf_output('actif', $ARR); ?></div>
				<div class="flex_main flex_h flex_align_middle">
					<div class="flex_h flex_align_middle flex_main">
						<div class="padding"><?= $APP->cf_output('code', $ARR); ?></div>
					</div>
					<div class="flex_h flex_align_middle flex_main">
						<div class="padding textgris"><?= $APP->cf_output_icon('heureDebut'); ?></div>
						<div class="padding"><?= $APP->cf_output('heureDebut', $ARR); ?></div>
					</div>
					<div class="flex_h flex_align_middle flex_main">
						<div class="padding textgris"><?= $APP->cf_output_icon('heureFin'); ?></div>
						<div class="padding"><?= $APP->cf_output('heureFin', $ARR); ?></div>
					</div>
				</div>
				<a class="padding_more cursor" data-table="<?= $table ?>" data-table_value="<?= $table_value ?>" data-module_link="update" data-target_flyout="true"  data-vars="<?= $query_link ?>">
					<a><i class="fa fa-ellipsis-h"></i></a>
				</a>
			</div>
		</div>
	</div>
</div>