<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             0.0.1
 * @package           Examity_Client
 *
 * @wordpress-plugin
 * Examity Client:    Examity test proctoring within Wordpress.
 * Plugin URI:        http://example.com/examity-client-uri/
 * Description:       Enables Examity test proctoring within Wordpress.
 * Version:           0.0.1
 * Author:            Jason Sherman or Your Company
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       examity-client
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'EXAMITY_CLIENT_VERSION', '0.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-examity-client-activator.php
 */
function activate_examity_client() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-examity-client-activator.php';
	Examity_Client_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-examity-client-deactivator.php
 */
function deactivate_examity_client() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-examity-client-deactivator.php';
	Examity_Client_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_examity_client' );
register_deactivation_hook( __FILE__, 'deactivate_examity_client' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-examity-client.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_examity_client() {

	$plugin = new Examity_Client();
	$plugin->run();

}
run_examity_client();
