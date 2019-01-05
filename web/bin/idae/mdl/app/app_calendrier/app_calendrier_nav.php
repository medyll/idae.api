<?
	include_once($_SERVER['CONF_INC']);
	$time = uniqid();

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
?>
<div class="applink flex_h flex_align_middle flex_padding_more borderb" style="overflow: hidden">
	<div class="flex_main" style="overflow: hidden">
		<div class="flex_h flex_align_middle flex_padding">
			<div class="flex_main">
				<a class="avoid bold change_month ellipsis h3 text-bold" data-vars="<?= http_build_query(['sd' => $sd]) ?>" style="margin:0;">
					<?= $tabmonth[intval($moisEnCours)] ?>
				</a>
			</div>
			<div class="flex_main">
				<a class="avoid bold change_year ellipsis aligncenter h3  text-bold" data-vars="<?= http_build_query(['sd' => $sd]) ?>" style="margin:0;">
					<?= $anneeEnCours ?>
				</a>
			</div>
		</div>
	</div>
	<div style="min-width:50px;width:50px;" class="text-center previous_month padding"
	     data-vars="<?= http_build_query(['sd' => $moisPrec]) ?>">
			<i class="fa fa-chevron-left"></i>
	</div>
	<div style="min-width:50px;width:50px;" class="avoid text-center next_month borderl padding"
	     data-vars="<?= http_build_query(['sd' => $moisSuiv]) ?>">
		<i class="fa fa-chevron-right"></i>
	</div>
</div>
