<?php

	namespace Idae\Rest;

	use Idae\Connect\IdaeConnect;
	use Idae\Data\Scheme\Model\IdaeDataSchemeModel;

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 07/06/2018
	 * Time: 22:12
	 */
	class IdaeDataRest extends IdaeConnect {

		private $appscheme_code = null; // there can be more than one
		private $appscheme_model = null;
		private $query_vars = [];

		public function __construct() {
			// get the HTTP method, path and body of the request
			echo $method = $_SERVER['REQUEST_METHOD'];
			$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
			$input   = json_decode(file_get_contents('php://input'), true);
		}

		public function sort_page_query($table, $REQUEST) {

			$groupBy       = empty($REQUEST['groupBy']) ? '' : $REQUEST['groupBy'];
			$sortBy        = empty($REQUEST['sortBy']) ? empty($APP_TABLE['sortFieldName']) ? $nom : $APP_TABLE['sortFieldName'] : $REQUEST['sortBy'];
			$sortDir       = empty($REQUEST['sortDir']) ? empty($APP_TABLE['sortFieldOrder']) ? 1 : (int)$APP_TABLE['sortFieldOrder'] : (int)$REQUEST['sortDir'];
			$sortBySecond  = empty($REQUEST['sortBySecond']) ? empty($APP_TABLE['sortFieldSecondName']) ? $nom : $APP_TABLE['sortFieldSecondName'] : $REQUEST['sortBySecond'];
			$sortDirSecond = empty($REQUEST['sortDirSecond']) ? empty($APP_TABLE['sortFieldSecondOrder']) ? 1 : (int)$APP_TABLE['sortFieldSecondOrder'] : (int)$REQUEST['sortDirSecond'];

			$page   = (!isset($REQUEST['page'])) ? 0 : $REQUEST['page'];
			$nbRows = (empty($REQUEST['nbRows'])) ? empty($settings_nbRows) ? 500 : (int)$settings_nbRows : $REQUEST['nbRows'];
		}

		public function parse($REQUEST) {
			$REQUEST = \function_prod::cleanPostMongo($REQUEST, true);

			if (!empty($REQUEST['table'])) {
				$this->appscheme_code  = $REQUEST['table'];
				$this->appscheme_model = new IdaeDataSchemeModel ($REQUEST['table']);

			}

			foreach ($REQUEST as $key_request => $value_vars):

				switch ($key_request) {
					case 'vars':
						foreach ($value_vars as $key_vars => $value):
							$this->get_action_vars($key_vars, $value);
						endforeach;
						break;
					case 'search':
						$this->get_action_search($value_vars);

						break;
					case '$value_vars':
						$this->get_action_search_start($value_vars);

						break;
					case 'vars_in':
						foreach ($value_vars as $key_vars_in => $value_vars_in):
							$value_vars['$in']              = json_decode($value_vars_in['$in']);
							$this->query_vars[$key_vars_in] = $value_vars_in;
						endforeach;
						break;
				}

			endforeach;
			$this->build_query();
		}

		/**
		 * build final query, adding sort or group operators
		 */
		private function build_query() {
			//Helper::dump($this->query_vars);
			\Helper::dump($this->query_vars);
			if (!empty($this->appscheme_code)) {
				$table = $this->appscheme_code;
				$APP   = new App($table);
				$rs    = $APP->find($this->query_vars);
				$arr   = iterator_to_array($rs);
				\Helper::dump($arr);

			}

		}

		private function get_action_vars($key_vars, $value_vars) {
			if (!is_array($value_vars)) {
				$this->query_vars[$key_vars] = $value_vars;
			} else {
				foreach ($value_vars as $key_vars_index => $value):
					switch ($key_vars_index) {
						case 'gte':
						case 'lte':
						case 'ne':
							$this->query_vars[$key_vars]['$' . $key_vars_index] = is_int($value) ? (int)$value : $value;
							break;
						default:
							if (is_array($value_vars)) $this->query_vars[$key_vars] = ['$in' => array_values($value_vars)];
							break;
					}

				endforeach;
			}
		}

		private function get_action_search($search_str) {

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

		public function get_action_search_start($search_str) {
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

		public function get_action_vasr_search() {
			if (!empty($vars_search)) { // vars_search est un array, avec des noms de table
				foreach ($vars_search as $key_field => $field_value):
					if (empty($field_value)) continue;
					$regexp         = new MongoRegex("/" . $field_value . "/i");
					$where['$or'][] = [$key_field => $regexp];
				endforeach;
			}
		}

		public function get_action_vasr_search_fk($vars_search_fk) {
			if (!empty($vars_search_fk)) { // vars_search est un array, avec des noms de table
				$vars_search_fk = array_filter($vars_search_fk);

				foreach ($vars_search_fk as $table_key => $val_search):
					if (empty($val_search)) continue;
					$testid = substr(trim($table_key), 0, 2);;
					if ($testid == 'id') {
						$table_key = substr($table_key, -strlen($table_key) + 2);

						if (is_array($val_search)) {
							$vars['id' . $table_key] = ['$in' => $val_search];
						} else {
							$vars['id' . $table_key] = (int)$val_search;
						}
						continue;
					}

					$where_fk = [];
					// => on devrait tourner dans tout les champs de la table vars_search
					$APPKEY           = new App($table_key);
					$APP_TABLE_SCHEME = $APPKEY->get_field_list();

					// tourne ds fields
					foreach ($APP_TABLE_SCHEME as $key_field => $field_scheme):
						$tmp_name          = $field_scheme['field_name'];
						$regexp            = new MongoRegex("/" . $val_search . "/i");
						$where_fk['$or'][] = [$tmp_name => $regexp];
					endforeach;
					// query distinct id
					$rs                      = $APPKEY->distinct($table_key, $where_fk, 200, 'no_full');
					$vars['id' . $table_key] = ['$in' => array_values($rs)];
				endforeach;
				if (droit('DEV') && $DEBUG) {
					skelMdl::send_cmd('act_notify', ['msg'     => '$where_fk <pre>' . json_encode($where_fk, JSON_PRETTY_PRINT) . '</pre>',
					                                 'options' => ['sticky' => 0]], session_id());
				}
			}
		}

		public function get_action_vasr_search_rfk() {
			if (!empty($vars_search_rfk)) { // vars_search est un array, avec des noms de table

				foreach ($vars_search_rfk as $table_key => $val_search):
					$where_rfk = [];
					$testid    = substr(trim($table_key), 0, 2);;
					if ($testid == 'id') $table_key = substr($table_key, -strlen($table_key) + 2);
					// => on devrait tourner dans tout les champs de la table vars_search
					$APPKEY           = new App($table_key);
					$APP_TABLE_SCHEME = $APPKEY->get_display_fields($table_key);

					// tourne ds fields
					if ($testid == 'id'):
						$where_rfk['id' . $table_key] = (int)$val_search;
					endif;
					foreach ($APP_TABLE_SCHEME as $key_field => $field_scheme):
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
