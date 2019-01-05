<?
	include_once($_SERVER['CONF_INC']);

	// ini_set('display_errors', 55);
	$table = $this->HTTP_VARS['table'];

	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);

	$APP       = new App($table);
	$APPOBJ    = $APP->appobj($table_value, $vars);
	$APP_TABLE = $APP->app_table_one;

	//
	$EXTRACTS_VARS = $APP->extract_vars($table_value, $vars);
	extract($EXTRACTS_VARS, EXTR_OVERWRITE);
	//

	$iconAppscheme = $APP->iconAppscheme;

	$html_edit_link = 'table=' . $table . '&table_value=' . $table_value;

	$Idae = new Idae($table);

	$html_table_statut     = $Idae->fiche_next_statut($table_value, true);
	$html_table_statut_has = $Idae->module('fiche_has', "table=$table&table_value=$table_value&table_type=statut");
	$ARR_COLLECT           = $Idae->get_table_fields($table_value, ['codeAppscheme_field' => ['nom', 'code']]);

	$ARR_FIELDS = array_column($ARR_COLLECT, 'appscheme_fields');

	$crypted_fiche_link = AppLink::fiche($table,$table_value );

	$COLLECT_FIELDS = [];
	foreach ($ARR_COLLECT as $key => $ARR_FIELD_GROUP) {
		foreach ($ARR_FIELD_GROUP['appscheme_fields'] as $CODE_ARR_FIELD => $ARR_FIELD) {
			$COLLECT_FIELDS[] = $ARR_FIELD;
		}
	}

    $options = ['apply_droit'        => [$type_session,
                                         'R'],
                'data_mode'          => 'fiche',
                'scheme_field_view'  => 'short',
                'field_draw_style'   => 'draw_html_field',
                'scheme_field_view_groupby' =>  'group',
                'fields_scheme_part' => 'all',
                'show_field_name'  => 0,
                'field_composition'  => ['hide_field_icon'  => 1,
                                         'hide_field_name'  => 1,
                                         'hide_field_value' => 1]];

 /*   $Fabric = new AppDataSchemeFieldDrawerFabric($table, $options);
    $Fabric->fetch_data(["id$table" => $table_value]);
    $tplData = $Fabric->get_templateDataHTML();*/
?>
<div class="animated fadeIn">
	<div class="borderb  blanc" data-crypted_link="<?= $crypted_fiche_link ?>">
		<div class="flex_h flex_align_middle ">
			<div class="flex_main">
				<div class="flex_h   flex_align_middle">
					<div class="padding  text-center  " style="width: 50px;">
						<? if (!empty($APP_TABLE['hasImageScheme']) && !empty($this->HTTP_VARS['table_value'])): ?>
							<div>
								<img class="boxshadow" style="width:40px;border-radius: 50%;" src="<?= AppSite::imgApp($table, $table_value, 'square') ?>">
							</div>
						<? else : ?>
							<div class="boxshadowr">
								<i style="color: <?= $APPOBJ->ICON_COLOR ?>" class="fa fa-<?= $APPOBJ->ICON ?>"></i>
							</div>
						<? endif; ?>
					</div>
					<div class="hide-for-small-only" style="width:150px;">
						<div class="flex_h flex_align_middle   ">
							<div class="padding_more">
								<?= $APPOBJ->NAME_APP ?>
							</div>
						</div>
					</div>
					<div class="flex_main   " style="width: 100%;">
						<div class="flex_align_middle flex_h flex_wrap  " style="width: 100%;">
                            <?//   var_dump($tplData['scheme_field_image']) ?>
							<?
								foreach ($COLLECT_FIELDS as $CODE_ARR_FIELD => $ARR_FIELD) { ?>
									<div class=" " style="min-width: 50%;">
										<div class="flex_h flex_align_middle  ">
											<div class="text-bold hide-for-small-only" style="width:80px;">
												<div class="flex_h flex_align_middle">
													<div>
														<i class="fa fa-<?= $ARR_FIELD['icon'] ?> item_icon"></i>
													</div>
													<div class="flex_main padding_more">
														<div class="ellipsis">
															<?= ucfirst(idioma($ARR_FIELD['nom'])) ?>
														</div>
													</div>
												</div>
											</div>
											<div class="text-bold padding_more text-center show-for-small-only" style="width:20px;">
												<i class="fa fa-<?= $ARR_FIELD['icon'] ?> item_icon"></i>
											</div>
											<div class="<?= $ARR_FIELD['css_bol'] ?> flex_main">
												<div class=" padding_more" style="white-space: nowrap;">
													<?= $ARR_FIELD['value_html'] ?>
												</div>
											</div>
										</div>
									</div>
								<? } ?>
						</div>
					</div>
				</div>
			</div>
			<a class="padding_more cursor" data-crypted_link="<?= $crypted_fiche_link ?>">
				<i class="fa fa-ellipsis-h"></i>
			</a>
		</div>
	</div>
</div>