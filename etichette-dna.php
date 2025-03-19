<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://tobugroup.com
 * @since             1.0.0
 * @package           Etichette_Dna
 *
 * @wordpress-plugin
 * Plugin Name:       Etichette Dna
 * Plugin URI:        https://tobugroup.com
 * Description:       Generazione e gestione dell'etichettatura digitale del vino
 * Version:           1.0.0
 * Author:            Tobugroup S.r.l.
 * Author URI:        https://tobugroup.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       etichette-dna
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ETICHETTE_DNA_VERSION', '1.0.0' );
define( 'ETICHETTE_DNA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ETICHETTE_DNA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-etichette-dna-activator.php
 */
function activate_etichette_dna() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-etichette-dna-activator.php';
	Etichette_Dna_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-etichette-dna-deactivator.php
 */
function deactivate_etichette_dna() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-etichette-dna-deactivator.php';
	Etichette_Dna_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_etichette_dna' );
register_deactivation_hook( __FILE__, 'deactivate_etichette_dna' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-etichette-dna.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_etichette_dna() {

	$plugin = new Etichette_Dna();
	$plugin->run();

}
run_etichette_dna();

// Includi il file della dashboard
require_once plugin_dir_path(__FILE__) . 'admin/dashboard.php';

function etichette_dna_add_admin_menu() {
    add_menu_page(
        'Etichette DNA',
        'Etichette DNA',
        'manage_options',
        'etichette-dna',
        'etichette_dna_admin_page',
        'dashicons-tag',
        30
    );
}
add_action('admin_menu', 'etichette_dna_add_admin_menu');

// Caricamento delle classi
require_once ETICHETTE_DNA_PLUGIN_DIR . 'includes/class-etichette-dna-db.php';
require_once ETICHETTE_DNA_PLUGIN_DIR . 'includes/class-etichette-dna-ajax.php';

// Attivazione del plugin
register_activation_hook(__FILE__, array('Etichette_DNA_DB', 'create_tables'));

// Inizializzazione AJAX
function init_etichette_dna_ajax() {
    new Etichette_DNA_Ajax();
}
add_action('init', 'init_etichette_dna_ajax');

// Caricamento degli script e stili admin
function enqueue_etichette_dna_admin_scripts() {
    wp_enqueue_script(
        'etichette-dna-dashboard',
        ETICHETTE_DNA_PLUGIN_URL . 'admin/js/dashboard.js',
        array('jquery'),
        ETICHETTE_DNA_VERSION,
        true
    );

    // Passa le variabili necessarie a JavaScript
    wp_localize_script(
        'etichette-dna-dashboard',
        'etichetteDnaParams',
        array(
            'nonce' => wp_create_nonce('etichette_dna_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
        )
    );
}
add_action('admin_enqueue_scripts', 'enqueue_etichette_dna_admin_scripts');

require_once ETICHETTE_DNA_PLUGIN_DIR . 'admin/class-etichette-dna-admin-notices.php';

// Inizializza le notifiche admin
function init_etichette_dna_admin_notices() {
    new Etichette_DNA_Admin_Notices();
}
add_action('init', 'init_etichette_dna_admin_notices');