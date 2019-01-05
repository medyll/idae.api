<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 25/12/2017
	 * Time: 15:30
	 */
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$APP = new App('appscheme');
	//
	$arr_allowed_c = droit_table($type_session, 'C');
	$arr_allowed_r = droit_table($type_session, 'R');
	$arr_allowed_l = droit_table($type_session, 'L');
	//
	$APP_SCH    = new App('appscheme');
	$APP_SCH_TY = new App('appscheme_type');

	$IDAE_SELF = new Idae($type_session);
	$ARR_SELF  = $IDAE_SELF->findOne([$name_idtype_session => $idtype_session]);

	$html_entete        = $IDAE_SELF->module('fiche_entete', "table=$type_session&table_value=" . $_SESSION["id$type_session"]);
	$html_entete_fields = $IDAE_SELF->module('fiche_fields', "edit_field=true&codeAppscheme_field_type=bool&table=$type_session&table_value=" . $_SESSION["id$type_session"]);

	$arr_sch_type = $APP_SCH->distinct_all('idappscheme_type', ['codeAppscheme' => ['$in' => $arr_allowed_r]]);
	$RS_TY        = $APP_SCH_TY->find(['idappscheme_type' => ['$in' => $arr_sch_type]])->sort(['nomAppscheme_type' => 1]);

	$crypted_console = AppLink::console('commande');

