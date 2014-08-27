<?php
/**
 * Holds all installation & deactivation-related functionality.  
 *
 * @package   Kanzu_Support
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Kanzu_Support_Install' ) ) :

class Kanzu_Support_Install {



	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
	}
 

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();

				}

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

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
		self::single_activate();
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
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
	
		add_action('admin_menu',  array( $this,'kanzu_support_menu_pages'),0);
		add_filter('plugin_action_links',  array( $this,'kanzu_support_plugin_action_links'), 10, 2);
		$this->ks_create_tables();
		
		//add_action( 'admin_init', array( $this, 'install_actions' ) );
		//add_action( 'admin_init', array( $this, 'check_version' ), 5 );
		//add_action( 'in_plugin_update_message-kanzusupport/kanzusupport.php', array( $this, 'in_plugin_update_message' ) );
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = kanzu_support_desk()->ks_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	* Install Kanzu Support
	*/
   private function ks_create_tables() {
            global $wpdb;        
			//$wpdb->hide_errors();		            
             
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); 

                $kanzusupport_tables = "
				CREATE TABLE `{$wpdb->prefix}kanzusupport_tickets` (
				`primkey` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
				`title` VARCHAR(512) NOT NULL, `initial_message` TEXT NOT NULL, 
				`user_id` INT NOT NULL, `email` VARCHAR(256) NOT NULL, 
				`assigned_to` INT NOT NULL DEFAULT '0', 
				`severity` VARCHAR(64) NOT NULL, 
				`resolution` VARCHAR(64) NOT NULL, 
				`time_posted` VARCHAR(128) NOT NULL, 
				`last_updated` VARCHAR(128) NOT NULL, 
				`last_staff_reply` VARCHAR(128) NOT NULL, 
				`target_response_time` VARCHAR(128) NOT NULL,
                `type` VARCHAR( 255 ) NOT NULL
				);	
				CREATE TABLE `{$wpdb->prefix}kanzusupport_replies` (
				`primkey` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`ticket_id` INT NOT NULL ,
				`user_id` INT NOT NULL ,
				`timestamp` VARCHAR( 128 ) NOT NULL ,
				`message` TEXT NOT NULL
				);				
			";

      dbDelta( $kanzusupport_tables );
  
 }
 
 
	/**
	 * Add menu items in the admin panel
	 */
	private function kanzu_support_menu_pages() {
    //Add the top-level admin menu
    $page_title = 'Kanzu Support';
    $menu_title = 'Kanzu Support';
    $capability = 'manage_options';
    $menu_slug = 'kanzu-support';
    $function = 'kanzu_support_settings';
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function);
    
	//Add the ticket pages
	$ticket_types = array();
	$ticket_types['ks-my-unresolved']='My unresolved tickets';
	$ticket_types['ks-all-tickets']='All tickets';
	$ticket_types['ks-unassigned']='Unassigned tickets';
	$ticket_types['ks-recently-updated']='Recently updated';
	$ticket_types['ks-recently-resolved']='Recently resolved';
	$ticket_types['ks-closed']='Closed';	
    
	foreach ( $ticket_types as $submenu_slug => $submenu_title ) {
		$submenu_function = 'kanzu_support_tickets';
		add_submenu_page($menu_slug, $page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
	}
	
	// Add submenu page with same slug as parent to ensure no duplicates
    $sub_menu_title = 'Settings';
    add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function);

    // Now add the submenu page for Help
    $submenu_page_title = 'Kanzu Support Help';
    $submenu_title = 'Help';
    $submenu_slug = 'kanzu-support-help';
    $submenu_function = 'kanzu_support_help';
    add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
	
	}

	private function kanzu_support_settings() {
		if (!current_user_can('manage_options')) {
			wp_die('You do not have sufficient permissions to access this page.');
		}

    // Render the HTML for the Settings page or include a file that does
	}

	private function kanzu_support_help() {
		if (!current_user_can('manage_options')) {
			wp_die('You do not have sufficient permissions to access this page.');
		}

    // Render the HTML for the Help page or include a file that does
	}

	/** 
	 * Handle all the tickets requests
	 */
	 private function kanzu_support_tickets($type){
	 
	 }




	/**
	 * Add a link on the plugins page, under our plugin name, linking to our settings page. 
	 */
	private function kanzu_support_plugin_action_links($links, $file) {
		static $this_plugin;

		if (!$this_plugin) {
			$this_plugin = plugin_basename(__FILE__);
		}

		if ($file == $this_plugin) {
			// The "page" query string value must be equal to the slug
			// of the Settings admin page we defined earlier, which in
			// this case equals "kanzu-support".
			$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=kanzu-support">Settings</a>';
			array_unshift($links, $settings_link);
		}

		return $links;
	}

	 
	 //Will handle updates
	 private function ks_update(){
	 
	 
	 }


}

endif;

return new Kanzu_Support_Install();
