<?php
/**
 * Holds all installation & deactivation-related functionality.  
 *
 * @package   Kanzu_Support_Desk
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

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
		//Re-direct on plugin activation
		add_action( 'admin_init', array( $this, 'redirect_to_dashboard'    ) );
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
			self::redirect_to_dashboard();
		}

	}
	
	/**
	 * Redirect to a welcome page
	 */
	public static function redirect_to_dashboard(){
		// Bail if no activation redirect transient is set
	    if ( ! get_transient( '_wc_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_wc_activation_redirect' );

		// Bail if we are waiting to install or update via the interface update/install links
		if ( get_option( '_wc_needs_update' ) == 1 || get_option( '_wc_needs_pages' ) == 1 )
			return;

		// Bail if activating from network, or bulk, or within an iFrame
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) || defined( 'IFRAME_REQUEST' ) )
			return;

		if ( ( isset( $_GET['action'] ) && 'upgrade-plugin' == $_GET['action'] ) && ( isset( $_GET['plugin'] ) && strstr( $_GET['plugin'], 'kanzu-support-desk.php' ) ) )
			return;

		wp_redirect( admin_url( 'admin.php?page='.KSD_SLUG ) );
		exit;
		
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
	

		self::ks_create_tables();
		
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
	* Install Kanzu Support
	*/
   private static function ks_create_tables() {
            global $wpdb;        
			$wpdb->hide_errors();		            
             
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); 
                $kanzusupport_tables = "
				CREATE TABLE `{$wpdb->prefix}kanzusupport_tickets` (
				`tkt_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
				`tkt_title` VARCHAR(512) NOT NULL, 
                `tkt_initial_message` TEXT NOT NULL, 
				`tkt_description` TEXT ,
				`tkt_channel` INT(10),
				`tkt_status` ENUM('OPEN','ASSIGNED','PENDING','RESOLVED'),
				`tkt_logged_by` INT NOT NULL, 
				`tkt_severity` ENUM ('URGENT', 'HIGH', 'MEDIUM','LOW'), 
				`tkt_resolution` VARCHAR(64) NOT NULL, 
				`tkt_time_logged` VARCHAR(128) NOT NULL, 
				`tkt_time_updated` VARCHAR(128) NOT NULL, 
				`tkt_private_notes` TEXT,
				`tkt_tags` VARCHAR(255),   /*Uses WordPress tags*/
				tkt_customer_rating INT(2) /*Uses NPS which rates from 0 to 10*/
				);	
				CREATE TABLE `{$wpdb->prefix}kanzusupport_replies` (
				`rep_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`rep_tkt_id` INT NOT NULL ,
				`rep_type` INT ,
				 rep_is_cc VARCHAR(200),
				 rep_is_bcc VARCHAR(200),
				 rep_date_created TIMESTAMP,
				 rep_created_by INT,
				 rep_date_modified DATETIME,
				 rep_message TEXT NOT NULL
				);				
				CREATE TABLE `{$wpdb->prefix}kanzusupport_customers` ( /*We store only what's not in the WordPress users table*/
				cust_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				cust_firstname VARCHAR(100) ,
				cust_lastname VARCHAR(100),
				cust_company_name VARCHAR(128),
				cust_phone_number VARCHAR(100),
				cust_about TEXT,
				cust_creation_date DATETIME,
				cust_created_by INT, 
				cust_lastmodification_date DATETIME,
				cust_modified_by INT
				);
				CREATE TABLE `{$wpdb->prefix}kanzusupport_assignment` (
				assign_id INT,
				assign_tkt_id INT,
				assign_assigned_to INT,
				assign_date_assigned DATETIME,
				assign_assigned_by INT
				);
				CREATE TABLE `{$wpdb->prefix}kanzusupport_attachments` (
				att_id INT
				att_name VARCHAR(100),
				att_filename VARCHAR(255),
				att_tkt_id INT,
				att_date_created DATETIME,
				att_reply_id INT
				);
				
				CREATE TABLE `{$wpdb->prefix}kanzusupport_channeltypes` (
				chantype_id INT ,
				chantype_name VARCHAR(200),
				chantype_description TEXT
				);
				
				CREATE TABLE `{$wpdb->prefix}kanzusupport_channels` (
				chan_id INT ,
				chan_chantype_id VARCHAR(200),
				chan_handle VARCHAR(200),
				chan_description TEXT
				);
			";

      dbDelta( $kanzusupport_tables );
  
 }
 
 
	

	 
	 //Will handle updates
	 private function ks_update(){
	 
	 
	 }


}

endif;

return new Kanzu_Support_Install();
