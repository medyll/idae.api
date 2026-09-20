<?php
/**
 * Created by PhpStorm.
 * User: Meddy
 * Date: 11/10/2019
 * Time: 12:47
 */

namespace Functions;


/**
 * URL helpers: inlining remote files, and obfuscating values placed in a URL.
 */
class UrlFunc
{
		
		/**
		 * Fetches a file and returns it as a `data:` URI.
		 *
		 * The MIME type is taken from the file extension, not sniffed, and TLS
		 * certificates are not verified.
		 *
		 * @param string $filename Path or URL
		 * @return string
		 */
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
		
		/**
		 * Obfuscates a string for use in a URL.
		 *
		 * This is not encryption: it is a byte-wise addition against a key hardcoded in
		 * this file, then base64. It hides a value from a casual reader and nothing
		 * more. Never pass anything secret through it.
		 *
		 * @param string $string
		 * @return string URL-encoded base64
		 * @see decryptUrl()
		 */
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
		
		/**
		 * Reverses encryptUrl(). See the warning there.
		 *
		 * @param string $string
		 * @return string
		 */
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