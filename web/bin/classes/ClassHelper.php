<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 10/08/2017
	 * Time: 22:20
	 */
	class Helper {


		static function dump($vars){
			if(ENVIRONEMENT=='PROD') return '';
		//	echo "<pre style='border:1px solid #ccc;padding:1rem;'>";
			echo  json_encode($vars,  JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ;
		//	echo "</pre>";
		}
	}