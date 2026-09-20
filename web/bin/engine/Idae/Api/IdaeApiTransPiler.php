<?php

namespace Idae\Api;

use Idae\Api\IdaeApiOperatorMongoDbPhp;
use Idae\Query\IdaeQuery;

use function array_filter;
use function array_map;
use function array_values;
use function cleanTel;
use function droit;
use function explode;
use function is_array;
use function is_int;
use function iterator_to_array;
use function json_encode;
use function method_exists;
use function session_id;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function substr_replace;
use function trim;
use function ucfirst;
use function var_dump;
use const JSON_PRETTY_PRINT;

/**
 * Builds the MongoDB `$or` clauses behind the app's search boxes.
 *
 * Unfinished: the methods below read `$this->appscheme_model` and
 * `$this->query_vars`, which this class never declares or sets, and several of
 * them build a local `$where` that is then dropped. Nothing calls them today.
 */
class IdaeApiTransPiler
{



	private function get_action_search($search_str)
	{

		if (!empty($search_str)) {
			$regexp = new MongoRegex("/" . $search_str . "/i");

			if ($this->appscheme_model->has_field('nom')) $this->query_vars['$or'][] = ['nom' . $Table => $regexp];
			if ($this->appscheme_model->has_field('prenom')) $this->query_vars['$or'][] = ['prenom' . $Table => $regexp];
			if ($this->appscheme_model->has_field('email')) $this->query_vars['$or'][] = ['email' . $Table => $regexp];
			if ($this->appscheme_model->has_field('code')) $this->query_vars['$or'][] = ['code' . $Table => $regexp];
			if ($this->appscheme_model->has_field('reference')) $this->query_vars['$or'][] = ['reference' . $Table => $regexp];
			if ($this->appscheme_model->has_field('telephone')) $this->query_vars['$or'][] = ['telephone' . $Table => new MongoRegex("/" . cleanTel($search_str) . "/i")];

			// tourne ds fk
			/*if (sizeof($GRILLE_FK) != 0) {
					foreach ($GRILLE_FK as $field):
						$code_fk = 'nom' . ucfirst($field['codeAppscheme']);
						$nom_fk  = 'nom' . ucfirst($field['nomAppscheme']);
						//$regexp         = new MongoRegex("/" . $nom_fk . "/i");
						$where['$or'][] = [$code_fk => $regexp];
						$where['$or'][] = [$nom_fk => $regexp];
					endforeach;
				}*/
		}
	}

	/**
	 * Search anchored at the start of a field, across the scheme's name, surname,
	 * email, code, reference and phone fields.
	 *
	 * @param string $search_str
	 * @return void
	 */
	public function get_action_search_start($search_str)
	{
		if (!empty($search_str)) {
			$regexp = new MongoRegex("/" . $search_str . "./i");

			if ($this->appscheme_model->has_field('nom')) $this->query_vars['$or'][] = ['nom' . $Table => $regexp];
			if ($this->appscheme_model->has_field('prenom')) $this->query_vars['$or'][] = ['prenom' . $Table => $regexp];
			if ($this->appscheme_model->has_field('email')) $this->query_vars['$or'][] = ['email' . $Table => $regexp];
			if ($this->appscheme_model->has_field('code')) $this->query_vars['$or'][] = ['code' . $Table => $regexp];
			if ($this->appscheme_model->has_field('reference')) $this->query_vars['$or'][] = ['reference' . $Table => $regexp];
			if ($this->appscheme_model->has_field('telephone')) $this->query_vars['$or'][] = ['telephone' . $Table => new MongoRegex("/" . cleanTel($search_str) . "./i")];
		}
	}

	/**
	 * Per-field search. Incomplete: reads a `$vars_search` that is never set.
	 *
	 * @return void
	 */
	public function get_action_vasr_search()
	{
		if (!empty($vars_search)) { // vars_search est un array, avec des noms de table
			foreach ($vars_search as $key_field => $field_value) :
				if (empty($field_value)) continue;
				$regexp         = new MongoRegex("/" . $field_value . "/i");
				$where['$or'][] = [$key_field => $regexp];
			endforeach;
		}
	}

