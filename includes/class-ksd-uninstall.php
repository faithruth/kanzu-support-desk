<?php
/**
 * Fired when the plugin is uninstalled. Hooks into the freemius after_uninstall event
 * Doesn't hook directly into WP's uninstall.php because that would prevent
 * freemius from working
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */



if ( ! class_exists( 'KSD_Uninstall' ) ) :

class KSD_Uninstall {

    public function __construct(){
        add_action( 'after_uninstall', array( $this, 'do_uninstall' ) );
    }

    /**
     * Do the uninstallation. Delete tables and options
     */
    public function do_uninstall(){
        global $wpdb;
        if ( is_multisite() ) {
            $blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );

            $this->delete_options();
            $this->delete_roles();
            $this->remove_caps();

            if ( $blogs ) {
                foreach ( $blogs as $blog ) {
                                switch_to_blog( $blog['blog_id'] );
                                $this->delete_options();
                                $this->delete_roles();
                                $this->remove_caps();
                                $this->delete_tables();
                                $this->delete_ticket_info();
                                restore_current_blog();
                        }
                }
        } else {
           $this->delete_options();
           $this->delete_roles();
           $this->remove_caps();
           $this->delete_tables();
           $this->delete_ticket_info();
        }

        //Send email to user on uninstall
        $this->send_uninstall_email();
    }

    /**
     * Delete all Kanzu Support tables
     */
    private function delete_tables(){
        global $wpdb;
        $wpdb->hide_errors();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        //Because of foreign key constraints, we need to delete the tables in the order below
        $tables    = array( 'kanzusupport_assignments','kanzusupport_attachments','kanzusupport_replies','kanzusupport_tickets' );
        $deleteTables   = array();
        //Iterate through the tables for deletion
        foreach ( $tables as $table ){
            $deleteTables[] = "DROP TABLE IF EXISTS `{$wpdb->prefix}{$table}`;";
        }
        //Optimize the options table
        $deleteTables[]  = "OPTIMIZE TABLE `{$wpdb->prefix}options`;";
        foreach ( $deleteTables as $delete_table_query ){
            $wpdb->query( $delete_table_query ); //We use this instead of dbDelta because of how complex the latter's query would be
        }
    }

    private function delete_options(){
        delete_option( 'kanzu_support_desk' );//Can't use KSD_OPTIONS_KEY since it isn't defined here
        delete_option( 'ksd_activation_time' );
        delete_option( 'ksd_notifications' );
    }

    private function delete_roles(){
        $ksd_roles = array( 'ksd_customer', 'ksd_agent', 'ksd_supervisor' );
        foreach( $ksd_roles as $role ){
            remove_role( $role );
        }
    }

    /**
     * Delete all tickets and related meta information
     * @since 2.0.0
     */
    private function delete_ticket_info(){
        global $wpdb;
        $wpdb->hide_errors();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $delete_ticket_info_sql = array();
        $delete_ticket_info_sql[]  = "DELETE FROM `{$wpdb->prefix}posts` WHERE `post_type` = 'ksd_ticket';";
        $delete_ticket_info_sql[]  = "DELETE FROM `{$wpdb->prefix}posts` WHERE `post_type` = 'ksd_reply';";
        $delete_ticket_info_sql[]  = "DELETE FROM `{$wpdb->prefix}posts` WHERE `post_type` = 'ksd_private_note';";
        $delete_ticket_info_sql[]  = "DELETE FROM `{$wpdb->prefix}posts` WHERE `post_type` = 'ksd_ticket_activity';";
        $delete_ticket_info_sql[]  = "DELETE FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` like '_ksd_tkt%';";

        foreach ( $delete_ticket_info_sql as $delete_ticket_query ){
            $wpdb->query( $delete_ticket_query );
        }
    }

	/**
	 * Remove KSD core role capabilities
	 *
	 * @access public
	 * @since 2.2.9
	 * @return void
	 */
	public function remove_caps() {
            KSD()->roles->modify_all_role_caps( 'remove' );
	}

    public function send_uninstall_email(){
        global $current_user;

        $subject  = __( 'Can you kindly tell me what was wrong with the plugin?', 'kanzu-support-desk' );

        $message  = __( 'Hi ' . $current_user->user_login . ',', 'kanzu-support-desk' ) . '\r\n' ;
        $message .= __( 'We\'ve noticed that you installed the plugin and then uninstalled it fairly quickly.
It seems something did not quite add up. Sorry about that. Would you be so kind to tell us what was the reason for uninstalling the plugin? We want to make sure we fix that for our future users.', 'kanzu-support-desk' ) . '\r\n' ;
        $message .= __( 'Sincerely,', 'kanzu-support-desk' ) . '\r\n' ;
        $message .= __( 'The Team, Kanzu Code', 'kanzu-support-desk' ) . '\r\n' ;
        $message .= __( 'Kanzu Support Desk', 'kanzu-support-desk' ) . '\r\n' ;

        wp_mail( $current_user->user_email, $subject, $message );
    }

}
endif;

return new KSD_Uninstall();
