<?php
/**
 * Front-end of Kanzu Support Desk
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Public' ) ) :

class KSD_Public {

    public function __construct() {

        //Do public-facing includes
        $this->do_public_includes();

        //Enqueue styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
        //Add form for new ticket to the footer
        add_action( 'wp_footer', array( $this , 'generate_new_ticket_form' ) );
        //Handle AJAX
        add_action( 'wp_ajax_nopriv_ksd_log_new_ticket', array( $this, 'log_new_ticket' ) );
        add_action( 'wp_ajax_nopriv_ksd_register_user', array( $this, 'register_user' ) );
        add_action( 'wp_ajax_nopriv_ksd_reply_ticket', array( $this, 'reply_ticket' ) );
        add_action( 'wp_ajax_ksd_admin_bar_clicked', array( $this, 'admin_bar_clicked' ) );

        //Add a shortcode for the public form
        add_shortcode( 'ksd_support_form', array( $this,'form_short_code' ) );
        add_shortcode( 'ksd_my_tickets', array( $this,'display_my_tickets' ) );

        //Add custom post types
        add_action( 'init', array( 'KSD_Custom_Post_Types', 'create_custom_post_types' ) );

        //Add custom ticket statuses
        add_action( 'init', array( 'KSD_Custom_Post_Types', 'custom_ticket_statuses' ) );

        //Add widget for the support form
        add_action( 'widgets_init', array( $this, 'register_support_form_widget' ) );

        //Style public view of tickets
        add_filter( 'the_content', array( $this, 'apply_templates' ) );

        //Redirect customers on login
        //add_filter( 'login_redirect', array ( $this, 'do_login_redirect' ), 10, 3 );

        //Add CC button to tinyMCE editor
        $this->add_tinymce_cc_button();

        //Add ticket cc
        add_filter( 'the_content', array( $this, 'add_ticket_cc') );

        //Allow secret URL for tickets from guests
        add_action( 'template_redirect', array( $this, 'allow_secret_urls' ) );

        //Remove 'Protected' from ticket titles
        add_filter( 'protected_title_format', array( $this, 'remove_protected_prefix' ) );

        //Add content to WooCommerce/EDD Pages
        add_action( 'woocommerce_after_my_account', array( $this, 'woo_edd_append_ticket_list' ) );
        add_action( 'edd_after_purchase_history', array( $this, 'woo_edd_append_ticket_list' ) );
        add_action( 'edd_after_download_history', array( $this, 'woo_edd_append_ticket_list' ) );
        add_action( 'edd_customer_after_tables', array( $this, 'edd_customers_admin_append_ticket_table' ) );

        //Add 'Create ticket' to Woo Orders page
        add_filter( 'woocommerce_my_account_my_orders_columns', array ( $this, 'woo_orders_add_table_headers' ) );
        add_filter( 'woocommerce_my_account_my_orders_actions' , array ( $this, 'woo_orders_add_ticket_button' ), 10, 2 );

        //Filter tickets archive page
        add_action('pre_get_posts', array( $this, 'hide_ticket_archive_content' ));

        //Only show a user his own attachments
        add_filter( 'ajax_query_attachments_args', array( $this, 'filter_media' ) );

        //Add attachments to ticket content
        add_filter( 'ksd_the_ticket_content', array( $this, 'append_attachments_to_content' ), 10, 2 );

        //Filter ksd-public-grecaptcha script tags
        add_filter( 'script_loader_tag', array( $this, 'add_async_defer_attributes' ), 10, 2 );

        //Add support tickets tab to WooCommerce single product view
        add_filter( 'woocommerce_product_tabs', array( $this, 'woo_support_tickets_tab' ), 999 );

        //Add admin bar menu
        add_action( 'admin_bar_menu', array( $this, 'display_admin_bar_menu' ), 999 );

        add_filter( 'post_row_actions', array( $this, 'modify_row_actions' ), 10, 2 );

        add_action( 'ksd_after_ticket_content', array( $this, 'append_ticket_replies') );

        //Contact form 7
        add_action( 'wpcf7_form_class_attr', array( $this, 'append_ksd_class_to_wpcf7' )  );
    }


    /**
     * Change the 'edit' link displayed in the row actions of the ticket
     * grid to 'reply' and update the 'view' item if a hash URL exists
     *
     * @param  array  $actions Row actions
     * @param  object $post    WP_Post
     * @return array         Modified row actions
     */
    public function modify_row_actions( $actions, $post ){
        if ( 'ksd_ticket' == $post->post_type ){

            $title = _draft_or_post_title();
            $actions['edit'] = sprintf(
                '<a href="%s" aria-label="%s">%s</a>',
                get_edit_post_link( $post->ID ),
                /* translators: %s: post title */
                esc_attr( sprintf( __( 'Reply &#8220;%s&#8221;','kanzu-support-desk' ), $title ) ),
                __( 'Reply', 'kanzu-support-desk' )
            );

            $hash_url =  get_post_meta( $post->ID, '_ksd_tkt_info_hash_url', true );
            if ( ! empty(  $hash_url ) ) {
                $actions['view'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    $hash_url,
                    /* translators: %s: post title */
                    esc_attr( sprintf( __( 'View &#8220;%s&#8221;','kanzu-support-desk' ), $title ) ),
                    __( 'View', 'kanzu-support-desk' )
                );
            }

        }

        return $actions;
    }

    /**
     * Decrement notifications counter when KSD Admin Bar is clicked
     */
    public function admin_bar_clicked(){
        $node_id = ltrim( sanitize_text_field( $_POST['node_id'] ), 'wp-admin-bar' );
        //KSD Admin Bar clicked array map stored and fetched directly from DB to speed up queries
        $admin_bar_clicked = get_option( 'admin_bar_clicked', array() );
        $notifications = get_option( 'admin_bar_notifications', 0 );
        $response = array();
        if( ! isset( $admin_bar_clicked[$node_id] ) || ! $admin_bar_clicked[$node_id] ){
            $admin_bar_clicked[$node_id] = true;
            $notifications--;
            update_option( 'admin_bar_clicked', $admin_bar_clicked );
            update_option( 'admin_bar_notifications', $notifications );
            $response['message'] = 'Success';
        }
        echo json_encode( $response );
        die();
    }

    /**
     * Display KSD Admin Bar
     *
     * @param WP_Admin_Bar $admin_bar
     */
    public function display_admin_bar_menu( $admin_bar ){

        $this->add_ksd_admin_bar_node( array(
            'id'        => 'ksd-qs-tour',
            'title'     => __( 'Quick Tour', 'kanzu-support-desk' ),
            'href'      => admin_url('edit.php?post_type=ksd_ticket&ksd_getting_started=1')
        ) );

        $this->add_ksd_admin_bar_node( array(
            'id'        => 'ksd-feedback',
            'title'     => __( 'Give Feedback', 'kanzu-support-desk' ),
            'href'      => admin_url('edit.php?post_type=ksd_ticket&page=ksd-feedback')
        ) );

        $this->add_ksd_admin_bar_parent( $admin_bar );

        //Merge with default node so that each node maintains the same parent
        $default_node = array(
            'parent' => 'ksd-admin-bar',
            'meta'  => array(
                'target'     => '_blank' //Target is set to blank on all child nodes so that ajax call that decrements notifications counter has time to complete
            )
        );
        //KSD Admin Bar nodes stored and fetched directly from DB to speed up queries
        $admin_bar_nodes = get_option( KSD()->ksd_admin_bar_nodes, array() );

        foreach( $admin_bar_nodes as $admin_bar_node ){
            $admin_bar_node = wp_parse_args( $default_node, $admin_bar_node );
            $admin_bar->add_node( $admin_bar_node );
        }

    }

    /**
     * Add children nodes to KSD Admin Bar Menu
     *
     * @param array $args
     */
    public function add_ksd_admin_bar_node( $args ){
        //KSD Admin Bar notifications stored and fetched directly from DB to speed up queries
        $notifications = get_option( 'admin_bar_notifications', 0 );
        $admin_bar_nodes = get_option( KSD()->ksd_admin_bar_nodes, array() );
        $node_id[] = $args['id'];
        $node_ids = array();
        foreach( $admin_bar_nodes as $admin_bar_node ){
            $node_ids[] = $admin_bar_node['id'];
        }
        $node_mods = array_diff( $node_id, $node_ids );
        if( ! empty( $node_mods ) ){
            $admin_bar_nodes[] = $args;
            $notifications++;
            update_option( KSD()->ksd_admin_bar_nodes, $admin_bar_nodes );
            update_option( 'admin_bar_notifications', $notifications );
        }
    }

    /**
     * Add KSD Admin Bar Menu parent node
     *
     * @param WP_Admin_Bar $admin_bar
     */
    public function add_ksd_admin_bar_parent( $admin_bar ){
        $notifications = get_option( 'admin_bar_notifications', 0 );
        $parent_node_title = '<span class="ksd-admin-icon" style="display:inline-block; margin: 2px 0;" ><img src="'. KSD_PLUGIN_URL .'assets/images/icons/kc_white_icon_25x25.png" /></span>';
        if( $notifications ){
            $parent_node_title .= '<span class="ksd-admin-bar-notice" style="display: inline-block;background: #d54e21;border-radius: 10px;padding: 0 6px;font-size: 9px;height: 20px;vertical-align: top;line-height: 20px;margin: 4px 4px;" >'. $notifications .'</span>';
        }
        $args = array(
            'id'        => 'ksd-admin-bar',
            'title'     => $parent_node_title,
            'parent'    => 'root-default',
        );
        $admin_bar->add_node( $args );
    }

    public function woo_support_tickets_tab( $tabs = array() ){
        //Check WooCommerce show support tickets tab setting
        $settings = Kanzu_Support_Desk::get_settings();
        if( 'yes' == $settings['show_woo_support_tickets_tab'] ){
            //Add Support Tickets tab
            $tabs['support_tickets'] = array(
                'title'     => __( 'Support Tickets', 'kanzu-support-desk' ),
                'priority'  => 50,
                'callback'  => array( $this, 'woocommerce_support_tickets_tab' )
            );
        }
        return $tabs;
    }

    public function woocommerce_support_tickets_tab(){
        include_once( KSD_PLUGIN_DIR.  "includes/public/class-ksd-templates.php");
        $ksd_template = new KSD_Templates();
        $ksd_template->get_template_part( 'woocommerce', 'support-tickets-tab', true );
    }

    public function add_async_defer_attributes( $tag, $handle ){
        if( KSD_SLUG . '-public-grecaptcha' != $handle ){
            return $tag;
        }
        return str_replace( ' src', ' async defer src', $tag );
    }

    /**
     * In the ticket archive, only show the current user's
     * tickets. This prevents one user from seeing another's
     * tickets
     *
     * @since 2.2.4
     */
    public function hide_ticket_archive_content( $query ){

        if ( is_admin() || ! $query->is_main_query() ){
           return;
        }

        if ( is_post_type_archive( 'ksd_ticket' ) && ! empty( $query->query['post_type'] ) &&  'ksd_ticket' == $query->query['post_type'] ) {
            $query->set( 'author', get_current_user_id() );
            return;
        }

    }

    /**
     * Filter attachments to only show those of the current user
     * @return $query
     */
    public function filter_media( $query ){
        if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ){
                $query['author'] = get_current_user_id();
        }
        return $query;
    }
    /**
     * Tickets that have hash URLs have the word 'Protected' prepended to the title.
     * We remove that word here
     * @param string $title
     * @return string
     */
    public function remove_protected_prefix( $title ) {
        global $post;

        if ( 'ksd_ticket' == $post->post_type ){
           return '%s';
        }
        return $title;
    }

    /**
     * Include files required by the public-facing logic
     */
    private function do_public_includes() {
        require_once( KSD_PLUGIN_DIR .  'includes/public/class-ksd-widget-support-form.php' );
        include_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-hash-urls.php' );
        include_once( KSD_PLUGIN_DIR .  'includes/class-ksd-custom-post-types.php' );
        include_once( KSD_PLUGIN_DIR .  'includes/class-ksd-onboarding.php' );
        include_once( KSD_PLUGIN_DIR .  'includes/class-ksd-session.php' );
    }

    public function allow_secret_urls(){
        $hash_urls = new KSD_Hash_Urls();
        $hash_urls->redirect_guest_tickets();
    }

    /**
     * Prepends the CC to the ticket
     *
     * @global WP_Post $post
     * @param int $post_id
     * @return string $content
     * @since 2.0.4
     */
    public function add_ticket_cc( $content ) {
        global $post;
        $cc = get_post_meta( $post->ID, '_ksd_tkt_info_cc', true);
        if ( "" !== trim( $cc) ) {
            $content = '<div class="ksd-ticket-cc"><span class="ksd-cc-emails">' . __( 'CC', 'kanzu-support-desk' ) . $cc . '</span></div>' . $content;
        }
        return $content;
    }

    /**
     * Append ticket attachment HTML to ticket content
     * @param string $ticket_content
     * @param int $ticket_id
     * @return string Ticket content
     */
    public function append_attachments_to_content( $ticket_content, $ticket_id ){
        $attachment_html    = '';
        $attachments        = get_post_meta( $ticket_id, '_ksd_tkt_attachments', true );
        if ( '' !== $attachments ){
            foreach( $attachments as $attach_id ){
                $attachment_html .= '<li><a href="' . get_attachment_link( $attach_id ) . '">' .  get_the_title( $attach_id ) . '</a></li>';
            }
            $ticket_content .= '<div class="ksd-attachments-addon"><h3>' . __( 'Attachments', 'kanzu-support-desk' ).':</h3> <ul class="ksd_attachments">' . $attachment_html. '</ul></div>';
        }
        return $ticket_content;
    }

    /**
     * Add cc button
     * @since 2.0.3
     */
    private function add_tinymce_cc_button() {
        if ( is_admin() ) {
            return;
        }
        add_filter( "mce_external_plugins", array ( $this, "add_tinymce_cc_plugin" ) );
        add_filter( 'mce_buttons', array ( $this, 'register_tinymce_cc_button' ), 10, 2 );
    }

    /**
     * Register the CC tinymce button
     * @param array $plugin_array
     * @return string
     * @since 2.0.3
     */
    public function add_tinymce_cc_plugin( $plugin_array ) {
            $plugin_array['KSDCC'] = KSD_PLUGIN_URL. '/assets/js/ksd-wp-editor-cc.js';
            return $plugin_array;
    }

    /**
     * Register the CC button
     * @param type $buttons
     * @return type
     * @since 2.0.3
     */
    public function register_tinymce_cc_button( $buttons,  $editor_id ) {
            global $current_screen;
            array_push( $buttons, 'ksd_cc_button' );
            return $buttons;
    }

    /**
     * Register the support form widget
     */
    public function register_support_form_widget() {
        register_widget( 'KSD_Support_Form_Widget' );
    }


    /**
     * Generate the ticket form that's displayed in the front-end
     * NB: We only show the form if you enabled the 'show_support_tab' option
     */
    public function generate_new_ticket_form() {
        $settings = Kanzu_Support_Desk::get_settings();
        if( "yes" == $settings['show_support_tab'] ) {?>
           <button id="ksd-new-ticket-public"><?php echo $settings['support_button_text']; ?></button><?php
           $close_image = KSD_PLUGIN_URL."assets/images/icons/close.png";
           $before_form             = '<div class="ksd-close-form-wrapper"><span class="ksd_close_button">'.__( 'Close', 'kanzu-support-desk' ).' <img src="'.$close_image.'"  width="32" height="32" Alt="'.__('Close', 'kanzu-support-desk').'" /></span></div>';
           $form_wrapper_classes    = 'ksd-form-hidden-tab hidden';
           $form_classes            = 'ksd-form-hidden-tab-form';
           $show_onboarding         = FALSE;
           echo self::generate_support_form( $before_form, $form_wrapper_classes, $form_classes, $show_onboarding );
        }
    }

    /**
     * Display a form wherever shortcode [ksd_support_form] is used
     */
   public function form_short_code() {
        return self::generate_support_form();
   }

   /**
    * Generate a public-facing support form
    * @TODO Use this to generate all support forms. Replace the need for html-public-new-ticket.php
    */
   public static function generate_support_form( $before_form='', $form_wrapper_classes='ksd-form-short-code', $form_classes='ksd-form-short-code-form', $show_onboarding=true ) {
        $settings = Kanzu_Support_Desk::get_settings();
        //Include the templating and admin classes
        include_once( KSD_PLUGIN_DIR.  "includes/admin/class-ksd-admin.php");
        include_once( KSD_PLUGIN_DIR.  "includes/public/class-ksd-templates.php");
        if ( "yes" == $settings['enable_customer_signup'] && ! is_user_logged_in() ) {
            ob_start();
            include( KSD_PLUGIN_DIR .  'templates/default/html-public-register.php' );
            return apply_filters( 'ksd_registration_form', ob_get_clean() );
        } else{
            ob_start();
            $ksd_template = new KSD_Templates();
            $template_part = $ksd_template->get_template_part( 'single', 'submit-ticket', false );
            if( !empty( $template_part ))
                include( $template_part );
            return ob_get_clean();
        }
   }

   /**
    * Append ticket list to WooCommerce 'My Account' page
    *
    * @since 2.2.0
    */
   public function woo_edd_append_ticket_list(){
       printf( '<h2>%s</h2>',__( 'My Tickets','kanzu-support-desk' ) );
       $this->display_my_tickets();
   }

   public function edd_customers_admin_append_ticket_table(){
       printf( '<h3>%s</h3>',__( 'Tickets','kanzu-support-desk' ) );
       $this->get_ticket_table();
   }

   /**
    * Display a customer's tickets
    * @since 2.0.0
    */
   public function display_my_tickets() {
        //Include the templating and admin classes
        include_once( KSD_PLUGIN_DIR.  "includes/admin/class-ksd-admin.php");
        include_once( KSD_PLUGIN_DIR.  "includes/public/class-ksd-templates.php");
        $settings = Kanzu_Support_Desk::get_settings();
        if ( "yes" == $settings['enable_customer_signup'] && ! is_user_logged_in() ) {
            $form_wrapper_classes   = 'ksd-form-short-code';
            $form_classes           = 'ksd-form-short-code-form';
            include( KSD_PLUGIN_DIR .  'templates/default/html-public-register.php' );
        } else{
            $ksd_template = new KSD_Templates();
            $ksd_template->get_template_part( 'list', 'my-tickets' );
        }
   }

   /**
    * Get the current user's tickets displayed in a table.
    * Used primarily to append the table to the EDD admin customer page
    * @since 2.3.1
    */
   public function get_ticket_table(){
        include_once( KSD_PLUGIN_DIR.  "includes/public/class-ksd-templates.php");
        $ksd_template = new KSD_Templates();
        $ksd_template->get_template_part( 'list', 'my-tickets-table' );
   }

    	/**
	 * Register and enqueue front-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_public_styles() {
            wp_enqueue_style( 'thickbox' );
            wp_enqueue_style( KSD_SLUG .'-public-css', KSD_PLUGIN_URL . 'assets/css/ksd-public.css' , array() , KSD_VERSION );
        }

        /**
         * Enqueue scripts used solely at the front-end
         * @since 1.0.0
         */
        public function enqueue_public_scripts() {
            if( ! is_admin() ){
                 wp_enqueue_media();
            }
            if( is_user_logged_in() ){
                wp_enqueue_script( KSD_SLUG . '-admin-bar-js', KSD_PLUGIN_URL . 'assets/js/ksd-admin-bar.js', array( 'jquery' ), '1.0.0' );
                wp_localize_script( KSD_SLUG . '-admin-bar-js', 'ksd_admin_bar', array(
                    'ajax_url'  => admin_url( 'admin-ajax.php' )
                ) );
            }

            wp_enqueue_script( 'media-upload' );
            wp_enqueue_script( 'thickbox' );
            wp_enqueue_script( KSD_SLUG . '-public-js', KSD_PLUGIN_URL .  'assets/js/ksd-public.js' , array( 'jquery', 'jquery-ui-core', 'jquery-ui-tooltip', 'media-upload', 'thickbox' ), KSD_VERSION );
            $ksd_public_labels =  array();
            $ksd_public_labels['msg_grecaptcha_error']  = sprintf( __( 'Please check the <em>%s</em> checkbox and wait for it to complete loading', 'kanzu-support-desk'), "I'm not a robot" );
            $ksd_public_labels['msg_error_refresh']     = __( 'Sorry, but it seems like something went wrong. Please try again or reload the page.', 'kanzu-support-desk');
            $ksd_public_labels['msg_reply_sent']        = __( 'Your reply has been sent successfully. We will get back to you shortly. Thank you.', 'kanzu-support-desk');
            $ksd_public_labels['lbl_name']              = __( 'Name', 'kanzu-support-desk');
            $ksd_public_labels['lbl_subject']           = __( 'Subject', 'kanzu-support-desk');
            $ksd_public_labels['lbl_email']             = __( 'Email', 'kanzu-support-desk');
            $ksd_public_labels['lbl_first_name']        = __( 'First Name', 'kanzu-support-desk');
            $ksd_public_labels['lbl_last_name']         = __( 'Last Name', 'kanzu-support-desk');
            $ksd_public_labels['lbl_username']          = __( 'Username', 'kanzu-support-desk');
            $ksd_public_labels['lbl_CC']                = __( 'CC', 'kanzu-support-desk');
            $ksd_public_labels['lbl_reply_to_all']      = __( 'Reply', 'kanzu-support-desk' );
            $ksd_public_labels['lbl_populate_cc']       = __( 'Populate CC field', 'kanzu-support-desk' );

            //@TODO Don't retrieve settings again. Use same set of settings
            $settings = Kanzu_Support_Desk::get_settings();

            wp_localize_script( KSD_SLUG . '-public-js', 'ksd_public' ,
                    array(  'ajax_url'                      => admin_url( 'admin-ajax.php'),
                            'admin_post_url'                => admin_url( 'admin-post.php' ),
                            'ksd_public_labels'             => $ksd_public_labels,
                            'ksd_submit_tickets_url'        => get_permalink( $settings['page_submit_ticket'] )
                    )
                    );
            //Check whether enable_recaptcha is checked.
            if ( "yes" == $settings['enable_recaptcha'] && $settings['recaptcha_site_key'] !== '' ) {
               wp_enqueue_script( KSD_SLUG . '-public-grecaptcha', '//www.google.com/recaptcha/api.js?onload=ksdRecaptchaCallback&render=explicit', array(), KSD_VERSION );
               wp_localize_script( KSD_SLUG . '-public-grecaptcha', 'ksd_grecaptcha', array(
                   'site_key'       => $settings['recaptcha_site_key']
               ) );
            }

        }

        /**
         * Apply templates to a user's tickets prior to display
         * Allow the tickets to be modified by actions before and after and for the ticket content itself
         * to be modified using a filter
         * @since 2.0.0
         */
        public function apply_templates( $content ) {
            global $post;
            if ( $post && $post->post_type == 'ksd_ticket' && is_singular( 'ksd_ticket' ) && is_main_query() && !post_password_required() ) {
                $settings = Kanzu_Support_Desk::get_settings();
                if ( "yes" == $settings['enable_customer_signup'] && ! is_user_logged_in() ) {  //@TODO Send the current URL as the redirect URL for the 'login' and 'Register' action
                    include_once( KSD_PLUGIN_DIR.  "includes/admin/class-ksd-admin.php");
                    $form_wrapper_classes   = 'ksd-form-short-code';
                    $form_classes           = 'ksd-form-short-code-form';
                    include( KSD_PLUGIN_DIR .  'templates/default/html-public-register.php' );
                    return;
                }

                global $current_user;
                if ( in_array( 'ksd_customer', $current_user->roles ) && $current_user->ID != $post->post_author ) {//This is a customer
                   return __( "Sorry, you do not have sufficient priviledges to view another customer's tickets", "kanzu-support-desk" );
                }

                //Do actions before the ticket
                ob_start();
                do_action( 'ksd_before_ticket_content', $post->ID );
                $content = ob_get_clean() . $content;

                //Modify the ticket content
                $content = apply_filters( 'ksd_ticket_content', $content );

                //Do actions after the ticket
                ob_start();
                do_action( 'ksd_after_ticket_content', $post->ID );
                $content .= ob_get_clean();
            }
            return $content;
        }




        /**
         * Log a new ticket. We use the backend logic
         */
        public function log_new_ticket() {
            //First check the CAPTCHA to prevent spam
             $settings = Kanzu_Support_Desk::get_settings();
            if ( "yes" == $settings['enable_recaptcha'] && $settings['recaptcha_site_key'] !== '' ) {
                $recaptcha_response = $this->verify_recaptcha();
                if ( $recaptcha_response['error'] ) {
                    echo json_encode( $recaptcha_response['message'] );
                    die();//This is important for WordPress AJAX
                }
            }

            //Use the admin side logic to do the ticket logging
            $ksd_admin =  KSD_Admin::get_instance();
            $ksd_admin->log_new_ticket( $_POST );
        }



        /**
         * Add a reply to a ticket
         */
        public function reply_ticket(){
            $ksd_admin =  KSD_Admin::get_instance();
            $ksd_admin->reply_ticket( $_POST );
        }

        /**
         * Register a user
         * @since 2.0.0
         */
        public function register_user() {
            //Check the nonce
            if ( ! wp_verify_nonce( $_POST['register-nonce'], 'ksd-register' ) ) {
                  die ( __( 'Busted!', 'kanzu-support-desk') );
            }
            //@TODO Currently accepts defaults ( 'Last Name''First Name') Disable this
            //Perform server-side validation
            $first_name = sanitize_text_field( $_POST['ksd_cust_firstname'] );
            $last_name  = sanitize_text_field( $_POST['ksd_cust_lastname'] );
            $username   = sanitize_text_field( $_POST['ksd_cust_username'] );
            $email      = sanitize_text_field( $_POST['ksd_cust_email'] );
            $password   = sanitize_text_field( $_POST['ksd_cust_password'] );

            //@TODO Check if WP registrations are enabled

            //Check that we have all required fields
            if ( empty ( $first_name ) || empty ( $username ) || empty ( $email ) || empty ( $password ) ) {
                $response = __( 'Sorry, a required field is missing. Please fill in all fields.', 'kanzu-support-desk' );
                echo ( json_encode( $response ) );
                die();
            }
            //Check that the fields are valid
            if ( ( strlen ( $first_name ) || strlen( $last_name ) || strlen( $username ) ) < 2  ) {
                $response = __( 'Sorry, the name provided should be at least 2 characters long.', 'kanzu-support-desk' );
                echo ( json_encode( $response ) );
                die();
            }
            if ( !is_email( $email ) ) {
                $response = __( 'Sorry, the email you provided is not valid.', 'kanzu-support-desk' );
                echo ( json_encode( $response ) );
                die();
            }
            //Check if the username is new
            if ( username_exists( $username ) ) {
                $response = __( 'Sorry, that username is already taken. Please choose another one', 'kanzu-support-desk' );
                echo ( json_encode( $response ) );
                die();
            }
            //Yay! Register the user
            $userdata = array(
                        'user_login'    => $username,
                        'user_pass'     => $password,
                        'user_email'    => $email,
                        'display_name'  => $first_name.' '.$last_name,
                        'first_name'    => $first_name,
                        'role'          => 'ksd_customer'
            );
            if ( !empty( $last_name ) ) {//Add the last name if it was provided
                $userdata['last_name']  =   $last_name;
            }
            try {
                $user_id = wp_insert_user( $userdata ) ;
                if ( ! is_wp_error( $user_id ) ) {//Successfully created the user
                    $login_url = sprintf ( '<a href="%1$s" title="%2$s">%3$s</a>', wp_login_url(), __( 'Login', 'kanzu-support-desk' ), __( 'Click here to login', 'kanzu-support-desk' ) ) ;
                    $response = sprintf ( __( 'Your account has been successfully created! If you are not automatically redirected in 5 seconds, %s', 'kanzu-support-desk' ), $login_url );

                    //Sign in the user
                    $creds                  = array();
                    $creds['user_login']    = $username;
                    $creds['user_password'] = $password;
                    $creds['remember']      = false;
                    wp_signon( $creds, false );//We don't check whether this happens

                    echo ( json_encode( $response ) );
                    die();
                }
                else{//We had an error
                    $error_message = __( 'Sorry, but something went wrong. Please retry or reload the page.', 'kanzu-support-desk');
                   if ( isset( $user_id->errors['existing_user_email'] ) ) {//The email's already in use. Ask the user to reset their password
                       $lost_password_url = sprintf ( '<a href="%1$s" title="%2$s">%3$s</a>', wp_lostpassword_url(), __( 'Lost Password', 'kanzu-support-desk' ), __( 'Click here to reset your password', 'kanzu-support-desk' ) ) ;
                       $error_message = sprintf( __( 'Sorry, that email address is already used! %s', 'kanzu-support-desk' ), $lost_password_url );
                   }
                        throw new Exception( $error_message, -1);
                }
             }catch( Exception $e) {
                $response = array(
                    'error'=> array( 'message' => $e->getMessage() , 'code'=>$e->getCode() )
                );
                echo json_encode( $response);
                die();// IMPORTANT: don't leave this out
            }

        }

        /**
         * Check, using Google reCAPTCHA, whether the submitted ticket was sent
         * by a human
         */
        private function verify_recaptcha() {
                $response = array();
                $response['error'] = true;//Pre-assume an error is going to occur
                if ( empty( $_POST['g-recaptcha-response'] ) ) {
                   $response['message'] = __( "ERROR - Sorry, the \"I'm not a robot\" field is required. Please refresh this page & check it.", "kanzu-support-desk");
                   return $response;
                }
                $settings = Kanzu_Support_Desk::get_settings();
                $recaptcha_args = array(
                    'secret'    =>  $settings['recaptcha_secret_key'],
                    'response'  =>  $_POST['g-recaptcha-response']
                );
		$google_recaptcha_response = wp_remote_get( esc_url_raw ( add_query_arg( $recaptcha_args, 'https://www.google.com/recaptcha/api/siteverify' ) ), array( 'sslverify' => false ) );
		 if ( is_wp_error( $google_recaptcha_response ) ) {
                     $response['message'] = __( 'Sorry, an error occurred. Please retry', 'kanzu-support-desk');
                     return $response;
                 }
                $recaptcha_text = json_decode( wp_remote_retrieve_body( $google_recaptcha_response ) );
                if ( $recaptcha_text->success ) {
                     $response['error'] = false;
                     return $response;
                }
                else{
                    switch ( $recaptcha_text->{'error-codes'}[0] ) {
                        case 'missing-input-secret':
                            $response['message'] = __( 'Sorry, an error occurred due to a missing reCAPTCHA secret key. Please refresh the page and retry.', 'kanzu-support-desk');
                            break;
                        case 'invalid-input-secret':
                            $response['message'] = __( 'Sorry, an error occurred due to an invalid or malformed reCAPTCHA secret key. Please refresh the page and retry.', 'kanzu-support-desk');
                            break;
                        case 'missing-input-response':
                            $response['message'] = __( 'Sorry, an error occurred due to a missing reCAPTCHA input response. Please refresh the page and retry.', 'kanzu-support-desk');
                            break;
                        case 'invalid-input-response':
                            $response['message'] = __( 'Sorry, an error occurred due to an invalid or malformed reCAPTCHA input response. Please refresh the page and retry.', 'kanzu-support-desk');
                            break;
                        default:
                            $response['message'] = $settings['recaptcha_error_message'];
                    }
                    return $response;
                }
        }

        /**
         * Redirect user after successful login.
         *
         * @param string $redirect_to URL to redirect to.
         * @param string $request URL the user is coming from.
         * @param object $user Logged user's data.
         * @return string
         * @since 2.0.0
         * @since 2.2.1 Filter commented out
         */
        public function do_login_redirect( $redirect_to, $request, $user ) {
                global $user;//is there a user to check?
                if ( isset( $user->roles ) && is_array( $user->roles ) ) {
                        //check for admins
                        if ( in_array( 'ksd_customer', $user->roles ) ) {
                                //@TODO Check $request and send customer to 'My Tickets' or to 'Create new ticket'
                                $current_settings = Kanzu_Support_Desk::get_settings();//Get current settings
                                return get_permalink( $current_settings['page_my_tickets'] ); //redirect customers to 'my tickets'
                        }
                }
            return $redirect_to;
        }



        /**
         * Add an extra column header to the WooCommerce Orders table
         * @param array $table_headers
         * @return string
         */
        public function woo_orders_add_table_headers( $table_headers ){
            $table_headers['order-tickets'] = '&nbsp;';
            return $table_headers;
        }

        /**
         * Add a 'contact support' button to the WooCommerce orders table
         * @param Array $actions
         * @param Object $order
         * @return Array
         * @TODO Receive and process woo_order_id
         */
        public function woo_orders_add_ticket_button( $actions, $order ){
            $ksd_settings = Kanzu_Support_Desk::get_settings();
            $url = esc_url( add_query_arg( 'woo_order_id', $order->id, get_permalink( $ksd_settings['page_submit_ticket'] ) ) );
            $actions['ksd-woo-orders-create-ticket'] = array(
                'url' => $url,
                'name' => __( 'Contact Support','kanzu-support-desk' )
            );
            return $actions;
        }


        /**
         * Append content to a single ticket. We use this to append replies
         * @param int $tkt_id The ticket's ID
         * @TODO Move this to a more appropriate class
         */
        public function append_ticket_replies( $tkt_id ){
            //Retrieve the replies
            require_once( KSD_PLUGIN_DIR . 'includes/admin/class-ksd-admin.php' );
            KSD()->templates->get_template_part( 'single', 'replies' );
            KSD()->templates->get_template_part( 'form', 'new-reply' );
        }


        /**
         * Append a KSD class to the WP contact form 7 form if the
         * ksd_support_form setting is active
         * //@todo while save is underway and ksd_support_form, check for required fields
         * //when form is submitted, create ticket before 'feedback' entry is created
         */
        public function append_ksd_class_to_wpcf7( $class ){
            if( class_exists( 'WPCF7_ContactForm' ) ):
                $wpcf7 = WPCF7_ContactForm::get_current();
                if( $wpcf7->is_true( 'ksd_support_form' ) ):
                    return $class.' ksd-support-form';
                endif;
            endif;
            return $class;
        }


}
endif;

return new KSD_Public();
