<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 05/07/2018
	 * Time: 23:53
	 */

	class IdaeDataSchemeConsolidate extends IdaeDataDB {

		/**
		 * IdaeDataSchemeConsolidate constructor.
		 *
		 * @param $appscheme_code
		 *
		 * @throws \Exception
		 */
		public function __construct($appscheme_code) {

			parent::__construct($appscheme_code);
		}

		public function consolidate_scheme($table_value = '') {

			// table sur laquelle on bosse
			$name_id    = $this->appscheme_nameid;
			$name_table = $this->appscheme_code;
			$Name_table = ucfirst($name_table);
			$GRILLE_FK  = $this->get_grille_fk();

			$col      = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme']);
			$arr_vars = (empty($table_value)) ? [] : [$name_id => $table_value];
			$rs       = $this->find($arr_vars);
			//
			while ($arr = $rs->getNext()):
				$arr_new  = [];
				$value_id = (int)$arr[$name_id];
				// DATES debut et fin => t(ime)
				foreach ([null,
				          'Creation',
				          'Debut',
				          'Fin'] as $k => $TYPE_DATE) {
					$suffix_field = $TYPE_DATE . $Name_table;
					$to_time      = strtotime($arr['date' . $suffix_field]);
					if (!empty($arr['date' . $suffix_field])):
						if (($arr['time' . $suffix_field]) != $to_time) {
							$arr_new['time' . $suffix_field]    = $to_time;
							$arr_new['isoDate' . $suffix_field] = new MongoDate($to_time);
						}
					endif;
				}
				//

				if (!empty($arr['color' . $Name_table]) && empty($arr['bgcolor' . $Name_table]) && $arr['color' . $Name_table] != $arr['bgcolor' . $Name_table]):

				endif;

				if ($this->has_field('slug')) {
					if (empty($arr['slug' . $Name_table]) && !empty($arr['nom' . $Name_table])) {
						$arr_new['slug' . $Name_table] = format_uri($arr['nom' . $Name_table]);
					}
				}
				// FKS : prendre nom et code => appscheme_has_table_field

				foreach ($GRILLE_FK as $field):
					$id_fk = $field['idtable_fk'];
					if (empty($arr[$id_fk])) continue;
					$arr_lnk   = $this->appscheme_model_instance->findOne(['codeAppscheme' => $field['table_fk']]);
					$rs_fields = $this->appscheme_has_field_model_instance->find(['idappscheme'      => $this->idappscheme,
					                                                              'idappscheme_link' => (int)$arr_lnk['idappscheme']]);


					$arrq = $this->plug($field['base_fk'], $field['table_fk'])->findOne([$id_fk => (int)$arr[$id_fk]]);
					if (empty($arrq)) continue;
					foreach ($rs_fields as $arr_fields) {
						if ($arr[$arr_fields['codeAppscheme_field'] . ucfirst($field['table_fk'])] == $arrq[$arr_fields['codeAppscheme_field'] . ucfirst($field['table_fk'])]) continue;
						$arr_new[$arr_fields['codeAppscheme_field'] . ucfirst($field['table_fk'])] = $arrq[$arr_fields['codeAppscheme_field'] . ucfirst($field['table_fk'])];
					}
					//
					foreach ($this->app_default_fields_add as $pfield_name) {
						$full_field_name = $pfield_name . ucfirst($field['table_fk']);
						if (array_key_exists($full_field_name, $arrq)) {
							if ($arr[$full_field_name] !== $arrq[$full_field_name]) {
								$arr_new[$full_field_name] = $arrq[$full_field_name];
							}
						}
					}

				endforeach;

				// TYPE STATUT
				$arr_has = ['statut',
				            'type',
				            'categorie',
				            'groupe','group'];
				foreach ($arr_has as $key => $statut_type):
					$Statut_type        = ucfirst($statut_type);
					$name_table_type    = $name_table . '_' . $statut_type;
					$Name_table_type    = ucfirst($name_table_type);
					$name_table_type_id = 'id' . $name_table_type;
					$_nom               = 'nom' . $name_table_type;
					//
					if (!empty($this->app_table_one['has' . $Statut_type . 'Scheme']) && !empty($arr[$name_table_type_id])):
						$APP_TYPE = new App($name_table_type);
						$arr_tmp  = $APP_TYPE->findOne([$name_table_type_id => (int)$arr[$name_table_type_id]]);

						foreach ($this->app_default_fields_add as $pfield_name) {
							if (array_key_exists($pfield_name . $Name_table_type, $arr_tmp)) {
								if ($arr_tmp[$pfield_name . $Name_table_type] !== $arr[$pfield_name . $Name_table_type]) {
									$arr_new[$pfield_name . $Name_table_type] = $arr_tmp[$pfield_name . $Name_table_type];
								}
							}
						}

					endif;

				endforeach;

				if ($name_table == 'contrat'):
					if (!empty($arr['idclient'])):
						$APP_TMP  = new App('client');
						$rs_test  = $col->find(['idclient' => (int)$arr['idclient']])->sort(['dateFinContrat' => 1]);
						$arr_test = $rs_test->getNext();
						$arr_cl   = $APP_TMP->findOne(['idclient' => (int)$arr['idclient']]);

						if ($arr_cl['dateFinClient'] != $arr_test['dateFinContrat']) $APP_TMP->update(['idclient' => (int)$arr['idclient']], ['dateFinClient' => $arr_test['dateFinContrat']]);
					endif;
				endif;
				if ($name_table == 'conge'):
					$new_value = $arr['nomAgent'] . ' ' . strtolower($arr['codeConge_type']) . ' ' . fonctionsProduction::moisDate_fr($arr['dateDebutConge']);
					if ($arr['nom' . $Name_table] != $new_value) {
						$arr_new['nom' . $Name_table] = $new_value;
					}
				endif;
				if ($name_table == 'ressource'):
					$new_value = $arr['quantite' . $Name_table] . ' * ' . $arr['nomProduit'] . ' - ' . $arr['nomProspect'] . $arr['nomClient'];
					if ($arr['nom' . $Name_table] != $new_value) {
						$arr_new['nom' . $Name_table] = $new_value;
					};
				endif;
				if ($name_table == 'opportunite_ligne'):
					$new_value = $arr['quantite' . $Name_table] . ' * ' . $arr['nomProduit'];
					if ($arr['nom' . $Name_table] != $new_value) {
						$arr_new['nom' . $Name_table] = $new_value;
					};
				endif;

				if ($name_table == 'commande'):
					$new_value = date('dmy') . auto_code($arr['codeClient']) . auto_code($arr['codeShop']);
					if ($arr['code' . $Name_table] != $new_value) {
						// $arr_new['code' . $Name_table] = $new_value;
					};
				endif;

				if (empty($arr['code' . $Name_table]) && empty($arr_new['code' . $Name_table])):
					$arr_new['code' . $Name_table] = auto_code($arr['nom' . $Name_table]);
				endif;
				if (empty($arr['nom' . $Name_table]) && empty($arr_new['nom' . $Name_table])):
					foreach ($GRILLE_FK as $field):
						$id_fk = $field['idtable_fk'];
						if (empty($arr[$id_fk])) continue;
						//
						$dsp_name = $arr['nom' . ucfirst($field['table_fk'])];
						//
						$arr_new['nom' . $Name_table] .= strtolower($dsp_name) . ' ';
					endforeach;
				endif;
				if (empty($arr['nom' . $Name_table]) && empty($arr_new['nom' . $Name_table])):
					if (!empty($APP_TABLE['hasTypeScheme'])):
						//	$arr_new['nom' . $Name_table] = $arr['nom' . $Name_table.'_type'];
					endif;
					// $arr_new['nom' . $Name_table] .= substr(strip_tags($arr['code' . $Name_table]),0,5)  ;
					if (!empty($APP_TABLE['hasDateScheme']) && !empty($arr['dateDebut' . $Name_table])):
						$arr_new['nom' . $Name_table] .= ' ' . date_fr($arr['dateDebut' . $Name_table]);
					endif;
					// $arr_new['nom' . $Name_table] .= substr(strip_tags($arr['description' . $Name_table]),0,10);
				endif;
				if (sizeof($arr_new) != 0) {
					$arr_new ['updated_fields'] = $arr_new; // log ???
					// versioning
					$arr_new['dateModification' . $Name_table]  = date('Y-m-d');
					$arr_new['heureModification' . $Name_table] = date('H:i:s');
					$arr_new['timeModification' . $Name_table]  = time();

					$col->update([$name_id => $value_id], ['$set' => $arr_new], ['upsert' => true]);
				}
				if ($rs->count() == 1) {
					return $arr_new;
				}

			endwhile;
		}
	}