<?php
/*
 * Plugin Name: Kanzu Support Desk
 * Plugin URI: http://kanzucode.com/kanzu-support-desk
 * Description: A simple support desk (ticketing) system for your WordPress site. 
 * Version: 1.0.0
 * Author: Kanzu Code
 * Author URI: URI: http://kanzucode.com
 * License: GPL2
 * Text Domain: kanzu-support-desk
 * 
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//if ( ! class_exists( 'KanzuSupport' ) ) :


/**
 * Main Kanzu Support Desk Class
 *
 * @class KanzuSupport
 * @version	1.0.0
 */
final class KanzuSupportDesk {

	/**
	 * @var string
	 */
	public $version = '1.0.0';

	
	public function __construct() {
		// Auto-load classes on demand
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		// Define constants
		$this->define_constants();

		// Include required files
		$this->includes();
	}
	
	/**
	 * Define KS Constants
	 */
	private function define_constants() {
		define( 'KS_PLUGIN_FILE', __FILE__ );
		define( 'KS_VERSION', $this->version );
		
		//Store the Plugin version. We'll need this for upgrades
	if (!defined('KANZU_SUPPORT_VERSION_KEY'))
		define('KANZU_SUPPORT_VERSION_KEY', 'kanzu_support_version');
	//Store the version in the database as an option
	add_option(KANZU_SUPPORT_VERSION_KEY, KS_VERSION);

	}
	
	/**
	 * Include all the files we need
	 */
	private function includes() {
		include_once( 'includes/class-ks-install.php' );
		 
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