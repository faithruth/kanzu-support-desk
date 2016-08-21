<?php
/**
 * KSD's admin notices
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Admin_Notices' ) ) : 
    
class KSD_Admin_Notices {

    public function __construct(){
        add_action( 'admin_notices', array ( $this, 'display_admin_notices' ) );
        add_action( 'ksd_hide_notices', array( $this, 'hide_admin_notices' ) );        
    }
    
    public function display_admin_notices() {
//        if ( ! current_user_can( 'ksd_manage_licenses') ){
//            return;
//        }
        $ksd_admin_notices = get_option( KSD()->ksd_admin_notices, array() );     
        if ( $ksd_admin_notices ) {
            $notice_body = '';
            foreach ( $ksd_admin_notices as $admin_notice_name ){
                ob_start();
                include_once( KSD_PLUGIN_DIR .  "templates/admin/notices/{$admin_notice_name}.php");
                $notice_body .= ob_get_clean();                      
            }
            echo $notice_body;        
        }
    }   
    
    public function hide_admin_notices(){
        $notice_name = sanitize_key( $_GET['ksd_notice'] );
        self::remove_notice( $notice_name );
    }


    public static function add_notice( $notice_name ){
        $notices = array_unique( array_merge( get_option( KSD()->ksd_admin_notices, array() ), array( $notice_name ) ) );
        update_option( KSD()->ksd_admin_notices, $notices );        
    }
    
    public static function remove_notice( $notice_name ) {
        $notices = array_diff( get_option( KSD()->ksd_admin_notices, array() ), array( $notice_name ) );
        update_option( KSD()->ksd_admin_notices, $notices );
    }    
}

endif;

return new KSD_Admin_Notices();