<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://tobugroup.com
 * @since      1.0.0
 *
 * @package    Etichette_Dna
 * @subpackage Etichette_Dna/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Etichette_Dna
 * @subpackage Etichette_Dna/includes
 * @author     Tobugroup S.r.l. <info@tobugroup.com>
 */
class Etichette_Dna_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'etichette-dna',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
