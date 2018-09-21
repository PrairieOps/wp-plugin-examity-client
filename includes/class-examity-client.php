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
use GuzzleHttp\Psr7;
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
                $this->loader->add_action( 'admin_init', $this, 'api_access_token' );

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
                $this->loader->add_action( 'init', $this, 'api_access_token' );
                $this->loader->add_action( 'the_post', $this, 'api_user_info' );
                $this->loader->add_action( 'the_post', $this, 'api_course_create' );

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

                $base_uri = get_option( $this->plugin_name . '_api_url', 'http://localhost/changeme' );
                $timeout = get_option( $this->plugin_name . '_api_timeout', '1' );
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

                // If the current access token is more than 30 minutes old, get a new one.
                $api_access_token_timestamp = get_option( $this->plugin_name . '_api_access_token_timestamp', '1969-12-31T11:59:59Z' );
                $now = new DateTime('NOW');
                $api_access_token_datetime = new DateTime($api_access_token_timestamp);
                $diff = $now->diff($api_access_token_datetime)->format('%i');

                if($diff > 30) {
                    delete_option( $this->plugin_name . '_api_access_token' );
                }

                // Try to pull the token from options.
                $api_access_token = get_option( $this->plugin_name . '_api_access_token' );

                // Return it if it's there.
                if($api_access_token) {
                    return $api_access_token;
                // Otherwise post credentials to get a token. 
                } else {
                    $client = $this->api_client();
                    $client_id = get_option( $this->plugin_name . '_api_client_id', 'changeme' );
                    $secret_key = get_option( $this->plugin_name . '_api_secret_key', 'changeme' );
                    try {
                    $response = $client->request(
                        'POST',
                        'token',
                        ['json' => [
                            'clientID' => $client_id,
                            'secretKey' => $secret_key,
                        ]]
                    );

                    $decoded_response = json_decode($response->GetBody(), false);
                    $api_access_token = $decoded_response->authInfo->access_token;
                    $api_access_token_timestamp = $decoded_response->timeStamp;

                    // Update the option with the current token.
                    update_option( $this->plugin_name . '_api_access_token', $api_access_token );
                    update_option( $this->plugin_name . '_api_access_token_timestamp', $api_access_token_timestamp );

                    // Return the current token.
                    return $api_access_token;
                    } catch (RequestException $e) {
                        $requestExceptionMessage = RequestExceptionMessage::fromRequestException($e);
                        error_log($requestExceptionMessage);
                    } catch (\Exception $e) {
                        error_log($e);
                    }
                }
         }

         public function api_user_del( $current_user ) {
            $api_access_token = $this->api_access_token();
            $client = $this->api_client();

            try {
                $response = $client->request(
                    'DELETE',
                    'user/' . $current_user->user_email,
                    ['headers' => [
                        'Authorization' => $api_access_token,
                    ]]
                );

                return $response;
             } catch (RequestException $e) {
                 $requestExceptionMessage = RequestExceptionMessage::fromRequestException($e);
                 error_log($requestExceptionMessage);
             } catch (\Exception $e) {
                 error_log($e);
             }
         }

         public function api_user_create( $current_user ) {

            $api_access_token = $this->api_access_token();
            $client = $this->api_client();

            $headers = [
                'Authorization' => $api_access_token,
            ];

            $json = [
                'userId' => $current_user->user_email,
                'firstName' => $current_user->user_firstname,
                'lastName' => $current_user->user_lastname,
                'emailAddress' => $current_user->user_email
            ];

            $body = Psr7\stream_for(json_encode($json));
    
            $request = new Psr7\Request(
                    'POST',
                    'user',
                    $headers
            );


            try {
                $response = $client->send($request->withBody($body));
                return $response;
            } catch (RequestException $e) {
                 $requestExceptionMessage = RequestExceptionMessage::fromRequestException($e);
                 error_log($requestExceptionMessage);
            } catch (\Exception $e) {
                 error_log($e);
            }

         }

         public function api_user_info( $post_object ) {
             if ($post_object->post_type == 'sfwd-quiz') {

                 $current_user = wp_get_current_user();

                 $api_access_token = $this->api_access_token();
                 $client = $this->api_client();
                 try {
                     $response = $client->request(
                         'GET',
                         'user/' . $current_user->user_email . '/info',
                         ['headers' => [
                             'Authorization' => $api_access_token,
                         ]]
                     );

                     $decoded_response = json_decode($response->GetBody(), false);

                     if ($decoded_response->message == 'User details not found.') {
                         // register the user if they don't exist
                         $this->api_user_create( $current_user );
                     // Obviously this is debugging behavior to drop.
                     //} elseif ($decoded_response->userInfo->userId == $current_user->user_email) {
                     } elseif ($decoded_response == $current_user->user_email) {
                         // print the user details to the page.
                         echo print_r($decoded_response->userInfo);
                         // delete the user.
                         //$this->api_user_del( $current_user );
                     } else {
                         // Return the response.
                         return $response;
                     }
                 } catch (RequestException $e) {
                     $requestExceptionMessage = RequestExceptionMessage::fromRequestException($e);
                     error_log($requestExceptionMessage);
                 } catch (\Exception $e) {
                     error_log($e);
                 }
             }
         }

         public function api_course_create( $post_object ) {

            $api_access_token = $this->api_access_token();
            $client = $this->api_client();

            // @TODO replace with actual logic to query course instructor.
            $instructor = wp_get_current_user();

            $courseId = get_current_blog_id() . '_'  . $post_object->ID;

            $headers = [
                'Authorization' => $api_access_token,
            ];
            $json = [
                'courseId' => $courseId,
                'courseName' => $post_object->post_name,
                'userId' => $instructor->user_email,
                'firstName' => $instructor->user_firstname,
                'lastName' => $instructor->user_lastname,
                'emailAddress' => $instructor->user_email
            ];
            $body = Psr7\stream_for(json_encode($json));

            $request = new Psr7\Request(
                    'POST',
                    'course',
                    $headers
            );

            try {
                $response = $client->send($request->withBody($body));
                return $response;
            } catch (RequestException $e) {
                 $requestExceptionMessage = RequestExceptionMessage::fromRequestException($e);
                 error_log($requestExceptionMessage);
            } catch (\Exception $e) {
                 error_log($e);
            }

         }

}
