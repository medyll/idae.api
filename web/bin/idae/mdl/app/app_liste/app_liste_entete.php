<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$table = $_POST['table'];
	$Table = ucfirst($table);

	$table_value = (int)$_POST['table_value'];
	$vars        = empty($_POST['vars']) ? [] : function_prod::cleanPostMongo($_POST['vars'], 1);
	$groupBy     = empty($_POST['groupBy']) ? '' : $_POST['groupBy'];
	$page        = (!isset($_POST['page'])) ? 0 : $_POST['page'];
	$nbRows      = (empty($_POST['nbRows'])) ? empty($settings_nbRows) ? 10 : (int)$settings_nbRows : $_POST['nbRows'];
	//
	$APP    = new App($table);
	$Idae   = new Idae($table);
	$APPOBJ = $APP->appobj($table_value, $vars);

	$rs_app = $APP->find($vars)->skip(($nbRows * $page))->limit($nbRows);

	$count_false = $rs_app->count(false);
	$count_true  = $rs_app->count(true);
	//
	$white_COLOR = '#ffffff';

?>
<div class="fiche_<?= $table ?>" data-table="<?= $table ?>" data-table_value="<?= $table_value ?>" style="overflow:hidden;position:sticky;top:0;z-index:200;">
	<div class="fiche_<?= $table ?> flex_h flex_padding flex_align_middle borderb boxshadowb">
		<div class="text-center small-4">
			<div class="padding_more"><i class="fa fa-<?= $APPOBJ->ICON ?> fa-2x" style="color:<?= $APPOBJ->ICON_COLOR ?>"></i></div>
		</div>
		<div class="flex_main">
			<div class="padding_more relative">
				<h3><?= $APP->nomAppscheme ?></h3>

				<div class="flex_h flex_align_middle flex_padding"><? //= $Idae->liste_titre($_POST); ?></div>
			</div>
		</div>
		<div class="  flex_h flex_padding_more">
			<? if ($count_true != $count_false) {
				?>
				<div><?= $count_true ?></div>
				<div><?= $count_false ?></div>
				<?
			} else { ?>
				<div><?= $count_true ?></div>
			<? } ?>
		</div>
	</div>
</div>
<style>
	.fiche_<?=$table?> {
		background : <?= $white_COLOR ?>;
		background : -moz-linear-gradient(-45deg, <?= $white_COLOR ?> 60%, <?= $APPOBJ->ICON_COLOR ?> 100%);
		background : -webkit-linear-gradient(-45deg, <?= $white_COLOR ?> 60%,<?= $APPOBJ->ICON_COLOR ?> 100%);
		background : linear-gradient(135deg, <?= $white_COLOR ?> 60%,<?= $APPOBJ->ICON_COLOR ?> 100%);
		filter     : progid:DXImageTransform.Microsoft.gradient(startColorstr='<?= $white_COLOR ?>', endColorstr='<?= $APPOBJ->ICON_COLOR ?>', GradientType=1);
	}
</style>