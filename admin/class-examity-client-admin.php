<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    Examity_Client
 * @subpackage Examity_Client/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Examity_Client
 * @subpackage Examity_Client/admin
 * @author     Jason Sherman <jsn.sherman@gmail.com>
 */
class Examity_Client_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $examity_client    The ID of this plugin.
	 */
	private $examity_client;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $examity_client       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $examity_client, $version ) {

		$this->examity_client = $examity_client;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Examity_Client_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Examity_Client_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->examity_client, plugin_dir_url( __FILE__ ) . 'css/examity-client-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Examity_Client_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Examity_Client_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->examity_client, plugin_dir_url( __FILE__ ) . 'js/examity-client-admin.js', array( 'jquery' ), $this->version, false );

	}

}