	/**
	 * Search across a foreign key: resolves the matching ids in the related
	 * collection first, then filters on them with `$in`.
	 *
	 * A key starting with `id` is treated as a direct id filter and not resolved.
	 *
	 * @param array $vars_search_fk Search term per related table
	 * @return void
	 */
	public function get_action_vasr_search_fk($vars_search_fk)
	{
		if (!empty($vars_search_fk)) { // vars_search est un array, avec des noms de table
			$vars_search_fk = array_filter($vars_search_fk);

			foreach ($vars_search_fk as $table_key => $val_search) :
				if (empty($val_search)) continue;
				$testid = substr(trim($table_key), 0, 2);;
				if ($testid == 'id') {
					$table_key = substr($table_key, -strlen($table_key) + 2);

					if (is_array($val_search)) {
						$vars['id' . $table_key] = ['$in' => $val_search];
					} else {
						$vars['id' . $table_key] = (int) $val_search;
					}
					continue;
				}

				$where_fk = [];
				// => on devrait tourner dans tout les champs de la table vars_search
				$APPKEY           = new App($table_key);
				$APP_TABLE_SCHEME = $APPKEY->get_field_list();

				// tourne ds fields
				foreach ($APP_TABLE_SCHEME as $key_field => $field_scheme) :
					$tmp_name          = $field_scheme['field_name'];
					$regexp            = new MongoRegex("/" . $val_search . "/i");
					$where_fk['$or'][] = [$tmp_name => $regexp];
				endforeach;
				// query distinct id
				$rs                      = $APPKEY->distinct($table_key, $where_fk, 200, 'no_full');
				$vars['id' . $table_key] = ['$in' => array_values($rs)];
			endforeach;
			if (droit('DEV') && $DEBUG) {
				skelMdl::send_cmd('act_notify', [
					'msg'     => '$where_fk <pre>' . json_encode($where_fk, JSON_PRETTY_PRINT) . '</pre>',
					'options' => ['sticky' => 0]
				], session_id());
			}
		}
	}

	/**
	 * Reverse of get_action_vasr_search_fk(), searching the display fields of the
	 * related collection. Incomplete: reads a `$vars_search_rfk` that is never set.
	 *
	 * @return void
	 */
	public function get_action_vasr_search_rfk()
	{
		if (!empty($vars_search_rfk)) { // vars_search est un array, avec des noms de table

			foreach ($vars_search_rfk as $table_key => $val_search) :
				$where_rfk = [];
				$testid    = substr(trim($table_key), 0, 2);;
				if ($testid == 'id') $table_key = substr($table_key, -strlen($table_key) + 2);
				// => on devrait tourner dans tout les champs de la table vars_search
				$APPKEY           = new App($table_key);
				$APP_TABLE_SCHEME = $APPKEY->get_display_fields($table_key);

				// tourne ds fields
				if ($testid == 'id') :
					$where_rfk['id' . $table_key] = (int) $val_search;
				endif;
				foreach ($APP_TABLE_SCHEME as $key_field => $field_scheme) :
					if ($testid == 'id') continue;
					$tmp_name           = $field_scheme['nomAppscheme_field'];
					$tmp_name_raw       = $field_scheme['codeAppscheme_field'];
					$regexp             = new MongoRegex("/" . $val_search . "/i");
					$where_rfk['$or'][] = [$tmp_name => $regexp];
					if ($tmp_name_raw == 'adresse') {
						$where_rfk['$or'][] = ['adresse1' . ucfirst($table_key) => $regexp];
						$where_rfk['$or'][] = ['codePostal' . ucfirst($table_key) => $regexp];
						$where_rfk['$or'][] = ['ville' . ucfirst($table_key) => $regexp];
					}
				endforeach;
				// query distinct id

				$rs = $APPKEY->distinct($table, $where_rfk, 200, 'no_full');

				$vars['id' . $table] = ['$in' => array_values($rs)];

			endforeach;
		}
	}
}
