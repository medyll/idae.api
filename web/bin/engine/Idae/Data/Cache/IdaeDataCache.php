<?php

	namespace Idae\Data\Cache;

	use Predis;

	/**
	 * Cache facade for query results. The client is wired up, but the three access
	 * methods are still empty: nothing is cached yet.
	 */
	class IdaeDataCache {

		private $options;
		private $client;

		/**
		 * IdaeDataCache constructor.
		 *
		 * @param $options
		 */
		public function __construct($options = []) {


			$this->options = array_merge([
				                             'scheme'             => 'tcp',
				                             'host'               => '127.0.0.1',
				                             'port'               => 6379,
				                             'read_write_timeout' => 0,
				                             'prefix' => 'data:'
			                             ], $options);

			$this->client = new Predis\Client($options);


		}

		/**
		 * Not implemented.
		 *
		 * @param string $cache_keys
		 * @param array  $data
		 * @return void
		 */
		public function set(string $cache_keys, array $data) {

		}

		/**
		 * Not implemented; always returns null.
		 *
		 * @param string $cache_keys
		 * @return null
		 */
		public function get(string $cache_keys) {

		}

		/**
		 * Not implemented.
		 *
		 * @param string $cache_keys
		 * @return void
		 */
		public function delete(string $cache_keys) {

		}
	}
