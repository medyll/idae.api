<?php

	/**
	 * todo as a module we can add to root, it as a default template dir, but we can add options
	 * eg classFiche = new IdaeView('fiche');
	 */
	class IdaeView {

		static public function create($table, $type_session = null) {

			$Session      = IdaeSession::getInstance();
			$type_session = $type_session ?: $Session->type_session;

			$options = ['apply_droit'               => [$type_session,
			                                            'C'],
			            'data_mode'                 => 'fiche',
			            'scheme_field_view'         => 'native',
			            'field_draw_style'          => 'draw_html_input',
			            'scheme_field_view_groupby' => 'group',
			            'fields_scheme_part'        => 'all',
			            'field_composition'         => ['hide_field_icon'  => 1,
			                                            'hide_field_name'  => 1,
			                                            'hide_field_value' => 1]];

			$Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);
			$Fabric->init_app_fields();
			$tplData = $Fabric->get_templateData();

			$parameters['scheme_field_native']        = $tplData['scheme_field_native'];
			$parameters['scheme_field_fk_grouped']    = $tplData['scheme_field_fk_grouped'];
			$parameters['scheme_field_fk_nongrouped'] = $tplData['scheme_field_fk_nongrouped'];

		}

		static public function fiche($table, $table_value, $type_session = null) {

			$Session      = IdaeSession::getInstance();
			$type_session = $type_session ?: $Session->type_session;

			$Idae       = Idae::getInstance($table);
			$IdaeDataDB = IdaeDataDB::getInstance($table);

			$ARR = $IdaeDataDB->findOne(["id$table" => (int)$table_value]);

			$options = ['apply_droit'               => [$type_session,
			                                            'R'],
			            'data_mode'                 => 'fiche',
			            'scheme_field_view'         => 'native',
			            'field_draw_style'          => 'draw_html_field',
			            'scheme_field_view_groupby' => 'group',
			            'fields_scheme_part'        => 'all',
			            'field_composition'         => ['hide_field_icon'  => 1,
			                                            'hide_field_name'  => 1,
			                                            'hide_field_value' => 1]];

			$Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);

			$Fabric->fetch_query($ARR, 'findOne');
			$tplData = $Fabric->get_templateDataHTML();

			$parameters['scheme_field_native']        = $tplData['scheme_field_native'];
			$parameters['scheme_field_fk_grouped']    = $tplData['scheme_field_fk_grouped'];
			$parameters['scheme_field_fk_nongrouped'] = $tplData['scheme_field_fk_nongrouped'];
			$parameters['scheme_field_rfk']           = $tplData['scheme_field_rfk'];

			$parameters['fiche_entete'] = $Idae->module('fiche_entete', ['table'       => $table,
			                                                             'table_value' => $table_value]);
			$parameters['fiche_ligne']  = $Idae->module('app_fiche/fiche_ligne', ['table' => $table . '_ligne',
			                                                                      'vars'  => ["id$table" => $table_value]]);

		}

		static public function update($table, $table_value, $type_session = null) {

			$Session      = IdaeSession::getInstance();
			$type_session = $type_session ?: $Session->type_session;

			$Idae       = Idae::getInstance($table);
			$IdaeDataDB = IdaeDataDB::getInstance($table);

			$ARR = $IdaeDataDB->findOne(["id$table" => (int)$table_value]);

			$options = ['apply_droit'               => [$type_session,
			                                            'U'],
			            'data_mode'                 => 'fiche',
			            'scheme_field_view'         => 'native',
			            'field_draw_style'          => 'draw_html_input',
			            'scheme_field_view_groupby' => 'group',
			            'fields_scheme_part'        => 'all',
			            'hide_field_empty'          => 0,
			            'hide_field_icon'           => 0,
			            'hide_field_name'           => 0,
			            'hide_field_value'          => 0,
			            'field_composition'         => ['hide_field_icon'  => 0,
			                                            'hide_field_name'  => 0,
			                                            'hide_field_value' => 0]];

			$Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);
			$Fabric->fetch_query($ARR, 'findOne');
			$tplData = $Fabric->get_templateData();

			$parameters['scheme_field_native']        = $tplData['scheme_field_native'];
			$parameters['scheme_field_fk_grouped']    = $tplData['scheme_field_fk_grouped'];
			$parameters['scheme_field_fk_nongrouped'] = $tplData['scheme_field_fk_nongrouped'];
			$parameters['scheme_field_image']         = $tplData['scheme_field_image'];

			$parameters['fiche_entete'] = $Idae->module('fiche_entete', ['table'       => $table,
			                                                             'table_value' => $table_value]);
		}

		public function liste($table, $query_vars = [], $type_session = null) {

			$Session      = IdaeSession::getInstance();
			$type_session = $type_session ?: $Session->type_session;

			$options = ['apply_droit'        => [$type_session,
			                                     'R'],
			            'data_mode'          => 'liste',
			            'scheme_field_view'  => 'mini',
			            'field_draw_style'   => 'draw_html_field',
			            'fields_scheme_part' => 'main',
			            'hide_field_empty'   => 1,
			            'hide_field_icon'    => 1,
			            'hide_field_name'    => 1,
			            'hide_field_value'   => 1,
			            'field_composition'  => ['hide_field_icon'  => 1,
			                                     'hide_field_name'  => 1,
			                                     'hide_field_value' => 1]];

			$app = new IdaeDataDB($table);
			$arr = $app->findOne([]);

			$Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);
			$Fabric->fetch_data([]);
			$tplData = $Fabric->get_templateData();

			$out = '';
			foreach ($tplData as $index => $row) {
				$out .= $row['scheme_field_short'];
			}

			return $out;
		}

		static public function delete($table, $table_value) {

			$Session      = IdaeSession::getInstance();
			$type_session = $Session->type_session;

			$Idae       = Idae::getInstance($table);
			$IdaeDataDB = IdaeDataDB::getInstance($table);

			$ARR = $IdaeDataDB->findOne(["id$table" => (int)$table_value]);

			$options = ['apply_droit'               => [$type_session,
			                                            'R'],
			            'data_mode'                 => 'fiche',
			            'scheme_field_view'         => 'native',
			            'field_draw_style'          => 'draw_html_field',
			            'scheme_field_view_groupby' => 'group',
			            'fields_scheme_part'        => 'all',
			            'field_composition'         => ['hide_field_icon'  => 1,
			                                            'hide_field_name'  => 1,
			                                            'hide_field_value' => 1]];

			$Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);

			$Fabric->fetch_query($ARR, 'findOne');
			$tplData = $Fabric->get_templateDataHTML();

			$parameters['scheme_field_native']        = $tplData['scheme_field_native'];
			$parameters['scheme_field_fk_grouped']    = $tplData['scheme_field_fk_grouped'];
			$parameters['scheme_field_fk_nongrouped'] = $tplData['scheme_field_fk_nongrouped'];
			$parameters['scheme_field_rfk']           = $tplData['scheme_field_rfk'];

			$parameters['fiche_entete'] = $Idae->module('fiche_entete', ['table'       => $table,
			                                                             'table_value' => $table_value]);
			$parameters['fiche_ligne']  = $Idae->module('app_fiche/fiche_ligne', ['table' => $table . '_ligne',
			                                                                      'vars'  => ["id$table" => $table_value]]);
		}
	}