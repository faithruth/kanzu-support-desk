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
		add_action( 'wp_ajax_ksd_delete_ticket', array( $this, 'delete_ticket' ));
		add_action( 'wp_ajax_ksd_change_status', array( $this, 'change_status' ));
                add_action( 'wp_ajax_ksd_assign_to', array( $this, 'assign_to' ));
                add_action( 'wp_ajax_ksd_reply_ticket', array( $this, 'reply_ticket' ));
                add_action( 'wp_ajax_ksd_get_single_ticket', array( $this, 'get_single_ticket' ));   
                add_action( 'wp_ajax_ksd_get_ticket_replies', array( $this, 'get_ticket_replies' ));   
		add_action( 'wp_ajax_ksd_dashboard_ticket_volume', array( $this, 'get_dashboard_ticket_volume' )); 
                add_action( 'wp_ajax_ksd_get_dashboard_summary_stats', array( $this, 'get_dashboard_summary_stats' ));  
                add_action( 'wp_ajax_ksd_update_settings', array( $this, 'update_settings' )); 
                

		
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
	
		wp_enqueue_style( KSD_SLUG .'-admin-styles', plugins_url( '../../assets/css/admin-kanzu-support-desk.css', __FILE__ ), array(), KSD_VERSION );

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
                wp_enqueue_script( KSD_SLUG . '-admin-settings', plugins_url( '../../assets/js/ksd-settings.js', __FILE__ ), array( 'jquery','jquery-ui-core','jquery-ui-tabs','json2','jquery-ui-dialog','jquery-ui-tooltip' ), KSD_VERSION );
                wp_enqueue_script( KSD_SLUG . '-admin-script', plugins_url( '../../assets/js/admin-kanzu-support-desk.js', __FILE__ ), array( 'jquery','jquery-ui-core','jquery-ui-tabs','json2','jquery-ui-dialog','jquery-ui-tooltip' ), KSD_VERSION ); 
		$ksd_admin_tab = ( isset( $_GET['page'] ) ? $_GET['page'] : "" );	 //This determines which tab to show as active
                $agents_list = "<ul class='assign_to hidden'>";
                foreach (  get_users() as $agent ) {
                    $agents_list .= "<li ID=".$agent->ID.">".esc_html( $agent->display_name )."</li>";
                }
                $agents_list .= "</ul>";
                //Localization allows us to send variables to the JS script
				wp_localize_script(KSD_SLUG . '-admin-script','ksd_admin',array('admin_tab'=> $ksd_admin_tab,'ajax_url' => admin_url( 'admin-ajax.php'),'ksd_admin_nonce' => wp_create_nonce( 'ksd-admin-nonce' ),'ksd_tickets_url'=>admin_url( 'admin.php?page=ksd-tickets'),'ksd_agents_list'=>$agents_list,'ksd_current_user_id'=>get_current_user_id()));
		
                //Add the script that validates New Ticket additions
                wp_enqueue_script( KSD_SLUG . '-validate', plugins_url( '../../assets/js/jquery.validate.min.js', __FILE__ ), array("jquery"), "1.13.0" ); 

	}

	
	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . KSD_SLUG ) . '">' . __( 'Settings', KSD_SLUG ) . '</a>'
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
		
		//Add the ticket pages. This syntax, __('Word','domain'), allows us to do localization
		//@TODO Move this to separate functions
		$ticket_types = array();
		$ticket_types['ksd-dashboard']=__('Dashboard','kanzu-support-desk');
		$ticket_types['ksd-tickets']=__('Tickets','kanzu-support-desk');
                $ticket_types['ksd-new-ticket']=__('New Ticket','kanzu-support-desk');
		$ticket_types['ksd-settings']=__('Settings','kanzu-support-desk');
		$ticket_types['ksd-addons']=__('Add-ons','kanzu-support-desk');
		$ticket_types['ksd-help']=__('Help','kanzu-support-desk');
		
		foreach ( $ticket_types as $submenu_slug => $submenu_title ) {
			add_submenu_page($menu_slug, $page_title, $submenu_title, $capability, $submenu_slug, array($this,$function));
		}
	
	}
	
	/**
	 * Display the main Kanzu Support Desk admin dashboard
	 * @TODO Move output logic to separate functions
	 * @TODO Move some of this logic to ajax; like ticket deletion
         * @TODO Change $_POST field names for the form to match the table field names
         *       to use a forloop to do the ticket logging
	 */
	public function output_admin_menu_dashboard(){
		$this->do_admin_includes();
                if( isset( $_POST['ksd-submit'] ) ) {//If it's a form submission
                   // @TODO Switch this to AJAX        
                   $this->log_new_ticket("STAFF",$_POST['tkt_subject'],$_POST['ksd-ticket-description'],$_POST['customer_name'],$_POST['customer_email'],$_POST['assign-to'],$_POST['tkt_severity'],$_POST['tkt_logged_by'],"OPEN");
                   wp_redirect(admin_url('admin.php?page=ksd-tickets'));
                    exit;
                }
               else {//Output the dashboard
                   $settings = $this->get_settings();//We'll need these for the settings page
                    include_once('views/html-admin-wrapper.php');
                }
	}

	/**
	 * Include the files we use in the admin dashboard
	 */
    public function do_admin_includes() {		
		include_once( KSD_PLUGIN_DIR.  "/includes/admin/controllers/Tickets.php");
		include_once( KSD_PLUGIN_DIR.  "/includes/admin/controllers/Users.php");
                include_once( KSD_PLUGIN_DIR.  "/includes/admin/controllers/Assignments.php");  
                include_once( KSD_PLUGIN_DIR.  "/includes/admin/controllers/Replies.php");  

	}
	/** 
	 * Handle AJAX callbacks. Currently used to sort tickets 
	 */
	public function filter_tickets() {		 
	  if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) )
			die ( 'Busted!');
		$this->do_admin_includes();
                $check_ticket_assignments = "no";//This comes into play when we need to check the assignments table
		switch( $_POST['view'] ):
                        case '#tickets-tab-2': //'All Tickets'
				$filter=" tkt_status != 'RESOLVED'";
			break;
			case '#tickets-tab-3'://'Unassigned Tickets'
                                $filter = " = 0 ";
				$check_ticket_assignments = "yes"; 
			break;
			case '#tickets-tab-4'://'Recently Updated' i.e. Updated in the last hour. @TODO Make the '1 hour' configurable 
				$filter=" tkt_time_updated < DATE_SUB(NOW(), INTERVAL 1 HOUR)"; 
			break;
			case '#tickets-tab-5'://'Recently Resolved'.i.e Resolved in the last hour. Make this configurable
				$filter=" tkt_time_updated < DATE_SUB(NOW(), INTERVAL 1 HOUR) AND tkt_status = 'RESOLVED'"; 
			break;
			case '#tickets-tab-6'://'Resolved'
				$filter=" tkt_status = 'RESOLVED'";
			break;
			default://'My Unresolved'
                                $filter = " = ".get_current_user_id()." AND T.tkt_status != 'RESOLVED'";
				$check_ticket_assignments = "yes"; 
		endswitch;
                $raw_tickets = $this->filter_ticket_view( $filter, $check_ticket_assignments );
                $response = ( empty( $raw_tickets ) ? __( "Nothing to see here. Great work!","kanzu-support-desk" ) : $raw_tickets );
		echo json_encode($response);		 
		die();// IMPORTANT: don't leave this out
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
		$assign_ticket = new AssignmentsController();		
		$status = ( $assign_ticket->assignTicket( $_POST['tkt_id'],$_POST['tkt_assign_assigned_to'],$_POST['ksd_current_user_id'] ) ? __("Re-assigned","kanzu-support-desk") : __("Failed","kanzu-support-desk") );
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
         * @param type $channel
         * @param type $title
         * @param type $description
         * @param type $customer_name
         * @param type $customer_email
         * @param type $assign_to
         * @param type $severity Ticket's severity
         * @param type $status
         * @return type
         */
        public function log_new_ticket ( $channel,$title,$description,$customer_name,$customer_email,$assign_to,$severity,$tkt_logged_by,$status ){
            	$tO = new stdClass(); 
                $tO->tkt_subject    	    = $title;
                $tO->tkt_initial_message    = $description;
                $tO->tkt_description        = $description;
                $tO->tkt_channel            = $channel;
                $tO->tkt_severity           = $severity;
                $tO->tkt_status             = $status;
                $tO->tkt_logged_by          = $tkt_logged_by;

                $TC = new TicketsController();
               $new_ticket_id = $TC->logTicket( $tO );
               $this->do_ticket_assignment( $new_ticket_id,$assign_to,$tkt_logged_by );
               return ( $new_ticket_id > 0 ) ? True : False;
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
			$output_array = array();
                        $output_array[] = array("Day","Ticket Volume");
                        
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
          * Get all settings
          */
         public static function get_settings(){
             return get_option(Kanzu_Support_Install::$ksd_options_name);
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

