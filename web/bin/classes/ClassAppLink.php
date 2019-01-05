<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 03/06/2018
	 * Time: 18:19
	 * todo use ModulePaths
	 */
	class AppLink {

		static function fiche($table, $table_value = null, $vars = []) {

			$mdl = 'app_fiche';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function create($table, $table_value = null, $vars = []) {
			$mdl = 'app_crud/app_create';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function update($table, $table_value = null, $vars = []) {
			$mdl = 'app_update/app_update';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function delete($table, $table_value = null, $vars = []) {
			$mdl = 'app_crud/app_delete';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function delete_image($table, $table_value = null, $vars = []) {
			$mdl = 'app_crud/app_delete_image';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function map($table, $table_value = null, $vars = []) {
			$mdl = 'app_fiche_map/app_fiche_map';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function map_itinerary($table, $table_value = null, $vars = []) {
			$mdl = 'app_fiche_map/app_fiche_map_itineraire';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function liste($table, $table_value = null, $vars = []) {
			$mdl = 'app_liste/app_liste';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function liste_groupby($table, $table_value = null, $vars = []) {
			$mdl = 'app_liste/app_liste_groupby';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function liste_calendrier($table, $table_value = null, $vars = []) {
			$mdl = 'app_calendrier/app_calendrier_liste';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function liste_images($table, $table_value = null, $vars = []) {
			$mdl = 'app_img_liste';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function console($table, $table_value = null, $vars = []) {
			$type_session = $_SESSION['type_session'];
			$mdl          = "app_console/$type_session/app_console_$type_session";
			$uri          = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		static function home($table, $table_value = null, $vars = []) {
			$mdl = 'app_home';
			$uri = AppLink::build_uri($table, $table_value, $vars);

			return AppLink::build_link($mdl, $uri);
		}

		private static function build_uri($table, $table_value, $vars = []) {
			$out = array_filter(['table'       => $table,
			                     'table_value' => $table_value,
			                     'vars'        => array_filter($vars)]);

			return http_build_query($out);
		}

		private static function build_link($mdl, $uri) {

			return "idae/module/$mdl/$uri";//encrypt_url("$mdl/$uri");
		}
	}