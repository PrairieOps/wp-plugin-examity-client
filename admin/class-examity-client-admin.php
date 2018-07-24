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

        public function define_admin_page(){
                add_menu_page(
                  __('Examity Client', 'examity-client'),
                  __('Examity Client', 'examity-client'),
                  'manage_options',
                  'examity-client',
                  array(&$this, 'examity_client_page_callback')
                );
        }
        
        public function examity_client_page_callback(){
            include_once 'partials/examity-client-admin-display.php';
        }
        
        public function register_setting(){
            add_settings_section(
                $this->plugin_slug . '_general-section',
                __( 'General', 'examity-client' ),
                array( $this, $this->plugin_slug . '_general_line' ),
                $this->plugin_slug
            );
        
            add_settings_field(
                $this->plugin_slug . '_api_url',
                __("API Endpoint URL:", 'examity-client'),
                array( $this, 'examity_client_api_url_element' ),
                $this->plugin_slug,
                $this->plugin_slug . '_general-section',
                array( 'label_for' => $this->plugin_slug . '_api_url' )
            );
        
            register_setting($this->plugin_slug . '_general-section', $this->plugin_slug . '_api_url');
        
            add_settings_field(
                $this->plugin_slug . '_api_timeout',
                __("API Endpoint Timeout (seconds):", 'examity-client'),
                array( $this, 'examity_client_api_timeout_element' ),
                $this->plugin_slug,
                $this->plugin_slug . '_general-section',
                array( 'label_for' => $this->plugin_slug . '_api_timeout' )
            );
        
            register_setting($this->plugin_slug . '_general-section', $this->plugin_slug . '_api_timeout');

            add_settings_field(
                $this->plugin_slug . '_api_client_id',
                __("API Client ID:", 'examity-client'),
                array( $this, 'examity_client_api_client_id_element' ),
                $this->plugin_slug,
                $this->plugin_slug . '_general-section',
                array( 'label_for' => $this->plugin_slug . '_api_client_id' )
            );

            register_setting($this->plugin_slug . '_general-section', $this->plugin_slug . '_api_client_id');

            add_settings_field(
                $this->plugin_slug . '_api_secret_key',
                __("API Secret Key:", 'examity-client'),
                array( $this, 'examity_client_api_secret_key_element' ),
                $this->plugin_slug,
                $this->plugin_slug . '_general-section',
                array( 'label_for' => $this->plugin_slug . '_api_secret_key' )
            );

            register_setting($this->plugin_slug . '_general-section', $this->plugin_slug . '_api_secret_key');       

        }
 
        public function examity_client_general_line(){
            echo '<p>' . __( 'Please change the settings accordingly.', 'outdated-notice' ) . '</p>';
        }
        
        public function examity_client_api_url_element(){
            $url = get_option( $this->plugin_slug . '_api_url' );
            echo '<input type="url" name="' . $this->plugin_slug . '_api_url' . '" id="' . $this->plugin_slug . '_api_url' . '" value="' . $url . '"> ';
        }

        public function examity_client_api_timeout_element(){
            $timeout = get_option( $this->plugin_slug . '_api_timeout' );
            echo '<input type="number" name="' . $this->plugin_slug . '_api_timeout' . '" id="' . $this->plugin_slug . '_api_timeout' . '" value="' . $timeout . '"> ';
        }

        public function examity_client_api_client_id_element(){
            $client_id = get_option( $this->plugin_slug . '_api_client_id' );
            echo '<input type="text" name="' . $this->plugin_slug . '_api_client_id' . '" id="' . $this->plugin_slug . '_api_client_id' . '" value="' . $client_id . '"> ';
        }

        public function examity_client_api_secret_key_element(){
            $secret_key = get_option( $this->plugin_slug . '_api_secret_key' );
            echo '<input type="text" name="' . $this->plugin_slug . '_api_secret_key' . '" id="' . $this->plugin_slug . '_api_secret_key' . '" value="' . $secret_key . '"> ';
        }

        public function define_admin_page(){
                add_menu_page(
                  __('Examity Client', 'examity-client'),
                  __('Examity Client', 'examity-client'),
                  'manage_options',
                  'examity-client',
                  array(&$this, 'examity_client_page_callback')
                );
        }
        
        public function examity_client_page_callback(){
            include_once 'partials/examity-client-admin-display.php';
        }
        
        public function register_setting(){
            //$function = $this->option_name . '_general_line';
            $callback = 'examity_client_api_url_element';
            add_settings_section(
                $this->option_name.'_general-section',
                __( 'General', 'examity-client' ),
                array( $this, $callback ),
                $this->plugin_name
            );
        
            add_settings_field(
                $this->option_name . '_url',
                __("Text box label:", 'examity-client'),
                array( $this, $this->option_name . '_api_url_element' ),
                $this->plugin_name,
                $this->option_name.'_general-section',
                array( 'label_for' => $this->option_name . '_url' )
            );
        
            register_setting($this->option_name.'_general-section', $this->option_name . '_url');
        }
        
        public function examity_client_general_line(){
            echo '<p>' . __( 'Please change the settings accordingly.', 'outdated-notice' ) . '</p>';
        }
        
        public function examity_client_api_url_element(){
            $url = get_option( $this->option_name . '_url' );
            echo '<input type="url" name="' . $this->option_name . '_url' . '" id="' . $this->option_name . '_url' . '" value="' . $url . '"> ' . __( 'url', 'examity-client' );
        }

}
