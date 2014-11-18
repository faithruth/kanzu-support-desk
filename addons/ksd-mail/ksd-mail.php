<?php
/**
 * Plugin Name:       Kanzu Support Desk Mail Add-on
 * Plugin URI:        http://kanzucode.com/kanzu-support-desk/
 * Description:       Adds capability to log new support tickets via email in Kanzu Support Desk.
 * Version:           1.0.0
 * Author:            Kanzu Code
 * Author URI:        http://kanzucode.com
 * Text Domain:       kanzu-support-desk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 *
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'KSD_Mail' ) ) :


final class KSD_Mail {

	/**
	 * @var string
	 */
	public $version = '1.0.0';
	
	
	/**
	 * @var string
	 * Note that it should match the Text Domain file header in this file
	 */
	public $ksd_slug = 'ksd-mail';
	
	/**
	 * @var Kanzu_Support_Desk The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;
	
	/**
	 * Main KanzuSupport Instance
	 *
	 * Ensures only one instance of KanzuSupport is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return KanzuSupport - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ksd-mail' ), $this->version );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ksd-mail' ), $this->version );
	}

	public function __construct(){
            //Define constants
            $this->define_constants();

            //Include required files
            $this->includes();

            //Set-up actions and filters
            $this->setup_actions();

            /*
             * Register hooks that are fired when the plugin is activated  
             * When the plugin is deleted, the uninstall.php file is loaded.
             */
            register_activation_hook( __FILE__, array( 'KSD_Mail_Install', 'activate' ) );

        }
	
	/**
	 * Define KSD Mail Constants
	 */
	private function define_constants() {
             if ( ! defined( 'KSD_MAIL_VERSION' ) ) {                
		define( 'KSD_MAIL_VERSION', $this->version );
             }
             
            if ( ! defined( 'KSD_MAIL_DIR' ) ) {
            define( 'KSD_MAIL_DIR', plugin_dir_path( __FILE__ ) );
            }  
            if ( ! defined( 'KSD_STORE_URL' ) ) {
            define( 'KSD_STORE_URL', 'http://kanzucode.com' );
            }  

	}
	
	/**
	 * Include all the files we need
	 */
	private function includes() {
            //Do installation-related work
            include_once( KSD_MAIL_DIR . '/includes/class-ksd-mail-install.php' );
            
            //The rest
            include_once( KSD_MAIL_DIR . '/includes/libraries/class-ksd-mail.php' );
            include_once( KSD_PLUGIN_DIR . '/includes/controllers/class-ksd-tickets-controller.php' );
            include_once( KSD_PLUGIN_DIR . '/includes/controllers/class-ksd-users-controller.php' );
            include_once( KSD_MAIL_DIR .  '/includes/admin/class-ksd-mail-admin.php' );
            
            //Deliver updates like pizza
            if( !class_exists( 'KSD_Mail_Updater' ) ) {
                include_once( KSD_MAIL_DIR . '/includes/extras/class-ksd-mail-updater.php' );
            }
        }
	
         /**
          * Get all settings. Settings are stored as an array
          * with key KSD_Install::$ksd_options_name
          */
         public static function get_settings(){
            $mail_settings = array();
            if( class_exists('Kanzu_Support_Desk') ){//Check that Kanzu Support Desk is active. If it is, get settings
                $base_settings = Kanzu_Support_Desk::get_settings();
                $mail_settings = $base_settings[KSD_Mail_Install::$ksd_options_name];
            }
            else{
                add_settings_error(
                    'ksd-not-active',
                    '',
                    __( 'Kanzu Support Desk must be active to use this plugin. Please activate it','kanzu-support-desk' ),
                    'error'
                    );
            }
             return $mail_settings;
         }

	
	/**
	 * Setup KSD Mail actions
	 * @since    1.0.0
	 */
	public function setup_actions(){	
            add_action( 'admin_init', array ( $this, 'do_updates' ), 0 );
	}
        
        public function do_updates() {

	// retrieve our license key from the DB //@TODO Add check, if license isn't active, deactivate plugin
        $mail_settings  =   $this->get_settings();
	$license_key    =   trim( $mail_settings[ 'ksd_mail_license_key' ] );        
        $plugin_data    =   get_plugin_data(__FILE__);
	// setup the updater
	$ksd_updater = new KSD_Mail_Updater( KSD_STORE_URL, __FILE__, array( 
			'version' 	=> KSD_MAIL_VERSION, 		// current version number
			'license' 	=> $license_key, 		// license key  
			'item_name'     => $plugin_data['Name'], 	// name of this plugin
			'author' 	=> $plugin_data['Author']       // author of this plugin
		)
	);

        }

	/**
	* Added to write custom debug messages to the debug log (wp-content/debug.log). You
	* need to turn debug on for this to work
	*/
	public static function ksd_mail_log_me($message) {
            if (WP_DEBUG === true) {
                if (is_array($message) || is_object($message)) {
                    error_log(print_r($message, true));
                } else {
                    error_log($message);
                }
                            }
        } 
 

 }

    /**
     * The main function responsible for returning the one true Kanzu_Support_Desk Instance
     * to functions everywhere.
     *
     * Use this function like you would a global variable, except without needing
     * to declare the global.
     *
     * Example: <?php $ksd = Kanzu_Support_Desk(); ?>
     *
     * @return The one true Kanzu_Support_Desk Instance
     */
    function ksd_mail() {
            return KSD_Mail::instance();
    }

 
KSD_Mail();


endif; // class_exists check
