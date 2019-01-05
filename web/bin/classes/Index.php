<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 10/08/2017
	 * Time: 03:17
	 */


	class Index {
		// Redefine the parent method
		public function display()
		{
			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
			$html = $LATTE->renderToString(APPTPL.'orbit_big.html', $parameters);

		}
	}