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
	 * Same as $version but without the periods
	 */
	public $db_version = '100';
	
	/**
	 * @var string
	 * Note that it should match the Text Domain file header in this file
	 */
	public $KSD_SLUG = 'kanzu-support';
	
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
	
	//Set-up actions and filters
	$this->setup_actions();
	
	}
	
	/**
	 * Define Kanzu Support Constants
	 */
	private function define_constants() {
		define( 'KSD_PLUGIN_FILE', __FILE__ );
		define( 'KSD_VERSION', $this->version );
		define( 'KSD_DB_VERSION', $this->db_version );
		define( 'KSD_SLUG', $this->db_version );
		
		//Store the Plugin version. We'll need this for upgrades
		if (!defined('KANZU_SUPPORT_VERSION_KEY')) {
			define('KANZU_SUPPORT_VERSION_KEY', 'kanzu_support_version');
		}
		//Store the Db version
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
		include_once( 'includes/class-kanzu-support-install.php' );
		
		//Dashboard and Administrative Functionality 
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			require_once( plugin_dir_path( __FILE__ ) . 'includes/admin/class-kanzu-support-admin.php' );
			
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
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		Kanzu_Support_Install::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}
 

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
	
		$locale = apply_filters( 'plugin_locale', get_locale(), $this->KSD_SLUG );

		load_textdomain( $this->KSD_SLUG, trailingslashit( WP_LANG_DIR ) . $this->KSD_SLUG . '/' . $this->KSD_SLUG . '-' . $locale . '.mo' );
		load_plugin_textdomain( $this->KSD_SLUG, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}
	
		/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->KSD_SLUG . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->KSD_SLUG . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	
	/**
	 * Setup Kanzu Support's actions
	 */
	private function setup_actions(){
		//add_action( 'plugins_loaded', array( $this, 'get_instance' ) );
		
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added. Leave this out for now
		//add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
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