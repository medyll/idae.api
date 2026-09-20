<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 10/08/2017
	 * Time: 03:17
	 */


	/**
	 * The default landing page.
	 */
	class Index {
		// Redefine the parent method
		/**
		 * Renders the landing page through the global Latte instance.
		 *
		 * @return string HTML
		 */
		public function display()
		{
			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
			$html = $LATTE->renderToString(APPTPL.'orbit_big.html', $parameters);

		}
	}