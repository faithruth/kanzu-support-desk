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
         * The options name in the WP Db. We store all
         * options using a single options key
         */
        public static $ksd_options_name = "kanzu_support_desk";

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
	

		self::ksd_create_tables();
                self::set_default_options();
		
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
   private static function ksd_create_tables() {
            global $wpdb;        
		$wpdb->hide_errors();		            
             
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); 
                //@TODO Add foreign key constraint
                //@TODO Change assignment to assignments. Changed tkt_logged_by to assigned_by
                //@TODO Check how to tag assignments done by the system. Currently tkt_logged_by can be 0
                //@TODO Table defaults need internalization
                $kanzusupport_tables = "
				CREATE TABLE `{$wpdb->prefix}kanzusupport_tickets` (
				`tkt_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
				`tkt_subject` VARCHAR(512) NOT NULL,                                 
				`tkt_message` TEXT NOT NULL,
                                `tkt_message_excerpt` TEXT NOT NULL, 
				`tkt_channel` ENUM('STAFF','FACEBOOK','TWITTER','SUPPORT_TAB','EMAIL','CONTACT_FORM') DEFAULT 'STAFF',
				`tkt_status` ENUM('OPEN','ASSIGNED','PENDING','RESOLVED') DEFAULT 'OPEN',
				`tkt_severity` ENUM ('URGENT', 'HIGH', 'MEDIUM','LOW') DEFAULT 'LOW', 
				`tkt_resolution` VARCHAR(64) NOT NULL, /*@TODO Remove this field. Don't see its use*/
				`tkt_time_logged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
				`tkt_logged_by` BIGINT(20) NOT NULL, 
                                `tkt_assigned_to` BIGINT(20) NULL, 
				`tkt_time_updated` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP, 
				`tkt_updated_by` BIGINT(20) NOT NULL,                                 
				`tkt_private_notes` TEXT,       
				`tkt_tags` VARCHAR(255),   /*@TODO Use WordPress tags*/
				`tkt_customer_rating` INT(2), /*@TODO Use NPS scoring system which rates from 0 to 10*/
                                INDEX (`tkt_assigned_to`)
				);	
				CREATE TABLE `{$wpdb->prefix}kanzusupport_replies` (
				`rep_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`rep_tkt_id` BIGINT(20) NOT NULL ,
				`rep_type` INT ,/*@TODO To hold forwards*/
				`rep_is_cc` BOOLEAN DEFAULT FALSE,
				`rep_is_bcc` BOOLEAN DEFAULT FALSE,
				`rep_date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`rep_created_by` BIGINT(20) NOT NULL,
				`rep_date_modified` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
				`rep_message` TEXT NOT NULL,
                                 INDEX (`rep_tkt_id`)
				);				
				CREATE TABLE `{$wpdb->prefix}kanzusupport_customers` ( /*We store only what's not in the WordPress users table*/
				cust_id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				cust_user_id BIGINT(20),
                                cust_email VARCHAR(100) NOT NULL,
				cust_firstname VARCHAR(100) ,
				cust_lastname VARCHAR(100),
				cust_company_name VARCHAR(128),
				cust_phone_number VARCHAR(100),
				cust_about TEXT,
				cust_account_status ENUM('ENABLED','DISABLED') DEFAULT 'ENABLED',/*Whether account is enabled or disabled*/
				cust_creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
				cust_created_by BIGINT(20), 
				cust_lastmodification_date DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
				cust_modified_by BIGINT(20),
                                UNIQUE (cust_email)
				);
				CREATE TABLE `{$wpdb->prefix}kanzusupport_assignment` (
				assign_id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				assign_tkt_id BIGINT(20),
				assign_assigned_to BIGINT(20),
				assign_date_assigned TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				assign_assigned_by BIGINT(20),
                                INDEX (`assign_tkt_id`)
				);
				CREATE TABLE `{$wpdb->prefix}kanzusupport_attachments` (
				att_id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
				att_name VARCHAR(100),
				att_filename VARCHAR(255),
				att_tkt_id BIGINT(20),
				att_date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				att_reply_id BIGINT(20),
                                INDEX (`att_tkt_id`)
				);
                                ";

      dbDelta( $kanzusupport_tables );
                     
 }
 
             private static function set_default_options() {                    
                
                 add_option( self::$ksd_options_name, self::get_default_options() );
                    
            }
            
            /**
             * Get default settings
             */
            public static function get_default_options(){
                $user_info = get_userdata(1);//Get the admin user's information. Used to set default email
                return  array (
                        /** DB Version ********************************************************/
                        'db_version'                        => KSD_VERSION,
                     
                        /** Mail Settings ****************************************************/
                        'mail_server'                       => "mail.example.com",    
                        'mail_account'                      => "user@example.com",         
                        'mail_password'                     => null,  
                        'mail_protocol'                     => "imap",      
                        'mail_useSSL'                       => "no",         
                        'mail_validate_certificate'         => "no",
                	'mail_port'                         => "143",
                        'mail_mailbox'         	            => "INBOX",
                    
                        /** Tickets ****************************************************/
                    
                        'enable_new_tkt_notifxns'           => "yes",
                        'ticket_mail_from_name'             => $user_info->display_name,//Defaults to the admin display name 
                        'ticket_mail_from_email'            => $user_info->user_email,//Defaults to the admin email
                        'ticket_mail_subject'               => __("Your support ticket has been received","kanzu-support-desk"),
                        'ticket_mail_message'               => __("Thank you for getting in touch with us. Your support request has been opened. Please allow at least 24 hours for a reply.","kanzu-support-desk"),
                        'recency_definition'                => __("1","kanzu-support-desk"),
                        'show_support_tab'                  => "yes",
                        'tab_message_on_submit'             => __("Thank you. Your support request has been opened. Please allow at least 24 hours for a reply.","kanzu-support-desk")

                    );
            }
 
 
	

	 
	 //Will handle updates
	 private function ks_update(){
	 
	 
	 }


}

endif;

return new Kanzu_Support_Install();
