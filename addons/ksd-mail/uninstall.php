<?php
/**
 * Fired when the addon is uninstalled.
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! class_exists( 'KSD_Mail_Uninstall' ) ) :

class KSD_Mail_Uninstall {
    
    public function __construct(){
        $this->do_uninstall();
    }
    
    private function delete_options(){
         delete_option( KSD_Mail_Install::$ksd_options_name );
    }

}
endif;

return new KSD_Mail_Uninstall(); 