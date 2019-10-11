<?php
/**
 * Created by PhpStorm.
 * User: Meddy
 * Date: 11/10/2019
 * Time: 12:50
 */

namespace Functions;


class ColorFunc
{
		
		public 	function linearGradient($hexStart, $hexStop, $iterationnr) {
				$rgbStart = hex2rgb($hexStart);
				list($ra, $ga, $ba) = $rgbStart;
				$rgbStop = hex2rgb($hexStop);
				list($rz, $gz, $bz) = $rgbStop;
				$colorindex = array();
				for ($iterationc = 1; $iterationc <= $iterationnr; $iterationc++) {
						$iterationdiff = $iterationnr - $iterationc;
						$colorindex[]  = '#' . dechex(intval((($ra * $iterationc) + ($rz * $iterationdiff)) / $iterationnr)) . dechex(intval((($ga * $iterationc) + ($gz * $iterationdiff)) / $iterationnr)) . dechex(intval((($ba * $iterationc) + ($bz * $iterationdiff)) / $iterationnr));
				}
				
				return array_reverse($colorindex);
		}
		
		public function hex2rgb($hex, $op = 1) {
				$hex = str_replace("#", "", $hex);
				
				if (strlen($hex) == 3) {
						$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
						$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
						$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
				} else {
						$r = hexdec(substr($hex, 0, 2));
						$g = hexdec(substr($hex, 2, 2));
						$b = hexdec(substr($hex, 4, 2));
				}
				$rgb = [$r,
				        $g,
				        $b,
				        $op];
				
				//return implode(",", $rgb); // returns the rgb values separated by commas
				return $rgb; // returns an array with the rgb values
		}
		
		public function rgb2hex($rgb) {
				$hex = "#";
				$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
				$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
				$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
				
				return $hex; // returns the hex value including the number sign (#)
		}
		
		function colorInverse($color) {
				$color = str_replace('#', '', $color);
				if (strlen($color) != 6) {
						return '000000';
				}
				$rgb = '';
				for ($x = 0; $x < 3; $x++) {
						$c   = 255 - hexdec(substr($color, (2 * $x), 2));
						$c   = ($c < 0) ? 0 : dechex($c);
						$rgb .= (strlen($c) < 2) ? '0' . $c : $c;
				}
				
				return '#' . $rgb;
		}
		
		function colorContrast($hexcolor) {
				$hexcolor = str_replace('#', '', $hexcolor);
				$r        = hexdec(substr($hexcolor, 0, 2));
				$g        = hexdec(substr($hexcolor, 2, 2));
				$b        = hexdec(substr($hexcolor, 4, 2));
				$yiq      = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
				
				return ($yiq >= 128) ? '#000000' : '#ffffff';
		}
}