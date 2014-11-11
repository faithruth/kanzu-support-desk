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

if ( ! class_exists( 'Kanzu_Support_Admin' ) ) :

class Kanzu_Support_Admin {

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
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );

		// Add an action link pointing to the options page.
		add_filter( 'plugin_action_links_' . plugin_basename(KSD_PLUGIN_FILE), array( $this, 'add_action_links' ) );		

		//Handle AJAX calls
		add_action( 'wp_ajax_ksd_filter_tickets', array( $this, 'filter_tickets' ));
                add_action( 'wp_ajax_ksd_log_new_ticket', array( $this, 'log_new_ticket' ));
		add_action( 'wp_ajax_ksd_delete_ticket', array( $this, 'delete_ticket' ));
		add_action( 'wp_ajax_ksd_change_status', array( $this, 'change_status' ));
                add_action( 'wp_ajax_ksd_assign_to', array( $this, 'assign_to' ));
                add_action( 'wp_ajax_ksd_reply_ticket', array( $this, 'reply_ticket' ));
                add_action( 'wp_ajax_ksd_get_single_ticket', array( $this, 'get_single_ticket' ));   
                add_action( 'wp_ajax_ksd_get_ticket_replies', array( $this, 'get_ticket_replies' ));   
		add_action( 'wp_ajax_ksd_dashboard_ticket_volume', array( $this, 'get_dashboard_ticket_volume' )); 
                add_action( 'wp_ajax_ksd_get_dashboard_summary_stats', array( $this, 'get_dashboard_summary_stats' ));  
                add_action( 'wp_ajax_ksd_update_settings', array( $this, 'update_settings' )); 
                add_action( 'wp_ajax_ksd_reset_settings', array( $this, 'reset_settings' )); 
                add_action( 'wp_ajax_ksd_update_private_note', array( $this, 'update_private_note' ));                 

		
		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		//add_action( '@TODO', array( $this, 'action_method_name' ) );
		//add_filter( '@TODO', array( $this, 'filter_method_name' ) );

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
	
		wp_enqueue_style( KSD_SLUG .'-admin-styles', plugins_url( '../../assets/css/ksd-admin.css', __FILE__ ), array(), KSD_VERSION );
                wp_enqueue_style( KSD_SLUG .'-admin-css', plugins_url( '../../assets/css/ksd.css', __FILE__ ), array(), KSD_VERSION );

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_admin_scripts() { 
		
		//Load the script for charts. Load this before the next script. 
                //@TODO Uncomment the following line to use GoogleCharts online version for production. Using local for dev
                //wp_enqueue_script( KSD_SLUG . '-admin-charts', '//google.com/jsapi', array(), KSD_VERSION ); 
                wp_enqueue_script( KSD_SLUG . '-admin-charts', plugins_url( '../../assets/js/jsapi.js', __FILE__ ), array(), KSD_VERSION );
                wp_enqueue_script( KSD_SLUG . '-admin-utils', plugins_url( '../../assets/js/ksd-utils.js', __FILE__ ), array( 'jquery','jquery-ui-core','jquery-ui-tabs','json2','jquery-ui-dialog','jquery-ui-tooltip','jquery-ui-accordion' ), KSD_VERSION );
                wp_enqueue_script( KSD_SLUG . '-admin-settings', plugins_url( '../../assets/js/ksd-settings.js', __FILE__ ), array( 'jquery','jquery-ui-core','jquery-ui-tabs','json2','jquery-ui-dialog','jquery-ui-tooltip','jquery-ui-accordion' ), KSD_VERSION );
                wp_enqueue_script( KSD_SLUG . '-admin-dashboard', plugins_url( '../../assets/js/ksd-dashboard.js', __FILE__ ), array( 'jquery','jquery-ui-core','jquery-ui-tabs','json2','jquery-ui-dialog','jquery-ui-tooltip','jquery-ui-accordion' ), KSD_VERSION );
                wp_enqueue_script( KSD_SLUG . '-admin-tickets', plugins_url( '../../assets/js/ksd-tickets.js', __FILE__ ), array( 'jquery','jquery-ui-core','jquery-ui-tabs','json2','jquery-ui-dialog','jquery-ui-tooltip','jquery-ui-accordion' ), KSD_VERSION );
                wp_enqueue_script( KSD_SLUG . '-admin-script', plugins_url( '../../assets/js/ksd-admin.js', __FILE__ ), array( 'jquery','jquery-ui-core','jquery-ui-tabs','json2','jquery-ui-dialog','jquery-ui-tooltip','jquery-ui-accordion' ), KSD_VERSION ); 
		
                //Variables to send to the admin JS script
                $ksd_admin_tab = ( isset( $_GET['page'] ) ? $_GET['page'] : "" );//This determines which tab to show as active
                
                $agents_list = "<ul class='assign_to2 hidden'>";//The available list of agents
                foreach (  get_users() as $agent ) {
                    $agents_list .= "<li ID=".$agent->ID.">".esc_html( $agent->display_name )."</li>";
                }
                $agents_list .= "</ul>";
                
                //This array allows us to internalize (translate) the words/phrases/labels displayed in the JS 
                $admin_labels_array = array();
                $admin_labels_array['dashboard_chart_title']        = __('Incoming Tickets','kanzu-support-desk');
                $admin_labels_array['dashboard_open_tickets']       = __('Total Open Tickets','kanzu-support-desk');
                $admin_labels_array['dashboard_unassigned_tickets'] = __('Unassigned Tickets','kanzu-support-desk');
                $admin_labels_array['dashboard_avg_response_time']  = __('Avg. Response Time','kanzu-support-desk');
                $admin_labels_array['tkt_trash']                    = __('Trash','kanzu-support-desk');
                $admin_labels_array['tkt_assign_to']                = __('Assign To','kanzu-support-desk');
                $admin_labels_array['tkt_change_status']            = __('Change Status','kanzu-support-desk');
                $admin_labels_array['tkt_subject']                  = __('Subject','kanzu-support-desk');
                $admin_labels_array['tkt_cust_fullname']            = __('Customer Name','kanzu-support-desk');
                $admin_labels_array['tkt_cust_email']               = __('Customer Email','kanzu-support-desk');
                $admin_labels_array['tkt_reply']                    = __('Reply','kanzu-support-desk');
                $admin_labels_array['tkt_forward']                  = __('Forward','kanzu-support-desk');
                $admin_labels_array['tkt_update_note']              = __('Update Note','kanzu-support-desk');
                $admin_labels_array['msg_still_loading']            = __('Still Loading...','kanzu-support-desk');
                $admin_labels_array['msg_loading']                  = __('Loading...','kanzu-support-desk');
                        
                
                //Localization allows us to send variables to the JS script
                wp_localize_script( KSD_SLUG . '-admin-script',
                                    'ksd_admin',
                                    array(  'admin_tab'             =>  $ksd_admin_tab,
                                            'ajax_url'              =>  admin_url( 'admin-ajax.php'),
                                            'ksd_admin_nonce'       =>  wp_create_nonce( 'ksd-admin-nonce' ),
                                            'ksd_tickets_url'       =>  admin_url( 'admin.php?page=ksd-tickets'),
                                            'ksd_agents_list'       =>  $agents_list,
                                            'ksd_current_user_id'   =>  get_current_user_id(),
                                            'ksd_labels'            =>  $admin_labels_array
                                        )
                                    );
		

	}

	
	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=ksd-settings' ) . '">' . __( 'Settings', KSD_SLUG ) . '</a>'
			),
			$links
		);

	}        
                        
	
	/**
	 * Add menu items in the admin panel 
	 */
	public function add_menu_pages() {
		//Add the top-level admin menu
		$page_title = __('Kanzu Support Desk','kanzu-support-desk');
		$menu_title = __('Kanzu Support Desk','kanzu-support-desk');
		$capability = 'manage_options';
		$menu_slug = KSD_SLUG;
		$function = 'output_admin_menu_dashboard';
		
			/*
			 * Add the settings page to the Settings menu.
			 *
			 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.		 
			 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
			 */
                        
		add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this,$function),'dashicons-groups',40);
		
		//Add the ticket pages. 
		$ticket_types = array();
		$ticket_types[ 'ksd-dashboard' ]  =   __( 'Dashboard','kanzu-support-desk' );
		$ticket_types[ 'ksd-tickets' ]    =   __( 'Tickets','kanzu-support-desk' );
                $ticket_types[ 'ksd-new-ticket' ] =   __( 'New Ticket','kanzu-support-desk' );
		$ticket_types[ 'ksd-settings' ]   =   __( 'Settings','kanzu-support-desk' );
		$ticket_types[ 'ksd-addons' ]     =   __( 'Add-ons','kanzu-support-desk' );
		$ticket_types[ 'ksd-help' ]       =   __( 'Help','kanzu-support-desk' );               
		
		foreach ( $ticket_types as $submenu_slug => $submenu_title ) {
                    
                    add_submenu_page($menu_slug, $page_title, $submenu_title, $capability, $submenu_slug, array($this,$function));                        		
      
                }
                
	}
                        
	
	/**
	 * Display the main Kanzu Support Desk admin dashboard
	 */
	public function output_admin_menu_dashboard(){
		$this->do_admin_includes();               
                include_once( KSD_PLUGIN_DIR .  'includes/admin/views/html-admin-wrapper.php');                
	}
        
        /**
         * Add a screen option to the tickets sub-page
         
        public function add_tickets_screen_option(){
            $option = 'per_page';
 
            $args = array(
                'label' => __('Tickets', 'kanzu-support-desk'),
                'default' => 20,//The default number of tickets to display per page
                'option' => $this->tickets_per_page_options_key            
                    );
 
            add_screen_option( $option, $args );
        }*/
        
        /**
         * Set the tickets screen option when the user does a save
         * The tickets screen option tells us how many tickets the user
         * would like to view per page
         
        
        public function set_tickets_screen_option($status, $option, $value) {
 
            if ( $this->tickets_per_page_options_key == $option ) return $value;
 
        return $status;
 
        }*/
        

	/**
	 * Include the files we use in the admin dashboard
	 */
    public function do_admin_includes() {		
		include_once( KSD_PLUGIN_DIR.  "includes/controllers/Tickets.php");
		include_once( KSD_PLUGIN_DIR.  "includes/controllers/Users.php");
                include_once( KSD_PLUGIN_DIR.  "includes/controllers/Assignments.php");  
                include_once( KSD_PLUGIN_DIR.  "includes/controllers/Replies.php");  
                include_once( KSD_PLUGIN_DIR.  "includes/controllers/Customers.php");  

	}
	/** 
	 * Handle AJAX callbacks. Currently used to sort tickets 
	 */
	public function filter_tickets() {		 
	  if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) )
			die ( 'Busted!');
          
                try{
                    $this->do_admin_includes();

                    switch( $_POST['view'] ):
                            case '#tickets-tab-2': //'All Tickets'
                                    $filter=" tkt_status != 'RESOLVED'";
                            break;
                            case '#tickets-tab-3'://'Unassigned Tickets'
                                    $filter = " tkt_assigned_to IS NULL ";			
                            break;
                            case '#tickets-tab-4'://'Recently Updated' i.e. Updated in the last hour. 
                                    $settings = Kanzu_Support_Desk::get_settings();
                                    $filter=" tkt_time_updated < DATE_SUB(NOW(), INTERVAL ".$settings['recency_definition']." HOUR)"; 
                            break;
                            case '#tickets-tab-5'://'Recently Resolved'.i.e Resolved in the last hour. 
                                    $settings = Kanzu_Support_Desk::get_settings();
                                    $filter=" tkt_time_updated < DATE_SUB(NOW(), INTERVAL ".$settings['recency_definition']." HOUR) AND tkt_status = 'RESOLVED'"; 
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
                    if( $search != "" && $search !="Search..."){
                        $filter .= " AND UPPER(tkt_subject) LIKE UPPER('%$search%') ";
                    }

                    //order
                    $filter .= " ORDER BY tkt_time_logged DESC ";

                    //We now pick the limit from screen options

                  //  $per_page = get_user_meta(get_current_user_id(), $this->tickets_per_page_options_key, true);
                    //Switched back to $limit to address AJAX issue first

                    //limit
                    $count_filter = $filter; //Query without limit to get the total number of rows
                    $filter .= " LIMIT $offset , $limit " ;

                    //Results count
                    $tickets = new TicketsController(); 
                    $count   = $tickets->get_count( $count_filter );
                    $raw_tickets = $this->filter_ticket_view( $filter );

                    //$response = ( empty( $raw_tickets ) ? __( "Nothing to see here. Great work!","kanzu-support-desk" ) : $raw_tickets );

                    $response = array('data'=>'Undefined error in ' . __line__, 'status'=>'-1');
                    if( empty( $raw_tickets ) ){
                        //$response = __( "Nothing to see here. Great work!","kanzu-support-desk" );
                        $response['data'] = __( "Nothing to see here. Great work!","kanzu-support-desk" );
                        $response['status'] = 0;
                    }    else{
                        /*
                        $response = array(
                            0 => $raw_tickets,
                            1 => $count
                        );

                         */

                        $response['data'] = array(
                            0 => $raw_tickets,
                            1 => $count
                        );
                        $response['status'] = 1;

                    }



                    echo json_encode($response);		 
                    die();// IMPORTANT: don't leave this out
                }catch( Exception $e){
                    $response = array('data'=> $e , 'status'=>'-1');
                    die();// IMPORTANT: don't leave this out
                }    
	}
	/**
	 * Filters tickets based on the view chosen
	 */
	public function filter_ticket_view( $filter = "" , $check_ticket_assignments = "no" ) {
		$tickets = new TicketsController();                 
                $tickets_raw = $tickets->getTickets( $filter, $check_ticket_assignments ); 	
                //Process the tickets for viewing on the view. Replace the username and the time with cleaner versions
                foreach ( $tickets_raw as $ksd_ticket ) {
                    $this->format_ticket_for_viewing( $ksd_ticket );
                }                
                return $tickets_raw;
	}
        
	/**
         * Retrieve a single ticket and all its replies
         */
        public function get_single_ticket(){
             if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) )
			die ( 'Busted!');
            $this->do_admin_includes();	
            $tickets = new TicketsController();	
            $ticket = $tickets->getTicket($_POST['tkt_id']);
            $this->format_ticket_for_viewing($ticket);
            echo json_encode($ticket);
            die();
            
        }
        
        /**
         * Retrieve a ticket's replies
         */
        public function get_ticket_replies(){
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) )
			die ( 'Busted!');
            $this->do_admin_includes();	
            $replies = new RepliesController();
            $query = " rep_tkt_id = ".$_POST['tkt_id'];
            $response = $replies->getReplies($query);
            echo json_encode($response);
            die();
        }
	
	/**
	 * Delete a ticket
	 */
	public function delete_ticket(){
			  if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) )
			die ( 'Busted!');
		$this->do_admin_includes();	
		$tickets = new TicketsController();		
		$status = ( $tickets->deleteTicket( $_POST['tkt_id'] ) ? __("Deleted","kanzu-support-desk") : __("Failed","kanzu-support-desk") );
		echo json_encode($status);
		die();// IMPORTANT: don't leave this out
	}
	
	/**
	 * Change a ticket's status
	 */
	public function change_status(){
		 if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) )
			die ( 'Busted!');
		$this->do_admin_includes();	
		$tickets = new TicketsController();		
		$status = ( $tickets->changeTicketStatus( $_POST['tkt_id'],$_POST['tkt_status'] ) ? __("Updated","kanzu-support-desk") : __("Failed","kanzu-support-desk") );
		echo json_encode($status);
		die();// IMPORTANT: don't leave this out
	}
        
        /**
	 * Change a ticket's assignment
	 */
	public function assign_to(){
		 if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) )
			die ( 'Busted!');
		$this->do_admin_includes();	
                $updated_ticket = new stdClass();
                $updated_ticket->tkt_id = $_POST['tkt_id'];
                $updated_ticket->new_tkt_assigned_to = $_POST['tkt_assign_assigned_to'];
                $updated_ticket->new_tkt_logged_by = $_POST['ksd_current_user_id'];
		$assign_ticket = new TicketsController();		
		$status = ( $assign_ticket->update_ticket( $updated_ticket ) ? __("Re-assigned","kanzu-support-desk") : __("Failed","kanzu-support-desk") );
		echo json_encode($status);
		die();// IMPORTANT: don't leave this out
	}
        
        /**
         * Add a reply to a single ticket
         */
        
        public function reply_ticket(){
                if ( ! wp_verify_nonce( $_POST['edit-ticket-nonce'], 'ksd-edit-ticket' ) )
			die ( 'Busted!');
		$this->do_admin_includes();
                
                $tO = new stdClass(); 
                $tO->rep_tkt_id    	     = $_POST['tkt_id'];
                $tO->rep_message 	 = $_POST['ksd_ticket_reply'];

               $TC = new RepliesController();
               $response = $TC->addReply( $tO );
               $status = ( $response > 0  ? $tO->rep_message : __("Error", 'kanzu-support-desk') );
               echo json_encode($status);
                die();// IMPORTANT: don't leave this out
        }
        /**
         * Log new tickets
         */
        public function log_new_ticket (){
                if ( ! wp_verify_nonce( $_POST['new-ticket-nonce'], 'ksd-new-ticket' ) )
			die ( 'Busted!');
                
		$this->do_admin_includes();
            
            	$tkt_channel    = "STAFF"; //This is the default channel
                $tkt_status     = "OPEN";//The default status
                
                //Check what channel the request came from
                switch ( sanitize_text_field( $_POST['ksd_tkt_channel']) ){
                    case 'support_tab':
                        $tkt_channel   =       "SUPPORT_TAB";
                        break;
                    default:
                        $tkt_channel    =      "STAFF";
                }                       
                           
                $ksd_excerpt_length = 30;//The excerpt length to use for the message
                //We sanitize each input before storing it in the database
                $new_ticket = new stdClass(); 
                $new_ticket->tkt_subject    	    = sanitize_text_field( stripslashes( $_POST[ 'ksd_tkt_subject' ] ) );
                $new_ticket->tkt_message_excerpt    = wp_trim_words( sanitize_text_field( stripslashes( $_POST[ 'ksd_tkt_message' ] )  ), $ksd_excerpt_length );
                $new_ticket->tkt_message            = sanitize_text_field( stripslashes( $_POST[ 'ksd_tkt_message' ] ));
                $new_ticket->tkt_channel            = $tkt_channel;
                $new_ticket->tkt_status             = $tkt_status;
                
                //These other fields are only available if a ticket is logged from the admin side so we need to 
                //check if they are set
                if ( isset( $_POST[ 'ksd_tkt_severity' ] ) ) {
                $new_ticket->tkt_severity           = sanitize_text_field( $_POST[ 'ksd_tkt_severity' ] );   
                }
                if ( isset( $_POST[ 'ksd_tkt_logged_by' ] ) ) {
                $new_ticket->tkt_logged_by          = sanitize_text_field( $_POST[ 'ksd_tkt_logged_by' ] );
                }
                if ( isset( $_POST[ 'ksd_tkt_assigned_to' ] ) ) {
                    $new_ticket->tkt_assigned_to    = sanitize_text_field( $_POST[ 'ksd_tkt_assigned_to' ] );
                }
               
                //Get the settings. We need them for tickets logged from the support tab
                $settings = Kanzu_Support_Desk::get_settings();
                //Return a different message based on the channel the request came on
                $output_messages_by_channel = array();
                $output_messages_by_channel[ 'STAFF' ] = __("Ticket Logged", "kanzu-support-desk");
                $output_messages_by_channel[ 'SUPPORT_TAB' ] = $settings['tab_message_on_submit'];
                
                
                $TC = new TicketsController();
                $new_ticket_status = ( $TC->logTicket( $new_ticket ) > 0  ? $output_messages_by_channel[ $tkt_channel ] : __("Error", 'kanzu-support-desk') );
                
                //Store the customer's info if he/she's not yet in the Db
                $customer = new stdClass();
                $customer->cust_email           = sanitize_email( $_POST[ 'ksd_cust_email' ] );
                //Check whether one or more than one customer name was provided
                if( false === strpos( trim( sanitize_text_field( $_POST[ 'ksd_cust_fullname' ] ) ), ' ') ){//Only one customer name was provided
                   $customer->cust_firstname   =   sanitize_text_field( $_POST[ 'ksd_cust_fullname' ] );
                }
                else{
                   preg_match('/(\w+)\s+([\w\s]+)/', sanitize_text_field( $_POST[ 'ksd_cust_fullname' ] ), $customer_fullname );
                    $customer->cust_firstname   = $customer_fullname[1];
                    $customer->cust_lastname   = $customer_fullname[2];//We store everything besides the first name in the last name field
                }
                //Add the customer to the customer's table
                $CC = new Customers_Controller();
                $CC->addCustomer( $customer );
                
                if ( ( "yes" == $settings['enable_new_tkt_notifxns'] &&  $tkt_channel  ==  "SUPPORT_TAB") || ( $tkt_channel  ==  "STAFF" && "yes" == $_POST['ksd_send_email']) ){
                    $this->send_email( $customer->cust_email );
                }
                
                echo json_encode( $new_ticket_status );
                die();// IMPORTANT: don't leave this out
        }
        
        /**
         * Assign the ticket 
         * @TODO Add error check
         */
        private function do_ticket_assignment($ticket_id,$assign_to,$assign_by){
           $assignment = new AssignmentsController();
           $assignment->assignTicket( $ticket_id, $assign_to, $assign_by );                
            
        }
        
        /**
         * Replace a ticket's logged_by field with the nicename of the user who logged it
         * Replace the tkt_time_logged with a date better-suited for viewing
         * NB: Because we use {@link UsersController}, call this function after {@link do_admin_includes} has been called.   
         * @param type $ticket The ticket to modify
         */
        private function format_ticket_for_viewing($ticket){
            //Replace the username
            $users = new UsersController();
            $ticket->tkt_logged_by = str_replace($ticket->tkt_logged_by,$users->getUser($ticket->tkt_logged_by)->user_nicename,$ticket->tkt_logged_by);
            //Replace the date 
            $ticket->tkt_time_logged = date('M d',strtotime($ticket->tkt_time_logged));
            
            return $ticket;
        }
		
		/**
		 * Generate the ticket volumes displayed in the graph in the dashboard
		 */
		public function get_dashboard_ticket_volume(){
			 if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) )
				die ( 'Busted!');
			$this->do_admin_includes();
			$tickets = new TicketsController();		
			$tickets_raw = $tickets->get_dashboard_graph_statistics();
                        
                        $y_axis_label = __("Day", "kanzu-support-desk" );
                        $x_axis_label = __("Ticket Volume", "kanzu-support-desk" );
                        
			$output_array = array();
                        $output_array[] = array( $y_axis_label,$x_axis_label );
                        
			foreach ( $tickets_raw as $ticket ) {
				$output_array[] = array ($ticket->date_logged,$ticket->ticket_volume);			
			}
        
			
			echo json_encode($output_array, JSON_NUMERIC_CHECK);
			die();//Important
		}
                //@TODO Optimize the way the average response time is calculated
                public function get_dashboard_summary_stats(){
                    if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) )
				die ( 'Busted!');
                    $this->do_admin_includes();
                    $tickets = new TicketsController();	
                    $summary_stats = $tickets->get_dashboard_statistics_summary();
                    //Compute the average
                    $total_response_time = 0;
                    foreach ( $summary_stats["response_times"] as $response_time ) {
                        $total_response_time+=$response_time->time_difference;
                    }
                    $summary_stats["average_response_time"] = date('H:i:s', $total_response_time/count($summary_stats["response_times"]) ) ;

                    echo json_encode ( $summary_stats , JSON_NUMERIC_CHECK);                    
                    die();//Important
                }
         
         /**
          * Set a particular option
          * @param String $option_name Options name
          * @param String $new_value The new option value
          */
         public function update_option ( $option_name, $new_value ){
             
         }  
         
         /**
          * Update all settings
          */
         public function update_settings(){
            if ( ! wp_verify_nonce( $_POST['update-settings-nonce'], 'ksd-update-settings' ) )
                die ( 'Busted!');
            $updated_settings = array();
            //Iterate through the new settings and save them. 
            foreach ( Kanzu_Support_Install::get_default_options() as $option_name => $default_value ) {
                $updated_settings[$option_name] = $_POST[$option_name];
            }
            $status = update_option( Kanzu_Support_Install::$ksd_options_name, $updated_settings );

            echo json_encode ( ( $status ? __("Settings Updated") : __("Update failed. Please retry") ) );
            die();
         }
         
         /**
          * Reset settings to default
          */
         public function reset_settings(){
             	  if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) )
			die ( 'Busted!');
                  
            $status = update_option( Kanzu_Support_Install::$ksd_options_name, Kanzu_Support_Install::get_default_options() );

            echo json_encode ( ( $status ? __("Settings Reset") : __("Reset failed. Please retry") ) );
            die();
         }
         
         /**
          * Update a ticket's private note
          * @TODO Change tkt_private_notes to tkt_private_note
          * @TODO IMPORTANT: Escape user input
          */
         public function update_private_note(){
               if ( ! wp_verify_nonce( $_POST['edit-ticket-nonce'], 'ksd-edit-ticket' ) )
			die ( 'Busted!');
		$this->do_admin_includes();
                $updated_ticket = new stdClass();
                $updated_ticket->tkt_id = $_POST['tkt_id'];
                $updated_ticket->new_tkt_private_notes = $_POST['tkt_private_note'];
                $tickets = new TicketsController();		
		$status = ( $tickets->update_ticket( $updated_ticket  ) ? __("Noted","kanzu-support-desk") : __("Failed","kanzu-support-desk") );
		echo json_encode( $status );
		die();// IMPORTANT: don't leave this out             
         }
         
         /**
          * Send mail. 
          * @param string $to Recipient email address
          * @param string $type Type of email to send. Can be "new_ticket"
          */
         public function send_email( $to, $type="new_ticket" ){
             $settings = Kanzu_Support_Desk::get_settings();             
             switch ( $type ):
                 default://'new_ticket' is the default
                     $subject   = $settings['ticket_mail_subject'];
                     $message   = $settings['ticket_mail_message'];
                     $headers = 'From: '.$settings['ticket_mail_from_name'].' <'.$settings['ticket_mail_from_email'].'>' . "\r\n";
             endswitch;
             return wp_mail( $to, $subject, $message, $headers ); 
         }
 
                        
	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */

	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}
endif;

return new Kanzu_Support_Admin();

