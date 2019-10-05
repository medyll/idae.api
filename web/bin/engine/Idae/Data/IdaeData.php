<?php

	namespace Idae\Data;

	use Idae\Data\Db\IdaeDataDb;
	use Idae\Connect\IdaeConnect;

	class IdaeData {

		private $IdaeConnect;

		/**
		 * IdaeData constructor.
		 */
		public function __construct() {

			$this->IdaeConnect = new IdaeConnect();
		}

		public function getSchemeList(){

		return 	$this->IdaeConnect->appscheme_model_instance->find();

		}
	}
