<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 07/09/2015
	 * Time: 19:43
	 */
	class Session {
		protected $dbSession;
		protected $maxTime;

		public function __construct() {
			$opt = ['db' => 'admin', 'username' => MDB_USER, 'password' => MDB_PASSWORD];

			$this->conn   = new MongoClient('mongodb://admin:******@127.0.0.1',$opt);

			$sitebase_app = DEFINED(MDB_PREFIX) ? 'sitebase_session' : MDB_PREFIX . 'sitebase_session';
			if(ENVIRONEMENT=='PREPROD') $sitebase_app .='_preprod';
			if(ENVIRONEMENT=='PREPROD_LAN') $sitebase_app .='_preprod';

			$this->dbSession = $this->conn->$sitebase_app->session;
			$this->maxTime   = 3600;


			$this->dbSession->ensureIndex(['timeStamp' => 1]);
			$this->dbSession->ensureIndex(['timeStamp' => -1]);
		}

		public function open() { return true; }

		public function close() { return true; }

		public function read($id) {
			$this->gc();
			$doc = $this->dbSession->findOne(["_id" => $id], ["sessionData" => 1]);

			return $doc['sessionData'];
		}

		public function gc() {
			$lastAccessed = time() - $this->maxTime;
			$this->dbSession->remove(["timeStamp" => ['$lt' => $lastAccessed]]);
		}

		public function write($id, $data) {
			//
			if(empty($id)) return false;
			$set = ["sessionData" => $data, "timeStamp" => time(),'date_heure'=>date('d-m-Y H:i:s', time() - $this->maxTime),'referrer'=>$_SERVER['HTTP_REFERER'],'nodebug'=>true,'MDB_PREFIX'=>MDB_PREFIX,'ENVIRONEMENT'=>ENVIRONEMENT ];
			$this->dbSession->update(["_id" => $id],['$set'=>$set],['upsert'=>true]);

			return true;
		}

		public function destroy($id) {
			$this->dbSession->remove(["_id" => $id]);

			return true;
		}
	}



	ini_set('session.save_handler', 'user');
