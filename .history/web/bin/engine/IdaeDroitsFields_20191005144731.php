<?php

/**
 * Created by PhpStorm.
 * User: Mydde
 * Date: 04/06/2018
 * Time: 17:20
 */
	
class IdaeDroitsFields extends IdaeDataDB {

	private static $_instance = null;

	private $tableDroitField = '';

	public function __construct() {

		$init = new IdaeDataSchemeInit();
		$init->init_scheme('sitebase_app','appscheme_field_droit', ['fields' => ['nom', 'code']]);
		$init->init_scheme('sitebase_app','appscheme_field_droit_group', ['fields' => ['nom', 'code']]);

		$this->tableDroitField = $this->tableDroitField();


	}

	public static function getInstance() {

		if (is_null(self::$_instance)) {
			self::$_instance = new IdaeDroitsFields();
		}

		return self::$_instance;
	}

	/**
	 * return array of crud codes  for given user ($type session) and $scheme
	 * @param $type_session
	 * @param $scheme
	 * @return array
	 */
	public function droit_session_table($type_session, $scheme) {

		return (array)$this->tableDroitField[$type_session][$scheme];
	}

	/**
	 * return array of scheme with corresponfong crud code for given user ($type_session)
	 * @param $type_session
	 * @param $table
	 * @param string $CrudCode
	 * @return array
	 */
	public function droit_session_table_crud($type_session, $table, $CrudCode = 'R') {

		return (array)$this->tableDroitField[$type_session][$table][$CrudCode];
	}

	/**
	 * return array [allowed,forbidden] of fields with corresponfong crud code for given user ($type_session)
	 * @param $type_session
	 * @param $scheme
	 * @param string $CrudCode
	 * @param $field
	 * @return array
	 */
	public function droit_session_table_crud_field($type_session, $scheme, $CrudCode = 'R', $field) {

		return [(array)$this->tableDroitField[$type_session][$scheme][$CrudCode]['allowed'], (array)$this->tableDroitField[$type_session][$scheme][$CrudCode]['forbidden']];
	}

	/**
	 * return array of allowed fields with corresponding crud code for given user ($type_session)
	 * @param        $type_session
	 * @param        $scheme
	 * @param null $field
	 * @param string $CrudCode
	 * @return array
	 */
	public function droit_session_table_crud_field_allowed($type_session, $scheme, $CrudCode = 'R', $field = null) {

		return $this->droit_session_table_crud_field($type_session, $scheme, $CrudCode)['allowed'];
	}

	/**
	 * @return array
	 */
	private function tableDroitField() {
		return ['agent' => [], 'shop' => $this->shop_data(), 'livreur' => $this->livreur_data(), 'client' => $this->client_data()];
	}

	/**
	 * list of client avoided fields by CRUD rights
	 * @return mixed
	 */
	private function client_data() {

		$arr_tbl['client']['C']['allowed']   = [];
		$arr_tbl['client']['C']['forbidden'] = [];
		$arr_tbl['client']['R']['allowed']   = [];
		$arr_tbl['client']['R']['forbidden'] = [];
		$arr_tbl['client']['U']['allowed']   = [];
		$arr_tbl['client']['U']['forbidden'] = [];

		return $arr_tbl;
	}

	/**
	 * list of livreur avoided fields by CRUD rights
	 * @return mixed
	 */
	private function livreur_data() {

		$arr_tbl['livreur']['C']['allowed']   = [];
		$arr_tbl['livreur']['C']['forbidden'] = [];
		$arr_tbl['livreur']['R']['allowed']   = [];
		$arr_tbl['livreur']['R']['forbidden'] = ['actif','disponible','dateDebut','dateFin'];
		$arr_tbl['livreur']['U']['allowed']   = [];
		$arr_tbl['livreur']['U']['forbidden'] = ['actif','disponible','dateDebut','dateFin','code','login'];

		$arr_tbl['livreur_affectation']['C']['allowed']   = [];
		$arr_tbl['livreur_affectation']['C']['forbidden'] = [];
		$arr_tbl['livreur_affectation']['R']['allowed']   = [];
		$arr_tbl['livreur_affectation']['R']['forbidden'] = [];
		$arr_tbl['livreur_affectation']['U']['allowed']   = [];
		$arr_tbl['livreur_affectation']['U']['forbidden'] = [];

		$arr_tbl['commande']['C']['allowed']   = [];
		$arr_tbl['commande']['C']['forbidden'] = [];
		$arr_tbl['commande']['R']['allowed']   = [];
		$arr_tbl['commande']['R']['forbidden'] = [];
		$arr_tbl['commande']['U']['allowed']   = [];
		$arr_tbl['commande']['U']['forbidden'] = [];

		$arr_tbl['commande_facture']['C']['allowed']   = [];
		$arr_tbl['commande_facture']['C']['forbidden'] = [];
		$arr_tbl['commande_facture']['R']['allowed']   = [];
		$arr_tbl['commande_facture']['R']['forbidden'] = ['partShop','marge'];
		$arr_tbl['commande_facture']['U']['allowed']   = [];
		$arr_tbl['commande_facture']['U']['forbidden'] = [];



		return $arr_tbl;
	}

