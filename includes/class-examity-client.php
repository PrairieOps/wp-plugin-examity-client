<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    Examity_Client
 * @subpackage Examity_Client/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    Examity_Client
 * @subpackage Examity_Client/includes
 * @author     Jason Sherman <jsn.sherman@gmail.com>
 */

// Declare guzzle stuff.
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Examity_Client {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      Examity_Client_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $examity_client    The string used to uniquely identify this plugin.
	 */
	protected $examity_client;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {
		if ( defined( 'EXAMITY_CLIENT_VERSION' ) ) {
			$this->version = EXAMITY_CLIENT_VERSION;
		} else {
			$this->version = '0.0.1';
		}
		$this->plugin_name = 'examity-client';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Examity_Client_Loader. Orchestrates the hooks of the plugin.
	 * - Examity_Client_i18n. Defines internationalization functionality.
	 * - Examity_Client_Admin. Defines all hooks for the admin area.
	 * - Examity_Client_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Composer-managed dependencies.
		 */
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-examity-client-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-examity-client-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-examity-client-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-examity-client-public.php';

		$this->loader = new Examity_Client_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Examity_Client_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Examity_Client_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Examity_Client_Admin( $this->get_examity_client(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
                $this->loader->add_action( 'admin_menu', $plugin_admin, 'define_admin_page' );
                $this->loader->add_action( 'admin_init', $plugin_admin, 'register_setting' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Examity_Client_Public( $this->get_examity_client(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_examity_client() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    Examity_Client_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

        public function api_client() {

                $stack = HandlerStack::create();
                $logger = new Logger('Logger');
                $logger->pushHandler(new StreamHandler(dirname( __FILE__ ) . '/debug.log'), Logger::DEBUG);
                $stack->push(
                    Middleware::log(
                        $logger,
                        new MessageFormatter('{req_body} - {res_body}')
                    )
                );

                $base_uri = get_option( $this->plugin_name . '_api_url' );
                $timeout = get_option( $this->plugin_name . '_api_timeout' );
                $client = new Client([
                    // log requests.
                    'handler' => $stack,
                    // Base URI is used with relative requests
                    'base_uri' => $base_uri,
                    // You can set any number of default request options.
                    'timeout'  => $timeout,
                    'headers' => [
                        'User-Agent' => $this->plugin_name . '/' . $this->version,
                        'Content-Type' => 'application/json',
                    ]
                ]);

                return $client;
        }

        public function api_access_token() {
                $client = $this->api_client();
                $client_id = get_option( $this->plugin_name . '_api_client_id' );
                $secret_key = get_option( $this->plugin_name . '_api_secret_key' );
                try {
                $response = $client->request(
                    'POST',
                    'examity/api/token',
                    ['json' => [
                        'clientID' => $client_id,
                        'secretKey' => $secret_key,
                    ]]
                );
                $decoded_response = json_decode($response->GetBody(), false);
                return $decoded_response->authInfo->access_token;
                } catch (RequestException $e) {
                    $requestExceptionMessage = RequestExceptionMessage::fromRequestException($e);
                    error_log($requestExceptionMessage);
                } catch (\Exception $e) {
                    error_log($e);
                }
         }

}
