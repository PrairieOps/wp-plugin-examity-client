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
		//$this->loader->add_action( 'wp_enqueue_scripts', $this, 'sso_ajax' );
		$this->loader->add_action( 'the_post', $this, 'api_provision' );

                $this->loader->add_shortcode('examity-client-login', $this, 'sso_form_shortcode');
		$this->loader->add_filter( 'init', $this, 'sso_form_shortcode_filter' );

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

             // If the current access token is more than 55 minutes old, get a new one.
             $epoch =  new DateTime('1969-12-31T11:59:59Z');
             $api_access_token_datetime = get_option( $this->plugin_name . '_api_access_token_datetime', $epoch );
             $now = new DateTime('NOW');
             $diff = ($now->getTimeStamp() - $api_access_token_datetime->getTimeStamp())/60;
             if($diff > 55) {
                 delete_option( $this->plugin_name . '_api_access_token' );
                 delete_option( $this->plugin_name . '_api_access_datetime' );
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
                 $api_access_token_datetime = new DateTime($decoded_response->timeStamp);

                 // Update the option with the current token.
                 update_option( $this->plugin_name . '_api_access_token', $api_access_token );
                 update_option( $this->plugin_name . '_api_access_token_datetime', $api_access_token_datetime );

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

         public function api_user_del( $user_object ) {

             $api_access_token = $this->api_access_token();
             $client = $this->api_client();

             try {
                 $response = $client->request(
                     'DELETE',
                     'user/' . $user_object->user_email,
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

         public function api_user_create( $user_object ) {

            $api_access_token = $this->api_access_token();
            $client = $this->api_client();

            $headers = [
                'Authorization' => $api_access_token,
            ];

            $json = [
                'userId' => $user_object->user_email,
                'firstName' => $user_object->user_firstname,
                'lastName' => $user_object->user_lastname,
                'emailAddress' => $user_object->user_email
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

         public function api_user_info( $post_object, $user_object ) {

             // LearnDash API call.
             // returns true if the user has access to this learndash object.
             $has_access = sfwd_lms_has_access_fn($post_object->ID, $user_object->ID);

             // LearnDash API call.
	     // Courses with which this exam is associated
             $ldCourseId = learndash_get_course_id($post_object->ID);

             // leardash will return a course id of 0 when there isn't a match.
             // Proceed if there is a match for the object and the user has access.
             if ($ldCourseId > 0 && $has_access) {

                 $api_access_token = $this->api_access_token();
                 $client = $this->api_client();
                 try {
                     $response = $client->request(
                         'GET',
                         'user/' . $user_object->user_email . '/info',
                         ['headers' => [
                             'Authorization' => $api_access_token,
                         ]]
                     );

                     $decoded_response = json_decode($response->GetBody(), false);

                     if ($decoded_response->message == 'User details not found.') {
                         // register the user if they don't exist
                         $this->api_user_create( $user_object );
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

             // We need to namespace IDs to avoid collisions in a WordPress Network.
             $ldCourseId = learndash_get_course_id($post_object->ID);

             // leardash will return a course id of 0 when there isn't a match.
             if ($ldCourseId > 0) {
                 $api_access_token = $this->api_access_token();
                 $client = $this->api_client();

                 // Set the examity instructor to be the post author.
                 $instructor = get_user_by('id', $post_object->post_author);

                 $courseId = get_current_blog_id() . '_'  . $ldCourseId;
    
                 // Set the course name to be the post title.
                 $courseName = get_the_title($post_object);
    
                 $headers = [
                     'Authorization' => $api_access_token,
                 ];
                 $json = [
                     'courseId' => $courseId,
                     'courseName' => $courseName,
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


         public function api_course_enroll( $post_object, $user_object ) {

             // LearnDash API call.
             // returns true if the user has access to this learndash object.
             $has_access = sfwd_lms_has_access_fn($post_object->ID, $user_object->ID);

             // We need to namespace IDs to avoid collisions in a WordPress Network.
             $ldCourseId = learndash_get_course_id($post_object->ID);

             // leardash will return a course id of 0 when there isn't a match.
             // Proceed if there is a match for the object and the user has access.
             if ($ldCourseId > 0 && $has_access) {
                 $api_access_token = $this->api_access_token();
                 $client = $this->api_client();

                 $courseId = get_current_blog_id() . '_'  . $ldCourseId;
    
                 $userId = $user_object->user_email;
                 
                 $headers = [
                     'Authorization' => $api_access_token,
                 ];
                 $json = [
                     'courseId' => $courseId,
                     'userId' => $userId,
                 ];
                 $body = Psr7\stream_for(json_encode($json));
    
                 $request = new Psr7\Request(
                         'POST',
                         'course/' . $courseId . '/user/' . $userId,
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

         public function api_exam_create( $post_object ) {

             // LearnDash API call.
	     // Courses with which this exam is associated
             $ldCourseId = learndash_get_course_id($post_object->ID);

             // leardash will return a course id of 0 when there isn't a match.
             if ($ldCourseId > 0) {
                 $api_access_token = $this->api_access_token();
                 $client = $this->api_client();

                 // We need to namespace IDs to avoid collisions in a WordPress Network.
                 $courseId = get_current_blog_id() . '_'  . $ldCourseId;
	         $examId = get_current_blog_id() . '_'  . $post_object->ID;

                 // Set the course name to be the post title.
	         $examName = get_the_title($post_object);
	         $examURL = get_post_permalink($post_object, true, false);

                 // Exams are limited to 2 hours, plus 15 minutes grace.
                 // They are always open.
	         $examDuration = 135;
                 $examStartDate = '1969-12-31T23:59Z'; 
                 $examEndDate = '2999-12-31T23:59Z'; 
	            	 
                 $headers = [
                     'Authorization' => $api_access_token,
                 ];
                 $json = [
                     'courseId' => $courseId,
                     'examId' => $examId,
                     'examName' => $examName,
                     'examURL' => $examURL,
                     'examDuration' => $examDuration,
                     'examStartDate' => $examStartDate,
                     'examEndDate' => $examEndDate,
                 ];
                 $body = Psr7\stream_for(json_encode($json));

                 $request = new Psr7\Request(
                         'POST',
                         'course/' . $courseId . '/exam',
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

         public function api_provision( $post_object ) {

             $current_user = wp_get_current_user();

             // LearnDash API call.
             // returns true if the user has access to this learndash object.
             $has_access = sfwd_lms_has_access_fn($post_object->ID, $current_user->ID);

             // We need to namespace IDs to avoid collisions in a WordPress Network.
             $ldCourseId = learndash_get_course_id($post_object->ID);

             // leardash will return a course id of 0 when there isn't a match.
             // Proceed if there is a match for the object and the user has access.
             // This may seem redundant since some API calls already have
             // this auth logic, but not all of them do. Wrapping it here
             // keeps us from making API calls when anonymous/unenrolled users
             // browse the site.
             if ($ldCourseId > 0 && $has_access) {
                 // Perform Examity provisioning if this is a quiz.
                 if ($post_object->post_type == 'sfwd-quiz') {

                     // Add the exam.
                     $this->api_exam_create($post_object);

                 // Perform Examity provisioning if this is a course.
                 } elseif ($post_object->post_type == 'sfwd-courses') {

                     // Get or create the user.
                     $this->api_user_info($post_object, $current_user);

                     // Make sure the associated course exists.
                     $this->api_course_create(get_post($ldCourseId));
    
                     // Make sure the user is enrolled in the course.
                     $this->api_course_enroll(get_post($ldCourseId), $current_user);
    
                     // LearnDash API call.
                     // Returns array of global quizzes that are associated with the course.
                     $global_quizzes = learndash_get_global_quiz_list($ldCourseId);

                     // LearnDash API call.
                     // Returns array of quiz type course step IDs that are associated with the course.
                     $course_steps = learndash_course_get_steps_by_type( $ldCourseId, 'sfwd-quiz' );

                     // Perform provisioning for each quiz that we find as a course step.
                     if (count($course_steps) > 0) {
                         foreach ($course_steps as $course_step) {

                             $quiz_object = get_post($course_step);

                             // Add the exam.
                             $this->api_exam_create($quiz_object);
                         }
                     }

                     // Perform provisioning for each quiz that we find as a global quiz.
                     if (count($global_quizzes) > 0) {
                         foreach ($global_quizzes as $quiz_object) {

                             // Add the exam.
                             $this->api_exam_create($quiz_object);
                         }
                     }
                 }
             }
         }


         public function sso_form_shortcode() {

             // Provides a visible button that POSTs to the Examity SSO login form.

             // This may get called outside the loop.
             global $wp_query;
             $post_object = $wp_query->post;
             $current_user = wp_get_current_user();

             // LearnDash API call.
             // returns true if the user has access to this learndash object.
             $has_access = sfwd_lms_has_access_fn($post_object->ID, $current_user->ID);

             // Render Examity sign in form if the user has access to the object.
             if ($has_access) {

                 $sso_url = get_option( $this->plugin_name . '_sso_url', 'http://localhost/changeme' );
                 $sso_encryption_key = get_option( $this->plugin_name . '_sso_encryption_key', 'changeme' );
                 $sso_initialization_vector = hex2bin(get_option( $this->plugin_name . '_sso_initialization_vector', '0000000000000000' ));
                 $payload = $this->sso_encrypt($current_user->user_email, $sso_encryption_key, $sso_initialization_vector);
                 $form = '<form action="' . $sso_url . '" method="POST" name="login">
                          <input type="hidden" name="userName" value="' . $payload . '" />
                          <input class="button wpProQuiz_button" type="submit" name="submit" value="Examity®Access" >
                          </form>';
                 return (string)$form;
             }
         }

         public function sso_form_shortcode_filter( $content ) {
             return do_shortcode($content);
         }

         public function sso_ajax() {

             // Provides an AJAX POST to the Examity SSO login form.

             // This gets called outside the loop.
             global $wp_query;
             $post_object = $wp_query->post;
             $current_user = wp_get_current_user();

             // LearnDash API call.
             // returns true if the user has access to this learndash object.
             $has_access = sfwd_lms_has_access_fn($post_object->ID, $current_user->ID);

             // Perform Examity sign in if this is a course or quiz and the user has access to the object.
             if (($post_object->post_type == 'sfwd-courses' || $post_object->post_type == 'sfwd-quiz') && $has_access) {
                 $sso_url = get_option( $this->plugin_name . '_sso_url', 'http://localhost/changeme' );
                 $sso_encryption_key = get_option( $this->plugin_name . '_sso_encryption_key', 'changeme' );
                 $sso_initialization_vector = hex2bin(get_option( $this->plugin_name . '_sso_initialization_vector', '0000000000000000' ));
                 $payload = $this->sso_encrypt($current_user->user_email, $sso_encryption_key, $sso_initialization_vector);

                 if ( ! wp_script_is( 'jquery', 'done' ) ) {
                   wp_enqueue_script( 'jquery' );
                 }

                 wp_add_inline_script( 'jquery-migrate', 'jQuery(document).ready(function( $ ){
                     $.post( "' . $sso_url . '", { userName: "'. $payload . '" })
                 });' );
             }
         }

         public function sso_encrypt( $plaintext, $key, $iv ) {

               // Largely inspired by the PHP mycrypt docs:
               // https://secure.php.net/manual/en/function.mcrypt-encrypt.php
               $mcrypt_cipher = MCRYPT_3DES;
               $mcrypt_mode = MCRYPT_MODE_CBC;

               // Ideally, we'd create a random IV to use with CBC encoding.
               // But in reality, we need to use a fixed IV, which is basically
               // useless.
               // $iv_size = mcrypt_get_iv_size($mcrypt_cipher, $mcrypt_mode);
               // $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
 
               // We've got to deal with .NET PKCS7 data padding.
               $block_size = mcrypt_get_block_size($mcrypt_cipher, $mcrypt_mode);
               $plaintext_padding = $block_size - (strlen($plaintext) % $block_size);
               $plaintext .= str_repeat(chr($plaintext_padding), $plaintext_padding);

               // 3DES key must be 24 characters long.
               $key_padding = substr($key,0,8);
               while(strlen($key) < 24){
                   $key .= $key_padding;
               }

               // Encrypt.
               $ciphertext = mcrypt_encrypt($mcrypt_cipher, $key, $plaintext, $mcrypt_mode, $iv);

               // Ideally, we'd prepend the IV for it to be available for
               // decryption, but there's no parsing for that in Examity.
               //$ciphertext = $ciphertext . $iv;

               // Encode the result so it can be represented by a string.
               $ciphertext_base64 = base64_encode($ciphertext);

               return $ciphertext_base64;

         }

}
