<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    Examity_Client
 * @subpackage Examity_Client/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Examity_Client
 * @subpackage Examity_Client/public
 * @author     Jason Sherman <jsn.sherman@gmail.com>
 */
class Examity_Client_Public {

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
	 * @param      string    $examity_client       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $examity_client, $version ) {

		$this->examity_client = $examity_client;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->examity_client, plugin_dir_url( __FILE__ ) . 'css/examity-client-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->examity_client, plugin_dir_url( __FILE__ ) . 'js/examity-client-public.js', array( 'jquery' ), $this->version, false );

	}

        public function api_init() {
                $client = new Client([
                    // Base URI is used with relative requests
                    'base_uri' => 'http://httpbin.org',
                    // You can set any number of default request options.
                    'timeout'  => 2.0,
                ]);
	}

}
