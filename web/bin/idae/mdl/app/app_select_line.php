<?
	include_once($_SERVER['CONF_INC']);

	$vars             = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	$vars             = array_filter($vars);
	$table            = $this->HTTP_VARS['table'];
	$table_value      = empty($this->HTTP_VARS['table_value']) ? null : (int)$this->HTTP_VARS['table_value'];
	$table_from       = $this->HTTP_VARS['table_from'];
	$table_value_from = empty($this->HTTP_VARS['table_value_from']) ? null : (int)$this->HTTP_VARS['table_value_from'];

	$arr_allowed_r = droit_table($type_session, 'R', $table);
	
	$id     = 'id' . $this->HTTP_VARS['table'];
	$Table  = ucfirst($table);
	$nom    = 'nom' . ucfirst($table);
	$prenom = 'prenom' . ucfirst($table);
	$code   = 'code' . ucfirst($table);
	//
	//
	$APP  = new App($table);
	$Idae = new Idae($table_from);
	if (!empty($this->HTTP_VARS['vars_in'])) {
		foreach ($this->HTTP_VARS['vars_in'] as $key_vars => $value_vars):
			$value_vars['$in'] = json_decode($value_vars['$in']);
			$vars[$key_vars]   = $value_vars;
		endforeach;
	}
	//  vardump($vars);
	$HTTP_VARS  = $APP->translate_vars($vars);
	$APP_TABLE  = $APP->app_table_one;
	$ARR_FIELDS = $APP->get_field_list();

	$add = [];

	//
	if (!empty($this->HTTP_VARS['search'])):
		$search    = trim($this->HTTP_VARS['search']);
		$arrSearch = explode(' ', trim($search));
		foreach ($arrSearch as $key => $value) {
			// $out[] = new MongoRegex("/.*" . (string)$arrSearch[$key] . "*./i");
		}
		$out[] = new MongoRegex("/" . (string)$search . "/i");
		if (sizeof($out) == 1) {
			$add = ['$or' => [[$nom => ['$all' => $out]],
			                  [$id => (int)$this->HTTP_VARS['search']],
			                  ['code' . $Table => ['$in' => $out]],
			                  [$prenom => ['$in' => $out]]]];
		}
		if (is_int($this->HTTP_VARS['search'])):
			$add['$or'][] = [$id => (int)$this->HTTP_VARS['search']];
		endif;
		$rs = $APP->find($vars + $add)->sort(['nom' . $Table   => 1,
		                                      'ordre' . $Table => -1])->limit(250);
	// vardump_async(array_merge( $vars , $add),true);
	else:
		$rs = $APP->find($vars)->sort(['nom' . $Table   => 1,
		                               'ordre' . $Table => -1])->limit(250);
	endif;

	/*$options = ['apply_droit'        => [$type_session,
	                                     'U'],
	            'data_mode'          => 'liste',
	            'scheme_field_view'  => 'short',
	            'field_draw_style'   => 'draw_html_field',
	            'fields_scheme_part' => 'all',
	            'field_composition'  => ['hide_field_icon'  => 0,
	                                     'hide_field_name'  => 0,
	                                     'hide_field_value' => 0]];

	$Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);
	$Fabric->fetch_query($rs, 'find');
	$tplData = $Fabric->get_templateDataFields();*/

?>
<div name="vars[<?= $id ?>]" style="max-height:100%;overflow: auto">
    <div class="padding_more boxshadowb">
		<?= $Idae->module('fiche_entete', ['table' => $table]) ?>
    </div>
    <div class="padding more  ">
        <div class="padding_more blanc border4">

			<? while ($arr = $rs->getNext()) { ?>
                <div class="padding_more borderb blanc "
                     value="<?= $arr[$id] ?>" <?= ($table_value == $arr[$id]) ? 'selected' : '' ?>>
                    <div class="flex_h" data-table="<?= $table_from ?>"
                         data-table_value="<?= $table_value_from ?>" data-action="app_update" data-action_reload="true"
                         data-field="vars[<?= $id ?>]" data-value="<?= $arr[$id] ?>">
                        <div class="flex_main cursor">
							<?= $arr[$nom] ?>
							<?= htmlspecialchars(empty($arr[$prenom]) ? '' : ' ' . $arr[$prenom]); ?>
							<? if (array_key_exists('codePostal' . $Table, $ARR_FIELDS) && $arr['codePostal' . $Table]) { ?>
								<?= $arr['codePostal' . $Table] ?><? } ?>
                        </div>
                        <div>
                            <i class="fa fa-caret-right"></i>
                        </div>
                    </div>
                </div>
			<? } ?>

        </div>

    </div>
</div>
