<?php
/**
 * The Kanzu Support Desk
 *
 * A simple support desk (ticketing) system for your WordPress site
 *
 * @package   Kanzu_Support
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 *
 * @wordpress-plugin
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
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-kanzu-support.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Kanzu_Support', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Kanzu_Support', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Kanzu_Support', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * When we want to include Ajax within the dashboard, we'll change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-kanzu-support-admin.php' );
	add_action( 'plugins_loaded', array( 'Kanzu_Support_Admin', 'get_instance' ) );

}
