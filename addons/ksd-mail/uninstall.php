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
        $this->delete_options();
    }
    
    private function delete_options(){
          KSD_Mail::update_settings( array() );             
    }

}
endif;

return new KSD_Mail_Uninstall(); 