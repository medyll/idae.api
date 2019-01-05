<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$arr_allowed_c = droit_table($type_session, 'C', $table);
	$arr_allowed_r = droit_table($type_session, 'R', $table);
	$arr_allowed_u = droit_table($type_session, 'U', $table);
	$arr_allowed_d = droit_table($type_session, 'D', $table);
	$arr_allowed_l = droit_table($type_session, 'L', $table);

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
	$ARR_COLLECT = $Idae->get_table_fields($table_value);

	function draw_it($codeAppscheme_field, $ARR, $table = 'commande') {
		$APP   = new App($table);
	     $Table = ucfirst($table);

		$value = $ARR[$codeAppscheme_field . $Table]?: $ARR[$codeAppscheme_field];
		return $APP->draw_field(['field_name_raw' => $codeAppscheme_field,
		                         'table'          => $table,
		                         'field_value'    => $value]);
	}

	$APP_HAS_FIELD       = new App('appscheme_has_field');
	$APP_HAS_TABLE_FIELD = new App('appscheme_has_table_field');
	$APP_LIGNE           = new App('commande_ligne');

	$rs_ligne = $APP_LIGNE->find(["id$table"=>$table_value]);
	$arr_ligne      = iterator_to_array($rs_ligne);

	$RS_HAS_TABLE_FIELD  = $APP_HAS_TABLE_FIELD->find(['idappscheme' => (int)$APP_LIGNE->idappscheme])->sort(['ordreAppscheme_has_table_field' => 1]);
	$ARR_HAS_TABLE_FIELD = iterator_to_array($RS_HAS_TABLE_FIELD);

	$fields = $ARR_HAS_TABLE_FIELD;
	$liste  = $arr_ligne;

?>
<div>
	<div style="">
		<?= $Idae->module('fiche_entete', ['table'       => $table,
		                                   'table_value' => $table_value]) ?>
	</div>
	<div style="padding">

	</div>
	<div>
		<div class="">
			<div style="padding:1em;background-color: #fff">
				<div class="padding margin border4 blanc">
					<table style="width: 100%;">
						<tr>
							<td style="width:70%;">
								<table>
									<tr>
										<td style="width:100px;">Code</td>
										<td style="font-weight: bold"><?= draw_it('code', $ARR); ?></td>
									</tr>
									<tr>
										<td>Date</td>
										<td><?= draw_it('dateCreation', $ARR); ?></td>
									</tr>
									<tr>
										<td>Heure</td>
										<td><?= draw_it('heure', $ARR); ?></td>
									</tr>
								</table>
							</td>
							<td>
								<table>
									<tr>
										<td style="width:100px;">Nom</td>
										<td><?= draw_it('nom', $ARR); ?></td>
									</tr>
									<tr>
										<td>Email</td>
										<td><?= draw_it('email', $ARR); ?></td>
									</tr>
									<tr>
										<td>Adresse</td>
										<td><?= draw_it('adresse', $ARR); ?><?= draw_it('adresse2', $ARR); ?><br><?= draw_it('codePostal', $ARR); ?><?= draw_it('ville', $ARR); ?></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
				<br>
				<br>
				<br>
				<? if (!empty($APPOBJ->APP_TABLE['hasLigneScheme'])): ?>
					<table class="hover unstriped" style="width: 100%;">
						<thead  >
							<tr>
								<? foreach ($fields as $key => $field) { ?>
									<td  style="font-weight: bold;padding:1em;border-bottom:1px solid #ccc;">
										<i class="fa fa-<?= $field['iconAppscheme_field'] ?> "></i>
										<?= $field['nomAppscheme_field'] ?>
									</td>
								<? } ?>
							</tr>
						</thead>
						<tbody>
							<? foreach ($liste as $key_liste => $row) {
								$id = 'id' . $table ?>
								<tr data-table="<?= $table ?>" data-table_value="<?= $row[$id] ?>" class="cursor">
									<? foreach ($fields as $key => $field) {
										?>
										<td  style="padding:0.5em;border-bottom:1px solid #ededed;">
											<?= draw_it($field['codeAppscheme_has_table_field'], $row, 'commande_ligne'); ?>
										</td>
									<? } ?>
								</tr>
							<? } ?>
						</tbody>
					</table>
				<? endif; ?>
				<div class="padding margin border4 blanc">
					<?= $Idae->module('fiche_rfk', ['table'       => $table,
					                                'table_value' => $table_value]) ?>
				</div>
			</div>
			<div class="padding margin border4 blanc"><?= $Idae->module('app_fiche_fk/app_fiche_fk', ['mode' => 'fiche', 'table' => $table, 'table_value' => $table_value]) ?></div>
		</div>
	</div>
</div>