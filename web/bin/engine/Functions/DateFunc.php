<?php
/**
 * Created by PhpStorm.
 * User: Meddy
 * Date: 11/10/2019
 * Time: 12:55
 */

namespace Functions;


class DateFunc
{
		static function mois_fr($num)
		{
				$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				
				return $tabmonth[(int)$num];
		}
		
		static function date_fr($date)
		{
				$arrDate  = explode('-', $date);
				$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				
				return $arrDate[2] . ' ' . $tabmonth[(int)$arrDate[1]] . ' ' . $arrDate[0];
		}
		
		static function moisDate_fr($date)
		{
				$arrDate  = explode('-', $date);
				$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				
				return $tabmonth[(int)$arrDate[1]] . ' ' . $arrDate[0];
		}
		
		static function mois_short_Date_fr($date)
		{
				$arrDate  = explode('-', $date);
				$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				
				return substr($tabmonth[(int)$arrDate[1]], 0, 4) . ' ' . $arrDate[0];
		}
		
		static function jourMoisDate_fr($date)
		{
				$arrDate   = explode('-', $date);
				$tabmonth  = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				$tabjour   = [1 => "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
				$indexjour = date("w", strtotime($date));
				
				return $tabjour[$indexjour] . ' ' . $arrDate[2] . ' ' . $tabmonth[(int)$arrDate[1]] . ' ' . $arrDate[0];
		}
		
		static function jourMoisDate_fr_short($date)
		{
				$arrDate   = explode('-', $date);
				$tabmonth  = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				$tabjour   = [1 => "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
				$indexjour = date("w", strtotime($date));
				
				return $tabjour[$indexjour] . ' ' . $arrDate[2] . ' ' . $tabmonth[(int)$arrDate[1]];
		}
}