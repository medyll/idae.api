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

	$arr_allowed_c = droit_table($type_session, 'C');
	$arr_allowed_r = droit_table($type_session, 'R');
	$arr_allowed_l = droit_table($type_session, 'L');
	//
	$APP_SCH    = new App('appscheme');
	$APP_SCH_TY = new App('appscheme_type');

	$IDAE_SELF          = new Idae($type_session);
	$html_entete        = $IDAE_SELF->module('fiche_entete', "table=$type_session&table_value=" . $_SESSION["id$type_session"]);
	$html_entete_fields = $IDAE_SELF->module('fiche_fields', "edit_field=true&codeAppscheme_field_type=bool&table=$type_session&table_value=" . $_SESSION["id$type_session"]);

	$arr_sch_type = $APP_SCH->distinct_all('idappscheme_type', ['codeAppscheme' => ['$in' => $arr_allowed_r]]);
	$RS_TY        = $APP_SCH_TY->find(['idappscheme_type' => ['$in' => $arr_sch_type]])->sort(['nomAppscheme_type' => 1]);

	$crypted_profil  = AppLink::home($type_session, $idtype_session);
	$crypted_console = AppLink::console('commande');

?>
<div class="blanc" style="height:100%;overflow:hidden;">
	<div class=" " style="height:100%;overflow:auto;">
		<div class="grid-x align-center align-right">
			<div class="cell small-12 medium-12 large-12">
				<div class="text-center" data-crypted_link="<?= $crypted_console ?>">
					<div class="icon_home">
						<div class="icon_home_icon"><i class="fa fa-play  fa-5x"></i></div>
						<div class="text-shadow">Start</div>
					</div>
				</div>
			</div>
			<div class="cell small-5 medium-4 large-4">
				<div class="text-center">
					<a class="icon_home cursor" data-crypted_link="<?= $crypted_profil ?>">
						<div class="icon_home_icon"><i class="fa fa-male  fa-3x"></i></div>
						<div class="flex_main text-shadow">Mon profil</div>
					</a>
				</div>
			</div>
			<?

				while ($ARR_TY = $RS_TY->getNext()) {
					$idappscheme_type = (int)$ARR_TY['idappscheme_type'];
					$RS_SCH           = $APP_SCH->find(['idappscheme_type' => $idappscheme_type, 'codeAppscheme' => ['$in' => $arr_allowed_r]])->sort(['nomAppscheme' => 1]);

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
							$add_vars = "&vars[id$type_session]=$idtype_session";
							$arr_vars["id$type_session"] = $idtype_session;
						}
						$crypted_app_liste = AppLink::liste($table, null,$arr_vars);
						?>
						<div class="cell small-5 medium-4 large-4">
							<div class="text-center">
								<div class="icon_home cursor"  data-crypted_link="<?= $crypted_app_liste ?>" aria-label="Close menu" data-close>
									<div class=" icon_home_icon"><i class="fa fa-<?= $icon_table ?> fa-3x" style="color:<?= $color_table ?>"></i></div>
									<div class="text-shadow"><?= ucfirst(idioma($nom_table)) ?></div>
								</div>
							</div>
						</div>
					<? } ?>
				<? } ?>
			<div class="cell small-5 medium-4 large-4">
				<div class="text-center">
					<a class="icon_home cursor" onclick="post_logout_multi('<?= $type_session ?>');return false;" href="#quit">
						<div class="icon_home_icon"><i class="fa fa-ban  fa-3x"></i></div>
						<div class="flex_main text-shadow">Quitter</div>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
	[data-mdl=menu] {
		height : 100%;
	}
</style>