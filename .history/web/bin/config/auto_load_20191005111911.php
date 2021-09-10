<?php

	if (!function_exists('my_autoloader')) {
		function my_autoloader($class_name) {

			$dirs = array(APPCLASSES,
			              APPCLASSES_ENGINE,
			              APPCLASSES_VIEWS,
			              APPCLASSES_TOOLS,
			              OLDAPPCLASSES);

			foreach ($dirs as $directory) {
				echo $directory . 'Class' . $class_name . '.php<br>';
				if (file_exists($directory . 'Class' . $class_name . '.php')) {
					require($directory . 'Class' . $class_name . '.php');

					return true;
				}
				if (file_exists($directory . $class_name . '.php')) {
					require($directory . $class_name . '.php');

					return true;
				}

				// echo $directory . $class_name . '.php';
			}

		}

		spl_autoload_register('my_autoloader');
	}