<?php

	namespace Idae\Connect;

	use Idae\IdaeConstants;

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 23/05/14
	 * Time: 20:26
	 *
	 * @property \MongoCollection $appscheme_model_instance
	 * @property \MongoCollection $appscheme_base_model_instance
	 * @property \MongoCollection $appscheme_field_model_instance
	 * @property \MongoCollection $appscheme_field_group_model_instance
	 * @property \MongoCollection $appscheme_field_type_model_instance
	 * @property \MongoCollection $appscheme_has_field_model_instance
	 * @property \MongoCollection $appscheme_has_table_field_model_instance
	 */
	class IdaeConnect extends  \MongoDB\Client { //}  \MongoClient {

		// const should be in constants file instead of here
		CONST appscheme_model_name = 'appscheme'; // should be in datadb
		CONST appscheme_base_model_name = 'appscheme_base';
		CONST appscheme_field_model_name = 'appscheme_field';
		CONST appscheme_field_group_model_name = 'appscheme_field_group';
		CONST appscheme_field_type_model_name = 'appscheme_field_type';
		CONST appscheme_has_field_model_name = 'appscheme_has_field';
		CONST appscheme_has_table_field_model_name = 'appscheme_has_table_field';

		private static $_instance = null;

		public $appscheme_model_instance;
		public $appscheme_base_model_instance;
		public $appscheme_field_group_model_instance;
		public $appscheme_field_type_model_instance;
		public $appscheme_field_model_instance;
		public $appscheme_has_field_model_instance;
		public $appscheme_has_table_field_model_instance;
		/**
		 * @var \MongoClient $connection
		 */
		public $connection;
		public $connectionOptions;
		public $sitebase_app_name;
		/**
		 * @var \MongoDB $sitebase_app_instance
		 */
		public $sitebase_app_instance;

		/**
		 * todo :  check  parent::__construct(); and return $this as connection object
		 */
		public function __construct() {

			if (!defined('MDB_USER') || !defined('MDB_PASSWORD') || !defined('MDB_HOST')) {
				die ('Constante DB non definie');
			}

			$this->connectionOptions = ['db'       => 'admin',
			                            'username' => MDB_USER,
			                            'password' => MDB_PASSWORD,
			                            'connect'  => true];

			//parent::__construct('mongodb://'. MDB_HOST, $this->connectionOptions);

			$this->connect();
			// $this->connect_php7();
			$this->set_sitebase_app();
			$this->set_scheme_model_instance();

			return $this;
		}

		/**
		 * @return bool|\MongoClient
		 */
		public function connect() {

			try {
				$this->connection = new \MongoClient('mongodb://' . MDB_USER . ':' . MDB_PASSWORD . '@' . MDB_HOST, $this->connectionOptions);
			} catch (Exception $e) {
				echo 'Exception reçue : ', $e->getMessage(), "\n";

				return false;
			}

			return $this->connection;

		}

		public function connect_php7() {
			$this->connection = new \MongoClient('mongodb://tmp/mongodb-27017.sock', $this->connectionOptions);

			return $this->connection;
		}

		public static function getInstance() {

			if (is_null(self::$_instance)) {
				self::$_instance = new IdaeConnect();
			}

			return self::$_instance;
		}

		/**
		 * @param $base
		 * @param $table
		 *
		 * @return MongoCollection|string
		 * @throws Exception
		 */
		public function plug($base, $table) {
			if (empty($table) || empty($base) || !defined('MDB_USER')) {
				return 'choisir une base';
			}

			try {
				$db = $this->plug_base($base);

				return $db->selectCollection($table);
			} catch (Exception $e) {
				echo 'Exception reçue : ', $e->getMessage(), "\n";

				return false;
			}
		}

		/**
		 * @param $base
		 *
		 * @return \MongoDB  string
		 */
		public function plug_base($base) {
			if (empty($base) || !defined('MDB_USER')) {
				return 'choisir une base';
			}

			$base = MDB_PREFIX . $base;

			return $this->connection->selectDB($base);
		}

		public function plug_fs($base) {
			// PREFIX HERE POUR BASE
			$db = $this->plug_base($base);

			return $db->getGridFS();
		}

		private function set_sitebase_app() {

			$sitebase_app                = MDB_PREFIX . 'sitebase_app';
			$this->sitebase_app_name     = $sitebase_app;
			$this->sitebase_app_instance = $this->connection->selectDB($sitebase_app);

		}

		/**
		 * @return array of \MongoCollection
		 */
		private function getSchemeModelInstances() {
			return ['appscheme_model_instance'                 => $this->appscheme_model_instance,
			        'appscheme_base_model_instance'            => $this->appscheme_base_model_instance,
			        'appscheme_field_model_instance'           => $this->appscheme_field_model_instance,
			        'appscheme_field_group_model_instance'     => $this->appscheme_field_group_model_instance,
			        'appscheme_field_type_model_instance'      => $this->appscheme_field_type_model_instance,
			        'appscheme_has_field_model_instance'       => $this->appscheme_has_field_model_instance,
			        'appscheme_has_table_field_model_instance' => $this->appscheme_has_table_field_model_instance,
			];
		}

		private function set_scheme_model_instance() {

			$this->appscheme_model_instance                 = $this->selectMongoCollection(IdaeConstants::appscheme_model_name);
			$this->appscheme_base_model_instance            = $this->selectMongoCollection(IdaeConstants::appscheme_base_model_name);
			$this->appscheme_field_model_instance           = $this->selectMongoCollection(IdaeConstants::appscheme_field_model_name);
			$this->appscheme_field_group_model_instance     = $this->selectMongoCollection(IdaeConstants::appscheme_field_group_model_name);
			$this->appscheme_field_type_model_instance      = $this->selectMongoCollection(IdaeConstants::appscheme_field_type_model_name);
			$this->appscheme_has_field_model_instance       = $this->selectMongoCollection(IdaeConstants::appscheme_has_field_model_name);
			$this->appscheme_has_table_field_model_instance = $this->selectMongoCollection(IdaeConstants::appscheme_has_table_field_model_name);
		}

		/**
		 * @param $instance
		 *
		 * @return bool|\MongoCollection
		 */
		private function selectMongoCollection($instance) {
			try {
				return $this->sitebase_app_instance->selectCollection($instance);
			} catch (\Exception $e) {
				echo 'Exception reçue : ', $e->getMessage(), "\n";

				return false;
			}

		}
	}
