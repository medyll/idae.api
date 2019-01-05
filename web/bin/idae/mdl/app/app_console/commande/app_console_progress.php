<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];


	$table       = $this->HTTP_VARS['table'];
	$Table       = ucfirst($table);
	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	//
	$APP       = new App($table);
	$APP_TABLE = $APP->app_table_one;
	$APPOBJ    = $APP->appobj($table_value, $vars);
	$ARR       = $APP->findOne(["id$table" => (int)$table_value]);

	$Idae        = new Idae($table);
	$BIN         = new Bin();
	$arr_minutes = $BIN->get_elapsed_minutes_arr_for_commande($table_value);


	$start_time     = $arr_minutes['start_time'];
	$to_time        = $arr_minutes['to_time'];
	$max            = $arr_minutes['max'];
	$value_progress = $arr_minutes['value_progress'];

?>
<div class="flex_main relative padding"><?//= $table_value ?>
	<div id="text_animate_step_commande_<?= $table_value ?>" class="text-center"></div>
	<progress data-auto_progress="" id="animate_step_commande_<?= $table_value ?>" value="<?= $value_progress ?>" max="<?= $max ?>" style="width:70%;border:none;height:5px;"></progress>
</div>
<script>
	var interval_commande_<?=$table_value?> = setInterval (function () {
		if ( !$ ('#animate_step_commande_<?=$table_value?>').length ) clearInterval (interval_commande_<?=$table_value?>);
		$ ('#animate_step_commande_<?=$table_value?>').val ($ ('#animate_step_commande_<?=$table_value?>').val () + 1)
	}, 1000);
	$ ('#animate_step_commande_<?=$table_value?>').val ($ ('#animate_step_commande_<?=$table_value?>').val () + 1)
</script>