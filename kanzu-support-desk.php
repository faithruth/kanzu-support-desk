<?php
/*
  Plugin Name: Kanzu Support Desk
  Plugin URI: http://kanzucode.com/kanzu-support-desk
  Description: A simple support desk ticketing system for your WordPress site. 
  Version: 1.0.0
  Author: Kanzu Code
  Author URI: URI: http://kanzucode.com
  License: LGPL
  Text Domain: kanzu-support-desk
 */

 // Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

//Store the Plugin version. We'll need this for upgrades
if (!defined('KANZU_SUPPORT_VERSION_KEY'))
    define('KANZU_SUPPORT_VERSION_KEY', 'kanzu_support_version');

if (!defined('KANZU_SUPPORT_VERSION_NUM'))
    define('KANZU_SUPPORT_VERSION_NUM', '1.0.0');

add_option(KANZU_SUPPORT_VERSION_KEY, KANZUSUPPORT_VERSION_NUM);
 
 
 /**
  * Added to write custom debug messages
  */
 function kanzu_support_log_me($message) {
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
} 


/**
 * Add menu items in the admin panel
 */

add_action('admin_menu', 'kanzu_support_menu_pages');

function kanzu_support_menu_pages() {
    //Add the top-level admin menu
    $page_title = 'Kanzu Support';
    $menu_title = 'Kanzu Support';
    $capability = 'manage_options';
    $menu_slug = 'kanzu-support-settings';
    $function = 'kanzu_support_settings';
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function);

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

add_filter('plugin_action_links', 'kanzu_support_plugin_action_links', 10, 2);

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
        // this case equals "kanzu-support-settings".
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=kanzu-support-settings">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}
 
 
 
 
 
 ?>