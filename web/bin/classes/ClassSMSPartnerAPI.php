<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 10/05/2018
	 * Time: 15:42
	 */
	class SMSPartnerAPI {
		const BASE_URL = 'http://api.smspartner.fr/v1/';
		public $debug;

		public function __construct($debug = false) {
			$this->setDebug($debug);
		}

		public function setDebug($debug) {
			$this->debug = $debug;
		}

		public function getDebug() {
			return $this->debug;
		}

		public function checkCredits($params) {
			if (empty($params))
				return false;

			$result = $this->_postRequest(self::BASE_URL . 'me' . $params);

			return $this->returnJson($result);
		}

		public function checkStatusByNumber($params) {
			if (empty($params))
				return false;

			$result = $this->_postRequest(self::BASE_URL . 'message-status' . $params);

			return $this->returnJson($result);
		}

		public function sendSms($fields) {
			if (empty($fields))
				return false;

			$result = $this->_postRequest(self::BASE_URL . 'send', $fields);

			return $this->returnJson($result);
		}

		/**
		 * RequÃªte cURL - Vous n'Ãªtes pas sensÃ© appeler cette mÃ©thode
		 * @access private
		 *
		 */
		private function _postRequest($url, $fields = []) {
			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			if (!empty($fields)) {
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
			}

			$result = curl_exec($curl);
			if ($result === false) {
				if ($this->debug)
					echo curl_error($curl);
				else
					$result = curl_error($curl);
			} else
				curl_close($curl);

			return $result;
		}

		private function returnJson($string) {
			$json_array = json_encode($string);
			if (is_null($json_array))
				return $string;
			else
				return $json_array;
		}
	}

