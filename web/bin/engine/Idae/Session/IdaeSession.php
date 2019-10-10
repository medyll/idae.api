<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 16/06/2018
	 * Time: 01:30
	 */
	namespace Idae\Session;


	class IdaeSession {

		private static $_instance = null;

		public $type_session;
		public $type_session_value;
		public $idtype_session;
		public $name_idtype_session;
		public $private_key;

		public function __construct() {

			if (!empty($_SESSION['type_session'])) {
				$type_session        = $_SESSION["type_session"];
				$idtype_session      = $_SESSION["idtype_session"];
				$name_idtype_session = "id$type_session";

				$SELF     = new IdaeDB($_SESSION['type_session']);
				$ARR_SELF = $SELF->findOne([$name_idtype_session => $idtype_session]);

				$this->setSession($_SESSION['type_session'], $ARR_SELF);
			}

		}

		private function killSession() {
			unset($_SESSION["client"], $_SESSION["livreur"], $_SESSION["shop"]);
		}

		public function get_session() {

			return (object)get_object_vars($this);
		}

		/**
		 * @param       $type_session
		 * @param array $session_data
		 *
		 * @return bool|object
		 */
		public function setSession($type_session, $session_data = []) {

			try {
				if (empty($type_session) || empty($session_data)) {
					throw new Exception('Session vide', 'EMPTYSESSION', true);
				}
			} catch (Exception $e) {
				echo 'Exception reçue : ', $e->getMessage(), "\n";

				return false;
			};

			//$this->killSession();

			$type_session_identity = $type_session . '_identity';
			$Type_session          = ucfirst($type_session);

			$this->type_session           = $type_session;
			$this->name_idtype_session    = 'id' . $this->type_session;
			$this->type_session_value     = (int)$session_data[$this->name_idtype_session];
			$this->idtype_session         = $this->type_session_value;
			$this->private_key            = $session_data['private_key'];
			$this->$type_session          = $session_data['private_key'];
			$this->$type_session_identity = $session_data["prenom$Type_session"] . ' ' . $session_data["nom$Type_session"];

			$_SESSION["type_session"]             = $this->type_session;
			$_SESSION["idtype_session"]           = (int)$this->type_session_value;
			$_SESSION[$this->name_idtype_session] = (int)$this->type_session_value;
			$_SESSION[$type_session]              = $this->$type_session;
			$_SESSION[$type_session_identity]     = $this->$type_session_identity;

			return $this->get_session();
		}

		public static function getInstance() {

			if (is_null(self::$_instance)) {
				self::$_instance = new IdaeSession();
			}

			return self::$_instance;
		}

	}
