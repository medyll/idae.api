<?
	include_once($_SERVER['CONF_INC']);

	$table = $this->HTTP_VARS['table'];
	$Table = ucfirst($table);

	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	$groupBy     = empty($this->HTTP_VARS['groupBy']) ? '' : $this->HTTP_VARS['groupBy'];
	//
	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];
	//
	$APP       = new App($table);
	$Idae      = new Idae($table);
	$APP_TABLE = $APP->app_table_one;

	$crypted_update     = AppLink::update($table, $table_value);
	$crypted_image_link = AppLink::liste_images($table, $table_value);

    $options = ['apply_droit'        => [$type_session,
                                         'R'],
                'data_mode'          => 'fiche',
                'scheme_field_view'  => 'native',
                'field_draw_style'   => 'draw_html_field',
                'scheme_field_view_groupby' => 'group',
                'fields_scheme_part' => 'main',
                'show_only_fields'   => ['nom', 'password', 'mail', 'telephone', 'adresse', 'ville', 'codePostal', 'description','atout'],
                'field_composition'  => ['hide_field_icon'  => 1,
                                         'hide_field_name'  => 1,
                                         'hide_field_value' => 1]];

    $Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);
    $Fabric->fetch_data(["id$table" => $table_value]);
    $tplData = $Fabric->get_templateData();
?>
<div class=" ">
	<div>
		<?= $Idae->module('fiche_entete', ['table'       => $table,
		                                   'table_value' => $table_value]) ?>
	</div>
	<div class="grid-x ">
		<div class="cell small-12 medium-8 large-8">
			<br>
			<br>
			<div class="padding_more ">
                <?= $tplData['scheme_field_native'] ?>
				<?/*= $Idae->module('fiche_fields', ['table'               => $table,
				                                   'table_value'         => $table_value,
				                                   'titre'               => 0,
				                                   'codeAppscheme_field' => ['nom', 'password', 'mail', 'telephone', 'adresse', 'ville', 'codePostal', 'description']]) */?>
			</div>
			<div class="padding_more     ">
				<?= $Idae->module('app_liste_rfk', ['table'       => $table,
				                                    'table_value' => $table_value]) ?>
			</div>
		</div>
		<div class="cell small-12 medium-4 large-4">
			<br>
			<br>
			<br>
			<?
				if (($type_session == 'livreur' && $table == 'livreur') || ($type_session == 'shop' && $table == 'shop')) {
					?>
					<div class="   ">
						<div class="padding     text-center">
							<button class="button button_full" data-crypted_link="<?= $crypted_update ?>">Modifier informations</button>
						</div>
					</div>
				<? } ?>
			<? if (!empty($APP_TABLE['hasImageScheme'])) :
				$query_str = http_build_query($this->HTTP_VARS);

				?>
				<div class="text-center padding">
					<button class="    button button_full text-center cursor" data-crypted_link="<?= $crypted_image_link ?>">
						Voir images :
					</button>
				</div>
			<? endif; ?>
		</div>
	</div>
</div>
