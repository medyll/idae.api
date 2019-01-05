<?
	include_once($_SERVER['CONF_INC']);
	$sd         = $_POST['sd'];
	$calendarId = $_POST['calendarId'];
	$jour       = date("d", $sd);
	$mois       = date("m", $sd);
	$annee      = date("Y", $sd);
	$i          = 0;
	$lienP      = gmmktime(12, 0, 0, $mois, $jour, $annee - 8);
	$lienS      = gmmktime(12, 0, 0, $mois, $jour, $annee + 6);
?>
<div class="padding_more ededed border4" style="text-align:left;padding:0px;height: 100%;width:100%;overflow:hidden;" id="dynlistYear">
	<div style="height:100%;width:100%; " class="blanc applink toggler applinkblock flex_h flex_wrap flex_align_stretch">
		<?
			for ($m = $annee - 1; $m <= $annee + 7; $m++) {
				$lien = gmmktime(12, 0, 0, $mois, $jour, $m);
				// $class =($annee == date("Y", $sd)) ? 'active' :  '';

				?>
				<div class="text-center <?= $class ?> select_year flex_h flex_align_middle edededhover padding_more" data-vars="<?= http_build_query(['sd' => $lien]) ?>" style="width:33%;">
					<div class="flex_main padding_more">
						<a class="autoToggle ellipsis">
							<?= $m ?>
						</a>
					</div>
				</div>
			<? } ?>
	</div>
</div>
