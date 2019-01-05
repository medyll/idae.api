<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 05/01/15
	 * Time: 01:19
	 */

	include_once($_SERVER['CONF_INC']);


	$field_name     = $this->HTTP_VARS['field_name'];
	$field_name_raw = $this->HTTP_VARS['field_name_raw'];
	$table          = $this->HTTP_VARS['table'];
	$Table          = ucfirst($table);
	$table_value    = (int)$this->HTTP_VARS['table_value'];
	$vars           = empty($this->HTTP_VARS['vars']) ? array() : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	$field_name_raw = empty($this->HTTP_VARS['field_name_raw']) ? '' : $this->HTTP_VARS['field_name_raw'];
	$field_name     = empty($this->HTTP_VARS['field_name']) ? $field_name_raw . $Table : $this->HTTP_VARS['field_name'];

	//
	$APP = new App($table);
	//
	$APP_TABLE = $APP->app_table_one;
    //
	$GRILLE_FK = $APP->get_grille_fk($table);
	$HTTP_VARS = $APP->translate_vars($vars);
	$BASE_APP  = $APP_TABLE['base'];
	//
	$id  = 'id' . $table;
	$ARR = $APP->query_one([$id => $table_value]);
	//
	$field_value     = empty($this->HTTP_VARS['field_value']) ? $ARR[$field_name] : $this->HTTP_VARS['field_value'];


?>
<form action="<?= ACTIONMDL ?>app/actions.php"
      onsubmit="ajaxFormValidation(this);return false;" style="max-width:100%;">
	<input type="hidden"
	       name="F_action"
	       value="app_update"/>
	<input type="hidden"
	       name="table"
	       value="<?= $table ?>"/>
	<input type="hidden"
	       name="table_value"
	       value="<?= $table_value ?>"/>
	<input type="hidden"
	       name="scope"
	       value="<?= $id ?>"/>
	<input type="hidden"
	       name="<?= $id ?>"
	       value="<?= $table_value ?>"/>
	<div class="flex_h flex_align_middle">
		<div class=" flex_main">
			<?= $APP->draw_field_input(['table'=>$table,'field_name_raw' => $field_name_raw,'field_value'=>$field_value]); ?>
		</div>
		<div>
			<a  onclick="ajaxFormValidation($(this).parent('form').first())" style="width:20px;border:none;" class="    borderr"><i class="fa fa-check textvert"></i></a>
			<a onclick="$(this).parents('#edit_node').first().remove()" style="width:20px;" class="cancelHide cancelClose   padding  textrouge" ><i class="fa fa-ban"></i></a>
		</div>
	</div>
</form>