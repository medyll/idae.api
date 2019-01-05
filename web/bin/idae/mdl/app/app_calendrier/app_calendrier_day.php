<?
	include_once($_SERVER['CONF_INC']);
	$time = uniqid();
	// Helper::dump($this->HTTP_VARS);
	$type_session        = $_SESSION['type_session'];
	$Type_session        = ucfirst($type_session);
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$table                   = $this->HTTP_VARS['table'];
	$Table                   = ucfirst($table);
	$APP                     = new App($table);
	$APP_JOURS               = new App('jours');
	$APP_SECTEUR_JOURS_SHIFT = new App('secteur_jours_shift');
	$APP_LIV_AFFECT          = new App('livreur_affectation');

	//
	$vars = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo(array_filter($this->HTTP_VARS['vars'], "my_array_filter_fn"), 1);
	// Helper::dump($vars);

	// generalement , le nom du container
	$calendarId = $this->HTTP_VARS['calendarId'];
	$sd         = $this->HTTP_VARS['sd'];

	$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
	$tabjour  = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
	$tabjour  = ["L", "M", "M", "J", "V", "S", "D"];

	$jourEnCours  = date("d", $sd);
	$moisEnCours  = date("m", $sd);
	$anneeEnCours = date("Y", $sd);
	$indexJourCrt = date("w", $sd);
	if ($indexJourCrt == 0)
		$indexJourCrt = 7;

	$lienCalAvant = gmmktime(12, 0, 0, $moisEnCours - 1, $jourEnCours, $anneeEnCours);
	$lienCalApres = gmmktime(12, 0, 0, $moisEnCours + 1, $jourEnCours, $anneeEnCours);

	$anneeAvant = $moisEnCours . "','" . ($anneeEnCours - 1);
	$anneeApres = $moisEnCours . "','" . ($anneeEnCours + 1);

	$moyear = $tabmonth[intval($moisEnCours)] . "&nbsp;&nbsp;" . $anneeEnCours;
	$now    = date("Y/m/d", $sd);

	$moisPrec = mktime(12, 0, 0, $moisEnCours - 1, $jourEnCours, $anneeEnCours);
	$moisSuiv = mktime(12, 0, 0, $moisEnCours + 1, $jourEnCours, $anneeEnCours);
	$today    = date("d/m/Y", $sd);

	if (!empty($this->HTTP_VARS['table'])) {
		$table     = $this->HTTP_VARS['table'];
		$APPTMP    = new App($table);
		$type_date = $APPTMP->has_field('dateDebut') ? 'dateDebut' : 'date';
	}
