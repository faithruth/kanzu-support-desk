<?php
/**
 * Fired when the plugin is uninstalled.
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

if ( ! class_exists( 'Kanzu_Support_Uninstall' ) ) :

class Kanzu_Support_Uninstall {
    
    public function __construct(){
        $this->do_uninstall();
    }
    
    /**
     * Do the uninstallation. Delete tables and options
     */
    public function do_uninstall(){
        global $wpdb;
        if ( is_multisite() ) {
        $blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
        $this->delete_options();
        if ( $blogs ) {
            foreach ( $blogs as $blog ) {
                            switch_to_blog( $blog['blog_id'] );
                            $this->delete_options();
                            $this->delete_tables();
                            restore_current_blog();
                    }
            }
        } else {
           $this->delete_options();
           $this->delete_tables();
        }
    }
    
    /**
     * Delete all Kanzu Support tables
     */
    private function delete_tables(){
        global $wpdb;
        $wpdb->hide_errors();		
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); 
        $tables    = array('kanzusupport_tickets', 'kanzusupport_replies', 'kanzusupport_customers', 'kanzusupport_assignments');
        $deleteTables   = "";
        //Iterate through the tables for deletion
        foreach ( $tables as $table ){
            $deleteTables .= "DROP TABLE `{$wpdb->prefix}{$table}`;";
        }
        //Optimize the options table
        $deleteTables .= "OPTIMIZE TABLE `{$wpdb->prefix}options`;";
        dbDelta( $deleteTables );
    }
    
    private function delete_options(){
         delete_option( Kanzu_Support_Install::$ksd_options_name );
    }

}
endif;

return new Kanzu_Support_Uninstall(); 