	/**
	 * list of shop avoided fields by CRUD rights
	 * @return mixed
	 */
	private function shop_data() {

		$arr_tbl['commande']['C']['allowed']   = [];
		$arr_tbl['commande']['C']['forbidden'] = [];
		$arr_tbl['commande']['R']['allowed']   = [];
		$arr_tbl['commande']['R']['forbidden'] = [];
		$arr_tbl['commande']['U']['allowed']   = ['grilleFK' => ['commande_statut']];
		$arr_tbl['commande']['U']['forbidden'] = [];


		$arr_tbl['commande_facture']['C']['allowed']   = [];
		$arr_tbl['commande_facture']['C']['forbidden'] = [];
		$arr_tbl['commande_facture']['R']['allowed']   = [];
		$arr_tbl['commande_facture']['R']['forbidden'] = ['partLivreur','marge'];
		$arr_tbl['commande_facture']['U']['allowed']   = [];
		$arr_tbl['commande_facture']['U']['forbidden'] = [];

		$arr_tbl['shop']['C']['allowed']   = [];
		$arr_tbl['shop']['C']['forbidden'] = ['*'];
		$arr_tbl['shop']['R']['allowed']   = [];
		$arr_tbl['shop']['R']['forbidden'] = ['gps'];
		$arr_tbl['shop']['U']['allowed']   = [];
		$arr_tbl['shop']['U']['forbidden'] = ['slug', 'code', 'gpsData', 'codePostal', 'strip_key','gps','login'];

		$arr_tbl['shop_jours']['C']['allowed']   = [];
		$arr_tbl['shop_jours']['C']['forbidden'] = [];
		$arr_tbl['shop_jours']['R']['allowed']   = [];
		$arr_tbl['shop_jours']['R']['forbidden'] = [];
		$arr_tbl['shop_jours']['U']['allowed']   = [];
		$arr_tbl['shop_jours']['U']['forbidden'] = [];

		$arr_tbl['shop_jours_shift']['C']['allowed']   = [];
		$arr_tbl['shop_jours_shift']['C']['forbidden'] = [];
		$arr_tbl['shop_jours_shift']['R']['allowed']   = [];
		$arr_tbl['shop_jours_shift']['R']['forbidden'] = [];
		$arr_tbl['shop_jours_shift']['U']['allowed']   = [];
		$arr_tbl['shop_jours_shift']['U']['forbidden'] = [];

		$arr_tbl['shop_jours_shift_run']['C']['allowed']   = [];
		$arr_tbl['shop_jours_shift_run']['C']['forbidden'] = [];
		$arr_tbl['shop_jours_shift_run']['R']['allowed']   = [];
		$arr_tbl['shop_jours_shift_run']['R']['forbidden'] = [];
		$arr_tbl['shop_jours_shift_run']['U']['allowed']   = [];
		$arr_tbl['shop_jours_shift_run']['U']['forbidden'] = [];

		$arr_tbl['produit']['C']['allowed']   = [];
		$arr_tbl['produit']['C']['forbidden'] = ['slug','code','dateFin','duree_realisation', 'grilleFK' => ['produit']];
		$arr_tbl['produit']['R']['allowed']   = [];
		$arr_tbl['produit']['R']['forbidden'] = [];
		$arr_tbl['produit']['U']['allowed']   = [];
		$arr_tbl['produit']['U']['forbidden'] = ['slug', 'grilleFK' => ['produit']];

		return $arr_tbl;
	}

}