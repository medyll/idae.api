<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 25/05/2018
	 * Time: 03:39
	 */
	class ConsoleUi {

		function add_commande_to_console($idcommande) {
			$table        = 'commande';
			$APP_COMMANDE = new App($table);
			$ARR = $APP_COMMANDE->findOne(["id$table"=>(int)$idcommande]);

			$html_vars    = "table=$table&table_value=$idcommande";
			$html         = htmlspecialchars('<div  data-act_defer="" data-mdl="idae/module/app_console_thumb/' . $html_vars . '" data-vars="' . $html_vars . '"></div>');
			$html_livreur = htmlspecialchars('<div  data-act_defer="" data-mdl="idae/module/app_fiche/app_fiche_reserv/' . $html_vars . '" data-vars="' . $html_vars . '"></div>');

			AppSocket::send_cmd('act_insert_html', ['html_attributes' => ['data-table'        => 'commande',
			                                                              'data-idsecteur'    => $idsecteur,
			                                                              'data-type_session' => 'livreur'],
			                                        'data'            => ['red' => 'carpette'],
			                                        'html'            => $html_livreur], $room_livreur);

			AppSocket::send_cmd('act_insert_html', ['html_attributes' => ['data-table'        => 'commande',
			                                                              'data-idshop'       => $idshop,
			                                                              'data-idsecteur'    => $idsecteur,
			                                                              'data-type_session' => 'shop'],
			                                        'data'            => ['red' => 'carpette'],
			                                        'html'            => $html], $room_shop);
			$data_html_shop_2 = htmlspecialchars("<div data-act_defer data-mdl='idae/module/fiche_mini/table=$table&table_value=$idcommande' data-vars='table=$table&table_value=$idcommande'></div>");

			AppSocket::send_cmd('act_insert_html', ['html_attributes' => ['data-table' => $table, 'data-type_liste' => 'pool_statut_START'],
			                                        'data'            => ['some' => 'data'],
			                                        'html'            => $data_html_shop_2]);
		}

		function remove_commande_from_console($idcommande) {
			$table = 'commande';

			$oith                     = [];
			$commande_gutter_selector = "[data-console_liste] [data-table_value=$idcommande][data-table=$table]";
			$commande_shop_selector   = "[data-console_liste_detail] [data-table_value=$idcommande][data-table=$table]";
			$commande_agent_selector  = "[data-console_liste_detail] [data-table_value=$idcommande][data-table=$table]";

			array_push($oith, $commande_gutter_selector);
			array_push($oith, $commande_shop_selector);
			array_push($oith, $commande_agent_selector);

			AppSocket::send_cmd('act_remove_selector', $oith);

		}
	}