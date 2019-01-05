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
<div class="flex_h flex_align_middle" style="z-index:200;">
	<div class="flex_main   data-table="<?= $table ?>" data-table_value="<?= $table_value ?>" style="overflow:hidden;position:sticky;top:0;z-index:200;">
		<div class="fiche_<?= $table ?> flex_h  flex_align_middle borderb boxshadowb">
			<div class="text-center  ">
				<div class="padding_more"><i class="fa fa-<?= $APPOBJ->ICON ?> fa-2x" style="color:<?= $APPOBJ->ICON_COLOR ?>"></i></div>
			</div>
			<div class="flex_main text-center">
				<div class="inline padding_more relative text-center search_new_<?= $table ?>">
					<h3 class=" "><?= $APP->nomAppscheme ?></h3>
					<div class="flex_h flex_align_middle "><? //= $Idae->liste_titre($_POST); ?></div>
				</div>
			</div>
			<div class="  flex_h  flex_align_middle   ">
				<div class="flex_h   flex_align_middle boxshadowr text-bold" style="font-size: 1.7em;color:#666;text-shadow: 0 0 4px  #ccc">
					<? if ($count_true != $count_false) {
						?>
					<div class="  flex_h  flex_align_middle padding_more  ">
						<div><?= $count_true ?></div>
						<div><i class="fa fa-minus fa-rotate-90"></i> </div>
						<div><?= $count_false ?></div>
					</div>
						<?
					} else { ?>
						<div class="padding_more"><?= $count_true ?></div>
					<? } ?>
				</div>
			</div>
			<div style="" class="padding_more cursor  " onclick="$('#main_item_search_input').toggleContent();">
				<i class="fa fa-search fa-2x" style="color:#666;text-shadow: 0 0 4px  #ccc"></i>
			</div>
		</div>
	</div>
</div>
<style>
	.search_new_<?=$table?> {
		border-bottom : 2px solid <?= $APPOBJ->ICON_COLOR ?>;
	}
	.search_<?=$table?> {
		border-bottom: 2px solid <?= $APPOBJ->ICON_COLOR ?>;
		background : <?= $white_COLOR ?>;
		background : -moz-linear-gradient(-45deg, <?= $white_COLOR ?> 60%, <?= $APPOBJ->ICON_COLOR ?> 100%);
		background : -webkit-linear-gradient(-45deg, <?= $white_COLOR ?> 60%,<?= $APPOBJ->ICON_COLOR ?> 100%);
		background : linear-gradient(135deg, <?= $white_COLOR ?> 60%,<?= $APPOBJ->ICON_COLOR ?> 100%);
		filter     : progid:DXImageTransform.Microsoft.gradient(startColorstr='<?= $white_COLOR ?>', endColorstr='<?= $APPOBJ->ICON_COLOR ?>', GradientType=1);
	}
</style>