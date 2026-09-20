<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 07/09/2015
	 * Time: 19:43
	 */
	/**
	 * A PHP session handler backed by MongoDB, so sessions survive across the web
	 * front ends.
	 *
	 * Register it with session_set_save_handler(); PHP then calls the methods below.
	 * Expired sessions are swept on every read rather than on PHP's own schedule.
	 *
	 * Note: the constructor connects with a host and password written into this
	 * file rather than read from the configuration.
	 */
	class Session {
		protected $dbSession;
		protected $maxTime;

		/**
		 * Opens the session collection.
		 *
		 * The connection string here is hardcoded, credentials included, and ignores
		 * the MDB_* configuration the rest of the code uses.
		 */
		public function __construct() {
			$opt = ['db' => 'admin', 'username' => MDB_USER, 'password' => MDB_PASSWORD];

			$this->conn   = new MongoClient('mongodb://admin:gwetme2011@127.0.0.1',$opt);

			$sitebase_app = DEFINED(MDB_PREFIX) ? 'sitebase_session' : MDB_PREFIX . 'sitebase_session';
			if(ENVIRONEMENT=='PREPROD') $sitebase_app .='_preprod';
			if(ENVIRONEMENT=='PREPROD_LAN') $sitebase_app .='_preprod';

			$this->dbSession = $this->conn->$sitebase_app->session;
			$this->maxTime   = 3600;


			$this->dbSession->ensureIndex(['timeStamp' => 1]);
			$this->dbSession->ensureIndex(['timeStamp' => -1]);
		}

		/**
		 * Session open handler. Nothing to do: the connection is already open.
		 *
		 * @return bool Always true
		 */
		public function open() { return true; }

		/**
		 * Session close handler. Nothing to do.
		 *
		 * @return bool Always true
		 */
		public function close() { return true; }

		/**
		 * Reads a session's data, sweeping the expired ones first.
		 *
		 * @param string $id
		 * @return string Serialized session data
		 */
		public function read($id) {
			$this->gc();
			$doc = $this->dbSession->findOne(["_id" => $id], ["sessionData" => 1]);

			return $doc['sessionData'];
		}

		/**
		 * Deletes every session last touched more than `maxTime` seconds ago.
		 *
		 * @return void
		 */
		public function gc() {
			$lastAccessed = time() - $this->maxTime;
			$this->dbSession->remove(["timeStamp" => ['$lt' => $lastAccessed]]);
		}

		/**
		 * Writes a session's data, with the referrer and environment alongside it.
		 *
		 * @param string $id
		 * @param string $data Serialized session data
		 * @return bool False for an empty id
		 */
		public function write($id, $data) {
			//
			if(empty($id)) return false;
			$set = ["sessionData" => $data, "timeStamp" => time(),'date_heure'=>date('d-m-Y H:i:s', time() - $this->maxTime),'referrer'=>$_SERVER['HTTP_REFERER'],'nodebug'=>true,'MDB_PREFIX'=>MDB_PREFIX,'ENVIRONEMENT'=>ENVIRONEMENT ];
			$this->dbSession->update(["_id" => $id],['$set'=>$set],['upsert'=>true]);

			return true;
		}

		/**
		 * Deletes one session.
		 *
		 * @param string $id
		 * @return bool Always true
		 */
		public function destroy($id) {
			$this->dbSession->remove(["_id" => $id]);

			return true;
		}
	}


	// session_set_save_handler();
	// ini_set('session.save_handler', 'user');
