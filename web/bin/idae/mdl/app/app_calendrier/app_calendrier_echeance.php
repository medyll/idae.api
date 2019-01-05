<?
	include_once($_SERVER['CONF_INC']);
	// generalement , le nom du container

	$table = empty($_POST['table']) ? 'tache' : $_POST['table'];
	$Table = ucfirst($table);
	if (!empty($_POST['date'])) {
		$sd = $_POST['sd'] = strtotime(date_mysql($_POST['date']));
	}
	unset($_POST['date']);
	if (empty($_POST['sd'])) {
		$sd = $_POST['sd'] = time();
	} else {
		$sd = $_POST['sd'];
	};

	$APP = new App($table);
?>
<div style="width:750px;">
	<div class="titre_entete applink alignright">
		<a onclick="<?= fonctionsJs::app_create('tache') ?>"><i class="fa fa-<?= $APP->iconAppscheme ?>"></i> <?= idioma('Nouvelle tache') ?></a>
	</div>
	<div class="flex_h" style="width:100%;">
		<div class="frmCol1" style="overflow:hidden;">
			<div data-app_calendrier class="flex_v padding" style="margin:0 auto;overflow:hidden;height:450px;padding:0;width:100%;max-width:250px;">
				<div data-nav_cal><?= skelMdl::cf_module('app/app_calendrier/calendrier_nav', ['sd' => $sd]); ?></div>
				<div id="cal_echeance" data-nav_zone class="flex_main"><?= skelMdl::cf_module('app/app_calendrier/calendrier_day', ['sd' => $sd]); ?></div>
			</div>
		</div>
		<div class="frmCol2" id="cal_list_echeance" data-dsp_liste="true" data-vars="nbRows=10&table=<?= $table ?>&vars[idagent]=<?= $_SESSION['idagent'] ?>&vars[dateDebut<?= $Table ?>]=<?= date('Y-m-d') ?>" data-dsp="mdl" data-dsp-mdl="app/app/app_fiche_mini_full" style="max-height:450px;overflow:auto;">
		</div>
	</div>
	<div class="buttonZone">
		<input type="button" value="Fermer" class="cancelButton">
	</div>
</div>
<script>
	// load_table_in_zone ('nbRows=10&table=<?=$table?>&vars[idagent]=<?=$_SESSION['idagent']?>&vars[dateDebut<?=$Table?>]=<?=date('Y-m-d')?>', 'cal_list_echeance');
	$ ('cal_echeance').observe ('dom:act_click', function (event) {
		load_table_in_zone ('nbRows=10&table=<?=$table?>&vars[idagent]=<?=$_SESSION['idagent']?>&vars[dateDebut<?=$Table?>]=' + event.memo.value_us, 'cal_list_echeance');

	})
</script>