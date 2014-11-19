<?php
/**
 * Plugin Name:       Kanzu Support Desk - Mail
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
	public $ksd_mail_slug = 'ksd-mail';
        
        /**
         * The options key in the KSD settings array in the WP Db. We store all
         * KSD settings in a single array. In that array, KSD Mail settings 
         * are stored as an array with this key
         */
        private $ksd_mail_options_name = "ksd_mail";
        
        /**
         * Hold admin notices
         */
        public static $ksd_mail_admin_notices = array();
	
	/**
	 * @var Kanzu_Support_Desk The single inst  ance of the class
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
            
           if ( ! defined( 'KSD_MAIL_SLUG' ) ) {                
                define( 'KSD_MAIL_SLUG', $this->ksd_mail_slug);           
            }                
             
            if ( ! defined( 'KSD_MAIL_PLUGIN_URL' ) ) {
                define( 'KSD_MAIL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
             } 
            if ( ! defined( 'KSD_MAIL_PLUGIN_FILE' ) ) {
                define( 'KSD_MAIL_PLUGIN_FILE',  __FILE__ );
            } 
            if ( ! defined( 'KSD_MAIL_OPTIONS_KEY' ) ) {
                define( 'KSD_MAIL_OPTIONS_KEY',  $this->ksd_mail_options_name );
            } 
	}
	
	/**
	 * Include all the files we need
	 */
	private function includes() {
            
            //Do installation-related work
            include_once( KSD_MAIL_DIR . '/includes/class-ksd-mail-install.php' );
            
            //Deliver updates like pizza
            if( !class_exists( 'KSD_Mail_Updater' ) ) {
                include_once( KSD_MAIL_DIR . '/includes/extras/class-ksd-mail-updater.php' );
            }
            //The rest
            include_once( KSD_MAIL_DIR . '/includes/libraries/class-ksd-mail.php' );
            include_once( KSD_PLUGIN_DIR . '/includes/controllers/class-ksd-tickets-controller.php' );
            include_once( KSD_PLUGIN_DIR . '/includes/controllers/class-ksd-users-controller.php' );
            include_once( KSD_MAIL_DIR .  '/includes/admin/class-ksd-mail-admin.php' );
            

        }
	
         /**
          * Get all settings. Settings are stored as an array
          * with key KSD_OPTIONS_KEY
          */
         public static function get_settings(){
            $mail_settings = array();
             if( self::is_KSD_active() ){//Check that Kanzu Support Desk is active. If it is, get settings
                $base_settings = get_option( KSD_OPTIONS_KEY );
                $mail_settings = $base_settings[ KSD_MAIL_OPTIONS_KEY ];
                return $mail_settings;
            }
            else{
                exit();
            }            
         }
         /**
          * Update KSD Mail Settings
          * @param array $updated_mail_settings Updated KSD settings
          * @return boolean
          */
         public static function update_settings( $updated_mail_settings ){
              if( self::is_KSD_active() ){//Check that Kanzu Support Desk is active. If it is, get settings
                  $base_settings = get_option( KSD_OPTIONS_KEY );
                  $base_settings[ KSD_MAIL_OPTIONS_KEY ] = $updated_mail_settings;
                  return update_option( KSD_OPTIONS_KEY, $base_settings);                   
              }
              else{
                exit();
            }
         }
         
         /**
          * Check whether Kanzu Support Desk is active or not
          * @return boolean 
          */
         public static function is_KSD_active (){
             if( class_exists('Kanzu_Support_Desk') ){
                 return true;
             }  
             else{
                 $this->ksd_mail_admin_notices = array( 
                     'error' => __( 'Kanzu Support Desk must be active to use this plugin. Please activate it first','kanzu-support-desk' )
                 );
                return false;
            }
         }

	
	/**
	 * Setup KSD Mail actions
	 * @since    1.0.0
	 */
	public function setup_actions(){	
            
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
