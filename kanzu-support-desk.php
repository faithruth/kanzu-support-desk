<?php
/**
 * Plugin Name:       Kanzu Support Desk
 * Plugin URI:        http://kanzucode.com/kanzu-support-desk
 * Description:       A simple support desk (ticketing) system for your WordPress site
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

if ( !class_exists( 'Kanzu_Support_Desk' ) ) :


final class Kanzu_Support_Desk {

	/**
	 * @var string
	 */
	public $version = '1.0.0';
	
	/**
	 * @var string
	 * Same as $version but without the periods
	 */
	public $db_version = '100';
	
	/**
	 * @var string
	 * Note that it should match the Text Domain file header in this file
	 */
	public $ks_slug = 'kanzu-support-desk';
	
	/**
	 * @var KanzuSupport The single instance of the class
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
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'kanzu-support-desk' ), $this->version );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'kanzu-support-desk' ), $this->version );
	}

	public function __construct(){
	//Define constants
	$this->define_constants();

	//Include required files
	$this->includes();
	
	/*
	 * Register hooks that are fired when the plugin is activated or deactivated.
	 * When the plugin is deleted, the uninstall.php file is loaded.
	 */
	register_activation_hook( __FILE__, array( 'Kanzu_Support_Install', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'Kanzu_Support_Install', 'deactivate' ) );
	
	//Set-up actions and filters
	$this->setup_actions();
	}
	
	/**
	 * Define Kanzu Support Constants
         * @TODO Save version and DB version in the array we use to store
         * KSD variables
	 */
	private function define_constants() {
            
            if ( ! defined( 'KSD_PLUGIN_FILE' ) ) {
		define( 'KSD_PLUGIN_FILE', __FILE__ );
            }
             if ( ! defined( 'KSD_VERSION' ) ) {                
		define( 'KSD_VERSION', $this->version );
             }
              if ( ! defined( 'KSD_DB_VERSION' ) ) {                
		define( 'KSD_DB_VERSION', $this->db_version );
              }
               if ( ! defined( 'KSD_SLUG' ) ) {                
		define( 'KSD_SLUG', $this->ks_slug );           
               }                
                if ( ! defined( 'KSD_PLUGIN_DIR' ) ) {
		define( 'KSD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
                }                
                if ( ! defined( 'KSD_PLUGIN_URL' ) ) {
                define( 'KSD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
                 }                
		//Store the Plugin version. We'll need this for upgrades
		if (!defined('KANZU_SUPPORT_VERSION_KEY')) {
			define('KANZU_SUPPORT_VERSION_KEY', 'kanzu_support_version');
		}
		//Store the Db version. Might remove Db version altogether & work with just $version
		if (!defined('KANZU_SUPPORT_DB_VERSION_KEY')) {
			define('KANZU_SUPPORT_DB_VERSION_KEY', 'kanzu_support_db_version');
		}
		//Store the version in the database as an option
		add_option(KANZU_SUPPORT_VERSION_KEY, KSD_VERSION);
		add_option(KANZU_SUPPORT_DB_VERSION_KEY, KSD_DB_VERSION);
	}
	
	/**
	 * Include all the files we need
	 */
	private function includes() {
		//Do installation-related work
		include_once( 'includes/class-ksd-install.php' );
		
		//Dashboard and Administrative Functionality 
		if ( is_admin() ) {
			require_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-admin.php' );
			
		}
                //The front-end
                require_once( KSD_PLUGIN_DIR .  'includes/frontend/class-ksd-frontend.php' );
		 
		}
		
	

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
	
		$locale = apply_filters( 'plugin_locale', get_locale(), KSD_SLUG );

		load_textdomain( KSD_SLUG, trailingslashit( WP_LANG_DIR ) . KSD_SLUG . '/' . KSD_SLUG . '-' . $locale . '.mo' );
		load_plugin_textdomain( KSD_SLUG, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}
	
	/**
	 * Register and enqueue public-facing style sheet.
         * Be sure to use the word 'public' in the identifier to distinguish
         * them from the admin-side scripts and prevent collision
	 * @since    1.0.0
	 */
	public function enqueue_public_styles() {
		//wp_enqueue_style( KSD_SLUG . '-plugin-public-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
         * Be sure to use the word 'public' in the identifier to distinguish
         * them from the admin-side scripts and prevent collision
	 *
	 * @since    1.0.0
	 */
	public function enqueue_public_scripts() {		
		//wp_enqueue_script( KSD_SLUG . '-plugin-public-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), $this->version );
		
	}

	
	/**
	 * Setup Kanzu Support's actions
	 * @since    1.0.0
	 */
	private function setup_actions(){
			
		

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added. Leave this out for now
		//add_action( 'wpmu_new_blog', array( Kanzu_Support_Install, 'activate_new_site' ) );
		
                //Load public-facing styles and JS. Commented out for now; not required
               // add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_public_styles' ) );
		//add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
                
		/* Define custom functionality. Commented out for now
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		//add_action( '@TODO', array( $this, 'action_method_name' ) );
		//add_filter( '@TODO', array( $this, 'filter_method_name' ) );



	}	
	
	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define our action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define our filter hook callback here
	}
	

	/**
	* Added to write custom debug messages to the debug log (wp-content/debug.log). You
	* need to turn debug on for this to work
	*/
	public static function kanzu_support_log_me($message) {
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
function kanzu_support_desk() {
	return Kanzu_Support_Desk::instance();
}

 
kanzu_support_desk();


endif; // class_exists check
