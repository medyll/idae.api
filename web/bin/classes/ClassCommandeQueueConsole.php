<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 19/06/2018
	 * Time: 21:51
	 * @property \CommandeQueueTime $CommandeQueueTime
	 */
	class CommandeQueueConsole {

		private $CommandeQueueTime;

		public function __construct() {


		}

		static public function consoleShopSite($idshop) {

			$DB        = new IdaeDB('shop');
			$RS        = $DB->findOne(['idshop' => $idshop]);
			$idsecteur = $RS['idsecteur'];

			$credentials              = ['idsecteur' => $idsecteur, 'idshop' => $idshop, 'soncole' => 'site'];
			$CommandeQueueTime        = new CommandeQueueTime();
			$data['console_shop']     = $CommandeQueueTime->set_credentials($credentials)->get_times_shop($idshop);
			$CommandeQueueTime        = new CommandeQueueTime();
			$data ['console_secteur_livreur'] = $CommandeQueueTime->set_credentials($credentials)->get_times_secteur_livreur($idsecteur);



			return (object)$data;
		}

		static public function consoleShop($idshop) {

			$DB        = new IdaeDB('shop');
			$RS        = $DB->findOne(['idshop' => $idshop]);
			$idsecteur = $RS['idsecteur'];

			$data                            = [];
			$credentials                     = ['idsecteur' => $idsecteur, 'idshop' => $idshop];
			$CommandeQueueTime               = new CommandeQueueTime();
			$data['console_secteur_livreur'] = $CommandeQueueTime->set_credentials($credentials)->get_times_secteur_livreur($idsecteur);
			$CommandeQueueTime               = new CommandeQueueTime();
			$data ['console_secteur']        = $CommandeQueueTime->set_credentials($credentials)->get_times_secteur($idsecteur);
			$CommandeQueueTime               = new CommandeQueueTime();
			$data['console_shop']            = $CommandeQueueTime->set_credentials($credentials)->get_times_shop($idshop);

			return (object)$data;
		}

		static private function array_flatten($arr) {
			$arr = array_values($arr);
			while (list($k, $v) = each($arr)) {
				if (is_array($v)) {
					array_splice($arr, $k, 1, $v);
					next($arr);
				}
			}

			return $arr;
		}

		static public function consoleLivreur($idlivreur) {

			$DB        = new IdaeDB('livreur');
			$RS        = $DB->findOne(['idlivreur' => (int)$idlivreur]);
			$idsecteur = (int)$RS['idsecteur'];

			$data                            = [];
			$CommandeQueueTime               = new CommandeQueueTime();
			$credentials                     = ['idsecteur' => $idsecteur, 'idlivreur' => $idlivreur];
			$data['console_secteur_livreur'] = $CommandeQueueTime->set_credentials($credentials)->get_times_secteur_livreur($idsecteur);
			$CommandeQueueTime               = new CommandeQueueTime();
			$data ['console_secteur']        = $CommandeQueueTime->set_credentials($credentials)->get_times_secteur($idsecteur);

			return (object)$data;
		}

		/**
		 * @return $this|object \CommandeQueueTime[]
		 */
		static public function get_times_config() {


			$SELF     = new IdaeDB('secteur');
			$ARR_SELF = $SELF->findOne(['slugSecteur' => 'saint-malo']);

			$idsecteur                         = $ARR_SELF['idsecteur'];
			$data                              = [];
			$CommandeQueueTime                 = new CommandeQueueTime();
			$data ['get_times_secteur']        = $CommandeQueueTime->set_credentials(['idsecteur' => $idsecteur])->get_times_secteur($idsecteur);
			$CommandeQueueTime                 = new CommandeQueueTime();
			$data['get_times_secteur_livreur'] = $CommandeQueueTime->set_credentials(['idsecteur' => $idsecteur])->get_times_secteur_livreur($idsecteur);

			return (object)$data;
		}

	}

