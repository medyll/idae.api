<?
	include_once($_SERVER['CONF_INC']);
	$sd         = $this->HTTP_VARS['sd'];
	$calendarId = $this->HTTP_VARS['calendarId'];
	$jour       = date("d", $sd);
	$mois       = date("m", $sd);
	$annee      = date("Y", $sd);
	$tabMois2   = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
	$lienP      = mktime(12, 0, 0, $mois, $jour, $annee - 1);
	$lienS      = mktime(12, 0, 0, $mois, $jour, $annee + 1);



?>
<div class="padding_more ededed border4" style="text-align:left;height: 100%;overflow:hidden;" id="dynlistMois">
	<div class="    blanc  relative applink applinkblock toggler flex_h flex_wrap  flex_align_stretch " style="text-align:left;padding:0px;height: 100%;overflow:hidden;">
		<?
			for ($m = 1; $m <= 12; $m++) {
				$lien = mktime(12, 0, 0, $m, $jour, $annee);
				($mois == $m) ? $class = 'active' : $class = '';
				?>
				<div class="aligncenter select_month padding_more  edededhover flex_h flex_align_middle" style="width:33%;line-height: 3;" data-vars="<?= http_build_query(['sd' => $lien]) ?>">
					<div class="text-center applink applinkblock padding_more flex_main">
						<a class="ellipsis <?= $class ?> autoToggle"> <?= $tabMois2[$m] ?> </a>
					</div>
				</div>
			<? } ?>
	</div>
</div>