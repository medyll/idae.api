<?
	include_once($_SERVER['CONF_INC']);

	global $buildArr;
	global $IMG_SIZE_ARR;

	ini_set('display_errors' , 55);

	$table_value = (int)$_POST['table_value'];
	$table       = $_POST['table'];
	$id          = 'id' . $table;

	$nom  = 'nom' . ucfirst($table);
	$APP  = new App($table);
	$Idae = new Idae($table);
	//
	$APP_TABLE       = $APP->app_table_one;

	$arr = $APP->query_one([ $id => $table_value ]);

?>
<div class="flex_v" style="height:100%;">
	<div>
		<?= $Idae->module('fiche_entete' , [ 'table'       => $table ,
		                                     'table_value' => $table_value ]) ?>
	</div>
	<div class=" flex_main  relative" style="overflow:auto;">
		<div class = "    ">
			<? arsort($IMG_SIZE_ARR);
				foreach ($IMG_SIZE_ARR as $key => $value) {

				if ( empty($APP_TABLE['hasImage' . $key . 'Scheme']) ) {
					continue;
				}
				$vars              = [ 'table'           => $table ,
				                       'table_value'     => $table_value ,
				                       'codeTailleImage' => $key ,
				                       'needResize'      => true ];
				$vars['show_info'] = true;
				?>
                    <div>

	                    <?= $Idae->module('app_img_dyn' , [ 'edit'=>true,'table' => $table , 'table_value' => $table_value , 'codeTailleImage' => $key ]); ?>
                    </div>
			<? } ?>
		</div>
	</div>
	<div class="bordert">
		<?= $Idae->module('app_fiche/app_fiche_menu', ['table'       => $table,
		                                               'table_value' => $table_value]) ?>
	</div>
</div>