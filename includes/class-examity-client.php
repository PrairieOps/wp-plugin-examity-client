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
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
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
        // We currently only have placeholder css and js.
        //$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        //$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'define_admin_page' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_setting' );
        $this->loader->add_action( 'add_meta_boxes', $this, 'examity_client_meta' );
        $this->loader->add_action( 'save_post', $this, 'examity_client_save_meta' );

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
        // We currently only have placeholder css and js.
        //$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        //$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        $this->loader->add_action( 'examity_client_cron_api_provision', $this, 'api_provision_batch' );
        $this->loader->add_action( 'init', $this, 'examity_client_cron_scheduler' );

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

        $base_uri = get_option( $this->plugin_name . '_api_url' );
        $timeout = get_option( $this->plugin_name . '_api_timeout' );

        if ($base_uri != NULL && timeout != NULL) {

            $headers = [
                'User-Agent' => $this->plugin_name . '/' . $this->version,
                'Content-Type' => 'application/json',
            ];

            if (defined('WP_DEBUG') && true === WP_DEBUG) {
                $stack = HandlerStack::create();
                $logger = new Logger('Logger');
                $logger->pushHandler(new StreamHandler(dirname( __FILE__ ) . '/debug.log'), Logger::DEBUG);
                $stack->push(
                    Middleware::log(
                        $logger,
                        new MessageFormatter('{req_body} - {res_body}')
                    )
                );

                $client = new Client([
                    // log requests.
                    'handler' => $stack,
                    // Base URI is used with relative requests
                    'base_uri' => $base_uri,
                    // You can set any number of default request options.
                    'timeout'  => (float)$timeout,
                    'headers' => $headers
                ]);
            } else {
                $client = new Client([
                    // Base URI is used with relative requests
                    'base_uri' => $base_uri,
                    // You can set any number of default request options.
                    'timeout'  => (float)$timeout,
                    'headers' => $headers
                ]);
            }

            return $client;
        }
    }

    public function api_access_token() {

            // If the current access token is more than 55 minutes old, get a new one.
            $epoch =  new DateTime('1969-12-31T11:59:59Z');
            $api_access_token_datetime = get_option( $this->plugin_name . '_api_access_token_datetime', $epoch );
            try {
                $tz = new DateTimeZone(get_option('timezone_string'));
            } catch(\Exception $e) {
                $tz = new DateTimeZone(date_default_timezone_get());
            }
            $now = new DateTime('now', $tz);

            $diff = ($now->getTimeStamp() - $api_access_token_datetime->getTimeStamp())/60;
            if($diff > 55) {
                delete_option( $this->plugin_name . '_api_access_token' );
                delete_option( $this->plugin_name . '_api_access_datetime' );
            }

            // Try to pull the token and credentials from options.
            $api_access_token = get_option( $this->plugin_name . '_api_access_token' );
            $client_id = get_option( $this->plugin_name . '_api_client_id' );
            $secret_key = get_option( $this->plugin_name . '_api_secret_key' );

            // Return the token if it's there.
            if($api_access_token) {
                return $api_access_token;
            // Otherwise post credentials to get a token.
            } elseif ($client_id != NULL && $secret_key !=NULL) {
                $client = $this->api_client();
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
                    error_log($e->getMessage());
                } catch (\Exception $e) {
                    error_log($e);
                }
            }
    }

    public function api_user_del( $user_object ) {

        // Required fields.
        $userId = $user_object->user_email;

        // Proceed if we have the required field.
        if (filter_var($userId, FILTER_VALIDATE_EMAIL)) {

            $api_access_token = $this->api_access_token();
            $client = $this->api_client();

            try {
                $response = $client->request(
                    'DELETE',
                    'user/' . $userId,
                    ['headers' => [
                        'Authorization' => $api_access_token,
                    ]]
                );

                return $response;
            } catch (RequestException $e) {
                error_log($e->getMessage());
            } catch (\Exception $e) {
                error_log($e);
            }
        }
    }

    public function api_user_create( $user_object ) {

        // Required fields.
        $userId = $user_object->user_email;
        $firstName = $user_object->user_firstname;
        $lastName = $user_object->user_lastname;

        // Proceed if we have all required fields.
        if (filter_var($userId, FILTER_VALIDATE_EMAIL)
            && ($firstName != NULL) && ($lastName != NULL)) {

            $api_access_token = $this->api_access_token();
            $client = $this->api_client();

            $headers = [
                'Authorization' => $api_access_token,
            ];

            $json = [
                'userId' => $userId,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'emailAddress' => $userId
            ];

            $body = Psr7\stream_for(json_encode($json));

            $request = new Psr7\Request(
                    'POST',
                    'user',
                    $headers
            );

            // A not-so-useful async implementation.
            // We'd need to accumulate like api calls as promises
            // and then wait for them as a batch for this to be useful.
            try {
                $promise = $client->sendAsync($request->withBody($body));
                $promise->then(
                    function (ResponseInterface $response) {
                        return $response;
                    },
                    function (RequestException $e) {
                        error_log($e->getMessage());
                    }
                );
                $promise->wait();
            } catch (ClientException $e) {
                error_log($e->getMessage());
            } catch (\Exception $e) {
                error_log($e);
            }
        }

    }

    public function api_user_info( $post_object, $user_object ) {

        // LearnDash API call.
        // returns true if the user has access to this learndash object.
        $has_access = sfwd_lms_has_access_fn($post_object->ID, $user_object->ID);

        // LearnDash API call.
        // Courses with which this exam is associated
        $ldCourseId = learndash_get_course_id($post_object->ID);

        // Required field.
        $userId = $user_object->user_email;

        // leardash will return a course id of 0 when there isn't a match.
        // Proceed if there is a match for the object and the user has access.
        if (($ldCourseId != NULL) && $has_access
            && filter_var($userId, FILTER_VALIDATE_EMAIL)) {

            $api_access_token = $this->api_access_token();
            $client = $this->api_client();
            try {
                $response = $client->request(
                    'GET',
                    'user/' . $userId . '/info',
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
                error_log($e->getMessage());
            } catch (ClientException $e) {
                error_log($e->getMessage());
            } catch (\Exception $e) {
                error_log($e);
            }
        }
    }

    public function api_course_create( $post_object, $ldCourseId=NULL, $user_object=NULL ) {

        $blog_id = get_current_blog_id();

        if ($blog_id != NULL) {
            if ($ldCourseId == NULL) {
                // LearnDash API call.
                // Courses with which this exam is associated.
                $ldCourseId = learndash_get_course_id($post_object->ID);
            }

            if ($user_object == NULL) {
                // Set the examity instructor to the post author by default.
                $user_object = get_user_by('id', $post_object->post_author);
            }

            // Required fields.
            $userId = $user_object->user_email;
            $firstName =  $user_object->user_firstname;
            $lastName = $user_object->user_lastname;
            // Namespace the course id.
            $courseId = $blog_id . '_'  . $ldCourseId;
            // Set the course name to be the post title.
            $courseName = get_the_title($post_object);

            // leardash will return a course id of 0 when there isn't a match.
            // Proceed if we have all required fields.
            if (($ldCourseId != NULL)
                && filter_var($userId, FILTER_VALIDATE_EMAIL)
                && ($firstName != NULL) && ($lastName != NULL)
                && ($courseId != NULL) && ($courseName != NULL)) {

                $api_access_token = $this->api_access_token();
                $client = $this->api_client();

                $headers = [
                    'Authorization' => $api_access_token,
                ];
                $json = [
                    'courseId' => $courseId,
                    'courseName' => $courseName,
                    'userId' => $userId,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'emailAddress' => $userId
                ];

                $body = Psr7\stream_for(json_encode($json));

                $request = new Psr7\Request(
                        'POST',
                        'course',
                        $headers
                );

                // A not-so-useful async implementation.
                // We'd need to accumulate like api calls as promises
                // and then wait for them as a batch for this to be useful.
                try {
                $promise = $client->sendAsync($request->withBody($body));
                $promise->then(
                    function (ResponseInterface $response) {
                        return $response;
                    },
                    function (RequestException $e) {
                        error_log($e->getMessage());
                    }
                );
                $promise->wait();
                } catch (ClientException $e) {
                    error_log($e->getMessage());
                } catch (\Exception $e) {
                    error_log($e);
                }
            }
        }
    }


    public function api_course_enroll( $post_object, $user_object ) {
        $blog_id = get_current_blog_id();

        if ($blog_id != NULL) {
            // LearnDash API call.
            // returns true if the user has access to this learndash object.
            $has_access = sfwd_lms_has_access_fn($post_object->ID, $user_object->ID);

            // We need to namespace IDs to avoid collisions in a WordPress Network.
            $ldCourseId = learndash_get_course_id($post_object->ID);

            // Required fields.
            $courseId = $blog_id . '_'  . $ldCourseId;
            $userId = $user_object->user_email;

            // Not required for this API call, but required for a users in Examity.
            // If we don't have these, then the user was probably never successfully
            // Created. If the user doesn't exist, we can't enroll them.
            $firstName = $user_object->user_firstname;
            $lastName = $user_object->user_lastname;

            // leardash will return a course id of 0 when there isn't a match.
            // Proceed if there is a match for the object, the user has access
            // and we have all required fields.
            if (($ldCourseId != NULL) && $has_access && ($courseId != NULL)
                && ($firstName != NULL) && ($lastName != NULL)
                && filter_var($userId, FILTER_VALIDATE_EMAIL)) {

                $api_access_token = $this->api_access_token();
                $client = $this->api_client();

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


                // A not-so-useful async implementation.
                // We'd need to accumulate like api calls as promises
                // and then wait for them as a batch for this to be useful.
                try {
                    $promise = $client->sendAsync($request->withBody($body));
                    $promise->then(
                        function (ResponseInterface $response) {
                            return $response;
                        },
                        function (RequestException $e) {
                            error_log($e->getMessage());
                        }
                    );
                    $promise->wait();
                } catch (ClientException $e) {
                    error_log($e->getMessage());
                } catch (\Exception $e) {
                    error_log($e);
                }
            }
        }
    }

    public function api_exam_create( $post_object, $ldCourseId=NULL ) {
        $blog_id = get_current_blog_id();

        if ($blog_id != NULL) {
            if ($ldCourseId == NULL) {
                // LearnDash API call.
                // Courses with which this exam is associated.
                $ldCourseId = learndash_get_course_id($post_object->ID);
            }

            // Check to see if the author enabled provisioning for the exam.
            $is_enabled = get_post_meta( $post_object->ID, 'examity_client_sfwd_quiz_create', true);

            // Required fields.
            // We need to namespace IDs to avoid collisions in a WordPress Network.
            $courseId = $blog_id . '_'  . $ldCourseId;
            $examId = $blog_id . '_'  . $post_object->ID;

            // Set the course name to be the post title.
            $examName = get_the_title($post_object);
            // Examity doesn't like HTML entities here.
            $examName = preg_replace('/&#?[a-z0-9]+;/i', '', $examName);
            $examName = str_replace('  ', ' ', $examName);

            // The raw quiz permalink has an unresolved token.
            $quiz_permalink = get_post_permalink($post_object, true, false);
            $examURL = str_replace('%sfwd-quiz%', $post_object->post_name, $quiz_permalink);

            // Exams are limited to 2 hours, plus 15 minutes grace.
            // They are always open.
            $examDuration = 135;
            try {
                $tz = new DateTimeZone(get_option('timezone_string'));
            } catch(\Exception $e) {
                $tz = new DateTimeZone(date_default_timezone_get());
            }
            $now = new DateTime('now', $tz);

            // For whatever reason, we're getting local time
            // instead of UTC from DateTime now. Correct the offset.
            $offset = $tz->getOffset($now);
            $invert_offset = sprintf("%+d hours", (0 - ($offset/3600)));
            $now->modify($invert_offset);

            $now_plus_month = clone $now;
            $now_plus_month->modify('+5 years');
            $examStartDate = $now->format(DateTime::ISO8601);
            $examEndDate = $now_plus_month->format(DateTime::ISO8601);

            // leardash will return a course id of 0 when there isn't a match.
            // Only proceed if provisioning is enabled and there is a match
            // and we have all required fields.
            if ($is_enabled && ($ldCourseId != NULL) && ($courseId != NULL)
                && ($examId != NULL) && ($examName != NULL)
                && filter_var($examURL, FILTER_VALIDATE_URL)
                && ($examDuration != NULL) && ($examStartDate != NULL)
                && ($examEndDate != NULL)) {

                $api_access_token = $this->api_access_token();
                $client = $this->api_client();

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

                // A not-so-useful async implementation.
                // We'd need to accumulate like api calls as promises
                // and then wait for them as a batch for this to be useful.
                try {
                    $promise = $client->sendAsync($request->withBody($body));
                    $promise->then(
                        function (ResponseInterface $response) {
                            return $response;
                        },
                        function (RequestException $e) {
                            error_log($e->getMessage());
                        }
                    );
                    $promise->wait();
                } catch (ClientException $e) {
                    error_log($e->getMessage());
                } catch (\Exception $e) {
                    error_log($e);
                }
            }
        }
    }

    public function api_provision_the_post( $post_object ) {

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
            if (($ldCourseId != NULL) && $has_access) {
                // Perform Examity provisioning if this is a quiz.
                if ($post_object->post_type == 'sfwd-quiz') {

                    // Add the exam.
                    $this->api_exam_create($post_object, $ldCourseId);

                // Do some more checks if this is a course.
                } elseif ($post_object->post_type == 'sfwd-courses') {

                    // LearnDash API call.
                    // Returns array of global quizzes that are associated with the course.
                    $global_quizzes = learndash_get_global_quiz_list($ldCourseId);

                    // LearnDash API call.
                    // Returns array of quiz type course step IDs that are associated with the course.
                    $course_steps = learndash_course_get_steps_by_type( $ldCourseId, 'sfwd-quiz' );

                    // Perform Examity provisioning if this is a course with quizzes.
                    if ((count($course_steps) + count($global_quizzes)) > 0) {

                        // Get or create the user.
                        $this->api_user_info($post_object, $current_user);

                        // Make sure the associated course exists.
                        $this->api_course_create(get_post($ldCourseId));

                        // Make sure the user is enrolled in the course.
                        $this->api_course_enroll(get_post($ldCourseId), $current_user);

                        // Perform provisioning for each quiz that we find as a course step.
                        if (count($course_steps) > 0) {
                            foreach ($course_steps as $course_step) {

                                $quiz_object = get_post($course_step);

                                // Add the exam.
                                $this->api_exam_create($quiz_object, $ldCourseId);
                            }
                        }

                        // Perform provisioning for each quiz that we find as a global quiz.
                        if (count($global_quizzes) > 0) {
                            foreach ($global_quizzes as $quiz_object) {

                                // Add the exam.
                                $this->api_exam_create($quiz_object, $ldCourseId);
                            }
                        }
                    }
                }
            }
    }

    public function examity_client_cron_scheduler() {

        // If the configured interval is sooner than the next scheduled job, unschedule it.
        $interval = (int)get_option( $this->plugin_name . '_cron_interval', 43200);
        $scheduled = wp_next_scheduled( 'examity_client_cron_api_provision' );
        $schedule = time() + $interval;

        if ($scheduled != NULL && ($scheduled > $schedule)) {
            if (wp_next_scheduled ( 'examity_client_cron_api_provision' )) {
                wp_clear_scheduled_hook('examity_client_cron_api_provision' );
            }
        }

        if ($interval != NULL && $scheduled == NULL) {
            // Schedules the provisioning task to run.
            wp_schedule_single_event(time() + $interval, 'examity_client_cron_api_provision');
        }
    }

    public function api_provision_batch() {

        // Provisions all relevant objects found within all courses.

            $courses_args = array(
                'post_type'   => 'sfwd-courses',
                'numberposts' => -1
            );

            $courses = get_posts($courses_args);

            // Perform provisioning for each quiz that we find as a course step.
            if (count($courses) > 0) {
                foreach ($courses as $course_object) {

                    // We need to namespace IDs to avoid collisions in a WordPress Network.
                    $ldCourseId = learndash_get_course_id($course_object->ID);

                    // leardash will return a course id of 0 when there isn't a match.
                    if (($ldCourseId != NULL)) {

                        // LearnDash API call.
                        // Returns array of global quizzes that are associated with the course.
                        $global_quizzes = learndash_get_global_quiz_list($ldCourseId);

                        // LearnDash API call.
                        // Returns array of quiz type course step IDs that are associated with the course.
                        $course_steps = learndash_course_get_steps_by_type( $ldCourseId, 'sfwd-quiz' );

                        // Perform Examity provisioning if this is a course with quizzes.
                        if ((count($course_steps) + count($global_quizzes)) > 0) {

                            // LearnDash API call.
                            // Leverages WP User Query, and allows us to pass in arguments.
                            // Returns an array of users (including admins) with access to the course.
                            $users_args = array(
                                'fields'   => 'all',
                            );
                            $course_user_query = learndash_get_users_for_course($ldCourseId, $users_args, false);

                            if ( $course_user_query instanceof WP_User_Query ) {
                                $users = $course_user_query->get_results();
                            }

                            if (count($users) > 0) {

                                // Make sure the associated course exists.
                                $this->api_course_create($course_object, $ldCourseId);

                                // Perform provisioning for each quiz that we find as a course step.
                                if (count($course_steps) > 0) {
                                    foreach ($course_steps as $course_step) {

                                        $quiz_object = get_post($course_step);

                                        // Add the exam.
                                        $this->api_exam_create($quiz_object, $ldCourseId);
                                    }
                                }

                                // Perform provisioning for each quiz that we find as a global quiz.
                                if (count($global_quizzes) > 0) {
                                    foreach ($global_quizzes as $quiz_object) {

                                        // Add the exam.
                                        $this->api_exam_create($quiz_object, $ldCourseId);
                                    }
                                }
                                foreach ($users as $user_object) {

                                    // Get or create the user.
                                    $this->api_user_info($course_object, $user_object);

                                    // Make sure the user is enrolled in the course.
                                    $this->api_course_enroll(get_post($ldCourseId), $user_object);
                                }
                            }
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
                     <input class="button wpProQuiz_button" type="submit" name="submit" value="Examity®Access" />
                     </form>';
            return (string)$form;
        }
    }

    public function sso_form_shortcode_filter( $content ) {
        return do_shortcode($content);
    }

    public function examity_client_meta() {
            add_meta_box( 'examity_client_meta', 'Examity Client', array( $this, 'examity_client_sfwd_quiz_meta'), 'sfwd-quiz', 'normal', 'high' );
    }

    public function examity_client_sfwd_quiz_meta( $post ) {

        // Adds field to enable Examity exam provisioning.

        // Security checks
        wp_nonce_field( basename( __FILE__ ), 'examity_client_sfwd_quiz_create_nonce' );

        $examity_client_sfwd_quiz_create = checked(get_post_meta( $post->ID, 'examity_client_sfwd_quiz_create', true), 'on', false);

        $html = '<label for="examity_client_sfwd_quiz_create"><input type="checkbox" name="examity_client_sfwd_quiz_create" '
                . $examity_client_sfwd_quiz_create .

                ' /> Create exam in Examity</label>';

        echo $html;
    }

    public function examity_client_save_meta( $post_id ) {

        global $post;

        // Security checks
        if ( !isset( $_POST['examity_client_sfwd_quiz_create_nonce'] )
        || !wp_verify_nonce( $_POST['examity_client_sfwd_quiz_create_nonce'], basename( __FILE__ ) ) ) {
            return $post_id;
        }

        // Check current user permissions
        $post_type = get_post_type_object( $post->post_type );
        if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {
            return $post_id;
        }

        // Do not save the data if autosave
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if ($post->post_type == 'sfwd-quiz') {
            update_post_meta($post_id, 'examity_client_sfwd_quiz_create', $_POST['examity_client_sfwd_quiz_create']);
        }

        return $post_id;
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
        if (($post_object->post_type == 'sfwd-courses'
            || $post_object->post_type == 'sfwd-quiz') && $has_access) {

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
