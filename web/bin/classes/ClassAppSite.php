<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 20/07/2017
	 * Time: 18:48
	 */
	class AppSite extends App {

		public $site_page;
		public $meta_title;
		public $meta_description;

		function __construct($table = null) {
			parent::__construct($table);
		}

		static function imgApp($table, $table_value, $size = 'small') {
			return AppSite::imgSrc($table . '-' . $size . '-' . $table_value);
		}

		function makeTplData($table, $value_id, $soid = '') {

			$out = [];

			// les images
			$arr_size = ['mini', 'tiny', 'squary', 'square', 'small', 'long', 'large'];
			foreach ($arr_size as $k_size => $size):
				// if(!empty($this->app_table_one['hasImage'.$size.'Scheme'])){   }
				$out['image_' . $table . '_' . $size] = $this->imgSrc($table . '-' . $size . '-' . $value_id);

			endforeach;

			return $out;
		}

		static function imgSrc($image_name) {

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

			// echo FLATTENIMGDIR . $dir;

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

		public function render($html = '', $parameters = []) {
			global $LATTE;

			$parameters['route']            = $html;
			$parameters['site_page']        = $this->site_page;
			$parameters['meta_tile']        = $this->get_meta_title();
			$parameters['meta_description'] = $this->get_meta_description();

			$Fragment               = new Fragment();
			$parameters['menu_bar'] = $Fragment->menu_bar(true);
			$parameters['html_footer']   = $Fragment->footer(true);
		//	$LATTE->setAutoRefresh(true);
			$LATTE->setAutoRefresh(false);
			if ($this->site_page == 'application') {
				echo $html = $LATTE->renderToString(APPTPL . 'idae/body.html', $parameters);
			} else {
				echo $html = $LATTE->renderToString(APPTPL . 'body.html', $parameters);
			}
		}

		function get_meta_title() {
			$meta_tile = '';
			switch ($this->site_page):
				case 'index':
					$meta_tile = 'tac-tac city votre service de livraison preféré';
					break;
				case 'shop':
					$meta_tile = 'tac-tac city restaurant';
					break;

			endswitch;

			return empty($this->meta_title) ? $meta_tile : $this->meta_title;
		}

		function set_meta_title($meta_title = '') {
			$this->meta_title = $meta_title;
		}

		function get_meta_description() {
			return $this->meta_description;
		}

		function set_meta_description($meta_title = '') {
			$this->meta_description = $meta_title;
		}

		function set_page($site_page = '') {
			$this->site_page = $_SESSION['site_page'] = $site_page;
		}

		function get_page() {
			return $_SESSION['site_page'];
		}
	}