<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    Examity_Client
 * @subpackage Examity_Client/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.0.1
 * @package    Examity_Client
 * @subpackage Examity_Client/includes
 * @author     Jason Sherman <jsn.sherman@gmail.com>
 */
class Examity_Client_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
	public static function deactivate() {

            wp_clear_scheduled_hook('examity_client_cron_api_provision');
	}

}
