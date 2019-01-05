<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 31/08/2015
	 * Time: 09:57
	 */

	include_once($_SERVER['CONF_INC']);

	$APP          = new App();
	$field        = empty($_POST['field']) ? [] : $_POST['field'];
	$vars         = empty($_POST['vars']) ? [] : fonctionsProduction::cleanPostMongo($_POST['vars']);
	$add_field    = empty($_POST['add_field']) ? '' : $_POST['add_field'];
	$module_value = empty($_POST['module_value']) ? '*' : $_POST['module_value'];
	$display_mode = empty($_POST['display_mode']) ? 'horiz' : $_POST['display_mode'];
	$HTTP_VARS    = $APP->translate_vars($vars);
	if (empty($_POST['run'])):
		?>
		<? if ($display_mode == 'vert') { ?>
		<div class="padding">
			<?= idioma('Choisir') ?>
		</div>
	<? } ?>
		<table class="table_form">
			<tr>
				<? if ($display_mode == 'horiz') { ?>
					<td>Ajouter <i class="fa fa-plus"></i></td>
				<? } ?>
				<td class="">
					<div class="fauxInput inputMedium">
						<?
							foreach ($_POST['field'] as $key => $value):

								?>
								<a onclick="reloadModule('app/app_field_add','<?= $module_value ?>','<?= http_build_query($_POST) ?>&add_field=<?= $value ?>&run=1')"><?= $value ?></a>&nbsp;
								<?

							endforeach;
						?></div>
				</td>
			</tr>
		</table>
		<?
	else:
		unset($_POST['run']);
		?>
		<? if ($display_mode == 'vert') { ?>
		<div class="padding">
			<?= ucfirst($add_field) ?>
		</div>
	<? } ?>
		<table class="table_form" id="loaded_<?= $add_field ?>">
			<tr>
				<? if ($display_mode == 'horiz') { ?>
					<td>
						<?= ucfirst($add_field) ?>
					</td>
				<? } ?>
				<td>
					<input required placeholder="<?= $add_field ?>" datalist_input_name="vars[<?= 'id' . $add_field ?>]" datalist="app/app_select" populate name="vars[<?= 'nom' . ucfirst($add_field) ?>]" paramName="search"
					       vars="table=<?= $add_field ?>&<?= $HTTP_VARS ?>" type="text" class="inputMedium"/>
				</td>
				<td class="applink textrouge borderl">
					<a onclick="reloadModule('app/app_field_add','<?= $module_value ?>','<?= http_build_query($_POST) ?>&run=0')"><i class="fa fa-times-circle-o"></i></a>
				</td>
			</tr>
		</table>
		<script>
			$('loaded_<?= $add_field ?>').fire('dom:act_click')
		</script>
		<?
	endif;