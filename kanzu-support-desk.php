<?php
/**
 * Plugin Name:       Kanzu Support Desk
 * Plugin URI:        http://kanzucode.com/kanzu-support-desk
 * Description:       A simple support desk (ticketing) system for your WordPress site
 * Version:           1.0.0
 * Author:            Kanzu Code
 * Author URI:        http://kanzucode.com
 * Text Domain:       kanzu-support
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 *
 * @package   Kanzu_Support
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code *
 * @wordpress-plugin
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
	 */
	public $db_version = '100';
	
	/**
	 * @var string
	 * Note that it should match the Text Domain file header in this file
	 */
	public $ks_slug = 'kanzu-support';
	
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
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'kanzusupport' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'kanzusupport' ), '1.0.0' );
	}

	public function __construct(){
	// Define constants
	$this->define_constants();

	// Include required files
	$this->includes();
	
	/*
	 * Register hooks that are fired when the plugin is activated or deactivated.
	 * When the plugin is deleted, the uninstall.php file is loaded.
	 */
	register_activation_hook( __FILE__, array( 'Kanzu_Support_Install', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'Kanzu_Support_Install', 'deactivate' ) );
	
	//Set-up actions
	$this->setup_actions();
	
	}
	
	/**
	 * Define Kanzu Support Constants
	 */
	private function define_constants() {
		define( 'KS_PLUGIN_FILE', __FILE__ );
		define( 'KS_VERSION', $this->version );
		
		//Store the Plugin version. We'll need this for upgrades
		if (!defined('KANZU_SUPPORT_VERSION_KEY')) {
			define('KANZU_SUPPORT_VERSION_KEY', 'kanzu_support_version');
		}
		//Store the version in the database as an option
		add_option(KANZU_SUPPORT_VERSION_KEY, KS_VERSION);

	}
	
	/**
	 * Include all the files we need
	 */
	private function includes() {
		//Do installation-related work
		include_once( 'includes/class-kanzu-support-install.php' );
		
		//Public-Facing Functionality
		require_once( plugin_dir_path( __FILE__ ) . 'public/class-kanzu-support.php' ); 
		
		//Dashboard and Administrative Functionality 
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			require_once( plugin_dir_path( __FILE__ ) . 'admin/class-kanzu-support-admin.php' );
			add_action( 'plugins_loaded', array( 'Kanzu_Support_Admin', 'get_instance' ) );

		}
		/*
		 * When we decide to include Ajax within the dashboard, we'll change the if above to
		 *
		 * if ( is_admin() ) {
		 *   ...
		 * } 
		 */
		 
		}
	
	/**
	 * Setup Kanzu Support's actions
	 */
	private function setup_actions(){
		add_action( 'plugins_loaded', array( 'Kanzu_Support', 'get_instance' ) );
		
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
	* Added to write custom debug messages
	*/
	public function kanzu_support_log_me($message) {
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