?>
<div class="flex_v blanc" style="height:100%;overflow:hidden;">
	<div class="sticky" style="margin-top:0em;">
		<?= $html_entete; ?>
	</div>
	<div class="flex_main" style="overflow:auto;">
		<div class="blanc_transp_light   " data-close>
			<a class="flex_h flex flex_align_middle padding_more" data-crypted_link="<?= $crypted_console ?>">
				<span class="item_icon more text-center textbold"><i class="fa fa-play "></i> </span>
				<span class="flex_main text-shadow">Start</span>
			</a>
		</div>
		<?

			while ($ARR_TY = $RS_TY->getNext()) {
				$idappscheme_type = (int)$ARR_TY['idappscheme_type'];
				$RS_SCH           = $APP_SCH->find(['idappscheme_type' => $idappscheme_type, 'codeAppscheme' => ['$in' => $arr_allowed_r]])->sort(['nomAppscheme' => 1]);
				?>
				<div class=" ">
					<div class=" ">
						<?

							$arr_fields = ['nom', 'code', 'color', 'icon'];
							while ($ARR_SCH = $RS_SCH->getNext()) {
								$table = $ARR_SCH['codeAppscheme'];
								$extr  = [];
								foreach ($arr_fields as $key_f => $value_field) {
									$extr[$value_field . '_table'] = $ARR_SCH[$value_field . 'Appscheme'];
								}
								extract($extr); // "nom_table icon_table ..."
								$APP_SCH_TMP = new App($table);
								$add_vars    = '';
								$arr_vars    = [];
								if ($APP_SCH_TMP->has_field_fk($type_session)) {
									if ($arr_allowed_l[$type_session]) {
										// echo '.';
										$add_vars                    = "&vars[id$type_session]=$idtype_session";
										$arr_vars["id$type_session"] = $idtype_session;
									} else {
										// echo '..';
										$add_vars                    = "&vars[id$type_session]=$idtype_session";
										$arr_vars["id$type_session"] = $idtype_session;
									}
								}
								// si commandes, on rajoute secteur pour livreur, idshop pour shop
								// si statut plus que START :
								// idlivreur pour livreur, idshop pour shop
								if ($table == 'commande' && ($type_session == 'shop' || $type_session == 'livreur')) {
									$add_vars                    = "&vars[id$type_session]=$idtype_session";
									$arr_vars["id$type_session"] = $idtype_session;
								}

								$crypted_liste = AppLink::liste($table, null, $arr_vars);
								?>
								<div class="blanc_transp_light">
									<a class="flex_h flex flex_align_middle padding_more" data-crypted_link="<?= $crypted_liste ?>" aria-label="Close menu" data-close>
										<span class="item_icon_more text-center"><i class="fa fa-<?= $icon_table ?>" style="color:<?= $color_table ?>"></i></span>
										<span class="flex_main text-shadow"><?= ucfirst(idioma($nom_table)) ?></span>
										<span class="item_icon more"><i class="fa fa-caret-right"></i></span>
									</a>
								</div>
								<div class="flex_h flex_wrap flex_col_1  ">
									<?

										if ($ARR_SCH['hasStatutScheme']) {
											$value  = 'statut';
											$APPTMP = new App("$table" . "_" . "$value");
											$RS_TMP = $APPTMP->find();

											$Value  = ucfirst($value);
											$_table = $table . '_' . $value;
											$_Table = ucfirst($_table);
											$_id    = 'id' . $_table;
											$_nom   = 'nom' . $_Table;
											$_code  = 'code' . $_Table;
											$_icon  = 'icon' . $_Table;
											$_color = 'color' . $_Table;
											while ($ARR_TMP = $RS_TMP->getNext()) {
												$tpl_color = $APP->draw_field(['field_name_raw' => 'color',
												                               'field_name'     => $_color,
												                               'table'          => $value,
												                               'field_value'    => $ARR_TMP[$_color]]);
												$tpl_icon  = $APP->draw_field(['field_name_raw' => 'icon',
												                               'field_name'     => $_icon,
												                               'table'          => $value,
												                               'field_value'    => $ARR_TMP[$_icon]]);
												$tpl_nom   = $APP->draw_field(['field_name_raw' => 'nom',
												                               'field_name'     => $_nom,
												                               'table'          => $value,
												                               'field_value'    => $ARR_TMP[$_nom]]);

												// si commandes, on rajoute secteur pour livreur, idshop pour shop
												$ARR_SELF = $IDAE_SELF->findOne([$name_idtype_session => $idtype_session]);
												// si statut plus que START :
												// idlivreur pour livreur, idshop pour shop
												$add_vars = '';
												if ($table == 'commande') {
													if ($type_session == 'shop') {
														$add_vars = "&type_liste=shop_pool_" . $ARR_TMP[$_code];
													}
													if ($type_session == 'livreur') {
														$add_vars = "&type_liste=livreur_pool_" . $ARR_TMP[$_code];
													}
												}
												$data_vars = "table=$table&vars[$_id]=$ARR_TMP[$_id]$add_vars";
												?>
												<div data-module_link="app_console/app_console_<?= $type_session ?>" data-vars="<?= $data_vars ?>" data-close class="none flex_h flex_align_middle flex_padding flex_margin cursor borderb" style="line-height:2rem;">
													<div class="item_icon"><?= $tpl_icon ?></div>
													<div class="flex_main"><?= $tpl_nom ?></div>
													<div>
														<span data-<?= $name_idtype_session ?>="<?= $idtype_session ?>" data-idsecteur="<?= $ARR_SELF['idsecteur'] ?>" data-count="<?= $_table ?>" data-table_value="<?= $ARR_TMP[$_id] ?>" data-table="<?= $table ?>" class="ellipsis"></span>
													</div>
													<div class="item_icon"><?= $tpl_color ?></div>
												</div>
												<?
											}
										} ?>
								</div>
								<?
							} ?>
					</div>
				</div>
			<? } ?>
	</div>
	<? if (ENVIRONEMENT == 'PREPROD_LAN') { ?>
		<div class="padding_more  ">
			<a data-module_link="app_dev/test_1" data-close>page test 1</a>
			<a data-module_link="app_dev/test_2" data-close>page test 2</a>
		</div>
	<? } ?>
	<div class="flex_h   flex_align_middle flex_padding padding_more ededed bordert" style="margin-bottom:0;bottom:0px;">
		<div class="flex_main text-left ">
			<a class="  flex_h flex_align_middle " onclick="post_logout_multi('<?= $type_session ?>');return false;" href="#quit">
				<div class="padding"><i class="fa fa-ban  fa-2x"></i></div>
				<div class="padding">Quitter</div>
			</a>
		</div>
		<div class="flex_main  text-right">
			<a class=" flex_inline  flex_h flex_align_middle " href="#home" data-close>
				<div class="padding">Home</div>
				<div class="padding"><i class="fa fa-home  fa-2x"></i></div>
			</a>
		</div>
	</div>
</div>
<style>
	[data-mdl=menu] {
		height : 100%;
	}
</style>