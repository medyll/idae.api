<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$arr_allowed_c = droit_table($type_session, 'C', $table);
	$arr_allowed_r = droit_table($type_session, 'R', $table);
	$arr_allowed_u = droit_table($type_session, 'U', $table);
	$arr_allowed_d = droit_table($type_session, 'D', $table);
	$arr_allowed_l = droit_table($type_session, 'L', $table);

	$table       = $this->HTTP_VARS['table'];
	$Table       = ucfirst($table);
	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	//
	$APP       = new App($table);
	$APP_TABLE = $APP->app_table_one;
	$APPOBJ    = $APP->appobj($table_value, $vars);
	$ARR       = $APP->findOne(["id$table" => (int)$table_value]);

	if ($APP->has_field("dateDebut$table")) {
		echo "date";
	}
	$Idae        = new Idae($table);
	$BIN         = new Bin();
	$ARR_COLLECT = $Idae->get_table_fields($table_value);

	if ($APP->has_field('adresse')) {
		$html_map_link = $html_attr = 'table=' . $table . '&table_value=' . $table_value;
	}

	//echo sizeof($BIN->shop_commande_queue($ARR['idshop']));
	//Helper::dump((sizeof($BIN->shop_commande_queue($ARR['idshop']) > NB_MAX_COMMANDE_SHOP)));
?>
<span data-thumb_order="<?= (int)$ARR['timeFinPreparationCommande'] ?>" data-thumb_order_parent=""></span>
<div class=" padding ">
	<div class=" boxshadowb   ">
		<div class="  relative">
			<div class="flex_h flex_align_middle">
				<div class="flex_h flex_align_middle" style="width:100%;">
					<div class="padding_more">
						<?= $Idae->cf_output('color', $ARR, 'colorCommande_statut'); ?>
					</div>
					<div class="padding_more borderr" style="font-size:1.4em;width:140px;">
						<?= $Idae->cf_output('code', $ARR); ?>
					</div>
					<div class="padding_more" style="width:30px;">
						<i class="fa fa-clock-o fa-2x textgris"></i>
					</div>
					<div class="padding_more  borderr  text-center text-bold  " style="font-size:1.4em;"><?= $Idae->cf_output('heureDebutPreparation', $ARR); ?></div>
					<div class="padding_more  " style="width:30px;"><?= $Idae->cf_output('icon', $ARR, 'iconCommande_statut'); ?></div>
					<div class="padding_more borderr" style="width:120px;">
						<?= $Idae->cf_output('nom', $ARR, 'nomCommande_statut'); ?>
					</div>
					<div class="padding_more  text-center  " style="width:80px;"> emportée
						<div class=" text-bold">
							<?= $Idae->cf_output('heureFinPreparation', $ARR); ?></div>
					</div>
					<div class="padding_more  text-center " style="width:80px;"> livrée pour
						<div class=" text-bold">
							<?= $Idae->cf_output('heureLivraison', $ARR); ?></div>
					</div>
					<div class="flex_main relative padding_more" style="">
						<div class="padding   ">
							<?= $ARR['adresseCommande'] . ' ' . $ARR['adresse2Commande'] . ' ' . $ARR['codePostalCommande'] . ' ' . $ARR['villeCommande'] ?>
						</div>
						<div id="text_animate_step_commande_<?= $table_value ?>" class="text-center"></div>
						<div class="relative">
							<?= $Idae->module("app_console/$table/app_console_progress", ['table'       => $table,
							                                                              'table_value' => $table_value]) ?>
						</div>
					</div>
					<div class="padding_more text-bold " style="width:90px;">
						<?= $Idae->cf_output('nom', $ARR, 'nomLivreur'); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="relative none">
			<div class="blanc">
				<?= $Idae->module('app_fiche/fiche_fields_table', ['table'               => $table,
				                                                   'table_value'         => $table_value,
				                                                   'show_empty'          => 0,
				                                                   'codeAppscheme_field' => ['distance', 'dureeLivraison', 'volume']]) ?>
			</div>
		</div>
	</div>
</div>