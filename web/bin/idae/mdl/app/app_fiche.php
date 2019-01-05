<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$arr_allowed_r = droit_table($type_session, 'R', $table);
	if (empty($arr_allowed_r)) return false;

	$table       = $_POST['table'];
	$table_value = (int)$_POST['table_value'];
	$vars        = empty($_POST['vars']) ? [] : function_prod::cleanPostMongo($_POST['vars'], 1);

	$test_custom = Idae::module_exists('app_custom/' . $table . '/' . $table . '_fiche');
	if (!empty($test_custom)) {
		echo $Idae->module('app_custom/' . $table . '/' . $table . '_fiche', $this->HTTP_VARS);

		return;
	}

	$APP       = new App($table);

	$APP_TABLE = $APP->app_table_one;
	$APPOBJ    = $APP->appobj($table_value, $vars);
	$ARR       = $APP->findOne(["id$table" => (int)$table_value]);
	//
	$EXTRACTS_VARS = $APP->extract_vars($table_value, $vars);
	extract($EXTRACTS_VARS, EXTR_OVERWRITE);

	$Idae = new Idae($table);

	$crypted_image_link = AppLink::liste_images($table, $table_value);

	$options = ['apply_droit'               => [$type_session,
	                                            'R'],
	            'data_mode'                 => 'fiche',
	            'scheme_field_view'         => 'native',
	            'field_draw_style'          => 'draw_html_field',
	            'scheme_field_view_groupby' => 'group',
	            'fields_scheme_part'        => 'all',
	            'field_composition'         => ['hide_field_icon'  => 1,
	                                            'hide_field_name'  => 1,
	                                            'hide_field_value' => 1]];

	$Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);
	$Fabric->fetch_data(["id$table" => $table_value]);
	$tplData = $Fabric->get_templateDataHTML();

?>
<div class="flex_v" style="min-height:100%;z-index:0;">
    <div style="">
		<?= $Idae->module('fiche_entete', ['table'       => $table,
		                                   'table_value' => $table_value]) ?>
    </div>
    <div class="flex_main " style="overflow-y:auto;overflow-x:hidden;">
        <div class="grid-x    ">
            <div class="cell small-12 medium-9    large-8    small-order-2 medium-order-1">
                <div class="padding_more borderb">
					<?= $tplData['scheme_field_fk_grouped'] ?>
                </div>

				<?
					if ($table != $type_session || $idtype_session == (int)$ARR[$name_idtype_session]): ?>
                        <br>
                        <div class="padding_more boxshadowr">
							<?= $tplData['scheme_field_native'] ?>
                        </div>
                        <div class="padding_more none">
	                        <?= $tplData['scheme_field_image'] ?>
                        </div>
                        <div class="padding_more none">
							<?= $Idae->module('app_fiche_fields', ['table'       => $table,
							                                       'table_value' => $table_value,
							                                       'titre'       => 0,
							                                       'show_empty'  => 0]) ?>
                        </div>
					<? endif; ?>
				<? if (!empty($APPOBJ->APP_TABLE['hasLigneScheme'])): ?>
                    <div class="padding_more    ">
                        <div class="bordert">
							<?= $Idae->module('app_fiche/fiche_ligne', ['table' => $table . '_ligne',
							                                            'vars'  => ["id$table" => $table_value]]) ?>
                        </div>
                    </div>
				<? endif; ?>
            </div>
            <div class="cell small-12 medium-3 large-4 small-order-1 medium-order-2">
				<? if (!empty($APP_TABLE['hasImageScheme']) && $idtype_session == (int)$ARR[$name_idtype_session]) :

					?>
                    <div class="text-center padding_more  ">
                        <button class="button button_full" data-crypted_link="<?= $crypted_image_link ?>">
                            Voir images
                        </button>
                    </div>
                    <br>
				<? endif; ?>
                <div class="padding_more">
                    <?= $Idae->module('app_fiche_fk/app_fiche_fk_nongrouped', ['mode'        => 'fiche',
				                                                                                     'table'       => $table,
				                                                                                     'table_value' => $table_value]) ?></div>
                <div class="padding_more">
                    <div class="borderb"></div>
					<?= $Idae->module('fiche_rfk', ['table'       => $table,
					                                'table_value' => $table_value]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="bordert">
		<?= $Idae->module('app_fiche/app_fiche_menu', ['table'       => $table,
		                                               'table_value' => $table_value]) ?>
    </div>
</div>