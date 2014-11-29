<?php
/**
 * Fired when the addon is uninstalled.
 *
 * @package   KSD_Mail
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
          delete_transient( '_ksd_mail_license_last_check' );
          
          //Delete cron entries
          $ksd_mail_admin = KSD_Mail_Admin::get_instance();
          $ksd_mail_admin->delete_cron_schedule();
          
          
    }

}
endif;

return new KSD_Mail_Uninstall(); 