?>
<div class="applink" style="overflow:hidden;margin:0 auto;padding:0;width:100%;max-width:100%;">
	<table class="unstriped tableCalendrier" style="width:100%" border="0" cellpadding=0 cellspacing="1">
		<thead>
			<tr class="borderb">
				<? for ($i = 0; $i < 7; $i++) { ?>
					<td class="text-center borderb"><?= $tabjour[$i] ?></td>
				<? } ?>
			</tr>
		</thead>
		<tbody data-toggler>
			<?
				$num_day = date("w", mktime(0, 0, 0, $moisEnCours, 01, $anneeEnCours));
				if ($num_day == 0) {
					$num_day = 7;
				}
				$max_day = date("t", mktime(0, 0, 0, $moisEnCours, 01, $anneeEnCours));
				$cpt_day = 2;
				while ($cpt_day <= $max_day + $num_day) {
					// calcul le numero de semaine
					$nb_day = date("z", mktime(0, 0, 0, $moisEnCours, $cpt_day - $num_day + 3, $anneeEnCours));
					$val    = intval($nb_day / 7) + 1; ?>
					<tr>
						<?
							// affiche les jours du mois
							for ($i = 0; $i < 7; $i++) {
								$theday    = date("D", mktime(0, 0, 0, $moisEnCours, $cpt_day - $num_day, $anneeEnCours));
								$date_fr   = date("d/m/Y", mktime(0, 0, 0, $moisEnCours, $cpt_day - $num_day, $anneeEnCours));
								$date_us   = date("Y-m-d", mktime(0, 0, 0, $moisEnCours, $cpt_day - $num_day, $anneeEnCours));
								$date_str  = htmlspecialchars(function_prod::jourMoisDate_fr($date_us));
								$sd        = mktime(12, 0, 0, $moisEnCours, $cpt_day - $num_day, $anneeEnCours);
								$val       = date("d", mktime(0, 0, 0, $moisEnCours, $cpt_day - $num_day, $anneeEnCours));
								$jourferie = calcul_joursferies($moisEnCours, $cpt_day - $num_day, $anneeEnCours);
								$active    = ($sd == $this->HTTP_VARS['sd']) ? 'active' : '';
								$class     = "titrenum";
								if ((($cpt_day - $num_day) < 1) or (($cpt_day - $num_day) > $max_day)) {
									$class = "titrenum2";
									if (($theday == "Sun") or ($theday == "Sat") or ($jourferie)) {
										$class = "titrewend2";
									}
								}
								if (($theday == "Sun") or ($theday == "Sat") or ($jourferie)) {
									$class = "titrewend";
								}
								$now_css = '';
								if ($now == date("Y/m/d", mktime(0, 0, 0, $moisEnCours, $cpt_day - $num_day, $anneeEnCours))) {
									$now_css = "titrenow";
								}
								$cpt_day++;
								$ct = 0;
								if (!empty($this->HTTP_VARS['table'])) {
									$rsct         = $APPTMP->find(array_merge($vars, [$type_date . $Table => $date_us]))->sort(['heureDebut' . $Table])->limit(2);
									$ct           = $rsct->count();
									$ct_css       = ($ct == 0) ? ' ' : ' ';
									$has_some_css = ($ct != 0) ? 'calendar_day_active' : ' ';
									$arrDots      = [];
									$Idae         = new Idae($this->HTTP_VARS['table']);

									$ARR_JOURS = $APP_JOURS->findOne(['ordreJours' => $i]);
									$idjours   = $ARR_JOURS['idjours'];

									if ($this->HTTP_VARS['table'] == 'livreur_affectation') {
										$dots_AM                  = $dots_PM = null;
										$vars_qy_liv              = ['idlivreur' => $idtype_session, 'dateDebutLivreur_affectation' => $date_us];
										$vars_qy_liv['code_auto'] = 'AM';
										$ARR_AM                   = $APP_LIV_AFFECT->findOne($vars_qy_liv, ['_id' => 0]);
										$vars_qy_liv['code_auto'] = 'PM';
										$ARR_PM                   = $APP_LIV_AFFECT->findOne($vars_qy_liv, ['_id' => 0]);
										if (!empty($ARR_AM['idlivreur_affectation']) && !empty($ARR_PM['idlivreur_affectation'])) {
											$dots_mdl = 'app_custom/livreur_affectation/livreur_affectation_calendar_thumb';
											$dots_AM  = $Idae->module($dots_mdl, ['idlivreur' => $idtype_session, 'table_value' => $ARR_AM['idlivreur_affectation']]);
											$dots_PM  = $Idae->module($dots_mdl, ['idlivreur' => $idtype_session, 'table_value' => $ARR_PM['idlivreur_affectation']]);
										}
									}
								}
								?>
								<td style="white-space:nowrap" data-droptache="rebound" dropvalue="<?= $date_fr ?>" class="<?= $class ?> <?= $ct_css ?>  text-center relative" title="<?= $theday . ' ' . $date_fr ?>" value="<?= http_build_query(['sd' => $sd]) ?>">
									<a class="autoToggle cell_day inline <?= $now_css ?> <?= $active ?> <?= $ct_css ?>" onclick="$(this).trigger('dom:act_click',{value:'<?= $date_fr ?>',value_us:'<?= $date_us ?>',value_str:'<?= $date_str ?>',value_count:'<?= $ct ?>'});">
										<?= $val ?>
									</a>
									<div class="calendar_day_dots flex_h ">
										<? if ($dots_AM) { ?>
											<div class="flex_h flex_main">
												<div class="flex_main text-right"><?= $dots_AM; ?></div>
												<div>&nbsp;</div>
												<div class="flex_main text-left"><?= $dots_PM; ?></div>
											</div>
										<? } else if (!empty($rsct)) {
											while ($arr_ct = $rsct->getNext()) {
												$actif        = $arr_ct['actif' . $Table];
												$has_some_css = ($actif != 0) ? 'calendar_day_active' : 'calendar_day_inactive';
												?>
												<div class="<?= $has_some_css ?>"></div>
												<?
											}
										}
										?>
									</div>
								</td>
							<? } ?>
					</tr>
				<? } ?></tbody>
	</table>
</div>
