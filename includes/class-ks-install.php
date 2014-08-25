<?php
/** 
 * All things installation
 */
 
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//if ( ! class_exists( 'KS_Install' ) ) :


 class KS_Install{
 
 public function __construct() {
		register_activation_hook( KS_PLUGIN_FILE, array( $this, 'install_ks' ) );

		//add_action( 'admin_init', array( $this, 'install_actions' ) );
		//add_action( 'admin_init', array( $this, 'check_version' ), 5 );
		//add_action( 'in_plugin_update_message-woocommerce/woocommerce.php', array( $this, 'in_plugin_update_message' ) );
		
		add_action('admin_menu',  array( $this,'kanzu_support_menu_pages'/0);
		add_filter('plugin_action_links',  array( $this,'kanzu_support_plugin_action_links'), 10, 2);
	}
 
 function install_ks(){
	$this->ks_create_tables();

 }
 
 /**
  * Install Kanzu Support
  */
   function ks_create_tables() {
            global $wpdb;        
			$wpdb->hide_errors();		            
             
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
function kanzu_support_menu_pages() {
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

function kanzu_support_settings() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Render the HTML for the Settings page or include a file that does
}

function kanzu_support_help() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Render the HTML for the Help page or include a file that does
}

/** 
 * Handle all the tickets requests
 */
 function kanzu_support_tickets($type){
 
 }




/**
 * Add a link on the plugins page, under our plugin name, linking to our settings page. 
 */
function kanzu_support_plugin_action_links($links, $file) {
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
 function ks_update(){
 
 
 }
 
 
 }