<?php
/**
 * Admin side of Kanzu Support Desk
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Admin' ) ) :

class KSD_Admin {

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;


    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu. Also define the AJAX callbacks
     *
     * @since     1.0.0
     */
    public function __construct() {

        // Load admin style sheet and JavaScript.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        // Add the options page and menu item.
        add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );

        //Add the attachments button
        add_action( 'media_buttons', array( $this, 'add_attachments_button' ), 15 );

        //Load add-ons
        add_action( 'ksd_load_addons', array( $this, 'load_ksd_addons' ) );

        // Add an action link pointing to the settings page.
        add_filter( 'plugin_action_links_' . plugin_basename( KSD_PLUGIN_FILE ), array( $this, 'add_action_links' ) );

        //Set whether an incoming ticket is a reply
        add_filter( 'ksd_new_ticket_or_reply', array( $this, 'set_is_ticket_a_reply' ) );

        //Handle AJAX calls
        add_action( 'wp_ajax_ksd_filter_tickets', array( $this, 'filter_tickets' ) );
        add_action( 'wp_ajax_ksd_filter_totals', array( $this, 'filter_totals' ) );
        add_action( 'wp_ajax_ksd_log_new_ticket', array( $this, 'log_new_ticket' ) );
        add_action( 'wp_ajax_ksd_delete_ticket', array( $this, 'delete_ticket' ) );
        add_action( 'wp_ajax_ksd_change_status', array( $this, 'change_status' ) );
        add_action( 'wp_ajax_ksd_change_severity', array( $this, 'change_severity' ) );
        add_action( 'wp_ajax_ksd_assign_to', array( $this, 'assign_to' ) );
        add_action( 'wp_ajax_ksd_reply_ticket', array( $this, 'reply_ticket' ) );
        add_action( 'wp_ajax_ksd_get_single_ticket', array( $this, 'get_single_ticket' ) );
        add_action( 'wp_ajax_ksd_dashboard_ticket_volume', array( $this, 'get_dashboard_ticket_volume' ) );
        add_action( 'wp_ajax_ksd_get_dashboard_summary_stats', array( $this, 'get_dashboard_summary_stats' ) );
        add_action( 'wp_ajax_ksd_update_settings', array( $this, 'update_settings' ) );
        add_action( 'wp_ajax_ksd_reset_settings', array( $this, 'reset_settings' ) );
        add_action( 'wp_ajax_ksd_update_private_note', array( $this, 'update_private_note' ) );
        add_action( 'wp_ajax_ksd_send_feedback', array( $this, 'send_feedback' ) );
        add_action( 'wp_ajax_ksd_support_tab_send_feedback', array( $this, 'send_support_tab_feedback' ) );
        add_action( 'wp_ajax_ksd_disable_tour_mode', array( $this, 'disable_tour_mode' ) );
        add_action( 'wp_ajax_ksd_get_notifications', array( $this, 'get_notifications' ) );
        add_action( 'wp_ajax_ksd_notify_new_ticket', array( $this, 'notify_new_ticket' ) );
        add_action( 'wp_ajax_ksd_bulk_change_status', array( $this, 'bulk_change_status' ) );
        add_action( 'wp_ajax_ksd_change_read_status', array( $this, 'change_read_status' ) );
        add_action( 'wp_ajax_ksd_modify_license', array( $this, 'modify_license_status' ) );
        add_action( 'wp_ajax_ksd_enable_usage_stats', array( $this, 'enable_usage_stats' ) );
        add_action( 'wp_ajax_ksd_update_ticket_info', array( $this, 'update_ticket_info' ) );
        add_action( 'wp_ajax_ksd_get_ticket_activity', array( $this, 'get_ticket_activity' ) );
        add_action( 'wp_ajax_ksd_migrate_to_v2', array( $this, 'migrate_to_v2' ) );
        add_action( 'wp_ajax_ksd_deletetables_v2', array( $this, 'deletetables_v2' ) );
        add_action( 'wp_ajax_ksd_notifications_user_feedback', array( $this, 'process_notification_feedback' ) );
        add_action( 'wp_ajax_ksd_notifications_disable', array( $this, 'disable_notifications' ) );
        add_action( 'wp_ajax_ksd_send_debug_email', array( $this, 'send_debug_email' ) );
        add_action( 'wp_ajax_ksd_reset_role_caps', array( $this, 'reset_role_caps' ) );
        add_action( 'wp_ajax_ksd_get_unread_ticket_count', array( $this, 'get_unread_ticket_count' ) );
        add_action( 'wp_ajax_ksd_hide_questionnaire', array( $this, 'hide_questionnaire' ) );


        //Generate a debug file
        add_action( 'ksd_generate_debug_file', array( $this, 'generate_debug_file' ) );

        //Register KSD tickets importer
        add_action( 'admin_init', array( $this, 'ksd_importer_init' ) );

        //Do actions called in $_POST
        add_action( 'init', array( $this, 'do_post_and_get_actions' ) );

        //Add contextual help messages
        add_action( 'contextual_help', array ( $this, 'add_contextual_help' ), 10, 3 );


        //In 'Edit ticket' view, customize the screen
        add_action( 'add_meta_boxes', array( $this, 'edit_metaboxes' ), 10, 2 );

        //Add items to the submitdiv in 'edit ticket' view
        add_action( 'post_submitbox_misc_actions', array( $this, 'edit_submitdiv' ) );

        //Add hash URLs to single ticket view
        add_action( 'edit_form_before_permalink', array( $this, 'show_hash_url') );

        //Save ticket and its information
        add_filter( 'wp_insert_post_data' , array( $this, 'save_ticket_info' ) , '99', 2 );

        //Alter messages
        add_filter( 'post_updated_messages', array( $this, 'ticket_updated_messages' ) );

        //Add KSD Importer to tool box
        add_action( 'tool_box',  array( $this, 'add_importer_to_toolbox' ) );

        //Get final status for ticket logged by importation
        add_action( 'ksd_new_ticket_imported', array( $this, 'new_ticket_imported', 10, 2 ) );

        //Modify ticket deletion and un-deletion messages
        add_filter( 'bulk_post_updated_messages', array( $this, 'ticket_bulk_update_messages' ), 10, 2 );

        //Add custom views
        add_filter( 'views_edit-ksd_ticket', array( $this, 'ticket_views' ) );

        //Add CC button to tinyMCE editor
        $this->add_tinymce_cc_button();

        //Add headers to the tickets grid
        add_filter( 'manage_ksd_ticket_posts_columns', array( $this, 'add_tickets_headers' ) );
        //Populate the new columns
        add_action( 'manage_ksd_ticket_posts_custom_column', array( $this, 'populate_ticket_columns' ), 10, 2 );
        //Add sorting to the new columns
        add_filter( 'manage_edit-ksd_ticket_sortable_columns', array( $this, 'ticket_table_sortable_columns' ) );
        //Remove some default columns
        add_filter( 'manage_edit-ksd_ticket_columns', array( $this, 'ticket_table_remove_columns' ) );

        add_filter( 'request', array( $this, 'ticket_table_columns_orderby' ) );
        //Add ticket filters to the table grid drop-down
        add_action( 'restrict_manage_posts', array( $this, 'ticket_table_filter_headers' ) );
        add_filter( 'parse_query', array( $this, 'ticket_table_apply_filters' ) );
        //Display ticket status next to the ticket title
        add_filter( 'display_post_states', array( $this, 'display_ticket_statuses_next_to_title' ) );
        //Bulk edit
        add_action( 'bulk_edit_custom_box', array( $this, 'quick_edit_custom_boxes' ), 10, 2 );
        add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_custom_boxes' ), 10, 2 );

        //Add feedback
        add_action( 'admin_footer', array( $this, 'append_admin_feedback' ), 25 );

        //On EDD download/WooCommerce publish
        add_action(  'publish_product',  array( $this, 'on_new_product' ), 10, 2 );
        add_action(  'publish_download',  array( $this, 'on_new_product' ), 10, 2 );

        //Change 'Publish' button to 'Update'
        add_filter( 'gettext', array( $this, 'change_publish_button' ), 10, 2 );

        //Send tracking data
        add_action( 'admin_head', array( $this, 'send_tracking_data' ) );

        //Add 'My tickets' button to 'My profile' page
        add_action( 'personal_options', array( $this, 'add_my_tickets_link') );

        //Tag 'read' tickets in the ticket grid
        add_filter( 'post_class', array( $this, 'append_classes_to_ticket_grid' ), 10, 3 );
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
     * Register and enqueue admin-specific style sheet.
     *
     *
     * @since     1.0.0
     *
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style( KSD_SLUG .'-admin-css', KSD_PLUGIN_URL.'assets/css/ksd-admin.css', array(), KSD_VERSION );
    }

    /**
     * Register and enqueue admin-specific JavaScript.
     *
     *
     * @since     1.0.0
     *
     */
    public function enqueue_admin_scripts() {
            wp_enqueue_script( KSD_SLUG . '-admin-bar-js', KSD_PLUGIN_URL . 'assets/js/ksd-admin-bar.js', array( 'jquery' ), '1.0.0' );
            wp_localize_script( KSD_SLUG . '-admin-bar-js', 'ksd_admin_bar', array(
                'ajax_url'  => admin_url( 'admin-ajax.php' )
            ) );

            //Load the script for Google charts. Load this before the next script.
            wp_enqueue_script( KSD_SLUG . '-admin-gcharts', '//www.google.com/jsapi', array(), KSD_VERSION );
            wp_enqueue_script( KSD_SLUG . '-admin-js', KSD_PLUGIN_URL.'assets/js/ksd-admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-tabs', 'json2', 'jquery-ui-dialog', 'jquery-ui-tooltip', 'jquery-ui-accordion','jquery-ui-autocomplete' ), KSD_VERSION );


            //Variables to send to the admin JS script
            $ksd_admin_tab = ( isset( $_GET['page'] ) ?  $_GET['page']  : "" );//This determines which tab to show as active

            //This array allows us to internationalize (translate) the words/phrases/labels displayed in the JS
            $admin_labels_array = $this->get_admin_labels();

            $ticket_info = array( 'status_list' => $this->get_submitdiv_status_options() );

            //Get current settings
            $settings = Kanzu_Support_Desk::get_settings();

            //Localization allows us to send variables to the JS script
            wp_localize_script( KSD_SLUG . '-admin-js',
                                'ksd_admin',
                                array(  'admin_tab'                 =>  $ksd_admin_tab,
                                        'ajax_url'                  =>  admin_url( 'admin-ajax.php' ),
                                        'admin_post_url'            =>  admin_url( 'admin-post.php' ),
                                        'ksd_admin_nonce'           =>  wp_create_nonce( 'ksd-admin-nonce' ),
                                        'ksd_tickets_url'           =>  admin_url( 'admin.php?page=ksd-tickets' ),
                                        'ksd_current_user_id'       =>  get_current_user_id(),
                                        'ksd_labels'                =>  $admin_labels_array,
                                        'enable_anonymous_tracking' =>  $settings['enable_anonymous_tracking'],
                                        'ksd_ticket_info'           =>  $ticket_info,
                                        'ksd_current_screen'        =>  $this->get_current_ksd_screen(),
                                        'ksd_version'               =>  KSD_VERSION,
                                        'ksd_statuses'              =>  $this->get_status_list_options()
                                    )
                                );

    }


    /**
     * Do all KSD actions present in the $_POST & $_GET superglobals.
     * These functions are called on init
     * @since 1.7.0
     */
    public function do_post_and_get_actions() {
        if ( isset( $_POST['ksd_action'] ) ) {
            do_action( $_POST['ksd_action'], $_POST );
        }
        if ( isset( $_GET['ksd_action'] ) ) {
            do_action( $_GET['ksd_action'], $_GET );
        }
    }

    /**
     * Get the labels internationalized for use in
     * our JS, ksd-admin.js
     */
    private function get_admin_labels(){
        $admin_labels_array = array();
        $admin_labels_array['dashboard_chart_title']        = __( 'Incoming Tickets', 'kanzu-support-desk' );
        $admin_labels_array['dashboard_open_tickets']       = __( 'Total Open Tickets', 'kanzu-support-desk' );
        $admin_labels_array['dashboard_unassigned_tickets'] = __( 'Unassigned Tickets', 'kanzu-support-desk' );
        $admin_labels_array['dashboard_avg_response_time']  = __( 'Avg. Response Time', 'kanzu-support-desk' );
//      $admin_labels_array['tkt_trash']                    = __('Trash', 'kanzu-support-desk' );
//      $admin_labels_array['tkt_assign_to']                = __('Assign To', 'kanzu-support-desk' );
//      $admin_labels_array['tkt_change_status']            = __('Change Status', 'kanzu-support-desk' );
//      $admin_labels_array['tkt_change_severity']          = __('Change Severity', 'kanzu-support-desk' );
//      $admin_labels_array['tkt_mark_read']                = __('Mark as Read', 'kanzu-support-desk' );
//      $admin_labels_array['tkt_mark_unread']              = __('Mark as Unread', 'kanzu-support-desk' );
        $admin_labels_array['tkt_subject']                  = __( 'Subject', 'kanzu-support-desk' );
        $admin_labels_array['tkt_cust_fullname']            = __( 'Customer Name', 'kanzu-support-desk' );
        $admin_labels_array['tkt_cust_email']               = __( 'Customer Email', 'kanzu-support-desk' );
        $admin_labels_array['tkt_reply']                    = __( 'Send', 'kanzu-support-desk' );
        $admin_labels_array['tkt_forward']                  = __( 'Forward', 'kanzu-support-desk' );
        $admin_labels_array['tkt_update_note']              = __( 'Add Note', 'kanzu-support-desk' );
        $admin_labels_array['tkt_attach_file']              = __( 'Attach File', 'kanzu-support-desk' );
        $admin_labels_array['tkt_attach']                   = __( 'Attach', 'kanzu-support-desk' );
        $admin_labels_array['tkt_status_open']              = __( 'OPEN', 'kanzu-support-desk' );
        $admin_labels_array['tkt_status_pending']           = __( 'PENDING', 'kanzu-support-desk' );
        $admin_labels_array['tkt_status_resolved']          = __( 'RESOLVED', 'kanzu-support-desk' );
        $admin_labels_array['tkt_severity_low']             = __( 'LOW', 'kanzu-support-desk' );
        $admin_labels_array['tkt_severity_medium']          = __( 'MEDIUM', 'kanzu-support-desk' );
        $admin_labels_array['tkt_severity_high']            = __( 'HIGH', 'kanzu-support-desk' );
        $admin_labels_array['tkt_severity_urgent']          = __( 'URGENT', 'kanzu-support-desk' );
        $admin_labels_array['msg_still_loading']            = __( 'Loading Replies...', 'kanzu-support-desk' );
        $admin_labels_array['msg_loading']                  = __( 'Loading...', 'kanzu-support-desk' );
        $admin_labels_array['msg_sending']                  = __( 'Sending...', 'kanzu-support-desk' );
        $admin_labels_array['msg_reply_sent']               = __( 'Reply Sent!', 'kanzu-support-desk' );
        $admin_labels_array['msg_error']                    = __( 'Sorry, an unexpected error occurred. Kindly retry. Thank you.', 'kanzu-support-desk' );
        $admin_labels_array['msg_error_refresh']            = __( 'Sorry, but something went wrong. Please try again or reload the page.', 'kanzu-support-desk' );
        $admin_labels_array['pointer_next']                 = __( 'Next', 'kanzu-support-desk' );
        $admin_labels_array['lbl_toggle_trimmed_content']   = __( 'Toggle Trimmed Content', 'kanzu-support-desk' );
        $admin_labels_array['lbl_tickets']                  = __( 'Tickets', 'kanzu-support-desk' );
        $admin_labels_array['lbl_CC']                       = __( 'CC', 'kanzu-support-desk' );
        $admin_labels_array['lbl_reply_to_all']             = __( 'Reply', 'kanzu-support-desk' );
        $admin_labels_array['lbl_populate_cc']              = __( 'Populate CC field', 'kanzu-support-desk' );
        $admin_labels_array['lbl_save']                     = __( 'Save', 'kanzu-support-desk' );
        $admin_labels_array['lbl_update']                   = __( 'Submit', 'kanzu-support-desk' );
        $admin_labels_array['lbl_created_on']               = __( 'Created on', 'kanzu-support-desk' );
        $admin_labels_array['lbl_notification_nps_error']   = __( 'Please select one of the values above (0 to 10)', 'kanzu-support-desk' );

        //jQuery form validator internationalization
        $admin_labels_array['validator_required']           = __( 'This field is required.', 'kanzu-support-desk' );
        $admin_labels_array['validator_email']              = __( 'Please enter a valid email address.', 'kanzu-support-desk' );
        $admin_labels_array['validator_minlength']          = __( 'Please enter at least {0} characters.', 'kanzu-support-desk' );
        $admin_labels_array['validator_cc']                 = __( 'Please enter a comma separated list of valid email addresses.', 'kanzu-support-desk' );

        //Messages for migration to v2.0.0
        //$admin_labels_array['msg_migrationv2_started']      = __('Migration of your tickets and replies has started. This may take some time. Please wait...', 'kanzu-support-desk' );
       //$admin_labels_array['msg_migrationv2_deleting']     = __('Deleting tickets. This may take sometime. Please wait...', 'kanzu-support-desk' );

        return $admin_labels_array;
    }




    /**
     * Process the notification feedback
     *
     * @since 2.2.0
     */
    public function process_notification_feedback() {
        include_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-notifications.php' );
        $ksd_notify = new KSD_Notifications();
        $response = $ksd_notify->process_notification_feedback();
        echo json_encode( $response );
        die();
    }

    /**
     * Ajax handler for autocomplete user
     *
     */
    public function autocomplete_user(){
        global $current_user;
        $return = array();

	$users = get_users( array(
		'blog_id' => false,
		'search'  => '*' . $_REQUEST['term'] . '*',
		'exclude' => $current_user->ID,
		'search_columns' => array( 'user_login', 'user_nicename', 'user_email' ),
	) );

	foreach ( $users as $user ) {
		$return[] = array(
			/* translators: 1: user_login, 2: user_email */
			'label' => sprintf( __( '%1$s (%2$s)','kanzu-support-desk' ), $user->user_login, $user->user_email ),
			'value' => $user->user_login,
                        'ID'    => $user->ID
		);
	}

	wp_die( wp_json_encode( $return ) );
    }


    /**
     * Add a 'My Tickets' link to the Profile page
     * that's displayed when a ksd_customer logs in
     */
    public function add_my_tickets_link(){
        global $current_user;
        if ( isset( $current_user->roles ) && is_array( $current_user->roles ) && in_array( 'ksd_customer', $current_user->roles ) ) {
            $current_settings   = Kanzu_Support_Desk::get_settings();//Get current settings
            $link_label         = __( 'View My Tickets', 'kanzu-support-desk' );
            echo '<a href="'.get_permalink( $current_settings['page_my_tickets'] ).'" class="ksd-customer-ticket-link button button-primary">'.$link_label.'</a>';
        }
    }

     /**
      * Disable display of notifications
      * @since 2.2.0
      */
     public function disable_notifications() {
        $ksd_settings = Kanzu_Support_Desk::get_settings();
        $ksd_settings['notifications_enabled'] = "no";
        Kanzu_Support_Desk::update_settings( $ksd_settings );
        echo json_encode( __( 'Thanks for your time. If you ever have any feedback, please get in touch - feedback@kanzucode.com', 'kanzu-support-desk') );
        if ( !defined( 'PHPUNIT' ) ) die();
    }

     /**
      * Disable display of notifications
      *
      * @since 2.3.6
      */
     public function hide_questionnaire() {
        $ksd_settings = Kanzu_Support_Desk::get_settings();
        $ksd_settings['show_questionnaire_link'] = "no";
        Kanzu_Support_Desk::update_settings( $ksd_settings );
        wp_send_json_success(
                    array( 'message' => __( 'Questionnaire hidden', 'kanzu-support-desk') )
            );
    }

    /**
     * Update ticket messages  displayed
     * @since 2.0.0
     * @global Object $post
     * @global int $post_ID
     * @param String $messages
     * @return Array
     */
    public function ticket_updated_messages( $messages ) {
        global $post, $post_ID;

        $message_1 = sprintf( '%s <a href="%s">%s</a>',
                    __( 'Ticket updated.', 'kanzu-support-desk' ) ,
                    esc_url( get_permalink( $post_ID ) ),
                    __( 'View ticket', 'kanzu-support-desk' ) );

        $message_5 = isset( $_GET['revision'] ) ?
                    sprintf(
                       '%s %s',
                       __( 'Ticket restored to revision from', 'kanzu-support-desk' ),
                       wp_post_revision_title( ( int ) $_GET['revision'], false )
                    ) : false;

        $message_6 = sprintf( '%s <a href="%s">%s</a>' ,
                __( 'Ticket published.', 'kanzu-support-desk' ),
                __( 'View ticket.', 'kanzu-support-desk' ),
                esc_url( get_permalink( $post_ID ) ) );

        $message_8 = sprintf( '$s <a target="_blank" href="%s">%s</a>',
                    __( 'Ticket submitted.', 'kanzu-support-desk' ),
                    esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ),
                    __( 'Preview ticket', 'kanzu-support-desk' )
                );

        $message_9 = sprintf( '%s <strong>%1$s</strong>. <a target="_blank" href="%2$s">%s</a>',
                    __( 'Ticket scheduled for:','kanzu-support-desk' ),
                    date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) ),
                    esc_url( get_permalink( $post_ID ) ) ,
                    __( 'Preview ticket', 'kanzu-support-desk' )
                );

        $message_10 = sprintf( '%s <a target="_blank" href="%s">%s</a>',
                __( 'Ticket draft updated.', 'kanzu-support-desk' ),
                esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ,
                __( 'Preview ticket', 'kanzu-support-desk' )
                );

        $messages['ksd_ticket'] = array(
            0   => '',
            1   => $message_1,
            2   => __( 'Custom field updated.', 'kanzu-support-desk' ),
            3   => __( 'Custom field deleted.', 'kanzu-support-desk' ),
            4   => __( 'Ticket updated.' , 'kanzu-support-desk' ),
            5   => $message_5,
            6   => $message_6,
            7   => __( 'Ticket saved.', 'kanzu-support-desk' ),
            8   => $message_8,
            9   => $message_9,
            10  => $message_10
        );
        return $messages;
    }

    /**
     * Modify ticket bulk update messages
     * @since 2.0.0
     */
    public function ticket_bulk_update_messages( $bulk_messages, $bulk_counts ) {
        $bulk_messages['ksd_ticket'] = array(
                'updated'   => sprintf( _n( '%s ticket updated.', '%s tickets updated.', $bulk_counts['updated'], 'kanzu-support-desk' ), $bulk_counts['updated'] ),
                'locked'    => ( 1 == $bulk_counts['locked'] ) ? __( '1 ticket not updated, somebody is editing it.', 'kanzu-support-desk' ) :
                                   _n( '%s ticket not updated, somebody is editing it.', '%s tickets not updated, somebody is editing them.', $bulk_counts['locked'] ),
                'deleted'   => _n( '%s ticket permanently deleted.', '%s tickets permanently deleted.', $bulk_counts['deleted'] ),
                'trashed'   => _n( '%s ticket moved to the Trash.', '%s tickets moved to the Trash.', $bulk_counts['trashed'] ),
                'untrashed' => _n( '%s ticket restored from the Trash.', '%s tickets restored from the Trash.', $bulk_counts['untrashed'] ),
        );
        return $bulk_messages;
    }


    /**
     * Get the current KSD screen
     * @since 2.0.0
     */
    private function get_current_ksd_screen( $screen = null ) {
        $current_ksd_screen_id = 'not_a_ksd_screen';
        if ( null == $screen ) {
            $screen = get_current_screen();
        }
        if ( ! $screen || 'ksd_ticket' !== $screen->post_type ) {
             return $current_ksd_screen_id;
        }
        switch ( $screen->id ) {
             case 'edit-ksd_ticket'://Ticket Grid
                 $current_ksd_screen_id = 'ksd-ticket-list';
                break;
            case 'ksd_ticket'://Single ticket view and Add new ticket
                if ( 'add' == $screen->action ) {//Add new ticket
                    $current_ksd_screen_id = 'ksd-add-new-ticket';
                }else{//Single ticket view
                    $current_ksd_screen_id = 'ksd-single-ticket-details';
                }
                break;
            case 'edit-ticket_category'://Categories
                $current_ksd_screen_id = 'ksd-edit-categories';
                break;
            case 'edit-product':
                $current_ksd_screen_id = 'ksd-edit-products';
                break;
            case 'ksd_ticket_page_ksd-dashboard':
                $current_ksd_screen_id = 'ksd-dashboard';
                break;
             case 'ksd_ticket_page_ksd-settings'://Settings
                 $current_ksd_screen_id = 'ksd-settings';
                 break;
        }
        return $current_ksd_screen_id;
    }

    /**
     * Add contextual help messages
     * @param string $contextual_help
     * @param int $screen_id
     * @param Object $screen
     * @global $wp_version
     * @return string $contextual_help The contextual help
     * @since 2.0.0
     */
    public function add_contextual_help( $contextual_help, $screen_id, $screen ) {
        global $wp_version;

        $current_ksd_screen = $this->get_current_ksd_screen( $screen );
        if ( 'not_a_ksd_screen' == $current_ksd_screen ) {
             return $contextual_help;
        }

        switch ( $current_ksd_screen ) {
            case 'ksd-ticket-list':
                $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p> <p> <b> %s </b> %s </p><p> <b> %s </b> %s </p></span>',
                                            __( 'Tickets', 'kanzu-support-desk' ),
                                            __( 'All your tickets are displayed here. View the details of a single ticket by clicking on it.', 'kanzu-support-desk' ),
                                            __( 'Filtering', 'kanzu-support-desk' ),
                                            __( 'Filter tickets using ticket status or severity.', 'kanzu-support-desk' ),
                                            __( 'Sorting', 'kanzu-support-desk' ),
                                            __( 'Re-order tickets by clicking on the header of the column you would like to order by', 'kanzu-support-desk' )
                                    );
                break;
            case 'ksd-add-new-ticket':
                $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p><p> <b> %s :</b> %s </p></span>',
                                            __( 'New Ticket', 'kanzu-support-desk' ),
                                            __( 'Add details for a new ticket. Use the publish button to make the ticket publically visible', 'kanzu-support-desk' ),
                                            __( 'Save', 'kanzu-support-desk' ),
                                            __( 'When you save a ticket and do not publish it, it will NOT be visible to the customer. Use this for tickets that you are still making changes to', 'kanzu-support-desk' )
                                    );
            case 'ksd-single-ticket-details':
                $contextual_help = sprintf( '<span><h2> %s </h2> <p> <b> %s :</b> %s </p><p> <b> %s :</b> %s </p><p> <b> %s :</b> %s </p></span>',
                                            __( 'Reply ticket/Edit ticket information', 'kanzu-support-desk' ),
                                            __( 'Modify ticket information', 'kanzu-support-desk'),
                                            __( 'Modify the details of a ticket in the "Ticket Information" box. Change status, severity, assignee and other ticket information. Use the Update button to save your changes', 'kanzu-support-desk'),
                                            __( 'Reply your customer', 'kanzu-support-desk'),
                                            __( 'Type a reply and use the Send button to send your reply to your customer', 'kanzu-support-desk'),
                                            __( 'Private Notes', 'kanzu-support-desk'),
                                            __( 'Save a private note that will be viewed by other agents. Customers are NOT able to view private notes', 'kanzu-support-desk')
                                    );

                break;
            case 'ksd-edit-categories':
                $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p></span>',
                                            __( 'Ticket Categories', 'kanzu-support-desk'),
                                            __( 'Add/Edit/Delete ticket categories. Use categories to organize your tickets', 'kanzu-support-desk')
                                    );
                break;
            case 'ksd-edit-products':
               $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p></span>',
                                            __( 'Ticket Products', 'kanzu-support-desk'),
                                            __( 'Add/Edit/Delete ticket products. Use products to identify which of your products the ticket is attached to', 'kanzu-support-desk')
                                    );
                break;
            case 'ksd-dashboard':
                $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p></span>',
                                            __( 'Ticket Dashboard', 'kanzu-support-desk'),
                                            __( 'Shows an overview of your performance', 'kanzu-support-desk')
                                    );
                break;
            case 'ksd-settings':
                $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p></span>',
                                            __( 'KSD Settings', 'kanzu-support-desk'),
                                            __( 'Customize your KSD experience by modifying your settings. Each setting has a help message next to it.', 'kanzu-support-desk')
                                    );
                break;
        }

        if ( version_compare( $wp_version, '3.3', '>=' ) )://Sweet tabbed contextual help was introduced in 3.3
            $screen->add_help_tab( $this->add_support_help_tab() );
            $screen->add_help_tab(
                    array(
                    'id'       => $current_ksd_screen.'-help',
                    'title'    => __( 'Overview' ),
                    'content'  => $contextual_help
            ));
        else:
            return $contextual_help;
        endif;
    }


    private function add_support_help_tab(){
        ob_start();
        include_once( KSD_PLUGIN_DIR .  "templates/admin/help-support-tab.php" );
        $support_form = ob_get_clean();

        return array(
            'id'       => 'ksd-support-tab-help',
            'title'    => __( 'Help/Feedback' ),
            'content'  => $support_form
        );
    }

    /**
     * Modify the metaboxes on the ticket edit screen
     * @since 2.0.0
     */
    public function edit_metaboxes( $post_type, $post ) {
        if ( $post_type !== 'ksd_ticket' ) {
            return;
        }

        //Remove unwanted metaboxes
        $metaboxes_to_remove = array ( 'submitdiv' , 'authordiv', 'postcustom', 'postexcerpt', 'trackbacksdiv', 'tagsdiv-post_tag'  );
        foreach ( $metaboxes_to_remove as  $remove_metabox ) {
            remove_meta_box(  $remove_metabox, 'ksd_ticket', 'side' );
        }
        //Remove post meta fields
        remove_meta_box( 'postcustom', 'ksd_ticket', 'normal' );
        remove_meta_box( 'commentstatusdiv', 'ksd_ticket', 'normal' );
        remove_meta_box( 'commentsdiv', 'ksd_ticket', 'normal' );

        //Add a custom submitdiv
        $publish_callback_args = array( 'revisions_count' => 0, 'revision_id' => NULL   );
        add_meta_box( 'submitdiv', __( 'Ticket Information', 'kanzu-support-desk' ), 'post_submit_meta_box', null, 'side', 'high', $publish_callback_args );

        //Customer information
        add_meta_box(
                'ksd-ticket-info-customer',
                __( 'Customer Information', 'kanzu-support-desk'),
                array( $this,'output_ticket_info_customer' ),
                'ksd_ticket',
                'side',
                'high'
            );

        if ( $post->post_status !== 'auto-draft' ) {//For ticket updates
        //Add main metabox for ticket replies
        add_meta_box(
                'ksd-ticket-messages',
                __( 'Ticket Messages', 'kanzu-support-desk'),
                array( $this,'output_meta_boxes' ),
                'ksd_ticket',
                'normal',
                'high'
            );
        //For ticket activity
        add_meta_box(
                'ksd-ticket-activity',
                __( 'Ticket Activity', 'kanzu-support-desk'),
                array( $this,'output_meta_boxes' ),
                'ksd_ticket',
                'side',
                'high'
            );
        //For 'Other Tickets'
        add_meta_box(
                'ksd-other-tickets',
                __( 'Other Tickets', 'kanzu-support-desk'),
                array( $this,'output_meta_boxes' ),
                'ksd_ticket',
                'side',
                'high'
            );
        }
        //Mark ticket as read by current user
        global $current_user;
        update_post_meta( $post->ID, '_ksd_tkt_info_is_read_by_'.$current_user->ID, 'yes' );
        $new_ticket_activity = array();
        $new_ticket_activity['post_author']    = 0;
        $new_ticket_activity['post_title']     = $post->post_title;
        $new_ticket_activity['post_parent']    = $post->ID;
        $new_ticket_activity['post_content']   = sprintf( __( 'Ticket read by %s','kanzu-support-desk' ),  '<a href="' . admin_url( "user-edit. php?user_id={$current_user->ID}").'">' . $current_user->display_name.'</a>' );
        do_action( 'ksd_insert_new_ticket_activity', $new_ticket_activity );
    }

    /**
     * Edit the submitddiv box displayed in the sidebar of tickets
     * @since 2.0.0
     * @global type $post
     */
    public function edit_submitdiv() {
        global $post;
        if ( $post->post_type !== 'ksd_ticket' ) {
            return;
        }
        include_once( KSD_PLUGIN_DIR .  "templates/admin/metaboxes/html-ksd-ticket-info.php");
    }

    /**
     * While viewing a single ticket that has a hash URL,
     * display it in place of the permalink
     *
     * @param Object $post
     */
    public function show_hash_url( $post ){
        if ( $post->post_type !== 'ksd_ticket' ) {
            return;
        }
        $hash_url =   get_post_meta( $post->ID, '_ksd_tkt_info_hash_url', true );
        if ( empty(  $hash_url ) ) {
            return;
        }
        include_once( KSD_PLUGIN_DIR .  "templates/admin/metaboxes/hash-url.php");
    }


    /**
     * Output ticket meta boxes
     * @param Object $post The WP_Object
     * @param Array $metabox The metabox array
     * @since 2.0.0
     */
    public function output_meta_boxes( $post, $metabox  ) {
        //If this is the ticket messages metabox, format the content for viewing
        if ( $metabox['id'] == 'ksd-ticket-messages' ) {
            $post->content = $this->format_message_content_for_viewing( $post->content );
        }
        include_once( KSD_PLUGIN_DIR .  "templates/admin/metaboxes/html-". $metabox['id'].".php");
    }

    /**
     * Output the HTML of the customer fields in the 'Customer Information'
     * meta box. The customer fields are 'Customer','Customer Email','Customer Since', etc.
     * @param Object $post The WP_Object
     * @since 2.2.3
     */
    public function output_ticket_info_customer( $post ){
        ob_start();
        include_once( KSD_PLUGIN_DIR .  "templates/admin/metaboxes/html-ksd-ticket-info-customer.php");
        $customer_html = ob_get_clean();
        echo apply_filters( 'ksd_ticket_info_customer_html', $customer_html );
    }



    /**
     * Save ticket information
     * This ensures that at all times, tickets only have one of
     * our predefined statuses (new, open, pending, draft or resolved )
     * Also, it saves our metavalues
     * Note that this implements a filter; every return statement MUST return $data
     * @since 2.0.0
     */
    public function save_ticket_info( $data , $postarr ) {
        if ( 'ksd_ticket' !== $data['post_type'] ) {//Only handle our tickets
            return $data;
        }
        //Stop processing if it is a new ticket
        if ( 'auto-draft' == $data['post_status'] || ( isset ( $postarr['auto_draft'] ) && $postarr['auto_draft'] )) {
            return $data;
        }

        if ( wp_is_post_revision( $postarr['ID'] ) || wp_is_post_autosave( $postarr['ID'] ) ) {
            return $data;
        }

        //Set post_author to customer
        if ( isset( $postarr['_ksd_tkt_info_customer'] ) ) {
                $data['post_author'] = $postarr['_ksd_tkt_info_customer'];
        }
        //Save the ticket's meta information
        $this->save_ticket_meta_info( $postarr['ID'], $postarr['post_title'], $postarr );

        if ( 'publish' == $data['post_status'] ) {//Change published tickets' statuses from 'publish' to KSD native ticket statuses
            $post_status = ( 'auto-draft' == $postarr['hidden_ksd_post_status'] && isset( $postarr['hidden_ksd_post_status'] ) ? 'open' : $postarr['hidden_ksd_post_status'] );
            $data['post_status'] = $post_status;
        }
        return $data;
    }

    /**
     * Save a ticket's meta information. This includes severity, assignee, etc
     * @param int $tkt_id The ticket ID
     * @param string $tkt_title The ticket title
     * @param Array $meta_array The ticket meta information
     * @since 2.0.0
     *
     */
    private function save_ticket_meta_info( $tkt_id, $tkt_title, $meta_array ) {
        global $current_user;
        $ksd_dynamic_meta_keys = apply_filters( 'ksd_ticket_info_keys', array(
                '_ksd_tkt_info_severity'        => 'low',
                '_ksd_tkt_info_assigned_to'     => 0,
                '_ksd_tkt_info_channel'         => 'admin-form',
                '_ksd_tkt_info_cc'              => '',
                '_ksd_tkt_info_woo_order_id'    => '',
                '_ksd_tkt_info_post_id'         => ''
            ));

        $ksd_static_meta_keys = array(
            '_ksd_tkt_info_hash_url',
            '_ksd_tkt_info_referer'
        );

        //Save ticket customer meta information in the activity list. This is all we do with the _ksd_tkt_info_customer field
        if ( isset( $meta_array['_ksd_tkt_info_customer'] ) ) {
            $this->update_ticket_activity( '_ksd_tkt_info_customer', $tkt_title, $tkt_id, wp_get_current_user()->ID, $meta_array['_ksd_tkt_info_customer'] );
        }

        //For the read/unread indicator, save and add the activity separately
        if ( isset( $meta_array[ '_ksd_tkt_info_is_read_by_'.$current_user->ID ] ) ) {
            $this->update_ticket_read_state( $tkt_id, $tkt_title, $meta_array[ '_ksd_tkt_info_is_read_by_'.$current_user->ID ] );
        }

        //Save the static keys
        $this->save_static_meta_keys( $tkt_id,$ksd_static_meta_keys,$meta_array );

        //Update the dynamic meta information
        foreach ( $ksd_dynamic_meta_keys as $tkt_info_meta_key => $tkt_info_default_value ) {
            if ( ! isset( $meta_array[$tkt_info_meta_key] ) || -1 == $meta_array[$tkt_info_meta_key] ) {
                continue;//Only do this if the value exists
            }

            $tkt_info_old_value = get_post_meta( $tkt_id, $tkt_info_meta_key, true );

            if ( '' ==  $tkt_info_old_value  ) {//This is a new ticket.
                $tkt_info_meta_value = ( $tkt_info_default_value == $meta_array[$tkt_info_meta_key] ? $tkt_info_default_value : $meta_array[$tkt_info_meta_key] );
                add_post_meta( $tkt_id, $tkt_info_meta_key, $tkt_info_meta_value, true );
                continue;
            }
            if (  $tkt_info_old_value == $meta_array[$tkt_info_meta_key] ) {
                continue;
            }

            $this->update_ticket_activity( $tkt_info_meta_key, $tkt_title, $tkt_id, $tkt_info_old_value, $meta_array[$tkt_info_meta_key] );

            update_post_meta( $tkt_id, $tkt_info_meta_key, $meta_array[ $tkt_info_meta_key ] );
        }

    }

    /**
     * Save static meta keys. These are keys that have values
     * that don't change when a ticket is updated. They are only
     * populated when the ticket is first created
     * @param int $tkt_id                   Ticket ID
     * @param array $ksd_static_meta_keys   The meta keys
     * @param array $meta_array             The ticket's meta array. Note that this is passed by reference
     * @since 2.2.8
     */
    private function save_static_meta_keys( $tkt_id, $ksd_static_meta_keys, &$meta_array ){
        foreach ( $ksd_static_meta_keys as $tkt_info_meta_key ) {
            if( isset( $meta_array[ $tkt_info_meta_key ] ) ){
                       add_post_meta( $tkt_id, $tkt_info_meta_key, $meta_array[ $tkt_info_meta_key ], true );
                       unset( $meta_array[ $tkt_info_meta_key ] );
                   }
        }
    }

    /**
     * Change a ticket's read/unread state
     *
     * @global WP_User $current_user
     * @param int $post_id The ticket's ID
     * @param string $post_title The ticket's title
     * @param string $new_ticket_state read|unread
     */
    private function update_ticket_read_state( $post_id, $post_title, $new_ticket_state ){
        global $current_user;
        $new_ticket_activity = array();
        $new_ticket_activity['post_author']    = 0;
        $new_ticket_activity['post_title']     = $post_title;
        $new_ticket_activity['post_parent']    = $post_id;

        if( 'read' == $new_ticket_state ){
            update_post_meta( $post_id, '_ksd_tkt_info_is_read_by_'.$current_user->ID, 'yes' );
            $new_ticket_activity['post_content']   = sprintf( __( 'Ticket marked as read by %s','kanzu-support-desk' ),  $this->get_user_permalink( $current_user->ID ) );
        }
        if( 'unread' == $new_ticket_state ){
            delete_post_meta( $post_id, '_ksd_tkt_info_is_read_by_'.$current_user->ID );
            $new_ticket_activity['post_content']   = sprintf( __( 'Ticket marked as unread by %s','kanzu-support-desk' ),  $this->get_user_permalink( $current_user->ID ) );
        }
        do_action( 'ksd_insert_new_ticket_activity', $new_ticket_activity );
    }


    /*
     * Get categories options
     */
    public static function get_categories_options() {
        $args = array(
            'taxonomy'      => 'ticket_category',
            'hide_empty'    => 0,
        );
        $categories = get_categories( $args );
        $options = '';
        foreach ( $categories as $category ) {
            $options .= "<option value=" . $category->cat_ID . ">" . esc_html( $category->name ). "</option>";
        }
        return $options;
    }

    /**
     * Get the KSD severity list
     * @since 2.0.0
     */
    public function get_severity_list() {
        return array (
            'low'       => __( 'Low', 'kanzu-support-desk' ),
            'medium'    => __( 'Medium', 'kanzu-support-desk' ),
            'high'      => __( 'High', 'kanzu-support-desk' ),
            'urgent'    => __( 'Urgent', 'kanzu-support-desk' )
        );
    }

    /**
     * Get the KSD status list
     * @since 2.0.0
     */
    public function get_status_list() {
        return array (
            'open'      => __( 'Open', 'kanzu-support-desk' ),
            'pending'   => __( 'Pending', 'kanzu-support-desk' ),
            'resolved'  => __( 'Resolved', 'kanzu-support-desk' )
            );
    }

    /**
     * Get the corresponding localized version of the ticket status
     *
     * @since 2.2.9
     */
    public function get_localized_status( $status ){
        $localized_status='';
        switch( $status ){
            case 'new':
                $localized_status = __( 'New', 'kanzu-support-desk' );
                break;
            case 'open':
                $localized_status = __( 'Open', 'kanzu-support-desk' );
                break;
            case 'pending':
                $localized_status = __( 'Pending', 'kanzu-support-desk' );
                break;
            case 'resolved':
                $localized_status = __( 'Resolved', 'kanzu-support-desk' );
                break;
            case 'draft':
                $localized_status = __( 'Draft', 'kanzu-support-desk' );
                break;

        }
        return $localized_status;
    }

    /**
     * Get status list as select options
     */
    public function get_status_list_options(){
        $options = '';
        foreach( $this->get_status_list() as $value => $status ){
            $options.= '<option value="'.$value.'">'.$status.'</option>';
        }
        return $options;
    }

    /***
     * Create options for the status select item in the
     * submitdiv on the edit/reply ticket view
     * @since 2.0.0
     */
    private function get_submitdiv_status_options() {
        $status_options = '';
        foreach ( $this->get_status_list() as $status => $status_label ) {
            $status_options.="<option value='{$status}'>{$status_label}</option>";
        }
        //Add a 'draft' status
        $status_options.="<option value='draft'>"._x( 'Draft', 'status of a ticket', 'kanzu-support-desk' )."</option>";
        return $status_options;
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */
    public function add_action_links( $links ) {

            return array_merge(
                    array(
                            'settings' => '<a href="' . admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-settings' ) . '">' . __( 'Settings', 'kanzu-support-desk' ) . '</a>'
                    ),
                    $links
            );

    }


    /**
     * Add menu items in the admin panel
     */
    public function add_menu_pages() {
        //Add the top-level admin menu
        $page_title = 'Kanzu Support Desk';
        $capability = 'manage_ksd_settings';
        $menu_slug  = 'edit.php?post_type=ksd_ticket';
        $function   = 'output_admin_menu_dashboard';

        //Add the ticket sub-pages.
        $ticket_types = array();
        $ticket_types['ksd-dashboard']  =   __( 'Dashboard', 'kanzu-support-desk' );
        $ticket_types['ksd-settings']   =   __( 'Settings', 'kanzu-support-desk' );
        $ticket_types['ksd-feedback']   =   __( 'Give Feedback', 'kanzu-support-desk' );

        foreach ( $ticket_types as $submenu_slug => $submenu_title ) {
          add_submenu_page( $menu_slug, $page_title, $submenu_title, $capability, $submenu_slug, array( $this,$function ) );
        }

        //Remove ticket tags
        remove_submenu_page( 'edit.php?post_type=ksd_ticket', 'edit-tags.php?taxonomy=post_tag&amp;post_type=ksd_ticket' );

        //Reset rights in case someone's updating from a very old version
        $this->reset_user_rights();
    }

    /**
     * Temporarily added in 2.2.10 to fix
     * user rights for anyone who upgrades from 2.2.8 and doesn't get the new roles
     *
     * Remove this > 2.2.10
     */
    private function reset_user_rights(){
        if( ! isset( $_GET['post_type'] ) || ! isset( $_GET['taxonomy'] ) ){
            return;
        }
        if( 'ksd_ticket' != sanitize_text_field( $_GET['post_type'] ) ){
            return;
        }
        if( current_user_can( 'manage_options' ) && ! current_user_can( 'manage_ksd_settings' ) && ! current_user_can( 'edit_ksd_ticket' ) ){
            include_once( KSD_PLUGIN_DIR . 'includes/class-ksd-roles.php' );
            KSD()->roles->create_roles();
            KSD()->roles->modify_all_role_caps( 'add' );
            //Make the current user a supervisor. They need to re-select supervisors and agents
            global $current_user;
            KSD()->roles->add_supervisor_caps_to_user( $current_user );
            $user = new WP_User( $current_user->ID );
            KSD()->roles->modify_default_owner_caps( $user, 'add_cap' );
           // KSD_Admin_Notices::add_notice( 'update-roles' );//Inform the user of the changes they need to make
        }
    }
    /**
     * Add the button used to add attachments to a ticket
     * @param string $editor_id The editor ID
     */
    public function add_attachments_button( $editor_id ) {
        if ( ! isset( $_GET['page'] ) ) {
            return;
        }
        if ( strpos ( $editor_id , 'ksd_' ) !== false ) {//Check that we are modifying a KSD wp_editor. Don't modify wp_editor for posts, pages, etc
            echo "<a href='#' id='ksd-add-attachment-{$editor_id}' class='button {$editor_id}'>".__( 'Add Attachment', 'kanzu-support-desk' )."</a>";
        }
    }

    /**
     * Display the main Kanzu Support Desk admin dashboard
     */
    public function output_admin_menu_dashboard() {
            $this->do_admin_includes();

            $settings = Kanzu_Support_Desk::get_settings();

            if ( isset( $_GET['ksd-intro'] ) ) {
                include_once( KSD_PLUGIN_DIR .  'templates/admin/html-admin-intro.php');
            }
            else{
                include_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-settings.php');
                $addon_settings = new KSD_Settings();
                $addon_settings_html = $addon_settings->generate_addon_settings_html();
                include_once( KSD_PLUGIN_DIR .  'templates/admin/html-admin-wrapper.php');
            }
    }


    /**
     * Include the files we use in the admin dashboard
     */
    public function do_admin_includes() {
            include_once( KSD_PLUGIN_DIR.  "includes/libraries/class-ksd-controllers.php");
    }


    /**
     * Returns total tickets in each ticket filter category ie All, Resolved, etc...
     */
    public function filter_totals() {
        if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
              die ( __( 'Busted!', 'kanzu-support-desk') );
        }
        try{
            $this->do_admin_includes();
            $settings = Kanzu_Support_Desk::get_settings();
            $recency = $settings['recency_definition'];
            $tickets = new KSD_Tickets_Controller();
            $response  = $tickets->get_filter_totals( get_current_user_id() ,$recency );
        }catch( Exception $e ) {
            $response = array(
                'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
            );
        }
        echo json_encode( $response );
        if ( !defined( 'PHPUNIT' ) ) die();// IMPORTANT: don't leave this out
    }




    /**
     * Filter tickets in the 'tickets' view
     */
    public function filter_tickets() {
      if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
            die ( __( 'Busted!', 'kanzu-support-desk') );
      }

            try{
                $this->do_admin_includes();
                $value_parameters   =   array();
                switch ( $_POST['ksd_view'] ):
                        case '#tickets-tab-2': //'All Tickets'
                                $filter=" tkt_status != 'RESOLVED'";
                        break;
                        case '#tickets-tab-3'://'Unassigned Tickets'
                                $filter = " tkt_assigned_to IS NULL ";
                        break;
                        case '#tickets-tab-4'://'Recently Updated' i.e. Updated in the last hour.
                                $settings = Kanzu_Support_Desk::get_settings();
                                $value_parameters[] = $settings['recency_definition'];
                                $filter=" tkt_time_updated < DATE_SUB(NOW(), INTERVAL %d HOUR)";
                        break;
                        case '#tickets-tab-5'://'Recently Resolved'.i.e Resolved in the last hour.
                                $settings = Kanzu_Support_Desk::get_settings();
                                $value_parameters[] = $settings['recency_definition'];
                                $filter=" tkt_time_updated < DATE_SUB(NOW(), INTERVAL %d HOUR) AND tkt_status = 'RESOLVED'";
                        break;
                        case '#tickets-tab-6'://'Resolved'
                                $filter=" tkt_status = 'RESOLVED'";
                        break;
                        default://'My Unresolved'
                                $filter = " tkt_assigned_to = ".get_current_user_id()." AND tkt_status != 'RESOLVED'";
                endswitch;


                $offset =   sanitize_text_field( $_POST['offset'] );
                $limit  =   sanitize_text_field( $_POST['limit'] );
                $search =   sanitize_text_field( $_POST['search'] );

                //search
                if ( $search != "" ) {
                    $filter .= " AND UPPER(tkt_subject) LIKE UPPER(%s) ";
                    $value_parameters[] = '%' . $search.'%';
                }

                //order
                $filter .= " ORDER BY tkt_time_updated DESC ";//@since 1.6.2 sort by tkt_time_updated

                //limit
                $count_filter = $filter; //Query without limit to get the total number of rows
                $count_value_parameters =   $value_parameters;
                $filter .= " LIMIT %d , %d " ;
                $value_parameters[] = $offset;//The order of items in $value_parameters is very important.
                $value_parameters[] = $limit;//The order of placeholders should correspond to the order of entries in the array

                //Results count
                $tickets = new KSD_Tickets_Controller();
                $count   = $tickets->get_pre_limit_count( $count_filter,$count_value_parameters );
                $raw_tickets = $this->filter_ticket_view( $filter,$value_parameters );

                if ( empty( $raw_tickets ) ) {
                    $response = __( 'Nothing to see here. Great work!', 'kanzu-support-desk');
                }    else{

                    $response = array(
                        0 => $raw_tickets,
                        1 => $count
                    );

                }

                echo json_encode( $response );
                die();// IMPORTANT: don't leave this out


            }catch( Exception $e ) {
                $response = array(
                    'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
                );
                echo json_encode( $response );
                die();// IMPORTANT: don't leave this out
            }
    }
    /**
     * Filters tickets based on the view chosen
     * @param string $filter The filter [Everything after the WHERE clause] using placeholders %s and %d
     * @param Array $value_parameters The values to replace the $filter placeholders
     */
    public function filter_ticket_view( $filter = "", $value_parameters=array() ) {
            $tickets = new KSD_Tickets_Controller();
            //$tickets_raw = $tickets->get_tickets( $filter,$value_parameters );
            $tickets_raw = $tickets->get_tickets_n_reply_cnt( $filter,$value_parameters );
            //Process the tickets for viewing on the view. Replace the username and the time with cleaner versions
            foreach ( $tickets_raw as $ksd_ticket ) {
                $this->format_ticket_for_viewing( $ksd_ticket );
            }
            return $tickets_raw;
    }

    /**
     * Retrieve a single ticket and all its replies
     */
    public function get_single_ticket() {

        if (!wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce') ) {
            die( __( 'Busted!', 'kanzu-support-desk' ) ); //@TODO Change this to check referrer
        }
        $this->do_admin_includes();
        try {
            $response = get_post( $_POST['tkt_id'] );
            //@TODO Mark the ticket as read. Use custom field
            $this->do_change_read_status( $_POST['tkt_id'] );
        } catch ( Exception $e ) {
            $response = array(
                'error' => array( 'message' => $e->getMessage(), 'code' => $e->getCode() )
            );
        }
        echo json_encode( $response );
        die(); // IMPORTANT: don't leave this out
    }


    /**
     * Get tickets used in the merge tickets list
     */
    public function get_merge_tickets(){
        check_ajax_referer( 'ksd-merging', '_ajax_ksd_merging_nonce' );
        $results    = array();
        $args       = array(
                    'post_type'     => 'ksd_ticket',
                    'posts_per_page'=> 12,
                    'offset'        => 0,
                    'post_status'   => array('new','open','pending','resolved','draft'),
                    'post__not_in'  => array( sanitize_key( $_POST['parent_tkt_ID'] ) )
                );

	if ( isset( $_POST['search'] ) ) {
		$args['s'] = wp_unslash( $_POST['search'] );
	}


        $merge_tickets = get_posts( $args );
        foreach ( $merge_tickets as $ticket ) {
            $results[] = array(
                'ID'            => $ticket->ID,
                'title'         => trim( esc_html( $ticket->post_title ) )
            );
        }
        wp_die( wp_json_encode( $results ) );
    }

    /**
     * Merge two tickets
     */
    public function merge_tickets(){
        check_ajax_referer( 'ksd-merging', '_ajax_ksd_merging_nonce' );
        $parent_ticket_ID  = sanitize_text_field( $_POST['parent_tkt_ID'] );
        $merge_tkt_ID      = sanitize_text_field( $_POST['merge_tkt_ID'] );

        //Make the merge ticket a reply of the parent ticket
        $post_id = $this->transform_ticket_to_reply( $merge_tkt_ID, $parent_ticket_ID );

        if ( 0 == $post_id || is_wp_error( $post_id ) ){
            wp_send_json_error( array(
                'message' => __( 'Sorry, merging the tickets failed. Please retry', 'kanzu-support-desk' )
            ) );
        }

        //Change parent_id of all $merge_tkt_ID replies and notes to $parent_ticket_ID
        $this->change_replies_parent( $merge_tkt_ID, $parent_ticket_ID );

        //Delete $merge_tkt_ID's post meta since $parent_ticket_ID's now takes precedence
        $this->delete_ticket_meta( $merge_tkt_ID );

        //Delete ticket activity since merging it with the current will only become confusing and misleading
        $this->delete_ticket_activities( $merge_tkt_ID );

        //Record this  activity
        global $current_user, $post;
        $new_ticket_activity = array();
        $new_ticket_activity['post_author']    = $current_user->ID;
        $new_ticket_activity['post_title']     = get_the_title( $parent_ticket_ID );
        $new_ticket_activity['post_parent']    = $parent_ticket_ID;
        $new_ticket_activity['post_content']   = sprintf( __( ' merged Ticket #%d into this ticket','kanzu-support-desk' ),  $merge_tkt_ID );
        do_action( 'ksd_insert_new_ticket_activity', $new_ticket_activity );

        wp_send_json_success(
                    array('message' => __( 'Merging completed successfully! Reloading the page...', 'kanzu-support-desk') )
            );
    }

    /**
     * Get a customer's tickets
     * @param int $customer_id
     * @param array $query_params Extra criteria to use to filter the customer's ticket list
     * @since 2.0.0
     */
    public function get_customer_tickets( $customer_id, $query_params = array() ) {
        $my_ticket_args=array();
        $this->do_admin_includes();

        $my_ticket_args['post_type']        = 'ksd_ticket';
        $my_ticket_args['author']           = $customer_id;
        $my_ticket_args['post_status']      = array( 'new', 'open','pending','resolved' );
        $my_ticket_args['posts_per_page']   = -1;
        $my_ticket_args['offset']           = 0;

        if ( ! empty( $query_params ) ){
            $my_ticket_args = array_merge( $my_ticket_args,$query_params );
        }
        return apply_filters( 'ksd_my_tickets_array', get_posts( apply_filters( 'ksd_my_tickets_args', $my_ticket_args ) ) );
    }

    /**
     * Get ticket's replies and private notes
     * @param int $tkt_id The ticket ID
     * @param boolean $get_notes Whether to get private notes or not
     * @since 2.0.0
     */
    public function do_get_ticket_replies_and_notes( $tkt_id, $get_notes = true ) {
         $args = array( 'post_type' => 'ksd_reply', 'post_parent' => $tkt_id, 'order' => 'ASC', 'posts_per_page' => -1, 'offset' => 0 );

         if ( $get_notes || $this->current_user_can_view_private_notes() ) {
            $args['post_type']      = array ( 'ksd_reply', 'ksd_private_note' );
            $args['post_status']    = array ( 'private', 'publish' );
         }

        $replies = get_posts( $args );//@TODO Re-test this. Might need to change it to new WP_Query
        //Replace the reply author ID with the display name and get the reply's attachments
        foreach ( $replies as $reply ) {
            $reply->post_author_display_name = get_userdata( $reply->post_author )->display_name;
            //@TODO Get the reply's attachments

            $reply->post_author_avatar = get_avatar( $reply->post_author, 46 );

            //Change the time to something more human-readable
            $reply->post_date = date_i18n( __( 'g:i A d M Y' ), strtotime( $reply->post_date ) );

            //Format the message for viewing
            $reply->post_content = $this->format_message_content_for_viewing( $reply->post_content );

            //Add reply's CC
            $reply->ksd_cc = get_post_meta( $reply->ID, '_ksd_tkt_info_cc', true );

            //Add reply's Facebook Comment ID
            $reply_comment_id = get_post_meta( $reply->ID, '_ksd_rep_info_comment_id', true );
            if( ! empty( $reply_comment_id ) ){
                $reply->comment_id = $reply_comment_id;
            }
        }
        return $replies;
    }

    /**
     * Get a single ticket's activity
     * @since 2.0.0
     */
    public function get_ticket_activity() {
       if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce') ) {
            die( __('Busted!', 'kanzu-support-desk') );
        }
        $this->do_admin_includes();
        try {
            $args = array( 'post_type' => 'ksd_ticket_activity', 'post_parent' => sanitize_key( $_POST['tkt_id'] ), 'post_status' => 'private' );
            $ticket_activities = get_posts( $args );

            if ( count( $ticket_activities ) > 0 && !empty ( $_POST['tkt_id'] ) ) {
                //Replace the post_author IDs with names
                foreach ( $ticket_activities as $activity ) {
                    $activity->post_author = ( 0 == $activity->post_author ? '' :  get_userdata( $activity->post_author )->display_name );
                    $activity->post_date = date_i18n( __( 'M j, Y @ H:i' ), strtotime( $activity->post_date ) );
                }
            }
            else{
                $ticket_activities = __( 'No activity yet.', 'kanzu-support-desk' );
            }
        } catch ( Exception $e ) {
            $ticket_activities = array(
                'error' => array( 'message' => $e->getMessage(), 'code' => $e->getCode() )
            );
        }
        echo json_encode( $ticket_activities );
        die(); // IMPORTANT: don't leave this out
    }

    /**
     * Delete a ticket
     */
    public function delete_ticket() {
        try{
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
                         die ( __('Busted!', 'kanzu-support-desk') );
            }
                $this->do_admin_includes();
            $tickets = new KSD_Tickets_Controller();

            if ( !is_array( $_POST['tkt_id'] ) ) {
                if ( $tickets->delete_ticket( $_POST['tkt_id'] ) ) {
                    echo json_encode( __( 'Deleted', 'kanzu-support-desk' ) );
                } else {
                    throw new Exception( __( 'Failed', 'kanzu-support-desk' ), -1 );
                }
            } else {
            if ( is_array( $tickets->bulk_delete_tickets( $_POST['tkt_id'] ) ) ) {
                    echo json_encode( __('Tickets Deleted', 'kanzu-support-desk') );
                } else {
                    throw new Exception( __('Ticket Deletion Failed', 'kanzu-support-desk'), -1 );
                }
            }
            die();// IMPORTANT: don't leave this out
        }catch( Exception $e ) {
            $response = array(
                'error'=> array( 'message' => $e->getMessage() , 'code'=>$e->getCode() )
            );
            echo json_encode( $response );
            die();// IMPORTANT: don't leave this out
        }
    }

    /**
     * Change a ticket's status
     */
    public function change_status() {
        if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
                die ( __( 'Busted!', 'kanzu-support-desk' ) );
        }

        try{
            $this->do_admin_includes();
            $tickets = new KSD_Tickets_Controller();

            if ( !is_array( $_POST['tkt_id'] ) ) {//Single ticket update
                $updated_ticket = new stdClass();
                $updated_ticket->tkt_id = $_POST['tkt_id'];
                $updated_ticket->new_tkt_status = $_POST['tkt_status'];

                if ( $tickets->update_ticket( $updated_ticket ) ) {
                    echo json_encode( __( 'Updated', 'kanzu-support-desk') );
                }else {
                    throw new Exception( __( 'Failed', 'kanzu-support-desk') , -1 );
                }
            }
            else{//Update tickets in bulk
                $updateArray = array( "tkt_status" => $_POST['tkt_status'] );
                if ( is_array( $tickets->bulk_update_ticket(  $_POST['tkt_id'], $updateArray ) )  ) {
                    echo json_encode( __( 'Tickets Updated', 'kanzu-support-desk') );
                }else {
                    throw new Exception( __( 'Updates Failed', 'kanzu-support-desk') , -1 );
                }
            }
            die();// IMPORTANT: don't leave this out
        }catch( Exception $e ) {
            $response = array(
                'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
            );
            echo json_encode( $response );
            die();// IMPORTANT: don't leave this out
        }
    }


    /**
     * Change ticket's severity
     * @throws Exception
     */
    public function change_severity() {
        if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
                die ( __( 'Busted!', 'kanzu-support-desk' ) );
        }

        try{
            $this->do_admin_includes();
            $updated_ticket = new stdClass();
            $updated_ticket->tkt_id = $_POST['tkt_id'];
            $updated_ticket->new_tkt_severity = $_POST['tkt_severity'];

            $tickets = new KSD_Tickets_Controller();

            if ( $tickets->update_ticket( $updated_ticket ) ) {
                echo json_encode( __( 'Updated', 'kanzu-support-desk') );
            }else {
                throw new Exception( __( 'Failed', 'kanzu-support-desk') , -1 );
            }
            die();// IMPORTANT: don't leave this out
        }catch( Exception $e ) {
            $response = array(
                'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
            );
            echo json_encode( $response );
            die();// IMPORTANT: don't leave this out
        }
    }

    /**
     * Change a ticket's assignment
     */
    public function assign_to() {
        if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
                die ( __('Busted!', 'kanzu-support-desk') );
        }
       try{
           $this->do_admin_includes();
            $assign_ticket = new KSD_Tickets_Controller();
            if ( !is_array( $_POST['tkt_id'] ) ) {//Single ticket re-assignment
                $updated_ticket = new stdClass();
                $updated_ticket->tkt_id = $_POST['tkt_id'];
                $updated_ticket->new_tkt_assigned_to = $_POST['tkt_assign_assigned_to'];
                $updated_ticket->new_tkt_assigned_by = $_POST['ksd_current_user_id'];
                if ( $assign_ticket->update_ticket( $updated_ticket ) ) {
                    //Add the event to the assignments table
                    $this->do_ticket_assignment( $updated_ticket->tkt_id, $updated_ticket->new_tkt_assigned_to, $updated_ticket->new_tkt_assigned_by );
                    echo json_encode( __( 'Re-assigned', 'kanzu-support-desk' ) );
                } else {
                    throw new Exception( __( 'Failed', 'kanzu-support-desk'), -1 );
                }
            } else {//Bulk re-assignment
                $update_array = array (
                    'tkt_assigned_to' => $_POST['tkt_assign_assigned_to'],
                    'tkt_assigned_by' => $_POST['ksd_current_user_id']
                        );
               if ( is_array( $assign_ticket->bulk_update_ticket( $_POST['tkt_id'], $update_array ) ) ) {
                   //Add event to assignments table
                   foreach ( $_POST['tkt_id'] as $tktID ) {
                    $this->do_ticket_assignment( $tktID, $update_array['tkt_assigned_to'], $update_array['tkt_assigned_by'] );
                   }
                   echo json_encode( __( 'Tickets Re-assigned', 'kanzu-support-desk') );
                } else {
                    throw new Exception( __( 'Ticket Re-assignment Failed', 'kanzu-support-desk'), -1 );
                }
            }
            die();// IMPORTANT: don't leave this out
       }catch( Exception $e ) {
           $response = array(
               'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
           );
           echo json_encode( $response );
           die();// IMPORTANT: don't leave this out
       }
    }

    /**
     * Add a reply to a single ticket
     *
     * @param Array $ticket_reply_array The ticket reply Array. This exists wnen this function is called by an add-on
     * Note that add-ons have to provide tkt_id too. It's retrieved in the check before this function is called
     *
     */

    public function reply_ticket( $ticket_reply_array=null ) {
        //In add-on mode, this function was called by an add-on
        $add_on_mode = ( is_array( $ticket_reply_array ) ? true : false );

        if ( ! $add_on_mode ) {//Check for NONCE if not in add-on mode
            if ( isset( $_POST['ksd_admin_nonce'] ) || isset( $_POST['ksd_new_reply_nonce'] ) ) {
                if ( isset( $_POST['ksd_admin_nonce'] ) &&  ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
                    die ( __( 'Busted!', 'kanzu-support-desk') );
                }
                //Front end reply nonce check
                if ( isset( $_POST['ksd_new_reply_nonce'] ) &&  ! wp_verify_nonce( $_POST['ksd_new_reply_nonce'], 'ksd-add-new-reply' ) ) {
                    die ( __( 'Busted!', 'kanzu-support-desk') );
                }
            }else{
                die ( __( 'Busted!', 'kanzu-support-desk') );
            }
        }

        $this->do_admin_includes();
            try{
                $new_reply = array();
                //If this was called by an add-on, populate the $_POST array
                if ( $add_on_mode ) {
                    $_POST = $ticket_reply_array;
                    if ( isset( $_POST['ksd_rep_created_by'] ) ){
                        $new_reply['post_author'] = $_POST['ksd_rep_created_by'];
                    }
                    if( isset( $_POST['ksd_cust_email'] ) ){
                        $customer_email     = sanitize_email( $_POST['ksd_cust_email'] );
                        $customer_details   = get_user_by ( 'email', $customer_email );
                        if ( $customer_details ){
                            $new_reply['post_author'] = $customer_details->ID;
                        }
                        else{
                            $new_customer               = new stdClass();
                            $new_customer->user_email   = $customer_email;
                            $new_reply['post_author']   = $this->create_new_customer( $new_customer );
                        }
                    }
                }
                else{
                     $new_reply['post_author'] = get_current_user_id();
                }

                $parent_ticket_ID = sanitize_text_field( $_POST['tkt_id'] );
                $new_reply['post_title']      = wp_strip_all_tags( $_POST['ksd_reply_title'] );
                $new_reply['post_parent']     = $parent_ticket_ID;
                //Add KSD reply defaults
                $new_reply['post_type']       = 'ksd_reply';
                $new_reply['post_status']     = 'publish';
                $new_reply['comment_status']  = 'closed ';

                $cc = null;
                if ( isset( $_POST['ksd_tkt_cc'] ) && $_POST['ksd_tkt_cc'] != __( 'CC', 'kanzu-support-desk' ) ) {
                    $new_reply['rep_cc']       = sanitize_text_field( $_POST['ksd_tkt_cc'] );
                    $cc = $_POST['ksd_tkt_cc'];
                }

                if ( isset( $_POST['ksd_rep_date_created']) ) {//Set by add-ons
                    $new_reply['post_date'] =  $this->validate_post_date( sanitize_text_field( $_POST['ksd_rep_date_created'] ) );
                }

                $new_reply['post_content']	 = wp_kses_post( stripslashes( $_POST['ksd_ticket_reply'] )  );
                if ( strlen( $new_reply['post_content'] ) < 2 && ! $add_on_mode ) {//If the response sent it too short
                   throw new Exception( __( "Error | Reply too short", 'kanzu-support-desk' ), -1 );
                }

                //Add the reply to the replies table
                $new_reply_id = wp_insert_post( $new_reply );

                if( ! $add_on_mode  )// Allow addons not in add_on_mode to do something
                    do_action( 'ksd_new_reply_created', $parent_ticket_ID, $new_reply_id );

                if ( null !== $cc ) {
                    add_post_meta( $new_reply_id , '_ksd_tkt_info_cc', $cc, true );
                }

                //Mark ticket as unread @TODO Update this.
                $this->mark_ticket_reply_unread( $parent_ticket_ID );

                //Update the main ticket's tkt_time_updated field.
                $parent_ticket = get_post( $parent_ticket_ID );
                $parent_ticket->post_modified = current_time( 'mysql' );
                wp_update_post( $parent_ticket );

                //Do notifications
                if ( $parent_ticket->post_author == $new_reply['post_author'] ) {//This is a reply from the customer. Notify the assignee
                    $notify_user = $this->get_ticket_assignee_to_notify( $parent_ticket_ID );
                }
                else{//This is a reply from an agent. Notify the customer
                    $notify_user 		= get_userdata( $parent_ticket->post_author );
                }

                $parent_ticket_channel = get_post_meta( $parent_ticket_ID, '_ksd_tkt_info_channel', true );

                /**
                 * @filter `ksd_reply_logged_notfxn_email_message_{$parent_ticket_channel}` Right after a ticket reply is logged, this is applied to the message content of the email notification to be sent to the customer/agent. $parent_ticket_channel is the channel used to log the parent ticket
                 *
                 * @param string $new_reply_content The reply to be sent
                 * @param int $parent_ticket_ID Ticket ID of the parent ticket
                 */
                $ticket_reply_message = apply_filters( 'ksd_reply_logged_notfxn_email_message_'.$parent_ticket_channel, $new_reply['post_content'], $parent_ticket_ID );
                $ticket_reply_message .= Kanzu_Support_Desk::output_ksd_signature( $parent_ticket_ID );

                /**
                 * @filter `ksd_reply_logged_notfxn_email_subject_{$parent_ticket_channel}` Right after a ticket reply is logged, this is applied to the message subject of the email notification to be sent to the customer/agent. $parent_ticket_channel is the channel used to log the parent ticket
                 *
                 * @param string $new_reply_subject The reply to be sent
                 * @param int $parent_ticket_ID Ticket ID of the parent ticket
                 */
                $ticket_reply_subject = apply_filters( 'ksd_reply_logged_notfxn_email_subject', $parent_ticket->post_title, $parent_ticket_ID );

                //Like all good replies, prepend a Re:
                $ticket_reply_subject = 'Re:'.$ticket_reply_subject;

                $addon_tkt_id = ( isset( $_POST['ksd_addon_tkt_id'] ) ? $_POST['ksd_addon_tkt_id'] : 0 );
                $ticket_reply_headers = array();
                /**
                 * @filter `ksd_reply_logged_notfxn_email_headers_{$parent_ticket_channel}` Right after a ticket reply is logged, this is applied to the message headers of the email notification to be sent to the customer/agent. $parent_ticket_channel is the channel used to log the parent ticket
                 *
                 * @param array $ticket_reply_headers Headers of the email being sent
                 * @param WP_Post Object $parent_ticket The parent ticket
                 * @param int $addon_tkt_id The ID received from the add-on that logged this reply if this reply came from an add-on. Otherwise, it'll be 0
                 */
                $ticket_reply_headers	= apply_filters( 'ksd_reply_logged_notfxn_email_headers_'.$parent_ticket_channel, $ticket_reply_headers, $parent_ticket, $addon_tkt_id );


                $this->send_email( $notify_user->user_email, $ticket_reply_message, $ticket_reply_subject, $cc, array(), 0, $ticket_reply_headers );

                if ( $add_on_mode && ! isset( $_POST['ksd_public_reply_form'] ) ) {//ksd_public_reply_form is set for replies from the public reply form

                   /**
                    * @filter `ksd_new_reply_logged` Run when a new reply to a ticket is created.
                    *
                    * @param int $addon_tkt_id The ID specified by the add-on that logged this ticket
                    * @param int $new_reply_id The ID of the newly-created reply
                    */
                   do_action( 'ksd_new_reply_logged', $addon_tkt_id , $new_reply_id );


                   /**
                    * @filter 'ksd_new_reply_logged_'.{$parent_ticket_channel} Run when a new reply is logged.
                    * $parent_ticket_channel is the channel used to log the parent ticket. This allows particular
                    * addons to run custom actions when a new channel-specific reply is logged and not on every reply
                    *
                    * @since 2.3.4
                    *
                    * @param int $addon_tkt_id The ID specified by the add-on that logged this ticket
                    * @param int $new_reply_id The ID of the newly-created reply
                    */
                   do_action( 'ksd_new_reply_logged_'.$parent_ticket_channel, $addon_tkt_id , $new_reply_id );
                   return;//End the party if this came from an add-on. All an add-on needs if for the reply to be logged
               }

               if ( $new_reply_id > 0 ) {
                  //Add 'post_author' to the response
                   $new_reply['post_author'] = get_userdata ( $new_reply['post_author'] )->display_name;
                  echo json_encode(  $new_reply  );
               }else{
                   throw new Exception( __( "Error", 'kanzu-support-desk' ), -1 );
               }
               die();// IMPORTANT: don't leave this out
            }catch( Exception $e ) {
                $response = array(
                    'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
                );
                echo json_encode( $response );
                die();// IMPORTANT: don't leave this out
            }

    }

    /**
     * Remove all 'Ticket read' meta values
     * @param int $parent_ticket_id
     */
    private function mark_ticket_reply_unread( $parent_ticket_id ){
       $post_meta = get_post_meta( $parent_ticket_id );
       foreach( $post_meta as $meta_key => $meta_value ){
           if( false !== strpos( $meta_key, '_ksd_tkt_info_is_read_by_' ) ){
               delete_post_meta( $parent_ticket_id, $meta_key );
           }
       }
    }

    /**
     * Validate the post date before saving a post. This is usually set by add-ons
     * Adapted from wp-includes/post.php
     *
     * @param Date $post_date
     * @return Date in form 0000-00-00 00:00:00
     * @since 2.0.0
     */
    private function validate_post_date( $post_date ) {
        if ( empty( $post_date ) || '0000-00-00 00:00:00' == $post_date ) {
           return current_time('mysql');
        }
        // validate the date
        $mm = substr( $post_date, 5, 2 );
        $jj = substr( $post_date, 8, 2 );
        $aa = substr( $post_date, 0, 4 );
        $valid_date = wp_checkdate($mm, $jj, $aa, $post_date );
        if ( !$valid_date ) {
            return current_time('mysql');
        }
        return $valid_date;
    }

    /**
     * Filter KSD ticket message content in new tickets and in replies before it is displayed
     * or before it is sent in an email
     * This ensures that:
     *                   Double line-breaks in the text are converted into HTML paragraphs (<p>...</p>).
     *                   Returns given text with transformations of quotes to smart quotes, apostrophes, dashes, ellipses, the trademark symbol, and the multiplication symbol.
     * @param string $raw_message The ticket message
     * @return string The formatted message
     * @since 1.7.0s
     */
    private function format_message_content_for_viewing( $raw_message ) {
        return wpautop( wptexturize( str_replace( ']]>', ']]&gt;', $raw_message ) ) );//wpautop does the <p> replacements, wptexturize does the transformations
    }

    /**
     * Log new tickets or replies initiated by add-ons
     * Generally, this is called whenever a new ticket is logged
     * using the action ksd_log_new_ticket [and not the AJAX version
     * of the same action]
     *
     * @param Array $new_ticket New ticket array. Can also be a reply array
     *
     * @since 1.0.1
     */
    public function do_log_new_ticket( $new_ticket ) {
        $this->do_admin_includes();
        $new_ticket['is_reply'] = false;

        /**
         * @filter `ksd_new_ticket_or_reply` An incoming KSD ticket/reply. Add-ons should
         * modify $new_ticket and set $new_ticket['is_reply'] = true for this to be considered
         * a reply. If it is a reply, the add-on should also set the ticket parent ID in $new_ticket['tkt_id']
         */
        $new_ticket = apply_filters( 'ksd_new_ticket_or_reply', $new_ticket );

        //Handle Facebook channel replies
        if( 'Facebook Reply' == $new_ticket['ksd_tkt_subject'] ){
            $new_ticket['ksd_reply_title']              = $new_ticket['ksd_tkt_subject'];
            $new_ticket['ksd_ticket_reply']             = $new_ticket['ksd_tkt_message'];
            $new_ticket['ksd_rep_date_created']         = $new_ticket['ksd_tkt_time_logged'];
            $this->reply_ticket( $new_ticket );
            return;
        }

        if( $new_ticket['is_reply'] ){
            $this->reply_ticket( $new_ticket );
            return;
        }

        //This is a new ticket
        $this->log_new_ticket( $new_ticket, true );
    }

    /**
     * Decide whether an incoming ticket is a reply
     *
     * @param array $new_ticket The incoming ticket
     *
     */
    public function set_is_ticket_a_reply( $new_ticket ){
        if( false !== strpos( $new_ticket['ksd_tkt_subject'], '~' ) ){
            $ticket_subject_array   = explode( '~', $new_ticket['ksd_tkt_subject'] );
            $new_ticket[ 'tkt_id' ] = end( $ticket_subject_array );
            $new_ticket['is_reply'] = true;
        }
        return $new_ticket ;
    }




    /**
     * Log new ticket reply
     *
     * @sine 2.2.12
     *
     * @param type $new_ticket The reply to the ticket
     */
    public function do_reply_ticket ( $new_ticket ) {
        $this->do_admin_includes();

        $customer_details = get_user_by ( 'email', $new_ticket['ksd_cust_email'] );
        $new_ticket['ksd_tkt_cust_id']                = $customer_details->ID;

        if ( false === $customer_details ) { //Customer does not exist
            $cust_email           = sanitize_email( $new_ticket_raw['ksd_cust_email'] );//Get the provided email address
            //Check that it is a valid email address. Don't do this check in add-on mode
            if ( !is_email( $cust_email ) ) {
                 throw new Exception( __('Error | Invalid email address specified', 'kanzu-support-desk') , -1 );
            }
            $new_customer = new stdClass();
            $new_customer->user_email           = $cust_email;
            //Check whether one or more than one customer name was provided
            if ( false === strpos( trim( sanitize_text_field( $new_ticket_raw['ksd_cust_fullname'] ) ), ' ') ) {//Only one customer name was provided
               $new_customer->first_name   =   sanitize_text_field( $new_ticket_raw['ksd_cust_fullname'] );
            }
            else{
               preg_match('/(\w+)\s+([\w\s]+)/', sanitize_text_field( $new_ticket_raw['ksd_cust_fullname'] ), $new_customer_fullname );
                $new_customer->first_name   = $new_customer_fullname[1];
                $new_customer->last_name    = $new_customer_fullname[2];//We store everything besides the first name in the last name field
            }
            //Add the customer to the user table and get the customer ID
            $new_ticket['post_author']    =  $this->create_new_customer( $new_customer );
        }

        $new_ticket['ksd_tkt_cust_id'] = $customer_details->ID;

        $ticket_reply['tkt_id']                       = $new_ticket['ksd_tkt_id'];
        $ticket_reply['ksd_reply_title']              = $new_ticket['ksd_tkt_subject'];
        $ticket_reply['ksd_ticket_reply']             = $new_ticket['ksd_tkt_message'];
        $ticket_reply['ksd_rep_created_by']           = $new_ticket['ksd_tkt_cust_id'];
        $ticket_reply['ksd_rep_date_created']         = $new_ticket['ksd_tkt_time_logged'];

        //Add addon ticket ID
        if( isset( $new_ticket['ksd_addon_tkt_id'] ) ){
            $ticket_reply['ksd_addon_tkt_id']         = $new_ticket['ksd_addon_tkt_id'];
        }

        $this->reply_ticket( $ticket_reply );
    }


    /**
     * Check whether a new ticket already exists. If it does, return its current ID
     * We check against the ticket subject and author
     *
     * @param Object $new_ticket The new ticket to check
     * @param boolean $disable_ticket_author_check Whether to check against the ticket author. We don't check if the check's being done
     *                                              for a member of staff since they are not the ticket creator
     * @returns int  $ticket_id 0 if the ticket doesn't exist. The ticket's ID if it does
     *
     * @since 2.2.9
     */
    public function check_if_ticket_exists( $new_ticket, $disable_ticket_author_check=false ){
        global $wpdb;
        $ticket_id = 0;

        $TC = new KSD_Tickets_Controller();
        $value_parameters   = array();
        $filter             = " post_type = %s AND post_status != %s ";
        $value_parameters[] = 'ksd_ticket' ;
        $value_parameters[] = 'trash' ;

        if( ! $disable_ticket_author_check ){
            $filter             .= " AND post_author = %d ";
            $value_parameters[] = $new_ticket['ksd_tkt_cust_id'] ;
        }

        $TC->set_tablename( "{$wpdb->prefix}posts" );

        $customers_previous_tickets = $TC->get_tickets( $filter, $value_parameters );

        if ( count( $customers_previous_tickets ) > 0  ) {
            foreach( $customers_previous_tickets as $a_ticket ){
                if( false !== strpos( $new_ticket['ksd_tkt_subject'], $a_ticket->post_title ) ){
                    $ticket_id = $a_ticket->ID;
                    break;
                }
            }
        }
        return $ticket_id;
    }



    /**
     * Check if the user's a member of staff. The only users  considered as staff
     * are agents, supervisors and administrators
     *
     * @param Object $user The user to check
     * @return boolean Whether the user's a member of staff or not
     */
    public function is_user_staff( $user ){
       if( ! isset( $user->roles ) || ! is_array( $user->roles )  ){
           return false;
       }
       if( in_array( 'ksd_agent', $user->roles ) || in_array( 'ksd_supervisor', $user->roles ) || in_array( 'administrator', $user->roles ) ){
           return true;
       }
       return false;
    }

    /**
     * Convert a reply's object into a $_POST array
     * @param int $ticket_ID The parent ticket's ID
     * @param Object $reply The reply's object
     * @since 1.7.0
     */
    private function convert_reply_object_to_post( $ticket_ID, $reply ) {
        $_POST                          = array();
        $_POST['tkt_id']                = $ticket_ID; //The ticket ID
        $_POST['ksd_ticket_reply']      = $reply->tkt_message; //Get the reply
        $_POST['ksd_rep_created_by']    = $reply->tkt_cust_id; //The customer's ID
        $_POST['ksd_rep_date_created']  = $reply->tkt_time_logged; //@since 1.6.2
        $_POST['ksd_addon_tkt_id']      = $reply->addon_tkt_id; //The add-on's ID for this ticket

        if ( isset( $reply->tkt_cc ) ) {
            $_POST['ksd_tkt_cc'] = $reply->tkt_cc;
        }
        return $_POST;
    }


    /**
     * Change a new ticket object into a $_POST array. $POST arrays are
     * used by the functions that log new tickets & replies
     * Add-ons on the other hand supply $new_ticket objects. This function
     * is a bridge between the two
     * @param Object $new_ticket New ticket object
     * @return Array $_POST An array used by the functions that log new tickets. This
     *                      array is basically the same as the object but has ksd_ prefixing all keys
     */
    private function convert_ticket_object_to_post( $new_ticket ) {
        $_POST = array();
        foreach ( $new_ticket as $key => $value ) {
           $_POST['ksd_' . $key ] = $value;
        }
        return $_POST;
    }

    /**
     * Send agent replies to customers
     * @param int $customer_ID The customer's ID
     * @param string $message The message to send to the customer
     * @param string $subject The message subject
     * @return N/A
     */
    private function send_agent_reply( $customer_ID, $message, $subject ) {
        $cust_info = get_userdata( $customer_ID );
        $this->send_email( $cust_info->user_email, $message, 'Re: ' . $subject );
    }

    /**
     * Get the ticket assignee of a new reply or ticket
     * If no assignee exists, return the primary admin
     * @return Object User
     * @since 2.0.0
     */
    private function get_ticket_assignee_to_notify( $tkt_id ) {
        //$parent_ticket_ID, $new_reply['post_content'], 'Re: '. $parent_ticket->post_title,$cc
        $assignee_id = get_post_meta( $tkt_id, '_ksd_tkt_info_assigned_to', true );
        if ( empty( $assignee_id ) || 0 != $assignee_id ) {//No assignee
            $assignee_id = 1;
        }
        return get_userdata( $assignee_id );
    }


    /**
     * Log new tickets.  The different channels (admin side, front-end) all
     * call this method to log the ticket. Other plugins call $this->do_new_ticket_logging  through
     * an action
     * @param Array $new_ticket_raw A new ticket array. This is present when ticket logging was initiated
     *              by an add-on and from the front-end
     * @param boolean $from_addon Whether the ticket was initiated by an addon or not
     */
    public function log_new_ticket( $new_ticket_raw=null, $from_addon = false ) {
            if ( null == $new_ticket_raw ) {
                $new_ticket_raw = $_POST;
            }
           /* if ( ! $from_addon ) {//Check for NONCE if not in add-on mode
                if ( ! wp_verify_nonce( $new_ticket_raw['new-ticket-nonce'], 'ksd-new-ticket' ) ) {
                         die ( __('Busted!', 'kanzu-support-desk') );
                }
            }//@TODO Update this
            */

            $this->do_admin_includes();

            try{
            $supported__ticket_channels = apply_filters( 'ksd_channels', array ( "admin-form","support-tab","email","sample-ticket", "facebook" ) );
            $tkt_channel                = sanitize_text_field( $new_ticket_raw['ksd_tkt_channel']);
            if ( ! in_array( $tkt_channel, $supported__ticket_channels ) ) {
                throw new Exception( __('Error | Unsupported channel specified', 'kanzu-support-desk'), -1 );
            }

            $ksd_excerpt_length = 30;//The excerpt length to use for the message

            //Apply the pre-logging filter
            $new_ticket_raw = apply_filters( 'ksd_insert_ticket_data', $new_ticket_raw  );

            //We sanitize each input before storing it in the database
            $new_ticket = array();
            $new_ticket['post_title']           = sanitize_text_field( stripslashes( $new_ticket_raw['ksd_tkt_subject'] ) );
            $sanitized_message                  = wp_kses_post( stripslashes( $new_ticket_raw['ksd_tkt_message'] ) );
            $new_ticket['post_excerpt']         = wp_trim_words( $sanitized_message, $ksd_excerpt_length );
            $new_ticket['post_content']         = $sanitized_message;
            $new_ticket['post_status']          = ( isset( $new_ticket_raw['ksd_tkt_status'] ) && in_array( $new_ticket_raw['ksd_tkt_status'], array( 'new', 'open', 'pending', 'draft', 'resolved' ) ) ? sanitize_text_field( $new_ticket_raw['ksd_tkt_status'] ) : 'open' );

            if ( isset( $new_ticket_raw['ksd_cust_email'] ) ) {
                $new_ticket['ksd_cust_email']   = sanitize_email( $new_ticket_raw['ksd_cust_email'] );
            }

            if ( isset( $new_ticket_raw['ksd_tkt_time_logged'] ) ) {//Set by add-ons
                $new_ticket['post_date']        = $new_ticket_raw['ksd_tkt_time_logged'];
            }//No need for an else; if this isn't specified, the current time is automatically used

            if( isset( $_POST['ksd_tkt_attachment_ids'] ) ){
                $new_ticket_raw['ksd_attachment_ids'] = $_POST['ksd_tkt_attachment_ids'];
            }

            //Server side validation for the inputs. Only holds if we aren't in add-on mode
            if ( ( ! $from_addon && strlen( $new_ticket['post_title'] ) < 2 || strlen( $new_ticket['post_content'] ) < 2 ) ) {
                 throw new Exception( __('Error | Your subject and message should be at least 2 characters', 'kanzu-support-desk'), -1 );
            }

            //Get the settings. We need them for tickets logged from the support tab
            $settings = Kanzu_Support_Desk::get_settings();

            //Return a different message based on the channel the request came on
            $output_messages_by_channel = array();
            $output_messages_by_channel['admin-form'] = __( 'Ticket Logged. Sending notification...', 'kanzu-support-desk');
            $output_messages_by_channel['support-tab'] = $output_messages_by_channel['email'] = $output_messages_by_channel['facebook'] = $settings['ticket_mail_message'];
            $output_messages_by_channel['sample-ticket'] = __( 'Sample tickets logged.', 'kanzu-support-desk');

            global $current_user;
            if ( 'facebook' !=  $tkt_channel && 'sample-ticket' != $tkt_channel && $current_user->ID > 0 ) {//If it is a valid user
                $new_ticket['post_author']  = $current_user->ID;
                $cust_email                 = $current_user->user_email;
            }
            elseif ( isset ( $new_ticket_raw['ksd_tkt_cust_id'] ) ) {//From addons
                //@TODO Agents should not log tickets via add-ons otherwise the customer bug arises
                $new_ticket['post_author']  = $new_ticket_raw['ksd_tkt_cust_id'];
                $cust_email                 = $new_ticket_raw['ksd_cust_email'];
            }
            elseif ( get_user_by ( 'email', $new_ticket['ksd_cust_email'] )  ) {//Customer's already in the Db, get their customer ID
                $customer_details           = get_user_by ( 'email', $new_ticket['ksd_cust_email'] );
                $new_ticket['post_author']  = $customer_details->ID;
                $cust_email                 = $customer_details->user_email;
            }
            else{//The customer isn't in the Db. Let's add them. This is from an add-on
                $cust_email           = sanitize_email( $new_ticket_raw['ksd_cust_email'] );//Get the provided email address
                //Check that it is a valid email address. Don't do this check in add-on mode
                if ( !is_email( $cust_email ) ) {
                     throw new Exception( __('Error | Invalid email address specified', 'kanzu-support-desk') , -1 );
                }
                $new_customer = new stdClass();
                $new_customer->user_email           = $cust_email;
                //Check whether one or more than one customer name was provided
                if ( false === strpos( trim( sanitize_text_field( $new_ticket_raw['ksd_cust_fullname'] ) ), ' ') ) {//Only one customer name was provided
                   $new_customer->first_name   =   sanitize_text_field( $new_ticket_raw['ksd_cust_fullname'] );
                }
                else{
                   preg_match('/(\w+)\s+([\w\s]+)/', sanitize_text_field( $new_ticket_raw['ksd_cust_fullname'] ), $new_customer_fullname );
                    $new_customer->first_name   = $new_customer_fullname[1];
                    $new_customer->last_name    = $new_customer_fullname[2];//We store everything besides the first name in the last name field
                }
                //Add the customer to the user table and get the customer ID
                $new_ticket['post_author']    =  $this->create_new_customer( $new_customer );
            }

           //@TODO Separate action to log a private note needed

            //Add KSD ticket defaults
            $new_ticket['post_type']      = 'ksd_ticket';
            $new_ticket['comment_status'] = 'closed';

            //Add post password
            if( "no" == $settings['enable_customer_signup'] ){
                $post_password              = wp_generate_password( 5 );
                $new_ticket['post_password']= $post_password;
            }

            //Log the ticket
            $new_ticket_id = wp_insert_post( $new_ticket );

            //Add product to ticket
            if ( isset( $new_ticket_raw['ksd_tkt_product_id']) && intval( $new_ticket_raw['ksd_tkt_product_id'] ) > 0 ) {
               wp_set_object_terms( $new_ticket_id, intval( $new_ticket_raw['ksd_tkt_product_id'] ), 'product' );

            }

            //Add category to ticket
            if ( isset( $new_ticket_raw['ksd_tkt_cat_id'] ) ) {
                $cat_id = intval( $new_ticket_raw['ksd_tkt_cat_id'] );
                wp_set_object_terms( $new_ticket_id, $cat_id, 'ticket_category');
            }

            //Add meta fields
            $meta_array     = array();
            $meta_array['_ksd_tkt_info_channel']        = $tkt_channel;
            if( $tkt_channel == 'facebook' ){
                $meta_array['_ksd_tkt_info_post_id'] = $new_ticket_raw['ksd_addon_tkt_id'];
            }

            if ( wp_get_referer() ){
                $meta_array['_ksd_tkt_info_referer']    = wp_get_referer();
            }
            if ( isset( $new_ticket_raw['ksd_tkt_cc'] ) && $new_ticket_raw['ksd_tkt_cc'] != __( 'CC', 'kanzu-support-desk' ) ) {
                $meta_array['_ksd_tkt_info_cc']     = sanitize_text_field( $new_ticket_raw['ksd_tkt_cc'] );
            }

            //These other fields are only available if a ticket is logged from the admin side so we need to
            //first check if they are set
            if ( isset( $new_ticket_raw['ksd_tkt_severity'] ) ) {
                $meta_array['_ksd_tkt_info_severity']   =  $new_ticket_raw['ksd_tkt_severity'] ;
            }
            if ( isset( $new_ticket_raw['ksd_tkt_assigned_to'] ) && !empty( $new_ticket_raw['ksd_tkt_assigned_to'] ) ) {
                $meta_array['_ksd_tkt_info_assigned_to']            =  $new_ticket_raw['ksd_tkt_assigned_to'] ;
            }
            //If the ticket wasn't assigned by the user, check whether auto-assignment is set so we auto-assign it
            if ( empty( $new_ticket_raw['ksd_tkt_assigned_to' ] ) &&  !empty( $settings['auto_assign_user'] ) ) {
                $meta_array['_ksd_tkt_info_assigned_to']            = $settings['auto_assign_user'];
            }
            if ( isset( $new_ticket_raw['ksd_woo_order_id'] ) ) {
                $meta_array['_ksd_tkt_info_woo_order_id']           = $new_ticket_raw['ksd_woo_order_id'];
            }


            //Whom to we notify. Defaults to admin if ticket doesn't have an assignee
            $notify_user_id = ( isset( $meta_array['_ksd_tkt_info_assigned_to'] )? $meta_array['_ksd_tkt_info_assigned_to'] : 1 );
            $notify_user    = get_userdata(  $notify_user_id );

            //Create a hash URL
            if( "no" == $settings['enable_customer_signup'] ){
                include_once( KSD_PLUGIN_DIR.  "includes/admin/class-ksd-hash-urls.php" );
                $hash_urls = new KSD_Hash_Urls();
                $meta_array[ '_ksd_tkt_info_hash_url' ] = $hash_urls->create_hash_url( $new_ticket_id );
            }

            //Save ticket meta info
            $this->save_ticket_meta_info( $new_ticket_id, $new_ticket['post_title'], $meta_array );

            $new_ticket_status = (  $new_ticket_id > 0  ? $output_messages_by_channel[ $tkt_channel ] : __("Error", 'kanzu-support-desk') );

            //Save the attachments
            if ( isset( $new_ticket_raw['ksd_attachments'] ) ) {
                $this->add_ticket_attachments( $new_ticket_id, $new_ticket_raw['ksd_attachments'] );
            }
            if( isset( $new_ticket_raw['ksd_attachment_ids'] ) ){
                $this->save_ticket_attachments( $new_ticket_id, $new_ticket_raw['ksd_attachment_ids'] );
            }

            //If the ticket was logged by using the import feature, end the party here
            if ( isset( $new_ticket_raw['ksd_tkt_imported'] ) ) {
               do_action( 'ksd_new_ticket_imported', array( $new_ticket_raw['ksd_tkt_imported_id'], $new_ticket_id ) );
               return;
            }

            $cc = isset( $new_ticket_raw['ksd_tkt_cc'] ) && __( 'CC', 'kanzu-support-desk' ) !== $new_ticket_raw['ksd_tkt_cc']  ? $new_ticket_raw['ksd_tkt_cc'] : null;

           //Notify the customer that their ticket has been logged. CC is only used for tickets logged by admin-form
            if ( "yes" == $settings['enable_new_tkt_notifxns'] &&  $tkt_channel  ==  "support-tab" ) {
                $this->send_email( $cust_email , "new_ticket", $new_ticket['post_title'], $cc, array(), $new_ticket['post_author'] );
            }

            //For add-ons to do something after new ticket is added. We share the ID and the final status
            if ( isset( $new_ticket_raw['ksd_addon_tkt_id'] ) ) {
                do_action( 'ksd_new_ticket_logged', $new_ticket_raw['ksd_addon_tkt_id'], $new_ticket_id );
            }

            //@TODO If $tkt_channel  ==  "admin-form", notify the customer
            //@TODO If agent logs new ticket by addon, notify the customer
            if ( $tkt_channel  !==  "admin-form" && $tkt_channel  !==  "sample-ticket" ) {//Notify the agent
                $ksd_attachments = ( isset ( $new_ticket_raw['ksd_attachments'] ) ? $this->convert_attachments_for_mail( $new_ticket_raw['ksd_attachments'] ) : array() );
                $this->do_notify_new_ticket( $notify_user->user_email, $new_ticket_id, $cust_email, $new_ticket['post_title'], $new_ticket['post_content'], $ksd_attachments );
            }

            //If this was initiated by the email add-on, end the party here
            if ( "yes" == $settings['enable_new_tkt_notifxns'] &&  $tkt_channel  ==  "email") {
                 $email_subject = $new_ticket['post_title'] . " ~ {$new_ticket_id}";
                 $this->send_email( $cust_email, "new_ticket", $email_subject, $cc, array(), $new_ticket['post_author'] );//Send an auto-reply to the customer

                 return;
            }

            if ( $from_addon ) {
                return true; //For addon mode to ensure graceful exit from function.
            }

            echo json_encode( $new_ticket_status );
            if ( !defined( 'PHPUNIT' ) ) die();// IMPORTANT: don't leave this out

            }catch( Exception $e ) {
                $response = array(
                    'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
                );
                echo json_encode($response );
                if ( !defined( 'PHPUNIT' ) ) die();// IMPORTANT: don't leave this out
            }
    }

    /**
     * Update a ticket's activity
     *
     * @param string $changed_item The meta key to change
     * @param string $ticket_title The title of the ticket being affected
     * @param string $ticket_id The ticket's ID
     * @param string $old_value The old value
     * @param string $new_value The new value
     *
     * @since 2.0.0
     */
    private function update_ticket_activity( $changed_item, $ticket_title, $ticket_id, $old_value, $new_value ) {
       $this->do_admin_includes();
       try {
            $new_ticket_activity = array();
            $new_ticket_activity['post_title']     = $ticket_title;
            $new_ticket_activity['post_parent']    = $ticket_id;
            //Add KSD ticket activity defaults
            $new_ticket_activity['post_type']      = 'ksd_ticket_activity';
            $new_ticket_activity['post_status']    = 'private';
            $new_ticket_activity['comment_status'] = 'closed ';
            //Note that the person who did this assignment is captured in the post_author field which is autopopulated by current user's ID
            switch ( $changed_item ) {
                case '_ksd_tkt_info_severity':
                        $old_value = ( '' == $old_value ? 'low' : $old_value );
                        $activity_content = sprintf( __( 'changed severity from %1$s to %2$s', 'kanzu-support-desk' ), $old_value, $new_value );
                    break;
                case '_ksd_tkt_info_assigned_to':
                    $old_value_name = ( 0 == $old_value ? __( 'No One', 'kanzu-support-desk' ) : $this->get_user_permalink( $old_value ) );
                    $new_value_name = ( 0 == $new_value ? __( 'No One', 'kanzu-support-desk' ) :  $this->get_user_permalink( $new_value ) );
                    $activity_content = sprintf( __( 're-assigned ticket from %1$s to %2$s', 'kanzu-support-desk' ), $old_value_name, $new_value_name );
                    //Send an email to notify the new assignee
                    $this->do_notify_ticket_reassignment( $new_value, $ticket_id );
                    break;
                case '_ksd_tkt_info_customer':
                    $old_value_name =  ( 0 == $old_value ? __( 'No One', 'kanzu-support-desk' ) : $this->get_user_permalink( $old_value ) );
                    $new_value_name =  ( 0 == $new_value ? __( 'No One', 'kanzu-support-desk' ) : $this->get_user_permalink( $new_value ) );
                    $activity_content = sprintf( __( ' created ticket for %1$s', 'kanzu-support-desk' ), $new_value_name );
                    break;
                default:
                    return false;//Any unsupported meta key, end the party here
            }

            $new_ticket_activity['post_content'] = $activity_content;

            //Save the assignment
            $new_ticket_activity_id = wp_insert_post( $new_ticket_activity );

            if ( $new_ticket_activity_id > 0 ) {
                return true;
            } else {
                return false;
            }
        } catch ( Exception $e ) {
            return false;
        }
    }

    /**
     * Add attachment(s) to a ticket
     * Call this after $this->do_admin_includes() is called
     * @param int $ticket_id The ticket or reply's ID
     * @param Array $attachments_array Array containing the attachments
     * The array is of the form:
                    Array
                    (
                        [0] => Array(
                               [url]        => http://url/filename.txt,
                               [size]       =>  724 B,
                               [filename]   =>  filename.txt
                            ),
                        [1] => Array(
                               [url]        => http://url/filename.jpg,
                               [size]       =>  146 kB,
                               [filename]   =>  filename.jpg
                            )
     *                )
     * @param Boolean $is_reply Whether this is a reply or a ticket
     */
    private function add_ticket_attachments( $ticket_id, $attachments_array, $is_reply=false ) {
        $attachment_ids = array();
        foreach ( $attachments_array as $attachment ) {
            $filename = $attachment['url'];
            $filetype = wp_check_filetype( basename( $filename ), null );

            $attachment = array(
                    'guid'           => $filename,
                    'post_mime_type' => $filetype,
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
            );

            //Insert the attachment.
            $attach_id = wp_insert_attachment( $attachment, $filename, $ticket_id );
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            $attachment_ids[] = $attach_id;
        }

        $this->save_ticket_attachments( $ticket_id, $attachment_ids );
    }

    /**
     * Append attachments to a ticket
     * @param int $ticket_id
     * @param array $attachment_ids
     */
    private function save_ticket_attachments( $ticket_id, $attachment_ids ){
        add_post_meta( $ticket_id, '_ksd_tkt_attachments', $attachment_ids );
    }

    /**
     * Modify the ticket's attachments array for sending in mail.
     * The mail attachments array only contains filenames
     * @param Array $tickets_attachments_array The ticket attachment's array
     * @return Array $mail_attachments_array The attachments array to add to mail
     * @since 1.7.0
     */
    private function convert_attachments_for_mail( $tickets_attachments_array ) {
        $mail_attachments_array = array();
        $upload_dir = wp_upload_dir();
        $attachments_dir = $upload_dir['basedir'] . '/ksd/attachments/';
        foreach ( $tickets_attachments_array['filename'] as $single_attached_file ) {
            $mail_attachments_array[] = $attachments_dir. $single_attached_file;
        }
        return $mail_attachments_array;
    }

    /**
     * Replace a ticket's logged_by field with the nicename of the user who logged it
     * Replace the tkt_time_logged with a date better-suited for viewing
     * NB: Because we use {@link KSD_Users_Controller}, call this function after {@link do_admin_includes} has been called.
     * @param Object $ticket The ticket to modify
     * @param boolean $single_ticket_view Whether we are in single ticket view or not
     */
    private function format_ticket_for_viewing( $ticket, $single_ticket_view = false ) {
        //If the ticket was logged by an agent from the admin end, then the username is available in wp_users. Otherwise, we retrive the name
        //from the KSD customers table
       // $tmp_tkt_assigned_by = ( 'admin-form' === $ticket->tkt_channel ? $users->get_user($ticket->tkt_assigned_by)->display_name : $CC->get_customer($ticket->tkt_assigned_by)->cust_firstname );
        $tkt_user_data      =  get_userdata( $ticket->tkt_cust_id );
        $tmp_tkt_cust_id    =  $tkt_user_data->display_name;
        if ( $single_ticket_view ) {
            $tmp_tkt_cust_id.=  ' <' . $tkt_user_data->user_email.'>';
            $ticket->tkt_message = $this->format_message_content_for_viewing( $ticket->tkt_message );
        }
        //Replace the tkt_assigned_by name with a prettier one
        $ticket->tkt_cust_id = str_replace($ticket->tkt_cust_id,$tmp_tkt_cust_id,$ticket->tkt_cust_id );
        //Replace the date
        $ticket->tkt_time_logged = date('M d',strtotime($ticket->tkt_time_logged ) );

        return $ticket;
    }


    /**
     * Generate the ticket volumes displayed in the graph in the dashboard
     */
    public function get_dashboard_ticket_volume() {
        try{
             if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
                     die ( __('Busted!', 'kanzu-support-desk') );
             }
            $this->do_admin_includes();
            $tickets = new KSD_Tickets_Controller();
            $tickets_raw = $tickets->get_dashboard_graph_statistics();
            //If there are no tickets, the road ends here
            if ( count( $tickets_raw ) < 1 ) {
                $response = array(
                    'error'=> array(
                            'message' => __( "No logged tickets. Graphing isn't possible", "kanzu-support-desk") ,
                            'code'=> -1 )
                );
                echo json_encode($response );
                die();// IMPORTANT: don't leave this out
            }

            $y_axis_label = __( 'Day', 'kanzu-support-desk');
            $x_axis_label = __( 'Ticket Volume', 'kanzu-support-desk');

            $output_array = array();
            $output_array[] = array( $y_axis_label,$x_axis_label );

            foreach ( $tickets_raw as $ticket ) {
                    $output_array[] = array ( date_format( date_create($ticket->date_logged ),'d-m-Y') ,( float )$ticket->ticket_volume );//@since 1.1.2 Added casting since JSON_NUMERIC_CHECK was kicked out
            }
            echo json_encode( $output_array );//@since 1.1.2 Removed JSON_NUMERIC_CHECK which is only supported PHP >=5.3
            die();//Important

        }catch( Exception $e ) {
            $response = array(
                'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
            );
            echo json_encode($response );
            die();// IMPORTANT: don't leave this out
        }
    }
    /**
     * Get the statistics that show on the dashboard, above the graph
     */
    public function get_dashboard_summary_stats() {
        if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
                     die ( __('Busted!', 'kanzu-support-desk') );
        }
        $this->do_admin_includes();
        try{
            $tickets = new KSD_Tickets_Controller();
            $summary_stats = $tickets->get_dashboard_statistics_summary();
            //Compute the average. We do this here rather than using AVG in the DB query to take the load off the Db
            $total_response_time = 0;
            foreach ( $summary_stats["response_times"] as $response_time ) {
                $total_response_time+=$response_time->time_difference;
            }
            //Prevent division by zero
            if ( count($summary_stats["response_times"]) > 0 ) {
                $summary_stats["average_response_time"] = date('H:i:s', $total_response_time/count($summary_stats["response_times"]) ) ;
            }else{
                $summary_stats["average_response_time"] = '00:00:00';
            }
            echo json_encode ( $summary_stats );
        }catch( Exception $e ) {
            $response = array(
                'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
            );
            echo json_encode( $response );
        }
         die();// IMPORTANT: don't leave this out
    }

     /**
      * Update all settings
      */
     public function update_settings() {
        if ( ! wp_verify_nonce( $_POST['update-settings-nonce'], 'ksd-update-settings' ) ) {
            die ( __('Busted!', 'kanzu-support-desk') );
        }
        try{
            $old_settings = $updated_settings = Kanzu_Support_Desk::get_settings();//Get current settings

            //Iterate through the new settings and save them. We skip all multiple checkboxes; those are handled later. As of 1.5.0, there's only one set of multiple checkboxes, ticket_management_roles
            foreach ( $updated_settings as $option_name => $current_value ) {

                //Unset recapcha secret if it contains ********* i.e. password has not been set or changed
                if ( 'recaptcha_secret_key' === $option_name ) {
                    if( false !== strpos( $_POST['recaptcha_secret_key'], '************************************' ) ) {
                        continue;
                    }
                }

                if ( $option_name == 'ticket_management_roles' ) {
                    continue;//Don't handle multiple checkboxes in here @since 1.5.0
                }
                if ( $option_name == 'ticket_mail_message' ) {//Support HTML in ticket message @since 1.7.0
                    $updated_settings[$option_name] = ( isset ( $_POST[$option_name] ) ? wp_kses_post ( stripslashes ( $_POST[$option_name] ) ) : $updated_settings[$option_name] );
                    continue;
                }
                $updated_settings[$option_name] = ( isset ( $_POST[$option_name] ) && ! is_array( $_POST[$option_name] ) ? sanitize_text_field ( stripslashes ( $_POST[$option_name] ) ) : $updated_settings[$option_name] );
            }
            //For a checkbox, if it is unchecked then it won't be set in $_POST
            $checkbox_names = array("show_support_tab","tour_mode","enable_new_tkt_notifxns","enable_recaptcha","enable_notify_on_new_ticket","enable_anonymous_tracking","enable_customer_signup",
                    "supportform_show_categories","supportform_show_severity","supportform_show_products","show_woo_support_tickets_tab"
            );
            //Iterate through the checkboxes and set the value to "no" for all that aren't set
            foreach ( $checkbox_names as $checkbox_name ) {
                 $updated_settings[$checkbox_name] = ( !isset ( $_POST[$checkbox_name] ) ? "no" : $updated_settings[$checkbox_name] );
            }
            //Now handle the multiple checkboxes. As of 1.5.0, only have ticket_management_roles. If it isn't set, use administrator
            $updated_settings['ticket_management_roles'] = !isset( $_POST['ticket_management_roles'] ) ? "administrator" : $this->convert_multiple_checkbox_to_setting( $_POST['ticket_management_roles'] );

            //Apply the settings filter to get settings from add-ons
            $updated_settings = apply_filters( 'ksd_settings', $updated_settings, $_POST );

            $status = false;
            if ( $old_settings === $updated_settings ){//update_option returns false when there is no change to the settings
                $status = true;
            }else{
                $status = update_option( KSD_OPTIONS_KEY, $updated_settings );
            }

            if( true === $status){
                do_action( 'ksd_settings_saved' );
               echo json_encode(  __( 'Settings Updated', 'kanzu-support-desk' ) );
            }else{
                throw new Exception( __( 'Update failed. Please retry.', 'kanzu-support-desk'), -1 );
            }
            die();
        }catch( Exception $e ) {
            $response = array(
                'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
            );
            echo json_encode($response );
            die();// IMPORTANT: don't leave this out
        }
     }

     /**
      * Reset settings to default
      */
     public function reset_settings() {
        if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
            die ( __('Busted!', 'kanzu-support-desk') );
         }
         try{
            $ksd_install    = KSD_Install::get_instance();
            $base_settings  = $ksd_install->get_default_options();
            //Add the settings from add-ons
            $base_settings = apply_filters( 'ksd_settings', $base_settings );
            $status = update_option( KSD_OPTIONS_KEY, $base_settings );
            if ( $status ) {
                echo json_encode( __( 'Settings Reset', 'kanzu-support-desk') );
            }else{
                throw new Exception( __( 'Reset failed. Please retry', 'kanzu-support-desk'), -1 );
            }
            die();
        }catch( Exception $e ) {
            $response = array(
                'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
            );
            echo json_encode($response );
            die();// IMPORTANT: don't leave this out
        }
     }

     /**
      * Retrieve and display the list of add-ons
      * @since 1.1.0
      */
     public function load_ksd_addons() {
        ob_start();
        if ( false === ( $cache = get_transient( 'ksd_add_ons_feed' ) ) ) {
            $feed = wp_remote_get( 'https://kanzucode.com/?feed=ksdaddons', array( 'sslverify' => false ) );
            if ( ! is_wp_error( $feed ) ) {
                    if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
                            $cache = wp_remote_retrieve_body( $feed );
                            set_transient( 'ksd_add_ons_feed', $cache, 3600 );
                    }
            } else {
                    $cache = '<div class="add-on-error add-ons"><p>' . __( 'Sorry, an error occurred while retrieving the add-ons list. A re-attempt will be made later. Thank you.', 'kanzu-support-desk' ) . '</div>';
            }
        }
    echo $cache;
    echo ob_get_clean();
    }

     /**
      * Update a ticket's private note
      */
     public function update_private_note() {
        //  if ( ! wp_verify_nonce( $_POST['edit-ticket-nonce'], 'ksd-edit-ticket' ) ) {//@TODO Update this
        //	 die ( __('Busted!', 'kanzu-support-desk') );
        //   }
        $this->do_admin_includes();
        try {
            $new_private_note = array();
            $new_private_note['post_title']     = wp_strip_all_tags($_POST['ksd_reply_title']);
            $new_private_note['post_parent']    = sanitize_text_field($_POST['tkt_id']);
            //Add KSD private_note defaults
            $new_private_note['post_type']      = 'ksd_private_note';
            $new_private_note['post_status']    = 'private';
            $new_private_note['comment_status'] = 'closed ';

            $new_private_note['post_content'] = wp_kses_post( stripslashes($_POST['tkt_private_note']) );
            if ( strlen( $new_private_note['post_content'] ) < 2 ) {//If the private note sent it too short
                throw new Exception( __("Error | Private Note too short", 'kanzu-support-desk'), -1 );
            }
            //Save the private_note
            $new_private_note_id = wp_insert_post( $new_private_note );

            if ( $new_private_note_id > 0 ) {
                //Add 'post_author' to the response
                $new_private_note['post_author'] = get_userdata ( get_current_user_id() )->display_name;
                $response = $new_private_note;
            } else {
                throw new Exception( __('Failed', 'kanzu-support-desk'), -1 );
            }
        } catch ( Exception $e ) {
            $response = array(
                'error' => array('message' => $e->getMessage(), 'code' => $e->getCode() )
            );
        }
        echo json_encode( $response );
        die(); // IMPORTANT: don't leave this out
    }

    /**
     * Update a ticket's information
     * @since 2.0.0
     */
    public function update_ticket_info() {
       if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
             die ( __('Busted!', 'kanzu-support-desk') );
        }
        $this->do_admin_includes();
        try {
            $tkt_id = wp_strip_all_tags( $_POST['tkt_id'] );
            $ticket_title = wp_strip_all_tags( $_POST['ksd_reply_title'] );
            $tkt_info = array();
            parse_str(  wp_strip_all_tags ( $_POST['ksd_tkt_info'] ), $tkt_info ) ;

            //Update ticket status
            $post = get_post( $tkt_id );
            $post->post_status = $tkt_info['_ksd_tkt_info_status'];
            wp_update_post( $post );

            //Update the meta information
            foreach ( $tkt_info as $tkt_info_meta_key => $tkt_info_new_value ) {
                    if ( get_post_meta( $tkt_id, $tkt_info_meta_key, true ) == $tkt_info_new_value )
                            continue;
                    $this->update_ticket_activity( $tkt_info_meta_key, $ticket_title, $tkt_id, get_post_meta( $tkt_id, $tkt_info_meta_key, true ), $tkt_info_new_value );
                    update_post_meta( $tkt_id, $tkt_info_meta_key, $tkt_info_new_value );
                }

            $response = __( 'Ticket information updated', 'kanzu-support-desk' );

        } catch ( Exception $e ) {
            $response = array(
                'error' => array('message' => $e->getMessage(), 'code' => $e->getCode() )
            );
        }
        echo json_encode( $response );
        die(); // IMPORTANT: don't leave this out
    }


     /**
      * Mark a ticket as read or unread
      * @param int $ticket_ID The ticket ID
      * @param boolean $mark_as_read Whether to mark the ticket as read or not
      */
     private function do_change_read_status( $ticket_ID, $mark_as_read = true ) {
        $ticket_read_status = ( $mark_as_read ? 1 : 0 );
        $updated_ticket                  = new stdClass();
        $updated_ticket->tkt_id          = $ticket_ID;
        $updated_ticket->new_tkt_is_read = $ticket_read_status;
        $TC = new KSD_Tickets_Controller();
        return $TC->update_ticket( $updated_ticket );
    }


    /**
     * Change ticket's read status
     * @throws Exception
     */
    public function change_read_status() {
        if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
                die ( __('Busted!', 'kanzu-support-desk') );
        }

        try{
            $this->do_admin_includes();
            if ( $this->do_change_read_status( $_POST['tkt_id'], $_POST['tkt_is_read'] ) ) {
                echo json_encode( __( 'Ticket updated', 'kanzu-support-desk') );
            }else {
                throw new Exception( __( 'Update Failed. Please retry', 'kanzu-support-desk') , -1 );
            }
            die();// IMPORTANT: don't leave this out
        }catch( Exception $e ) {
            $response = array(
                'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode() )
            );
            echo json_encode($response );
            die();// IMPORTANT: don't leave this out
        }
    }


    /**
      * Create a new customer in wp_users
      * @param Object $customer The customer object
      */
     private function create_new_customer( $customer ) {
                $username = sanitize_user( preg_replace('/@(.)+/', '',$customer->user_email ) );//Derive a username from the emailID
                //Ensure username is unique. Adapted from WooCommerce
                $append     = 1;
                $new_username = $username;

                while ( username_exists( $username ) ) {
                    $username = $new_username . $append;
                    $append ++;
                }
                $password = wp_generate_password();//Generate a random password
                //First name
                $first_name = empty( $customer->first_name ) ? $username : $customer->first_name;

                $userdata = array(
                    'user_login'    => $username,
                    'user_pass'     => $password,
                    'user_email'    => $customer->user_email,
                    'display_name'  => empty( $customer->last_name ) ? $first_name : $first_name.' ' . $customer->last_name,
                    'first_name'    => $first_name,
                    'role'          => 'ksd_customer'
                );
                if ( !empty( $customer->last_name ) ) {//Add the username if it was provided
                    $userdata['last_name']  =   $customer->last_name;
                }
                $user_id = wp_insert_user( $userdata ) ;
                if ( !is_wp_error($user_id ) ) {
                    return $user_id;
                }
                return false;
        }

     /**
      * Disable tour mode
      * @since 1.1.0
      */
     public function disable_tour_mode() {
        $ksd_settings = Kanzu_Support_Desk::get_settings();
        $ksd_settings['tour_mode'] = "no";
        Kanzu_Support_Desk::update_settings( $ksd_settings );
        echo json_encode( 1 );
        die();
     }

     /**
      * Enable usage statistics
      * @since 1.6.7
      */
     public function enable_usage_stats() {
        $ksd_settings = Kanzu_Support_Desk::get_settings();
        $ksd_settings['enable_anonymous_tracking'] = "yes";
        Kanzu_Support_Desk::update_settings($ksd_settings );
        echo json_encode( __('Successfully enabled. Thank you!', 'kanzu-support-desk') );
        die();
    }
    /**
     * AJAX callback to send notification of a new ticket
     */
    public function notify_new_ticket() {
        // if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
       //       die ( __('Busted!', 'kanzu-support-desk') );
       // } //@TODO Update this NONCE check
          $this->do_notify_new_ticket();
          echo json_encode( __('Notification sent.', 'kanzu-support-desk') );
          die();//IMPORTANT. Shouldn't be left out
    }

     /**
      * Notify the primary administrator that a new ticket has been logged
      * The wp_mail call in send_mail takes a while (about 5s in our tests)
      * so for tickets logged in the admin side, we call this using AJAX
      * @param string $notify_email Email to notify
      * @param int $tkt_id
      * @param string $customer_email The email of the customer for whom the new ticket has been created
      * @param string $ticket_subject The new ticket's subject
      * @param Array $attachments Filenames to attach to the notification
      * @since 1.5.5
      */
     private function do_notify_new_ticket( $notify_email, $tkt_id, $customer_email = null, $ticket_subject = null, $ticket_message = null, $attachments = array() ) {
        $ksd_settings = Kanzu_Support_Desk::get_settings();
        // The blogname option is escaped with esc_html on the way into the database in sanitize_option
        // we want to reverse this for the plain text arena of emails.
        $blog_name = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );
        $notify_new_tkt_message  = sprintf( __( 'New customer support ticket on your site %s:', 'kanzu-support-desk'), $blog_name ) . "\r\n\r\n";
        if ( !is_null( $customer_email ) ) {
            $notify_new_tkt_message .= sprintf( __( 'Customer E-mail: %s', 'kanzu-support-desk'), $customer_email ) . "\r\n\r\n";
        }
        if ( !is_null( $ticket_subject ) ) {
            $notify_new_tkt_message .= sprintf( __( 'Ticket Subject: %s', 'kanzu-support-desk'), $ticket_subject ) . "\r\n\r\n";
        }
        if ( !is_null( $ticket_message ) ) {
            $notify_new_tkt_message .= sprintf( __( 'Ticket Message: %s', 'kanzu-support-desk'), $ticket_message ) . "\r\n\r\n";
        }
        $notify_new_tkt_message .= Kanzu_Support_Desk::output_ksd_signature( $tkt_id );
        $notify_new_tkt_subject = sprintf( __( '[%s] New Support Ticket', 'kanzu-support-desk' ), $blog_name );

        //Use two filters, ksd_new_ticket_notifxn_message and ksd_new_ticket_notifxn_subject, to make changes to the
        //the notification message and subject by add-ons
        $this->send_email( $notify_email, apply_filters( 'ksd_new_ticket_notifxn_message', $notify_new_tkt_message, $ticket_message , $ksd_settings, $tkt_id ), apply_filters( 'ksd_new_ticket_notifxn_subject', $notify_new_tkt_subject, $ticket_subject , $ksd_settings, $tkt_id ), null, $attachments );


     }

     /**
      * Notify an agent when a ticket has been re-assigned to them
      * @param int $new_user_id The ID of the agent to whom the ticket has been reassigned
      * @param int $tkt_id The ID of the ticket that's been re-assigned
      * @since 2.2.0
      * @since 2.2.9 Params $agent_name and $notify_email replaced by $new_user_id
      */
     public function do_notify_ticket_reassignment( $new_user_id, $tkt_id ){
         if( $new_user_id == 0 ){
             return;
         }
        $agent_name     = get_userdata( $new_user_id )->display_name;
        $notify_email   = get_userdata( $new_user_id )->user_email;
        $blog_name = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );
        $notify_tkt_reassign_message  = sprintf( __('Hi %1$s, A support ticket has been reassigned to you on %2$s:', 'kanzu-support-desk'), $agent_name, $blog_name ) . "\r\n\r\n";
        $notify_tkt_reassign_message .= Kanzu_Support_Desk::output_ksd_signature( $tkt_id );
        $notify_tkt_reassign_subject = sprintf( __('[%s] Support Ticket Reassigned to you', 'kanzu-support-desk' ), $blog_name );
        $this->send_email( $notify_email, $notify_tkt_reassign_message, $notify_tkt_reassign_subject );
     }

     /**
      * Return the HTML for a feedback form
      * @param string $position The position of the form
      * @param string $send_button_text Submit button text
      * @TODO Move this to templates folder
      */
     public static function output_feeback_form( $position, $send_button_text='Send' ) {
        $form = '<form action="#" class="ksd-feedback-' . $position.'" method="POST">';
        $form.= '<p><textarea name="ksd_user_feedback" rows="5" cols="100"></textarea></p>';
        $form.= '<input name="action" type="hidden" value="ksd_send_feedback" />';
        $form.= '<input name="feedback_type" type="hidden" value="' . $position.'" />';
        $form.= wp_nonce_field( 'ksd-send-feedback', 'feedback-nonce' );
        $form.= '<p><input type="submit" class="button-primary '.$position.'" name="ksd-feedback-submit" value="' . $send_button_text.'"/></p>';
        $form.= '</form>';
        $form.= '<div class="ksd-feedback-response"></div>';
        return $form;
     }

     public function send_debug_email(){
          $email = sanitize_email( $_POST['email'] );
          if ( ! is_email( $email ) ){
            wp_send_json_error( __( 'Error | Invalid email address specified','kanzu-support-desk' ) );
          }
          $message = __( 'This is the test message you requested for. Signed. Sealed. Delivered.', 'kanzu-support-desk' );
          if( $this->send_email( $email, $message ) ){
             wp_send_json_success( __( 'Email sent successfully','kanzu-support-desk' ) );
          }else{
             wp_send_json_error( sprintf( __( 'Error | Email sending failed. Please <a href="%s" target="_blank">read our guide on this</a>', 'kanzu-support-desk' ), 'https://kanzucode.com/knowledge_base/troubleshooting-wordpress-email-delivery/' ) );
          }
     }

     public function reset_role_caps(){
        include_once( KSD_PLUGIN_DIR . 'includes/class-ksd-roles.php' );
        KSD()->roles->modify_all_role_caps( 'add' );
        KSD()->roles->reset_customer_role_caps();
        wp_send_json_success( __( 'Roles reset. All should be well now','kanzu-support-desk' ) );
     }


     public function get_unread_ticket_count(){
        global $current_user;
        $args = array(
                    'post_type'     => 'ksd_ticket',
                    'posts_per_page'=>-1,
                    'post_status'   => array('new','open','pending'),
                    'meta_key'      => '_ksd_tkt_info_is_read_by_' . $current_user->ID,
                    'meta_compare'  => 'NOT EXISTS'
                );
        $query = new WP_Query( $args );
        if( $query->found_posts > 0 ){
            wp_send_json_success( $query->found_posts );
        }else{
            wp_send_json_error();
        }
     }

     /**
      * Send the KSD team feedback
      * @since 1.1.0
      */
     public function send_feedback() {
        $feedback_type  =  isset( $_POST['feedback_type'] ) ?  sanitize_text_field( $_POST['feedback_type'] ) : 'default';
        $user_feedback  = sanitize_text_field( $_POST['ksd_user_feedback'] );
        $current_user = wp_get_current_user();

        $data = array();
        $data['action'] = 'feedback';
        $data['feedback_item'] = 'kanzu-support-desk';
        $data['feedback_type'] = $feedback_type;
        $data['user_feedback'] = $user_feedback;
        $data['user_email'] = $current_user->user_email;

        if (  'waiting_list' == $feedback_type ) {
            $this->send_to_analytics( $data );
            $addon_message =  "{$user_feedback},{$current_user->user_email}";
            $response = ( $this->send_email( "feedback@kanzucode.com", $addon_message, "KSD Add-on Waiting List") ? __('Sent successfully. Thank you!', 'kanzu-support-desk') : __('Error | Message not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk') );
            echo json_encode( $response );
            die();
        }

        if ( strlen( $user_feedback ) <= 2 ) {
            $response = __("Error | The feedback field's empty. Please type something then send", "kanzu-support-desk");
            echo json_encode( $response );
            die();
        }

        $this->send_to_analytics( $data );
        $feedback_message = "{$user_feedback},{$feedback_type}";
        $response = ( $this->send_email( "feedback@kanzucode.com", $feedback_message, "KSD Feedback") ? __('Sent successfully. Thank you!', 'kanzu-support-desk') : __('Error | Message not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk') );
        echo json_encode( $response );
        die();
    }

    /**
     * Send feedback using the form in contextual help
     *
     * @since 2.3.6
     */
    public function send_support_tab_feedback(){

        $current_user   = wp_get_current_user();

        $subject = sanitize_text_field( $_POST['ksd_support_tab_subject'] );
        $message = sanitize_text_field( $_POST['ksd_support_tab_message'] );


        if ( strlen( $subject ) <= 2  ) {
            $response = __("Error | The subject field is too short. Please type something then send", "kanzu-support-desk");
            echo json_encode( $response );
            die();
        }

        if ( strlen( $message ) <= 2  ) {
            $response = __("Error | The message field is too short. Please type something then send", "kanzu-support-desk");
            echo json_encode( $response );
            die();
        }

        $feedback_message = "{$message},{$current_user->display_name},{$current_user->user_email}";
        $feedback_subject = "KSD Feedback - ".$subject;

        $response = ( $this->send_email( "feedback@kanzucode.com", $feedback_message, $feedback_subject ) ? __('Sent successfully. Thank you!', 'kanzu-support-desk') : __('Error | Message not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk') );
        echo json_encode( $response );
        die();
    }

    /**
     * Send feedback to Kanzu Analytics
     * @param array $data
     * @return array
     */
    public function send_to_analytics( $data ){
        return wp_remote_post( 'http://analytics.kanzucode.com/wp-json/kanzu-analytics/v1/api', array(
            'method'      => 'POST',
            'timeout'     => 20,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking'    => true,
            'body'        => $data,
        ) );
    }

     /**
      * Send mail.
      *
      * @param string $to Recipient email address
      * @param string $message The message to send. Can be "new_ticket"
      * @param string $subject The message subject
      * @param string $cc  A comma-separated list of email addresses to cc
      * @param Array $attachments Array of attachment filenames
      * @param int $customer_ID The customer's user ID
      * @param Array $extra_headers Extra email header fields @since 2.2.12
      * @deprecated 2.3.1
      */
     public function send_email( $to, $message="new_ticket", $subject=null, $cc=null,  $attachments= array(), $customer_ID=0, $extra_headers = array() ) {
        $settings = Kanzu_Support_Desk::get_settings();
        switch ( $message ):
            case 'new_ticket'://For new ticket auto-replies
                $message   = $settings['ticket_mail_message'];
                if( 0 !== $customer_ID ){
                   $customer = get_userdata( $customer_ID );
                   $message  =  preg_replace( '/{customer_display_name}/', $customer->display_name, $message );
                }
        endswitch;
        if( isset( $settings['ticket_mail_from_name'] ) && ! empty( $settings['ticket_mail_from_name'] ) && isset( $settings['ticket_mail_from_email'] ) && ! empty( $settings['ticket_mail_from_email'] ) ){
            $headers[] = 'From: ' . $settings['ticket_mail_from_name'].' <' . $settings['ticket_mail_from_email'].'>';
        }
        $headers[] = 'Content-Type: text/html; charset=UTF-8'; //@since 1.6.4 Support HTML emails
        if ( !is_null( $cc ) && ! empty( $cc ) ) {
            $headers[] = "Cc: $cc";
        }

        if( is_array( $extra_headers ) ){
            $headers = array_merge( $extra_headers, $headers );
        }

        return wp_mail( $to, $subject, $this->format_message_content_for_viewing( $message ), $headers, $attachments );
     }



     /**
      * Retrieve Kanzu Support Desk notifications. These are currently
      * retrieved from the KSD blog feed, http://blog.kanzucode.com/feed/
      * @since 1.3.2
      */
     public function get_notifications() {
        ob_start();
        if ( false === ( $cache = get_transient( 'ksd_notifications_feed' ) ) ) {
            $feed = wp_remote_get( 'http://kanzucode.com/work/blog/kanzu-support-desk-articles/feed/', array( 'sslverify' => false ) );
            if ( ! is_wp_error( $feed ) ) {
                    if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
                            $cache = wp_remote_retrieve_body( $feed );
                            set_transient( 'ksd_notifications_feed', $cache, 86400 );//Check everyday
                    }
            } else {
                $cache["error"] =  __( 'Sorry, an error occurred while retrieving the latest notifications. A re-attempt will be made later. Thank you.', 'kanzu-support-desk');
            }
        }
        echo json_encode( $cache );
        echo ob_get_clean();
        die();
     }

     /**
      * Convert the multiple checkbox input, which is an array in $_POST, into a setting,
      * which is a string of the values separated by |. We save them this way since we use
      * them in an SQL REGEXP which uses them as is
      * e.g. SELECT field1,field2 from table where REGEXP 'value1|value2|value3'
      * @param Array $multiple_checbox_array An array of the checked checkboxes in a set of multiple checkboxes
      * @return string A |-separated list of the checked values
      * @since 1.5.0
      */
     private function convert_multiple_checkbox_to_setting( $multiple_checbox_array ) {
         $setting_string = "administrator";//By default, the administrator has access
         foreach ( $multiple_checbox_array as $checkbox ) {
             $setting_string.="|". $checkbox;
         }
         return $setting_string;
     }

     /**
     * Append plugin to active plugin list
     * @since    1.1.1
     *
     */
    public static function append_to_activelist( $active_addons ) {
        $active_addons['ksd-mail'] =  'ksd-mail/ksd-mail.php';
        return $active_addons;
    }


    /**
     * Add KSD tickets import to the wordpress tools toolbox
     * @since   1.5.2
     */
    public  function add_importer_to_toolbox() {
        echo '
            <div class="tool-box">
                <h3 class="title"> ' . __( 'KSD Importer', 'kanzu-support-desk' ) . '</h3>
                 <p>
                 Import tickets into Kanzu Support Desk. Use the  <a href="?import=ksdimporter">KSD Importer </a>
                 </p>
            </div>
        ';
    }

    /**
     * Hand this over to the function in the Import class
     * @param int $imported_ticket_id
     * @param int $logged_ticket_id
     */
    public function new_ticket_imported( $imported_ticket_id, $logged_ticket_id ) {
        $importer = new KSD_Importer () ;
        $importer->new_ticket_imported( $imported_ticket_id, $logged_ticket_id );
    }

    /**
     * Initialize the KSD importer; it enables users to import
     * tickets into KSD
     * ksd_importer_init
     * @since   1.5.4
     */
    public function ksd_importer_init() {

        $id             = 'ksdimporter';
        $name           = __( 'KSD Importer', 'kanzu-support-desk' );
        $description    = __( 'Import support tickets into the Kanzu Support Desk plugin.', 'kanzu-support-desk' );

        include_once( KSD_PLUGIN_DIR.  "includes/libraries/class-ksd-importer.php" );
        $importer = new KSD_Importer ( ) ;
        $callback    = array( $importer, 'dispatch' );
        register_importer ( $id, $name, $description, $callback ) ;
    }

    /**
     * Handle an AJAX request to change the license's status. We use this to activate
     * and deactivate licenses
     */
    public function modify_license_status() {
    if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ) {
            die ( __('Busted!', 'kanzu-support-desk') );
      }

      $response = $this->do_license_modifications( $_POST['license_action'],$_POST['plugin_name'],$_POST['plugin_author_uri'],$_POST['plugin_options_key'],$_POST['license_key'],$_POST['license_status_key'],sanitize_text_field( $_POST['license'] ) );
      echo json_encode( $response );
      die();//Important. Don't leave this out
    }

    /**
     * For KSD plugins, make a remote call to Kanzu Code to activate/Deactivate/check license status
     * @param string $action The action to perform on the license. Can be 'activate_license', 'deactivate_license' or 'check_license'
     * @param string $plugin_name The plugin name
     * @param string $plugin_author_uri Plugin author's URI
     * @param string $plugin_options_key The plugin options key used to store its options in the KSD options array
     * @param string $license_key The key used to store the license
     * @param string $license_status_key The key used to store the license status
     * @param string $license The license to check
     * @return string $response Returns a response message
     */
    public function do_license_modifications( $action, $plugin_name, $plugin_author_uri, $plugin_options_key, $license_key, $license_status_key, $license = NULL ) {
            $response_message = __( 'Sorry, an error occurred. Please retry or reload the page', 'kanzu-support-desk' );

             /*Retrieve the license from the database*/
            //First get overall settings
            $base_settings = get_option( KSD_OPTIONS_KEY );
            //Check that the key exists
            $plugin_settings = ( isset ( $base_settings[ $plugin_options_key ] ) ? $base_settings[ $plugin_options_key ] : array() );

            if ( is_null( $license ) ) {
                $license    =   trim( $plugin_settings[ $license_key ] );
            }

            // data to send in our API request
            $api_params = array(
                    'edd_action'     => $action,
                    'license'                       => $license,
                    'item_name'                     => urlencode( $plugin_name ), // the name of our product in EDD
                    'url'                           => home_url()
            );
           $response = wp_remote_post( $plugin_author_uri, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

            // make sure the response came back okay
            if ( is_wp_error( $response ) ) {
                    return $response_message;
            }
            // decode the license data
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            switch ( $action ) {
                case 'activate_license':
                case 'check_license':
                    if ( $license_data && 'valid' == $license_data->license ) {
                        $plugin_settings[ $license_status_key ] = 'valid';
                        $addons = $this->get_installed_addons();
                        $response_message = __('License successfully validated. Welcome to a super-charged Kanzu Support Desk! Please reload the page.', 'kanzu-support-desk' );
                        foreach( $addons as $addon ){
                            if( $plugin_options_key === $addon ){
                                $response_message = apply_filters( 'ksd_message_succ_addon_lic_activation_' . $addon, $response_message );
                            }
                        }
                    }
                    else{//Invalid license
                        $plugin_settings[ $license_status_key ] = 'invalid';
                        $response_message = apply_filters( 'ksd_message_invalid_addon_license',__( 'Invalid License. Please renew your license', 'kanzu-support-desk' ) );
                    }
                    break;
                case 'deactivate_license':
                    if ( $license_data && 'deactivated' == $license_data->license ) {
                        $plugin_settings[ $license_status_key ] = 'invalid';
                        $response_message =apply_filters( 'ksd_message_succ_addon_lic_deactivation', __( 'Your license has been deactivated successfully. Thank you.', 'kanzu-support-desk' ) );
                    }
                    break;
            }
            //Retrieve the license for saving
            $plugin_settings[ $license_key ] = $license;

            //Update the Db
            $base_settings[ $plugin_options_key ] = $plugin_settings;
            update_option( KSD_OPTIONS_KEY, $base_settings );

            return $response_message;
    }

    /**
     * Generates a debug file for download
     *
     * @since       1.7.0
     * @return      void
     */
    public function generate_debug_file() {
        nocache_headers();

        header('Content-Type: text/plain' );
        header('Content-Disposition: attachment; filename="ksd-debug.txt"' );
        require_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-debug.php' );

        $ksd_debug =  KSD_Debug::get_instance();

        echo wp_strip_all_tags( $ksd_debug->retrieve_debug_info() );
        if ( !defined( 'PHPUNIT' ) ) die();
    }

    /**
     * Add cc button
     * @since 1.6.8
     */
    private function add_tinymce_cc_button() {
        if ( 'edit' !== filter_input ( INPUT_GET, 'action' ) && 'ksd_ticket' !== filter_input ( INPUT_GET, 'post_type' )  ) {
            return;
        }
        add_filter( "mce_external_plugins", array ( $this, "add_tinymce_cc_plugin" ) );
        add_filter( 'mce_buttons', array ( $this, 'register_tinymce_cc_button' ), 10, 2 );
    }

    /**
     * Register the CC tinymce button
     * @param array $plugin_array
     * @return string
     */
    public function add_tinymce_cc_plugin( $plugin_array ) {
        $plugin_array['KSDCC'] = KSD_PLUGIN_URL. 'assets/js/ksd-wp-editor-cc.js';
        return $plugin_array;
    }

    /**
     * Register the CC button
     * @param type $buttons
     * @return type
     */
    public function register_tinymce_cc_button( $buttons,  $editor_id ) {
        global $current_screen;
        if ( 'ksd_ticket' === $current_screen->post_type ) {//Add the CC button only if it is a KSD editor (not a post, page, etc editor)
            if ( ! in_array( 'ksd_cc_button', $buttons ) ) {
                array_push( $buttons, 'ksd_cc_button' );
            }
        }
        return $buttons;
    }

    /**
     * Modify the tickets grid and add ticket-specific headers
     * @param array $defaults The grid headers
     * @return array
     * @since 2.0.0
     */
    public function add_tickets_headers( $defaults ) {
        $defaults['status']         = __( 'Status', 'kanzu-support-desk' );
        $defaults['assigned_to']    = __( 'Assigned To', 'kanzu-support-desk' );
        $defaults['severity']       = __( 'Severity', 'kanzu-support-desk' );
        $defaults['customer']       = __( 'Customer', 'kanzu-support-desk' );
        $defaults['replies']        = __( 'Replies', 'kanzu-support-desk' );
        return $defaults;
    }

    /**
     * Define which ticket columns to add sorting to
     * @return string
     * @since 2.0.0
     */
    public function ticket_table_sortable_columns( $columns ) {
        $columns['status']      = 'status';
        $columns['assigned_to'] = 'assigned_to';
        $columns['severity']    = 'severity';
        $columns['customer']    = 'customer';
        return $columns;
    }

    /**
     * Remove some default columns. In particular, we remove the tags column
     * @since 2.0.0
     */
    public function ticket_table_remove_columns( $columns ) {
        unset($columns['tags']); //Remove tags
        return $columns;
    }

    /**
     * Order the ticket table columns by a particular field
     * @since 2.0.0
     */
    public function ticket_table_columns_orderby( $vars ) {
        if ( isset( $vars['orderby'] ) ) {
            switch ( $vars['orderby'] ) {
                case 'severity':
                    $vars = array_merge( $vars, array(
                        'meta_key' => '_ksd_tkt_info_severity',
                        'orderby' => 'meta_value'
                        ) );
                    break;
                case 'assigned_to':
                    $vars = array_merge( $vars, array(
                        'meta_key' => '_ksd_tkt_info_assigned_to',
                        'orderby' => 'meta_value_num'
                        ) );
                    break;
                case 'status':
                    $vars = array_merge( $vars, array(
                        'orderby' => 'post_status'
                        ) );
                    break;
                case 'customer':
                    $vars = array_merge( $vars, array(
                        'orderby' => 'post_author'
                        ) );
                    break;
            }
        }
        return $vars;
    }

    /**
     * Add filters to the WP ticket grid
     * @since 2.0.0
     */
    public function ticket_table_filter_headers() {
        global $wpdb, $current_screen;
        if ( $current_screen->post_type == 'ksd_ticket' ) {
            $ksd_statuses = array (
                'new'       => __( 'New', 'kanzu-support-desk' ),
                'open'      => __( 'Open', 'kanzu-support-desk' ),
                'pending'   => __( 'Pending', 'kanzu-support-desk' ),
                'resolved'  => __( 'Resolved', 'kanzu-support-desk' )
                );
            $filter  = '';
            $filter .= '<select name="ksd_statuses_filter" id="filter-by-status">';
            $filter .= '<option value="0">' . __( 'All statuses', 'kanzu-support-desk' ) . '</option>';
            $filter_status_by = ( isset ( $_GET['ksd_statuses_filter'] ) ? sanitize_key( $_GET['ksd_statuses_filter'] ) : 0 );
            foreach ( $ksd_statuses as $value => $name ) {
                $filter .= '<option ' .selected( $filter_status_by , $value , false ) . ' value="' . $value.'">' . $name . '</option>';
            }
            $filter .= '</select>';
            $ksd_severities = $this->get_severity_list();
            $filter .= '<select name="ksd_severities_filter" id="filter-by-severity">';
            $filter .= '<option value="0">' . __( 'All severities', 'kanzu-support-desk' ) . '</option>';
            $filter_severity_by = ( isset ( $_GET['ksd_severities_filter'] ) ? sanitize_key( $_GET['ksd_severities_filter'] ) : 0 );
            foreach ( $ksd_severities as $value => $name ) {
                $filter .= '<option ' .selected( $filter_severity_by , $value, false ) . ' value="' . $value.'">' . $name . '</option>';
            }
            $filter .= '</select>';
            $filter .= '<select name="ksd_unread_filter" id="filter-by-read-unread">';
            $filter .= '<option value="0">' . _x( 'All states','Ticket read and unread states', 'kanzu-support-desk' ) . '</option>';
            $filter .= '<option value="unread">' . _x( 'Unread', 'Ticket state', 'kanzu-support-desk' ) . '</option>';
            $filter .= '<option value="read">' . _x( 'Read','Ticket state', 'kanzu-support-desk' ) . '</option>';
            $filter .= '</select>';
            echo $filter;
         }
    }

    /**
     * Apply filters to the ticket grid
     * Called when a view is selected in the ticket grid and when the user filters a view
     * @since 2.0.0
     */
    public function ticket_table_apply_filters( $query ) {
        if ( is_admin() && isset( $query->query['post_type'] ) && 'ksd_ticket'  == $query->query['post_type'] ) {

             $qv = &$query->query_vars;
             //Change the ticket order
             $qv['orderby']     = 'modified';
             $qv['meta_query']  = array();

            if ( ! empty( $_GET['ksd_severities_filter'] ) ) {
                $qv['meta_query'][] = array(
                  'key' => '_ksd_tkt_info_severity',
                  'value' => $_GET['ksd_severities_filter'],
                  'compare' => '=',
                  'type' => 'CHAR'
                );
            }
            if ( ! empty( $_GET['ksd_statuses_filter'] ) ) {
                $qv['post_status'] =  sanitize_key( $_GET['ksd_statuses_filter'] );
            }
            if ( ! empty( $_GET['ksd_unread_filter'] ) ) {
                $qv['meta_query'][] = $this->get_ticket_state_meta_query( $_GET['ksd_unread_filter'] );
            }
            if ( ! empty( $_GET['ksd_view'] ) ) {
               switch ( sanitize_key( $_GET['ksd_view'] ) ) {
                   case 'mine':
                        $qv['meta_query'][] = array(
                            'key'           => '_ksd_tkt_info_assigned_to',
                            'value'         => get_current_user_id(),
                            'compare'       => '=',
                            'type'          => 'NUMERIC'
                          );
                        $qv['post_status'] = array( 'new', 'open', 'draft', 'pending' );//Don't show resolved tickets
                       break;
                   case 'unassigned':
                        $qv['meta_query'][] = array(
                            'key'           => '_ksd_tkt_info_assigned_to',
                            'value'         => 0,
                            'compare'       => '=',
                            'type'          => 'NUMERIC'
                          );
                       $qv['post_status'] = array( 'new', 'open', 'draft', 'pending' );//Don't show resolved tickets
                       break;
                   case 'recently_updated':
                       break;
                   case 'recently_resolved':
                       break;
               }
           }
        }
    }

    /**
     * Populate our custom ticket columns
     * @param string $column_name
     * @param int $post_id
     * @since 2.0.0
     */
    public function populate_ticket_columns( $column_name, $post_id ) {
        if ( $column_name == 'severity' ) {
            $ticket_severity = get_post_meta( $post_id, '_ksd_tkt_info_severity', true );
            echo  '' == $ticket_severity ? $this->get_ticket_severity_label( 'low' ) : $this->get_ticket_severity_label( $ticket_severity ) ;
        }
        if ( $column_name == 'assigned_to' ) {
            $ticket_assignee_id = get_post_meta( $post_id, '_ksd_tkt_info_assigned_to', true );
            echo $this->get_ticket_assignee_display_name( $ticket_assignee_id );
        }
        if ( $column_name == 'status' ) {
            global $post;
            echo   "<span class='{$post->post_status}'>" . $this->get_ticket_status_label( $post->post_status ) . "</span>";
        }
        if ( $column_name == 'customer' ) {
            global $post;
            echo   get_userdata( $post->post_author )->display_name;
        }
        if ( $column_name == 'replies' ) {
            global $wpdb;
            $reply_count
                    = $wpdb->get_var( " SELECT COUNT(ID) FROM {$wpdb->prefix}posts WHERE "
                    . " post_type = 'ksd_reply' AND post_parent = '${post_id}' "
                    );
            echo   $reply_count;
        }
    }

    /**
     * Get post status label
     *
     * @param string ticket status
     */
    public function get_ticket_status_label ( $post_status ) {
        $label = __( 'Unknown', 'kanzu-support-desk' );
        switch ( $post_status ){
            case 'open':
                $label = __( 'Open', 'kanzu-support-desk' );
            break;
            case 'pending':
                $label = __( 'Pending', 'kanzu-support-desk' );
            break;
            case 'resolved':
                $label = __( 'Resolved', 'kanzu-support-desk' );
            break;
            case 'new':
                $label = __( 'New', 'kanzu-support-desk' );
            break;
            case 'draft':
                $label = __( 'Draft', 'kanzu-support-desk' );
            break;
        }

        return $label;
    }


    /**
     * Get ticket sererity label
     *
     * @param string $ticket_severity ticket severity
     */
    public function get_ticket_severity_label ( $ticket_severity ) {
        $label = __( 'Unknown', 'kanzu-support-desk' );

        switch ( $ticket_severity ){
            case 'low':
                $label = __( 'Low', 'kanzu-support-desk' );
            break;
            case 'medium':
                $label = __( 'Medium', 'kanzu-support-desk' );
            break;
            case 'high':
                $label = __( 'High', 'kanzu-support-desk' );
            break;
            case 'urgent':
                $label = __( 'Urgent', 'kanzu-support-desk' );
            break;
        }

        return $label;
    }

    /**
     * In bulk edit mode, save changes to tickets
     * @TODO Add these changes to ticket activities
     */
    public function save_bulk_edit_ksd_ticket() {
        $post_ids           = ( ! empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : array();
        $update_columns     = array();
        $update_keys        = array( '_ksd_tkt_info_assigned_to', '_ksd_tkt_info_severity' );

        foreach( $update_keys as $key ){
            if( ! empty( $_POST[ $key ] ) ){
                $update_columns[$key] = wp_kses_post( $_POST[ $key ] );
            }
        }

        if ( ! empty( $post_ids ) && is_array( $post_ids ) && ! empty( $update_columns ) ) {
            foreach ( $post_ids as $post_id ) {
                foreach ( $update_columns as $ksd_key => $new_value ) {
                    update_post_meta( $post_id, $ksd_key, $new_value );
                }
            }
        }
        if ( !defined( 'PHPUNIT' ) ) die();
    }

    /**
     * Get a ticket assignee display name used in the 'All Tickets' list
     *
     * @param int $ticket_assignee_id
     * @return string The ticket assignee name
     */
    private function get_ticket_assignee_display_name( $ticket_assignee_id ){
        if( '' == $ticket_assignee_id || 0 == $ticket_assignee_id ){
            return __( 'No one', 'kanzu-support-desk' );
        }else{
            $ticket_assignee    = get_userdata( $ticket_assignee_id );
            if( false !== $ticket_assignee ){
                return $ticket_assignee->display_name;
            }
            return __( 'No one', 'kanzu-support-desk' );
        }
    }

    /**
     * Add custom views to the admin post grid
     * @param Array $views The default admin post grid views
     * @since 2.0.0
     */
    public function ticket_views( $views ) {
        unset( $views['publish'] ); //Remove the publish view
        $views['mine']              = "<a href='edit.php?post_type=ksd_ticket&amp;ksd_view=mine'>".__( 'Mine', 'kanzu-support-desk' )."</a>";
        $views['unassigned']        = "<a href='edit.php?post_type=ksd_ticket&amp;ksd_view=unassigned'>".__( 'Unassigned', 'kanzu-support-desk' )."</a>";
        return $views;
    }

    /**
     * Display ticket statuses next to the title in the ticket grid
     * We use this to remove 'draft','pending' and 'Password protected' ticket states
     * that are automatically added to tickets by WP
     * @global Object $post
     * @param type $states
     * @return type
     * @since 2.0.0
     */
    public function display_ticket_statuses_next_to_title( $states ) {
        global $post;
        if ( 'ksd_ticket' === $post->post_type ) {
            if ( $post->post_status == 'pending' || $post->post_status == 'draft' || !empty ( $post->post_password )  ) {
                return array ( );
            }
        }

        return $states;
    }





    public function append_admin_feedback(){
        include_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-notifications.php' );
        $ksd_notifications  = new KSD_Notifications();
        $notification       = $ksd_notifications->get_new_notification();
        echo $notification;
    }

    /**
     * When a new product/download is published by EDD or WooCommerce,
     * add it as a ticket product
     * @since 2.2.0
     */
    public function on_new_product( $postID, $new_product ){
        if( ! term_exists( $new_product->post_title, 'product' ) ){
            $cat_details = array(
                'cat_name' => $new_product->post_title,
                'taxonomy' => 'product'
            );
            wp_insert_category( $cat_details );
        }
    }

    /**
     * Send tracking data
     *
     * @return null
     */
    public function send_tracking_data(){
        $settings = Kanzu_Support_Desk::get_settings();
        if( 'yes' !== $settings['enable_anonymous_tracking'] ){
            return;
        }
        if( isset( $settings['ksd_tracking_last_send'] ) && $settings['ksd_tracking_last_send'] > strtotime( '-1 week' ) ){
            return;
        }

        $data = $this->get_tracking_data();

        wp_remote_post( 'http://analytics.kanzucode.com/wp-json/kanzu-analytics/v1/api', array(
            'method'      => 'POST',
            'timeout'     => 20,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking'    => true,
            'body'        => $data,
        ) );

        $settings['ksd_tracking_last_send'] = time();
        Kanzu_Support_Desk::update_settings( $settings );
    }

    /**
     * Aggregate tracking data to send
     *
     * @return array
     */
    private function get_tracking_data(){
	$data = array();
        $data['action'] = 'tracking_data';

        //Retrieve current plugin info
        $plugin_data = get_plugin_data( KSD_PLUGIN_FILE );
        $plugin = $plugin_data['Name'];
        $data['product'] = $plugin;

        // Retrieve current theme info
        $theme_data = wp_get_theme();
        $theme      = $theme_data->Name . ' ' . $theme_data->Version;
        $data['url']    = home_url();
        $data['theme']  = $theme;
        $data['email']  = get_bloginfo( 'admin_email' );

        // Retrieve current plugin information
        if( ! function_exists( 'get_plugins' ) ) {
                include ABSPATH . '/wp-admin/includes/plugin.php';
        }

        $plugins        = array_keys( get_plugins() );
        $active_plugins = get_option( 'active_plugins', array() );

        foreach ( $plugins as $key => $plugin ) {
            if ( in_array( $plugin, $active_plugins ) ) {
                // Remove active plugins from list so we can show active and inactive separately
                unset( $plugins[ $key ] );
            }
        }

        //Load all options
        $data['options'] = wp_load_alloptions();

        $data['active_plugins']   = $active_plugins;
        $data['inactive_plugins'] = $plugins;

        return $data;
    }


    /**
     * Add custom boxes to quick edit
     *
     * @param string $column_name
     * @param string $post_type
     * @return null
     */
    public function quick_edit_custom_boxes( $column_name, $post_type ) {
        if ( 'ksd_ticket' != $post_type ) {
            return;
        }?>
                        <?php
                        switch( $column_name ):
                            case 'assigned_to':?>
                                <fieldset class="inline-edit-col-right inline-edit-book">
                                    <div class="inline-edit-col column-<?php echo $column_name; ?>">
                                        <label class="inline-edit-group">
                                            <span class="title"><?php _e('Assigned To:','kanzu-support-desk'); ?></span>
                                            <select name="_ksd_tkt_info_assigned_to">
                                                <option value="-1">--<?php _e( 'No Change', 'kanzu-support-desk' );  ?>--</option>
                                                <?php foreach ( get_users( array( 'role__in' => array('ksd_agent','ksd_supervisor','administrator' ) ) ) as $agent ) { ?>
                                                <option value="<?php echo $agent->ID; ?>">
                                                    <?php echo $agent->display_name; ?>
                                                </option>
                                                <?php }; ?>
                                                <option value="0"><?php _e('No One', 'kanzu-support-desk'); ?></option>
                                            </select>
                                        </label>
                                        </div>
                                    </fieldset>
                            <?php
                            break;
                            case 'severity':
                                global $current_user;?>
                                <fieldset class="inline-edit-col-right inline-edit-book">
                                    <div class="inline-edit-col column-<?php echo $column_name; ?>">
                                    <label class="inline-edit-group">
                                        <span class="title"><?php _e('Severity:','kanzu-support-desk'); ?></span>
                                        <select name="_ksd_tkt_info_severity">
                                            <option value="-1">--<?php _e( 'No Change', 'kanzu-support-desk' );  ?>--</option><?php
                                            foreach ( $this->get_severity_list()  as $severity_label => $severity ) : ?>
                                            <option value="<?php echo $severity_label; ?>">
                                                <?php echo $severity; ?>
                                            </option>
                                        <?php endforeach; ?>
                                        </select>
                                    </label>
                                    </div>
                                </fieldset>
                                <fieldset class="inline-edit-col-right inline-edit-book">
                                    <div class="inline-edit-col column-<?php echo $column_name; ?>">
                                    <label class="inline-edit-group">
                                        <span class="title"><?php _e('State:','kanzu-support-desk'); ?></span>
                                        <select name="_ksd_tkt_info_is_read_by_<?php echo $current_user->ID ;?>">
                                            <option value="-1">--<?php _e( 'No Change', 'kanzu-support-desk' );  ?>--</option>
                                             <option value="read"><?php _ex('Read','Ticket state','kanzu-support-desk'); ?></option>
                                             <option value="unread"><?php _ex('Unread','Ticket state','kanzu-support-desk'); ?></option>
                                        </select>
                                    </label>
                                    </div>
                                </fieldset>
                <?php
                                break;
                        endswitch;

    }

    public function append_classes_to_ticket_grid( $classes, $class, $post_ID ){
        global $current_screen, $current_user;
        if ( $current_screen && ! isset( $current_screen->id ) && 'edit-ksd_ticket' == $current_screen->id ){
            return $classes;
        }
        if( 'yes' == get_post_meta( $post_ID, '_ksd_tkt_info_is_read_by_'.$current_user->ID, true ) ){
           $classes[] = 'read';
        }
        return $classes;
    }

    /**
     * Change the Publish button to update
     * @param string $translation
     * @param string $text
     * @return string
     * @TODO Re-do this. Not too consistent
     */
    public function change_publish_button( $translation, $text ) {
        if ( $text == 'Publish' && 'ksd_ticket' == get_post_type() ){
            return __( 'Update', 'kanzu-support-desk' );
        }

        return $translation;
    }


    /**
     * Get meta query used in filtering read/unread tickets
     * @global WP_User $current_user
     * @param string $ticket_state read|unread
     * @return array The meta query to use
     */
    private function get_ticket_state_meta_query( $ticket_state ){
        $state_meta_query = array();
        global $current_user;
        if ( 'read' == $ticket_state ){
            $state_meta_query =    array(
              'key'     => '_ksd_tkt_info_is_read_by_'.$current_user->ID,
              'value'   => 'yes',
              'compare' => '=',
              'type'    => 'CHAR'
            );
        }
        if ( 'unread' == $ticket_state ){
               $state_meta_query =    array(
              'key'     => '_ksd_tkt_info_is_read_by_'.$current_user->ID,
              'compare' => 'NOT EXISTS'
            );
        }
       return $state_meta_query;
    }

    private function get_user_permalink( $user_id ){
        if( 0 == $user_id ){
            return false;
        }
        $user = get_userdata( $user_id );
        return '<a href="' . admin_url( "user-edit.php?user_id={$user_id}").'">' . $user->display_name.'</a>';
    }

    /**
     * Change the parent ID of ticket replies and private notes
     *
     * @param int $old_parent_ID The current parent ticket ID
     * @param int $new_parent_ID The new parent ticket ID
     */
    private function change_replies_parent( $old_parent_ID, $new_parent_ID ){
        $reply_args = array(
            'post_type'         => array ( 'ksd_reply', 'ksd_private_note' ),
            'post_parent'       => $old_parent_ID,
            'post_status'       => array ( 'private', 'publish' ),
            'posts_per_page'    => -1,
            'offset'            => 0
          );

        $replies_and_notes = get_posts( $reply_args );
        foreach ( $replies_and_notes as $tkt_reply_or_note ){
            $update_details = array( 'ID' => $tkt_reply_or_note->ID, 'post_parent' => $new_parent_ID );
            wp_update_post( $update_details  );
        }
    }

    /**
     * Delete a ticket's meta info
     * @global Object $wpdb
     * @param int $ticket_ID
     */
    private function delete_ticket_meta( $ticket_ID ){
        global $wpdb;
        $merge_tickets_meta_keys_query  = "SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '_ksd_tkt_info%' AND post_id='{$ticket_ID}'";
        $merge_ticket_meta_keys         = $wpdb->get_results( $merge_tickets_meta_keys_query );
        foreach( $merge_ticket_meta_keys as $meta_info ){
            delete_meta( $meta_info->meta_id );
        }
    }

    /**
     * Delete a ticket's activities
     * @param int $ticket_ID
     */
    private function delete_ticket_activities( $ticket_ID ){
        $activity_args = array(
            'post_type'         => array ( 'ksd_ticket_activity' ),
            'post_parent'       => $ticket_ID,
            'post_status'       => array ( 'private' ),
            'posts_per_page'    => -1,
            'offset'            => 0
          );

        $ticket_activities = get_posts( $activity_args );
        foreach ( $ticket_activities as $activity ){
            wp_delete_post( $activity->ID, true );
        }
    }


    /**
     * Change a ticket into a reply
     * @param int $ticket_ID
     * @param int $new_parent_ticket_ID The ticket's new parent ID
     * @return int | WP_Error
     */
    private function transform_ticket_to_reply( $ticket_ID, $new_parent_ticket_ID ){
        $transformer_ticket = array(
            'ID'            => $ticket_ID,
            'post_type'     => 'ksd_reply',
            'post_status'   => 'publish',
            'post_parent'   => $new_parent_ticket_ID
        );
        return wp_update_post( $transformer_ticket  );
    }

    private function current_user_can_view_private_notes(){
        global $current_user;
        if ( isset( $current_user->roles ) && is_array( $current_user->roles ) && ( in_array( 'ksd_agent', $current_user->roles ) || in_array( 'ksd_supervisor', $current_user->roles ) || in_array( 'administrator', $current_user->roles ) ) ){

                return true;
        }
        return false;

    }

    private function get_installed_addons(){
        $addons = array();
        $settings = Kanzu_Support_Desk::get_settings();
        foreach( $settings as $key => $value ){
            if( 'ksd_' === substr( $key, 0, 4) ){
                if( 'ksd_owner' != $key && 'ksd_activation_time' != $key ){
                    $addons[] = $key;
                }
            }
        }
        return $addons;
    }

}

endif;

return new KSD_Admin();
