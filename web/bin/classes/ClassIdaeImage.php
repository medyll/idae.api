<?

	/**
	 * Image processing and GridFS storage: thumbnails, crops, resizes and
	 * watermarks.
	 *
	 * Split between Imagick and GD depending on the method; the `*Bytes` methods
	 * work on raw image data rather than paths, which is what the GridFS store
	 * hands back.
	 */
	class IdaeImage {

		/**
		 * Nothing to construct; the methods here are helpers.
		 */
		function __construct() {

		}

		/**
		 * Re-encodes an image as JPEG at quality 50, keeping its dimensions.
		 *
		 * @param string $name Path
		 * @return string Image bytes
		 */
		function lowImage($name) {
			$im = new Imagick($name);
			$im->setImageCompression(imagick::COMPRESSION_JPEG);
			$im->setImageCompressionQuality(50);
			$im->thumbnailImage($im->getImageWidth(), null);
			$bytesOut = $im->getimageblob();

			return $bytesOut;
		}

		/**
		 * Draws a translucent watermark over an image.
		 *
		 * Note that the fill colour is hardcoded in the body, so `$vars['fillColor']`
		 * has no effect.
		 *
		 * @param \Imagick $im
		 * @param array    $vars `fillColor` and `opacity`
		 * @return string Image bytes
		 */
		function imageBytesAnnotate($im, $vars = array('fillColor' => 'ffffff88',
		                                               'opacity'   => 60)) {
			$draw = new ImagickDraw();
			$draw->setFillColor('#ffffff88');
			$draw->setFontSize(10);
			//$im->annotateImage($draw , 40 , $height - 10 , 0 , "croisieres-maw.com");

			$draw->setFillColor('#00000088');
			$draw->setFontSize(10);

			//$im->annotateImage($draw , 40 , $height - 9 , 0 , "croisieres-maw.com");

			return $im;
		}

		/**
		 * Builds a thumbnail from image bytes, with GD.
		 *
		 * @param string $bytes Image bytes
		 * @param array  $vars `width` and `height`
		 * @return string Image bytes
		 */
		static function thumbImageBytes($bytes, $vars = array('width'  => 120,
		                                                      'height' => 60)) {
			$source_image  = imagecreatefromstring($bytes);
			$source_width  = imagesx($source_image);
			$source_height = imagesy($source_image);
			$target_width  = $vars['width'];
			$target_height = $vars['height'];

			$source_aspect_ratio  = $source_width / $source_height;
			$desired_aspect_ratio = $target_width / $target_height;

			if ($source_aspect_ratio > $desired_aspect_ratio) {
				/*
				 * Triggered when source image is wider
				 */
				$temp_height = $target_height;
				$temp_width  = ( int )($target_height * $source_aspect_ratio);
			} else {
				/*
				 * Triggered otherwise (i.e. source image is similar or taller)
				 */
				$temp_width  = $target_width;
				$temp_height = ( int )($target_width / $source_aspect_ratio);
			}
			/**
			 * Resize the image into a temporary GD image
			 */
			$temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
			imagecopyresampled($temp_gdim, $source_image, 0, 0, 0, 0, $temp_width, $temp_height, $source_width, $source_height);
			/**
			 * Copy cropped region from temporary image into the desired GD image
			 */
			$x0           = ($temp_width - $target_width) / 2;
			$y0           = ($temp_height - $target_height) / 2;
			$desired_gdim = imagecreatetruecolor($target_width, $target_height);
			imagecopy($desired_gdim, $temp_gdim, 0, 0, $x0, $y0, $target_width, $target_height);

			ob_start();
			imagejpeg($desired_gdim);
			$imageData = ob_get_contents();
			imagedestroy($temp_gdim);
			imagedestroy($desired_gdim);
			imagedestroy($source_image);
			ob_end_clean();

			return $imageData;

		}

		/**
		 * Stores image bytes in GridFS.
		 *
		 * @param string      $file_name
		 * @param string      $bytes Image bytes
		 * @param array       $metadata
		 * @param string      $base Database
		 * @param string|null $collection GridFS bucket; the default one when null
		 * @return mixed The stored file's id
		 */
		static function saveImageBytes($file_name, $bytes, $metadata = [], $base = 'sitebase_image', $collection = null) {
			$APP  = new App();
			$db   = $APP->plug_base($base);
			$grid = empty($collection) ? $db->getGridFs() : $db->getGridFs($collection);
			$grid->remove(['filename' => $file_name]);

			$ins['filename'] = $file_name;
			$ins['metadata'] = $metadata;

			return $grid->storeBytes($bytes, $ins);
		}

		/**
		 * Resizes image bytes.
		 *
		 * @param string $bytes Image bytes
		 * @param array  $vars `width` and `height`
		 * @return string|null Null for empty input
		 */
		static function imageBytesResize($bytes, $vars = array('width'  => 120,
		                                                       'height' => 60)) {
			if (empty($bytes)) {
				return;
			}
			$im = new \Imagick();
			$im->readImageBlob($bytes);
			$im->setImageCompression(imagick::COMPRESSION_JPEG);
			$im->setImageCompressionQuality(80);
			$geo    = $im->getImageGeometry();
			$width  = $vars['width'];
			$height = $vars['height'];

			if (($geo['width'] / $width) < ($geo['height'] / $height)) {
				$im->cropImage($geo['width'], floor($height * $geo['width'] / $width), 0, (($geo['height'] - ($height * $geo['width'] / $width)) / 2));
			} else {
				$im->cropImage(ceil($width * $geo['height'] / $height), $geo['height'], (($geo['width'] - ($width * $geo['height'] / $height)) / 2), 0);
			}
			//$im->charcoalImage (2,1);
			$im->thumbnailImage($width, $height, true);

			$draw = new ImagickDraw();
			$draw->setFillColor('#ffffff88');
			$draw->setFontSize(10);
			//$im->annotateImage($draw , 10 , $height - 10 , 0 , "croisieres-maw.com");

			$bytesOut = $im->getimageblob();

			return $bytesOut;
		}

		/**
		 * Reads an image out of GridFS and returns it at the requested size.
		 *
		 * @param string $id  GridFS id
		 * @param string $col Bucket
		 * @param string $base Database
		 * @param int    $width
		 * @param int    $height
		 * @return string Image bytes
		 */
		static function gridImage($id, $col = 'appimg', $base = 'sitebase_base', $width = 120, $height = 60) {
			$APP   = new App();
			$grid  = empty($col) ? $APP->plug_base($base)->getGridFs() : $APP->plug_base($base)->getGridFs($col);
			$data  = $grid->findOne(array('_id' => new MongoId($id)));
			$im    = new Imagick();
			$bytes = $data->getBytes();
			$im->readImageBlob($bytes);
			$im->setImageCompression(imagick::COMPRESSION_JPEG);
			$im->setImageCompressionQuality(80);
			$im->thumbnailImage($width, $height, true);
			$bytesOut = $im->getimageblob();

			return $bytesOut;
		}

		/**
		 * Crops an image held in GridFS.
		 *
		 * @param string $id   GridFS id
		 * @param string $col  Bucket
		 * @param string $base Database
		 * @param array  $vars Crop box
		 * @return string Image bytes
		 */
		static function cropImage($id, $col = 'fs', $base = 'sitebase_image', $vars = array()) {
			$APP  = new App();
			$grid = empty($col) ? $APP->plug_base($base)->getGridFs() : $APP->plug_base($base)->getGridFs($col);
			$data = $grid->findOne(array('_id' => new MongoId($id)));

			$im    = new Imagick();
			$bytes = $data->getBytes();
			$im->readImageBlob($bytes);
			$im->setImagePage(0, 0, 0, 0);

			$geo = $im->getImageGeometry();
			// <=> // taille ecran =>
			$im->thumbnailImage($vars['display_width'], $vars['display_height'], true);
			// on crop now
			$im->cropImage($vars['width'], $vars['height'], $vars['x1'], $vars['y1']);
			$im->thumbnailImage($vars['final_width'], $vars['final_height'], true);
			// taiile finale demandé (ratio )
			/*		$im->cropImage($width,$height,$x,$y);
					$im->thumbnailImage($fw,$fh,true);*/
			//
			$im->adaptiveSharpenImage(2, 1);
			//
			$bytesOut = $im->getimageblob();

			return $bytesOut;
		}

		/**
		 * Builds a thumbnail with Imagick. A null height keeps the aspect ratio.
		 *
		 * @param string   $name Image bytes
		 * @param int      $width
		 * @param int|null $height
		 * @return string Image bytes
		 */
		function thumbImage($name, $width = 120, $height = null) {
			$im = new Imagick();
			$im->readImageBlob($name);
			$im->setImageCompression(imagick::COMPRESSION_JPEG);
			$im->setImageCompressionQuality(90);
			$im->thumbnailImage($width, $height, true);
			$bytesOut = $im->getimageblob();

			return $bytesOut;
		}

		/**
		 * Writes a reflected copy of a JPEG next to it, as a PNG.
		 *
		 * @param string $name Path
		 * @return mixed
		 */
		function reflectImage($name) {
			$outname = str_replace('.jpg', '_reflect.png', $name);
			/* Lecture de l'image */
			clearstatcache();
			//if(!file_exists($name)) return false;
			$AgetHeaders = @get_headers($name);
			if (preg_match("|200|", $AgetHeaders[0])) {
				// file exists
			} else {
				// file doesn't exists
				return false;
			}
			$im = new Imagick($name);
			// profile icc
			//$icc_rgb = file_get_contents(PATHICC.'ISOcoated_v2_eci.icc');
			//$im->profileImage('icc', $icc_rgb);
			//$im->setImageColorSpace(Imagick::COLORSPACE_RGB);
			//
			/* Thumbnail the image */
			$im->thumbnailImage($im->getImageWidth(), null);
			$reflection = $im->clone();
			$reflection->flipImage();
			$gradient = new Imagick();
			$gradient->newPseudoImage($reflection->getImageWidth(), $reflection->getImageHeight() * 0.5, "gradient:transparent-black");
			$reflection->compositeImage($gradient, imagick::COMPOSITE_DSTOUT, 0, 0);
			$gradient->newPseudoImage($reflection->getImageWidth(), $reflection->getImageHeight() * 0.5, "gradient:black");
			$reflection->compositeImage($gradient, imagick::COMPOSITE_DSTOUT, 0, $reflection->getImageHeight() * 0.5);
			$opacity = new Imagick();
			$opacity->newImage($reflection->getImageWidth(), $reflection->getImageHeight(), new ImagickPixel('black'));
			$opacity->setImageOpacity(0.4);
			$reflection->compositeImage($opacity, imagick::COMPOSITE_DSTOUT, 0, 0);

			$canvas = new Imagick();

			$width  = $im->getImageWidth();
			$height = ($im->getImageHeight() * 1.5);

			$canvas->newImage($width, $height, 'none', "png");
			$canvas->compositeImage($im, imagick::COMPOSITE_SRCOVER, 0, 0);
			$canvas->compositeImage($reflection, imagick::COMPOSITE_SRCOVER, 0, $im->getImageHeight());
			$bytesOut = $canvas->getimageblob();
			//$canvas->writeImage(PATHTMP.'destination.jpg');
			//echo $outname;
			return $bytesOut; //$bytesOut;
		}

		/**
		 * Builds and stores a named thumbnail size for a file.
		 *
		 * Delegates to makeGdThumb() and returns immediately, so `$idd` is unused.
		 *
		 * @param string $file
		 * @param mixed  $idd Unused
		 * @param int    $width
		 * @param int    $height
		 * @param string $sizeName Name the size is stored under
		 * @param string $tag
		 * @param string $nameSizeFrom Source size to derive from
		 * @return void
		 */
		function makeThumb($file, $idd, $width = 250, $height = 120, $sizeName, $tag, $nameSizeFrom = 'large') {
			IdaeImage::makeGdThumb($file, $width, $height, $sizeName, $tag, $nameSizeFrom);

			return;
			$db    = skelMongo::connectBase('sitebase_image'); //$con->sitebase_image;
			$grid  = $db->getGridFS();
			$thumb = str_replace($nameSizeFrom, $sizeName, $file);
			$test  = $grid->findOne($file);
			if (!empty($test) && !empty($test->file['chunkSize'])) {
				$image = $test->getBytes();
				$image = imagecreatefromstring($image);
				$grid->remove(array('filename' => $thumb));
				//
				$im = new Imagick();
				//echo $im->identifyImage($image);
				//var_dump($im->getImageType($file)); exit;
				//
				//$im->readimageblob($image);
				$geo = $im->getImageGeometry();
				if (($geo['width'] / $width) < ($geo['height'] / $height)) {
					$im->cropImage($geo['width'], floor($height * $geo['width'] / $width), 0, (($geo['height'] - ($height * $geo['width'] / $width)) / 2));
				} else {
					$im->cropImage(ceil($width * $geo['height'] / $height), $geo['height'], (($geo['width'] - ($width * $geo['height'] / $height)) / 2), 0);
				}
				//$im->charcoalImage (2,1);
				$im->thumbnailImage($width, $height, true);
				$bytesOut = $im->getimageblob();
				$myMeta   = array('filename' => $thumb,
				                  'metadata' => array('width'       => $width,
				                                      'heigh'       => $height,
				                                      'tag'         => $tag,
				                                      'name'        => $thumb,
				                                      'size'        => $sizeName,
				                                      'mysqlid'     => $idd,
				                                      'contentType' => 'image/jpeg'));
				$grid->storeBytes($bytesOut, $myMeta);
				// echo "done ".$thumb;
			}
		}
		//
		//
		// $file,$idd,$width=250,$height=120,$sizeName,$tag,$nameSizeFrom='large'
		/**
		 * Builds a thumbnail with GD and stores it in GridFS under its size name.
		 *
		 * @param string $file
		 * @param int    $thumb_width
		 * @param int    $thumb_height
		 * @param string $sizeName Name the size is stored under
		 * @param string $tag
		 * @param string $nameSizeFrom Source size to derive from
		 * @param array  $metadata
		 * @return mixed
		 */
		static function makeGdThumb($file, $thumb_width = 250, $thumb_height = 120, $sizeName, $tag, $nameSizeFrom = 'large', $metadata = []) {
			//
			$APP = new App();
			ob_start();
			$time = time();
			//
			$db      = $APP->plug_base('sitebase_image'); //$con->sitebase_image;
			$grid    = $db->getGridFS();
			$thumb   = str_replace($nameSizeFrom, $sizeName, $file);
			$jpgName = str_replace('.jpg', '', $thumb) . '.jpg';
			$test    = $grid->findOne($file);
			if (!file_exists(PATHTMP . 'GDTMP/')) {
				@mkdir(PATHTMP . 'GDTMP/', 0777);
			}
			if (!empty($test) && !empty($test->file['chunkSize'])):
				$image      = $test->getBytes();
				$nametmp    = PATHTMP . 'GDTMP/' . $time . '-' . $file . '-' . $sizeName;
				$nameNewtmp = PATHTMP . 'GDTMP/' . $thumb;
				file_put_contents($nametmp, $image);
				$thumbNail = PhpThumbFactory::create($nametmp);
				$thumbNail->adaptiveResize($thumb_width, $thumb_height)->save($nameNewtmp, 'jpg');
				$bytesOut = $thumbNail->getImageAsString();
				$grid->remove(array('filename' => $thumb));

				$finalOut = file_get_contents($nameNewtmp);
				$myMeta   = array('filename'   => $thumb,
				                  'uploadDate' => new MongoDate(),
				                  'metadata'   => array_merge(array('time'          => time(),
				                                                    'date'          => date('Y-m-d'),
				                                                    'heure'         => date('H:i:s'),
				                                                    'width'         => $thumb_width,
				                                                    'heigh'         => $thumb_height,
				                                                    'tag'           => $tag,
				                                                    'table'         => $tag,
				                                                    'mongoTag'      => $tag,
				                                                    'filename'      => $thumb,
				                                                    'real_filename' => $thumb,
				                                                    'size'          => $sizeName), $metadata));
				$grid->storeBytes($finalOut, $myMeta, array('safe' => true));
				unlink($nametmp);
			endif;
			ob_end_clean();

			return $thumb;
		}
	}

?>