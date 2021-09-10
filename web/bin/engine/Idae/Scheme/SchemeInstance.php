<?php

	namespace Idae\Scheme;

	use Idae\Connect\IdaeConnect;
	use Idae\IdaeConstants;

	use MongoDB\Collection;
	use function is_null;

	class SchemeInstance    {

		/**
		 * @var \Idae\Scheme\SchemeInstance
		 */
		private static $_instance;

		private $appscheme_db_instance;
		private $appscheme_base_db_instance;
		private $appscheme_field_group_db_instance;
		private $appscheme_field_type_db_instance;
		private $appscheme_field_db_instance;
		private $appscheme_has_field_db_instance;
		private $appscheme_has_table_field_db_instance;
		/**
		 * @var \Idae\Connect\IdaeConnect|null
		 */
		private $conn;

		/**
		 * SchemeInstance constructor.
		 */
		public function __construct() {
			$this->conn = IdaeConnect::getInstance();
			$this->makeInstances();
		}

		public static function getInstance() {

			if (is_null(self::$_instance)) {
				self::$_instance = new SchemeInstance();
			}

			return self::$_instance;
		}

		private function makeInstances() {

			$this->appscheme_db_instance                 = $this->conn->selectMongoCollection(IdaeConstants::appscheme_model_name);
			$this->appscheme_base_db_instance            = $this->conn->selectMongoCollection(IdaeConstants::appscheme_base_model_name);
			$this->appscheme_field_db_instance           = $this->conn->selectMongoCollection(IdaeConstants::appscheme_field_model_name);
			$this->appscheme_field_group_db_instance     = $this->conn->selectMongoCollection(IdaeConstants::appscheme_field_group_model_name);
			$this->appscheme_field_type_db_instance      = $this->conn->selectMongoCollection(IdaeConstants::appscheme_field_type_model_name);
			$this->appscheme_has_field_db_instance       = $this->conn->selectMongoCollection(IdaeConstants::appscheme_has_field_model_name);
			$this->appscheme_has_table_field_db_instance = $this->conn->selectMongoCollection(IdaeConstants::appscheme_has_table_field_model_name);
		}

		public function __get($name) {
			// TODO: Implement __get() method.
			return $this->$name;
		}

		/**
		 * @return \MongoCollection
		 */
		public function getAppschemeModelInstance(): Collection {
			return $this->appscheme_db_instance;
		}

		/**
		 * @return \MongoCollection
		 */
		public function getAppschemeBaseModelInstance(): Collection {
			return $this->appscheme_base_db_instance;
		}

		/**
		 * @return \MongoCollection
		 */
		public function getAppschemeFieldModelInstance(): Collection {
			return $this->appscheme_field_db_instance;
		}

		/**
		 * @return \MongoCollection
		 */
		public function getAppschemeFieldGroupModelInstance(): Collection {
			return $this->appscheme_field_group_db_instance;
		}

		/**
		 * @return \MongoCollection
		 */
		public function getAppschemeFieldTypeModelInstance(): Collection {
			return $this->appscheme_field_type_db_instance;
		}

		/**
		 * @return \MongoCollection
		 */
		public function getAppschemeHasFieldModelInstance(): Collection {
			return $this->appscheme_has_field_db_instance;
		}

		/**
		 * @return \MongoCollection
		 */
		public function getAppschemeHasTableFieldModelInstance(): Collection {
			return $this->appscheme_has_table_field_db_instance;
		}

	}
