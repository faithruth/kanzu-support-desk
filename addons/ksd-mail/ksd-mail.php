<?php
/**
 * Plugin Name:       Kanzu Support Desk Mail Addon
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
            
             if ( ! defined( 'KSD_MAIL_EXTRAS' ) ) {                
		define( 'KSD_MAIL_EXTRAS', plugin_dir_path( __FILE__ ) . '/extras' );
             }

	}
	
	/**
	 * Include all the files we need
	 */
	private function includes() {
            //Do installation-related work
            include_once( KSD_MAIL_DIR . '/includes/class-ksd-mail-install.php' );
            include_once( KSD_MAIL_DIR . '/includes/libraries/class-ksd-mail.php' );

           // if ( is_admin() ) {
                    require_once( KSD_MAIL_DIR .  '/includes/admin/class-ksd-mail-admin.php' );
           // }
        }
	
         /**
          * Get all settings. Settings are stored as an array
          * with key KSD_Install::$ksd_options_name
          */
         public static function get_settings(){
             return get_option( KSD_Mail_Install::$ksd_options_name );
         }

	
	/**
	 * Setup KSD Mail actions
	 * @since    1.0.0
	 */
	private function setup_actions(){	

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
