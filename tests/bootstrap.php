<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Kanzu_Support_Desk
 */

		/** 
 	 	 * Migration fixer for PHPUnit 6 
 	 	 */ 
 	 	if ( class_exists( 'PHPUnit\Runner\Version' ) ) { 
 	 	        require_once dirname( __FILE__ ) . '/phpunit6-compat.php'; 
 	 	} 

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/kanzu-support-desk.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

echo PHP_EOL;
echo "Activating Kanzu Support Desk...\n";
activate_plugin( 'kanzu-support-desk/kanzu-support-desk.php' );

require_once( KSD_PLUGIN_DIR . 'includes/admin/class-ksd-admin.php' );

echo PHP_EOL;
echo 'KSD_VERSION: ' . KSD_VERSION;
echo PHP_EOL;
echo PHP_EOL;

define('PHPUNIT', 1);