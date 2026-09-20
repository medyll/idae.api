<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 19/06/2018
	 * Time: 21:51
	 */
	/**
	 * The queue's timing settings, and the widgets that render them.
	 *
	 * Load a set with one of the `get_times_*` methods, narrow it with
	 * set_credentials(), then render it or write it back with update().
	 */
	class CommandeQueueTime {

		private $current     = [];
		private $credentials = [];

		/**
		 * Loads the global timing settings.
		 *
		 * @return array
		 */
		public function get_times_config() {

			$this->current = CommandeQueueTimeFabric::get_times_config();

			return $this;
		}

		/**
		 * Loads the timing settings for a zone's couriers.
		 *
		 * @param int $idsecteur
		 * @return array
		 */
		public function get_times_secteur_livreur($idsecteur) {


			$this->current = CommandeQueueTimeFabric::get_times_secteur_livreur($idsecteur);

			return $this;
		}

		/**
		 * Loads the timing settings for a shop.
		 *
		 * @param int $idshop
		 * @return array
		 */
		public function get_times_shop($idshop) {

			$this->current = CommandeQueueTimeFabric::get_times_shop($idshop);

			return $this;
		}

		/**
		 * @param $idsecteur
		 *
		 * @return \CommandeQueueTime $this
		 */
		public function get_times_secteur($idsecteur) {


			$this->current = CommandeQueueTimeFabric::get_times_secteur($idsecteur);

			return $this;
		}

		/**
		 * Writes the loaded settings back, narrowed by the current credentials.
		 *
		 * @return mixed
		 */
		public function update() {

			$credential_out = $this->get_credentialsSelector();

			$arr_cmd = [];

			foreach ($this->current as $index => $item) {
				$selector       = "[data-wait_debug=$index]$credential_out";
				$selector_value = $item;

				array_push($arr_cmd, [$selector, $selector_value]);
			}
			SendCmd::insert_selectors($arr_cmd);
		}

		/**
		 * Sets the credentials the loaded settings are read and written under.
		 *
		 * @param array $credentials
		 * @return void
		 */
		public function set_credentials($credentials = []) {

			$this->credentials = $credentials;

			return $this;
		}

		/**
		 * Renders the settings as an HTML form.
		 *
		 * @return string HTML
		 */
		public function get_templateHTML() {

			$credential_out = $this->get_credentialsATTR();

			$a = '';
			foreach ($this->current as $index => $item) {
				$a .= "<span class='padding'>$index</span><span class='padding' $credential_out  data-wait_debug='$index'>$item</span><br>";
			}

			return $a;
		}

		/**
		 * Renders the settings as HTML, one object per setting rather than one form.
		 *
		 * @return string HTML
		 */
		public function get_templateObjHTML() {

			$credential_out = $this->get_credentialsATTR();

			$a = [];
			foreach ($this->current as $index => $item) {
				$a[$index]  = "<span class='padding' $credential_out  data-wait_debug='$index'>$item</span>";
			}

			return (object)$a;
		}

		/**
		 * The settings as plain data, for a caller that renders them itself.
		 *
		 * @return array
		 */
		public function get_templateObj () {

			$a = [];
			foreach ($this->current as $index => $item) {
				$a[$index]  = $item;
			}

			return (object)$a;
		}

		private function get_credentialsATTR() {
			$credential_out = [];
			foreach ($this->credentials as $index => $credential) {
				$credential_out[]  = "data-$index='$credential'";
			}

			return implode(' ',$credential_out);
		}

		private function get_credentialsSelector() {
			$credential_out = [];
			foreach ($this->credentials as $index => $credential) {
				$credential_out[]  = "[data-$index='$credential']";
			}

			return implode('',$credential_out);
		}
	}

