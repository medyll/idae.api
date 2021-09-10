<?php

	/*require "../vendor/predis/predis/autoload.php";
	Predis\Autoloader::register();*/
	
	if (!function_exists('idae_autoloader')) {
		function idae_autoloader($class_name) {

			$dirs = array(APPCLASSES,
			              APPCLASSES_ENGINE,
			              APPCLASSES_VIEWS,
			              APPCLASSES_TOOLS,
			              OLDAPPCLASSES);

			foreach ($dirs as $directory) {
				
				if (file_exists($directory . 'Class' . $class_name . '.php')) {
					require($directory . 'Class' . $class_name . '.php');

					return true;
				}
				if (file_exists($directory . $class_name . '.php')) {
					require($directory . $class_name . '.php');

					return true;
				}

				$path = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
				//echo $directory.$path. '.php<br>';
				if (file_exists($directory.$path. '.php')) {
					require_once($directory.$path . '.php');
					return true;
				}


				// echo $directory . $class_name . '.php';
			}

		}


		spl_autoload_register('idae_autoloader');
	}
