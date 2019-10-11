<?php
/**
 * Created by PhpStorm.
 * User: Meddy
 * Date: 11/10/2019
 * Time: 12:47
 */

namespace Functions;


class UrlFunc
{
		
		public function toDataUri($filename)
		{
				$parsed_URL        = parse_url($filename);
				$exploded          = explode('.', $parsed_URL['path']);
				$arrContextOptions = ["ssl" => ["verify_peer" => false,
				                                "verify_peer_name" => false,],];
				$mime              = end($exploded);//mime_content_type($filename);
				$data              = base64_encode(file_get_contents($filename), false, stream_context_create($arrContextOptions));
				
				return "data:$mime;base64,$data";
		}
		
		public function encryptUrl($string)
		{
				$key    = "idae654"; //key to encrypt and decrypts.
				$result = '';
				$test   = "";
				for ($i = 0; $i < strlen($string); $i++) {
						$char    = substr($string, $i, 1);
						$keychar = substr($key, ($i % strlen($key)) - 1, 1);
						$char    = chr(ord($char) + ord($keychar));
						
						$test[$char] = ord($char) + ord($keychar);
						$result      .= $char;
				}
				
				return urlencode(base64_encode($result));
		}
		
		public function decryptUrl($string)
		{
				$key    = "idae654"; //key to encrypt and decrypts.
				$result = '';
				$string = base64_decode(urldecode($string));
				for ($i = 0; $i < strlen($string); $i++) {
						$char    = substr($string, $i, 1);
						$keychar = substr($key, ($i % strlen($key)) - 1, 1);
						$char    = chr(ord($char) - ord($keychar));
						$result  .= $char;
				}
				
				return $result;
		}
}