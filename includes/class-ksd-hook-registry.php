<?php

namespace KSD;

/**
 * Defines all hooks
 *
 * @author Kanzu Code
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // End if().

class KSD_Hook_Registry {

	/**
	 * The DI container
	 *
	 * @var Object
	 */
	private $container;


	public function __construct() {
		$this->container = KSD()->container;
		$this->add_hooks();
	}

	private function add_hooks() {
		/*
         * Register hooks that are fired when the plugin is activated
         * When the plugin is deleted, the uninstall.php file is loaded.
         */
        register_activation_hook( __FILE__, array( 'KSD_Install', 'activate' ) );

        //Register a de-activation hook
        register_deactivation_hook( __FILE__, array( 'KSD_Install', 'deactivate' ) );

		$this->add_script_hooks();
	}

	private function add_script_hooks() {
		// $ksd_scripts = $this->container->get( 'KSD\KSD_Scripts' );

		$ksd_scripts_class = require_once( KSD_PLUGIN_DIR . 'includes/class-ksd-scripts.php' );

		$ksd_scripts = new KSD_Scripts();
		
		//Load scripts used in both the front and back ends
        add_action( 'admin_enqueue_scripts', array( $ksd_scripts, 'enqueue_general_scripts' ) );

        add_action( 'wp_enqueue_scripts', array( $ksd_scripts, 'enqueue_general_scripts' ) );
	}

}

return new KSD_Hook_Registry();
