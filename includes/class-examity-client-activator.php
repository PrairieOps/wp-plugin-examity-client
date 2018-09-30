<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    Examity_Client
 * @subpackage Examity_Client/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    Examity_Client
 * @subpackage Examity_Client/includes
 * @author     Jason Sherman <jsn.sherman@gmail.com>
 */
class Examity_Client_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */

        public static function examity_client_cron_schedules( $schedules=null ) {

            $schedules['five_minutes'] = array(
                'interval'=> 300,
                'display'=>  __('Once Every 5 minutes')
            );

            return $schedules;

        }

	public static function activate() {

            add_filter('cron_schedules', Examity_Client_Activator::examity_client_cron_schedules());

            if (! wp_next_scheduled ( 'examity_client_cron_api_provision' )) {
              wp_schedule_event(time(), 'five_minutes', 'examity_client_cron_api_provision');
            }

	}

}
