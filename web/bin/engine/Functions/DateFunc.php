<?php
/**
 * Created by PhpStorm.
 * User: Meddy
 * Date: 11/10/2019
 * Time: 12:55
 */

namespace Functions;


/**
 * French date formatting helpers.
 *
 * Every method here is hardcoded to French month and day names; none of them is
 * locale-aware. The `$date` arguments are `YYYY-MM-DD` strings.
 */
class DateFunc
{
		/**
		 * Month name for a month number, 1 for January.
		 *
		 * @param int|string $num
		 * @return string
		 */
		static function mois_fr($num)
		{
				$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				
				return $tabmonth[(int)$num];
		}
		
		/**
		 * Formats a date as `D Month YYYY`.
		 *
		 * @param string $date `YYYY-MM-DD`
		 * @return string
		 */
		static function date_fr($date)
		{
				$arrDate  = explode('-', $date);
				$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				
				return $arrDate[2] . ' ' . $tabmonth[(int)$arrDate[1]] . ' ' . $arrDate[0];
		}
		
		/**
		 * Formats a date as `Month YYYY`, dropping the day.
		 *
		 * @param string $date `YYYY-MM-DD`
		 * @return string
		 */
		static function moisDate_fr($date)
		{
				$arrDate  = explode('-', $date);
				$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				
				return $tabmonth[(int)$arrDate[1]] . ' ' . $arrDate[0];
		}
		
		/**
		 * Formats a date as `Moi YYYY`, the month name cut to four letters.
		 *
		 * @param string $date `YYYY-MM-DD`
		 * @return string
		 */
		static function mois_short_Date_fr($date)
		{
				$arrDate  = explode('-', $date);
				$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				
				return substr($tabmonth[(int)$arrDate[1]], 0, 4) . ' ' . $arrDate[0];
		}
		
		/**
		 * Formats a date as `Weekday D Month YYYY`.
		 *
		 * @param string $date `YYYY-MM-DD`
		 * @return string
		 */
		static function jourMoisDate_fr($date)
		{
				$arrDate   = explode('-', $date);
				$tabmonth  = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				$tabjour   = [1 => "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
				$indexjour = date("w", strtotime($date));
				
				return $tabjour[$indexjour] . ' ' . $arrDate[2] . ' ' . $tabmonth[(int)$arrDate[1]] . ' ' . $arrDate[0];
		}
		
		/**
		 * Abbreviated form of jourMoisDate_fr().
		 *
		 * @param string $date `YYYY-MM-DD`
		 * @return string
		 */
		static function jourMoisDate_fr_short($date)
		{
				$arrDate   = explode('-', $date);
				$tabmonth  = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
				$tabjour   = [1 => "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
				$indexjour = date("w", strtotime($date));
				
				return $tabjour[$indexjour] . ' ' . $arrDate[2] . ' ' . $tabmonth[(int)$arrDate[1]];
		}
}