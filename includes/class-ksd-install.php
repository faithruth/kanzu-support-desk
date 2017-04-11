<?php
/**
 * Holds all installation & deactivation-related functionality.  
 * On activation, activate is called.
 * On de-activation, deactivate is called
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Install' ) ) :

class KSD_Install {

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * The DB version
     * @since 1.5.0
     * @var int
     */
    protected static $ksd_db_version = 112;

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     *
     * @since     1.0.0
     */
    public function __construct() { 
        //Re-direct on plugin activation
        add_action( 'admin_init', array( $this, 'redirect_to_dashboard' ) );

        //Upgrade settings when the plugin is upgraded
        add_filter( 'ksd_upgrade_settings',  array( $this, 'upgrade_settings' ) );

        //Upgrade everything else apart from plugin settings 
        add_action('ksd_upgrade_plugin',array( $this, 'upgrade_plugin' ));

        //Migration check
        //@TODO: Reassess why it's best put later.    
    }



    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
                self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     *
     */
    public static function activate() {       
       // Bail if activating from network, or bulk. @since 1.1.0
        if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
            return;
        }

        //Check for re-activation.  
        $settings   =   Kanzu_Support_Desk::get_settings();
        if ( isset( $settings['kanzu_support_version'] ) ) {//Reactivation or upgrade     
            if ( $settings['kanzu_support_version'] == KSD_VERSION ) {//Bail out if it's a re-activation
                return;
            }          
            //Check if it's an upgrade. If it is, run the updates. @since 1.1.0
            if ( $settings['kanzu_support_version'] != KSD_VERSION ) { 
                do_action ( 'ksd_upgrade_plugin', $settings['kanzu_support_version'] );//Holds all upgrade-related changes except changes to the settings. We send the current version to the action           
                $settings['kanzu_support_version'] =  KSD_VERSION;   //Update the version
                $upgraded_settings = apply_filters( 'ksd_upgrade_settings', $settings );
                $upgraded_settings['onboarding_enabled'] = 'no';//@2.2.0 Disable onboarding. Change this in next version
                Kanzu_Support_Desk::update_settings( $upgraded_settings );                            
                set_transient( '_ksd_upgrade_redirect', 1, 60 * 60 );// Redirect to welcome screen. Only do tthis for upgrades that have a special intro message
                return;
             }
        }
        else{
            //This is a new installation. Yippee! 
            $ksd_install = self::get_instance();
            $ksd_install->set_default_options(); 	
            $ksd_install->create_support_pages_and_salt();
            $ksd_install->create_woo_edd_products();
            $ksd_install->set_default_notifications();            
            KSD()->roles->create_roles();  
            KSD()->roles->modify_all_role_caps( 'add' );  
            $ksd_install->make_installer_ksd_owner();  
            $ksd_install->log_initial_ticket();            
            set_transient( '_ksd_activation_redirect', 1, 60 * 60 );// Redirect to welcome screen
        }
        flush_rewrite_rules();//Because of the custom post types    
    }


    /**
     * Do de-activation stuff. Currently, doesn't do a thing
     */
    public static function deactivate () {
        flush_rewrite_rules();//Because of the custom post types   
    }        


   /**
     * Redirect to a welcome page on activation
     */
    public function redirect_to_dashboard() {
        // Bail if no activation redirect transient is set
        if ( ! get_transient( '_ksd_activation_redirect' ) && ! get_transient( '_ksd_upgrade_redirect' ) ) {
            return;
        }
        // Delete the redirect transient
        delete_transient( '_ksd_activation_redirect' );

        // Bail if activating from network, or bulk, or within an iFrame
        if ( is_network_admin() || isset( $_GET['activate-multi'] ) || defined( 'IFRAME_REQUEST' ) ) {
            return;
        }
        if ( ( isset( $_GET['action'] ) && 'upgrade-plugin' == $_GET['action'] ) && ( isset( $_GET['plugin'] ) && strstr( $_GET['plugin'], 'kanzu-support-desk.php' ) ) ) {
            return;
        }

        if ( get_transient( '_ksd_upgrade_redirect' ) ) { 
            delete_transient( '_ksd_upgrade_redirect' );
            wp_redirect( admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-dashboard' ) );  
            exit;	
        }
        wp_redirect( admin_url( 'edit.php?post_type=ksd_ticket&ksd_getting_started=1' ) );  
        exit;		
    }

    /**
     * Upgrade the plugin's settings
     * @param Array $settings The current plugin settings
     * @return Array $settings The upgraded settings array
     * @since 1.1.0
     */
    public function upgrade_settings( $settings ) {
        //Compare the user's current settings array against our new default array and pick-up any settings they don't have 
        //We'd have loved to use array_diff_key for this but it only exists for PHP 5 >= 5.1.0
        //For any setting that doesn't exist, we define it and assign it the default value @since 1.5.0
        foreach ( $this->get_default_options() as $setting_key => $setting_default_value ) {
            if ( !isset( $settings[$setting_key] ) ) {
               $settings[$setting_key] =  $setting_default_value;
            }
        }
        return $settings;
    }

    /**
     * During plugin upgrade, this makes all the required changes
     * apart from plugin setting changes which are done by {@link upgrade_settings}
     * Note that any changes to the DB are reflected by an increment in the Db number
     * @param {string} $previous_version The previous version
     * @since 1.2.0
     * @TODO Update this to be even more version conscious
     * NOTE: ALWAYS UPDATE THE DB VERSION IF YOU ALTER ANY OF THE TABLES
     */
    public function upgrade_plugin( $previous_version ) {
        global $wpdb;  
        $wpdb->hide_errors();
        $dbChanges = array();//Holds all DB change queries
        $sanitized_version =  str_replace('.', '', $previous_version) ;

        if ( $sanitized_version < 213 ) {//@since 2.2.0 Added ksd_notifications
             $this->set_default_notifications();
        }

        if ( $sanitized_version < 229 ) {//@since 2.2.9 Added admin, supervisor and agent custom roles
             KSD()->roles->create_roles();
             KSD()->roles->modify_all_role_caps( 'add' );  
             //Make the current user a supervisor. They need to re-select supervisors and agents
             global $current_user;
             KSD()->roles->add_supervisor_caps_to_user( $current_user );
             $this->make_installer_ksd_owner();    
             KSD_Admin_Notices::add_notice( 'update-roles' );//Inform the user of the changes they need to make
        }        

        if ( count( $dbChanges ) > 0 ) {  //Make the Db changes. We use $wpdb->query instead of dbDelta because of
                                        //how strict and verbose the dbDelta alternative is. We'd
                                        //need to rewrite CREATE table statements for dbDelta.
              foreach ( $dbChanges as $query ) {
                    $wpdb->query( $query );     
              }
        }
    }




    /**
     * Get default settings
     * @since 1.1.0 tour_mode
     * @since 1.3.1 enable_recaptcha
     * @since 1.3.2 enable_anonymous_tracking
     * @since 1.5.0 auto_assign_user
     * @since 1.5.4 enable_notify_on_new_ticket              
     * @since 1.7.0 notify_email             
     * @since 2.0.0 enable_customer_signup
     * @since 2.0.0 page_submit_ticket
     * @since 2.0.0 page_my_tickets
     * @since 2.2.0 onboarding_enabled
     * @since 2.2.0 notifications_enabled 
     * @since 2.2.1 Changed $user_info to current user, not primary admin 
     * @since 2.2.9 ksd_owner
     * @since 2.3.3 show_woo_support_tickets_tab
     * @since 2.3.6 show_questionnaire_link
     */
    public function get_default_options() {
        $user_info = wp_get_current_user();  
        return  array (
            /** KSD Version info ********************************************************/
            'kanzu_support_version'             => KSD_VERSION,
            'kanzu_support_db_version'          => self::$ksd_db_version,

            /** Tickets **************************************************************/
            'enable_new_tkt_notifxns'           => "yes",
            'enable_notify_on_new_ticket'       => "yes", 
            'notify_email'                      => $user_info->user_email, 
            'ticket_mail_from_name'             => $user_info->display_name, 
            'ticket_mail_from_email'            => $user_info->user_email, 
            'ticket_mail_subject'               => __( 'Your support ticket has been received', 'kanzu-support-desk' ),
            'ticket_mail_message'               => __( 'Thank you for getting in touch with us. Your support request has been opened. We will get back to you shortly.', 'kanzu-support-desk' ),
            'recency_definition'                => "1",
            'show_support_tab'                  => "yes",
            'support_button_text'               => "Click here for help",
            'tab_message_on_submit'             => __( 'Thank you. Your support request has been opened. We will get back to you shortly.', 'kanzu-support-desk' ),
            'tour_mode'                         => "no",  
            'enable_recaptcha'                  => "no", 
            'recaptcha_site_key'                => "",
            'recaptcha_secret_key'              => "",
            'recaptcha_error_message'           => sprintf ( __( 'Sorry, an error occurred. If this persists, kindly get in touch with the site administrator on %s', 'kanzu-support-desk' ), $user_info->user_email ),
            'enable_anonymous_tracking'         => "no", 
            'auto_assign_user'                  => $user_info->ID,   
            'ticket_management_roles'           => 'administrator', 
            'enable_customer_signup'            => "yes", 
            'page_submit_ticket'                => 0, 
            'page_my_tickets'                   => 0, 
            'salt'                              => '',
            'onboarding_enabled'                => 'yes',
            'notifications_enabled'             => 'yes',
            'ksd_owner'                         => $user_info->ID,
            'show_woo_support_tickets_tab'      => 'no',
            'show_questionnaire_link'           => 'yes',

            /**Support form settings**/
            'supportform_show_categories'       => 'no',
            'supportform_show_severity'         => 'no',
            'supportform_show_products'         => 'no'
            
            );
    }    
    
    /**
     * Make the user who installs KSD the owner
     * 
     * @since 2.2.9
     */
    private function make_installer_ksd_owner(){
       global $current_user;
       $user = new WP_User( $current_user->ID );
       KSD()->roles->modify_default_owner_caps( $user, 'add_cap' );        
    }



    private function set_default_options() {              
        add_option( KSD_OPTIONS_KEY, $this->get_default_options() );
        add_option( 'ksd_activation_time', date( 'U' ) );//Log activation time
        $this->set_default_notifications();
    }

    private function set_default_notifications(){
        include_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-notifications.php' );
        $ksd_notifications      = new KSD_Notifications();
        $notification_defaults  = $ksd_notifications->get_defaults();
        add_option( 'ksd_notifications', $notification_defaults );                
    }



    /**
     * Create the support pages
     * @since 2.0.0
     * @TODO Add this to the upgrade process
     */
    private function create_support_pages_and_salt() {
        $submit_ticket = wp_insert_post(
            array(
                'post_title'     => __( 'Submit Ticket', 'kanzu-support-desk' ),
                'post_content'   => '[ksd_support_form]',
                'post_status'    => 'publish',
                'post_author'    => 1,
                'post_type'      => 'page',
                'comment_status' => 'closed'
            )
        );

        $my_tickets = wp_insert_post(
            array(
                'post_title'     => __( 'My Tickets', 'kanzu-support-desk' ),
                'post_content'   => '[ksd_my_tickets]',
                'post_status'    => 'publish',
                'post_author'    => 1,
                'post_type'      => 'page',
                'comment_status' => 'closed'
            )
        );   
        
        include_once( KSD_PLUGIN_DIR .  "includes/admin/class-ksd-hash-urls.php");
        $hash_urls  = new KSD_Hash_Urls();   
        $salt       = $hash_urls->create_salt();
         //Update the settings
        $updated_settings = Kanzu_Support_Desk::get_settings();//Get current settings
        $updated_settings['page_submit_ticket'] = $submit_ticket;
        $updated_settings['page_my_tickets']    = $my_tickets;
        $updated_settings['salt']               = $salt;
        update_option( KSD_OPTIONS_KEY, $updated_settings );
    }

    /**
     * Log initial ticket
     *
     * @since 2.3.3
     */
    private function log_initial_ticket(){
        
        global $current_user;          

        $display_name           = $current_user->display_name; 
        $customer_ID            = $this->create_initial_ksd_customer_user();
        $kc_logo                = '<img src="'.KSD_PLUGIN_URL . '/assets/images/kanzu_code_logo.png" />';
        $first_ticket_message   = sprintf( '%s %s,<br/><p>%s</p><p>%s</p><div class="ksd-gs-kc-signature"><p>%s,<br/>%s Kanzu Code</p>%s</div>', __('Hi', 'kanzu-support-desk'), $display_name, __( 'Welcome to the Kanzu Support Desk (KSD) community *cue Happy Music and energetic dancers!*. Thanks for choosing us. We are all about making it simple for you to provide amazing customer support. We cannot wait for you to get started!', 'kanzu-support-desk' ), __( 'This is your first ticket. To see what happens when you reply, type a reply below and hit send. We will reply.', 'kanzu-support-desk'), __( 'Your friends', 'kanzu-support-desk' ), _x( 'Team','company team e.g. WordPress team', 'kanzu-support-desk' ), $kc_logo  );
               
    
        $new_ticket = array(
                'ksd_tkt_subject'       => __( "Welcome to Kanzu Support Desk","kanzu-support-desk" ),
                'ksd_tkt_message'       => $first_ticket_message,
                'ksd_tkt_channel'       => "sample-ticket",
                'ksd_tkt_status'        => "new",
                'ksd_tkt_severity'      => 'high',
                'ksd_cust_email'        => 'ksdcare@kanzucode.com',
                'ksd_tkt_logged_by'     => $customer_ID,
                'ksd_tkt_assigned_to'   => $current_user->ID
                );
        

        //Log the ticket
        do_action( 'ksd_log_new_ticket', $new_ticket );         
 
        
    }    

    /**
     * Create a initial customer in the installation. The first ticket's 
     * logged by this user
     *
     * @since 2.3.6 Changed `user_login` from `ksd_kanzu_code` to `ksd.kanzucode`
     * @return int userid of the new user. Is 0 is user isn't created successfully  
     */
    private function create_initial_ksd_customer_user(){
        $userdata = array(
            'user_login'  => 'ksd.kanzucode',
            'user_email'  => 'ksdcare@kanzucode.com',
            'user_url'    =>  "https://kanzucode.com",
            'first_name'  => 'Kanzu',
            'last_name'   => 'Code '.__( 'Helpdesk Customer', 'kanzu-support-desk' ),
            'description' => __( 'The first customer in your helpdesk', 'kanzu-support-desk' ),
            'user_pass'   =>  wp_generate_password(),
            'role'        => 'ksd_customer'
        );

        $user_id = wp_insert_user( $userdata ) ;

        //On success
        if ( ! is_wp_error( $user_id ) ) {
            return $user_id;
        }

        return 0;
    }

    /**
     * Create products from WooCommerce products and EDD downloads
     * @since 2.2.0
     */
    private function create_woo_edd_products(){
        $woocommerce_products   = $this->get_woocommerce_products();
        $edd_downloads          = $this->get_edd_downloads();
        $all_products           = array_merge( $edd_downloads,  $woocommerce_products );

        if ( ! post_type_exists( 'ksd_ticket' ) ) {
            include_once( KSD_PLUGIN_DIR .  'includes/class-ksd-custom-post-types.php' );
            $ksd_cpt = KSD_Custom_Post_Types::get_instance();
            $ksd_cpt->create_ksd_ticket();
        }
        foreach( $all_products as $ksd_new_product ){                    
            $cat_details = array(
                'cat_name' => $ksd_new_product->post_title,
                'taxonomy' => 'product'
                );

            wp_insert_category( $cat_details );
        }
    }

    private function get_woocommerce_products(){
        $args = array( 'post_type' => 'product', 'posts_per_page' => -1,  'post_status' => 'publish' );
        return get_posts( $args );   
    }

    private function get_edd_downloads(){
        $args = array( 'post_type' => 'download', 'posts_per_page' => -1,  'post_status' => 'publish' );
        return get_posts( $args );           
    }

}

endif;

return new KSD_Install();