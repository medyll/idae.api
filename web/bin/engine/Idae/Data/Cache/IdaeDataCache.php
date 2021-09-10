<?php

	namespace Idae\Data\Cache;

	use Predis;

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

		public function set(string $cache_keys, array $data) {

		}

		public function get(string $cache_keys) {

		}

		public function delete(string $cache_keys) {

		}
	}
