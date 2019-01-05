<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 26/06/2018
	 * Time: 21:27
	 */

	class IdaeDataSchemeImage {

		/**
		 * @param string  $table
		 * @param integer $table_value
		 * @param string  $size
		 *
		 * @return string
		 */
		static public function get_image_code($table, $table_value, $size = 'tiny') {
			$codeImage = $table . '-' . $size . '-' . $table_value;

			return $codeImage;
		}

		/**
		 * @param string  $table
		 * @param integer $table_value
		 * @param string  $size
		 *
		 * @return string
		 */
		static public function get_image_url($table, $table_value, $size) {
			$codeImage = self::get_image_code($table, $table_value, $size);

			return self::imgSrc($codeImage);
		}

		static public function imgSrc($image_name) {

			$APP            = new App();
			$raw_image_name = $image_name;
			$file_extension = strtolower(substr(strrchr($image_name, '.'), 1));
			if (empty($file_extension)) {
				$image_name .= '.jpg';
			};
			$type = empty($reflect) ? 'jpg' : 'png';

			$grid = $APP->plug_base('sitebase_image')->getGridFs();
			//
			$image = $grid->findOne(['filename' => $image_name]);
			if (empty($image)) {
				$image = $grid->findOne(['filename' => $raw_image_name]);
			}
			$dir  = $image->file['metadata']['tag'] . '/'; // tag = table
			$file = $image->file;

			switch ($file['metadata']['contentType']) {
				case "image/jpeg":
					$ext = 'jpg';
					break;
				case "image/jpg":
					$ext = 'jpg';
					break;
				case "image/gif":
					$ext = 'gif';
					break;
				case "image/png":
					$ext = 'png';
					break;
				default:
					$ext = 'jpg';
					break;
			}

			$image_file      = FLATTENIMGDIR . $dir . $image_name;
			$image_http_file = FLATTENIMGHTTP . $dir . $image_name;

			if (!file_exists(FLATTENIMGDIR)) {
				mkdir(FLATTENIMGDIR);
			}
			if (!file_exists(FLATTENIMGDIR . $dir)) {
				mkdir(FLATTENIMGDIR . $dir);
			}
			if (empty($image) && !file_exists($image_file)) {
				// echo "ok 3 ";
				return '';

				return empty($image) . " but not found $image_file";// HTTPIMAGES."blank.png?f=".$image_http_file;

			}
			if (!empty($image) || !file_exists($image_file)) {
				// on écrit image
				$file = $image->file;
				$sdir = $image->file['metadata']['tag'];
				if (is_array($sdir)) {
					$sdir = $sdir[0];
				}

				$dir = FLATTENIMGDIR . $sdir . '/';
				if (!file_exists($dir) && !empty($file['length'])) {
					mkdir($dir, 0777);
				}
				if (file_exists($dir) && !empty($file['length']) && !empty($file['chunkSize'])):

					if (file_exists($dir . $image_name)) {
						$length   = $image->file['length'];
						$filesize = filesize($dir . $image_name);

						if ($length == $filesize):
							return $image_http_file;
						else:
							//@chmod($dir , 777);
							$image->write($dir . $image_name);

							return $image_http_file;
						endif;
					}
					if (!file_exists($dir . $image_name)) {

						$image->write($dir . $image_name);

						return $image_http_file;
					}
				endif;

			}
			if (file_exists($image_file)) {
				return $image_http_file;

			}

			return $image_name;
		}

		static public function buildImageSizes($table, $table_value, $codeTailleImage, $base = 'sitebase_image', $collection = 'fs') {
			global $buildArr;
			global $IMG_SIZE_ARR;

			$IdaeConnect = IdaeConnect::getInstance();
			$conn_base   = $IdaeConnect->plug_base($base);
			$grid        = $conn_base->getGridFs($collection);
			$objImg      = $grid->findOne(array('metadata.table'           => $table,
			                                    'metadata.table_value'     => $table_value,
			                                    'metadata.codeTailleImage' => $codeTailleImage));
			$file        = $objImg->file;
			$bytes       = $objImg->getBytes();
			if (empty($bytes)) return false;

			$file_name = $file['filename'];
			$metadata  = $file['metadata'];



			$vars['build'] = [];

			switch ($codeTailleImage):
				case  'square':
					$vars['build'] = ['small'  => ['from'   => $codeTailleImage,
					                               'width'  => $buildArr['small'][0],
					                               'height' => $buildArr['small'][1]],
					                  'squary' => ['from'   => $codeTailleImage,
					                               'width'  => $buildArr['squary'][0],
					                               'height' => $buildArr['squary'][1]]];
					break;
				case  'small' :
					$vars['build'] = ['smally' => ['from'   => $codeTailleImage,
					                               'width'  => $buildArr['smally'][0],
					                               'height' => $buildArr['smally'][1]],
					                  'tiny'   => ['from'   => $codeTailleImage,
					                               'width'  => $IMG_SIZE_ARR['tiny'][0],
					                               'height' => $IMG_SIZE_ARR['tiny'][1]],
					                  'square' => ['from'   => $codeTailleImage,
					                               'width'  => $IMG_SIZE_ARR['square'][0],
					                               'height' => $IMG_SIZE_ARR['square'][1]]];
					break;
				case  'large':
					$vars['build'] = ['long'   => ['from'   => $codeTailleImage,
					                               'width'  => $IMG_SIZE_ARR['long'][0],
					                               'height' => $IMG_SIZE_ARR['long'][1]],
					                  'small'  => ['from'   => $codeTailleImage,
					                               'width'  => $IMG_SIZE_ARR['small'][0],
					                               'height' => $IMG_SIZE_ARR['small'][1]],
					                  'tiny'   => ['from'   => $codeTailleImage,
					                               'width'  => $IMG_SIZE_ARR['tiny'][0],
					                               'height' => $IMG_SIZE_ARR['tiny'][1]],
					                  'square' => ['from'   => $codeTailleImage,
					                               'width'  => $IMG_SIZE_ARR['square'][0],
					                               'height' => $IMG_SIZE_ARR['square'][1]]];
					break;
				case  'wallpaper':
					$vars['build'] = ['small'  => ['from'   => $codeTailleImage,
					                               'width'  => $IMG_SIZE_ARR['small'][0],
					                               'height' => $IMG_SIZE_ARR['small'][1]],
					                  'smally' => ['from'   => $codeTailleImage,
					                               'width'  => $buildArr['smally'][0],
					                               'height' => $buildArr['smally'][1]],
					                  'square' => ['from'   => $codeTailleImage,
					                               'width'  => $IMG_SIZE_ARR['square'][0],
					                               'height' => $IMG_SIZE_ARR['square'][1]],
					                  'tiny'   => ['from'   => $codeTailleImage,
					                               'width'  => $buildArr['tiny'][0],
					                               'height' => $buildArr['tiny'][1]]];
					break;

			endswitch;

			$ins_size = $metadata;
			unset($ins_size['filename']);

			foreach ($vars['build'] as $key => $build):
				if (empty($build['width']) || empty($build['height'])) continue;

				$new_src     = str_replace($codeTailleImage, $key, $file_name);
				$image_sized = $grid->findOne(["filename" => $new_src]);

				if (!empty($image_sized)) continue;

				$resized_bytes = IdaeImage::thumbImageBytes($bytes, ['width'  => $build['width'],
				                                                     'height' => $build['height']]);
				$ins_size      = array_merge($ins_size, ['codeTailleImage' => $key,
				                                         'width'           => $build['width'],
				                                         'height'          => $build['height']]);

				IdaeImage::saveImageBytes($new_src, $resized_bytes, $ins_size, $base, $collection);

				$image_file_sized = str_replace('.jpg', '', $new_src);
				AppSocket::send_cmd('act_notify', ['msg' => ' Génération => ' . $new_src . ' ' . $image_file_sized], session_id());

			endforeach;
		}
	}