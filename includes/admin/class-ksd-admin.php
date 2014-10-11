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
                
                wp_enqueue_script( KSD_SLUG . '-admin-script', plugins_url( '../../assets/js/admin-kanzu-support-desk.js', __FILE__ ), array( 'jquery','jquery-ui-core','jquery-ui-tabs','json2','jquery-ui-dialog' ), KSD_VERSION ); 
		$ksd_admin_tab = (isset($_GET['page']) ? $_GET['page'] : "");	 //This determines which tab to show as active
                $agents_list = "<ul class='assign_to hidden'>";
                foreach (  get_users() as $agent ) {
                    $agents_list .= "<li ID=".$agent->ID.">".esc_html( $agent->display_name )."</li>";
                }
                $agents_list .= "</ul>";
                //Localization allows us to send variables to the JS script
		wp_localize_script(KSD_SLUG . '-admin-script','ksd_admin',array('admin_tab'=> $ksd_admin_tab,'ajax_url' => admin_url( 'admin-ajax.php'),'ksd_admin_nonce' => wp_create_nonce( 'ksd-admin-nonce' ),'ksd_tickets_url'=>admin_url( 'admin.php?page=ksd-tickets'),'ksd_agents_list'=>$agents_list,'ksd_current_user_id'=>get_current_user_id()));
		 

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
                if( isset($_POST['ksd-submit']) ) {//If it's a form submission
                   // @TODO Switch this to AJAX        
                   $this->log_new_ticket("STAFF",$_POST['tkt_subject'],$_POST['ksd-ticket-description'],$_POST['customer_name'],$_POST['customer_email'],$_POST['assign-to'],$_POST['tkt_severity'],"OPEN");
                   wp_redirect(admin_url('admin.php?page=ksd-tickets'));
                    exit;
                }
               else {//Output the dashboard
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
		switch($_POST['view']):
			case '#tickets-tab-2':
				$filter="";
			break;
			case '#tickets-tab-3':
				$filter=" tkt_id > 30"; //Dummy filter
			break;
			case '#tickets-tab-4':
				$filter=" tkt_id > 60"; //Dummy filter
			break;
			case '#tickets-tab-5':
				$filter=" tkt_id > 50"; //Dummy filter
			break;
			case '#tickets-tab-6':
				$filter=" tkt_status != 'OPEN'";
			break;
			default:
				$filter=" tkt_status = 'OPEN'";
		endswitch;
                $filter.= " ORDER BY tkt_time_logged DESC ";
                $raw_tickets = $this->filter_ticket_view($filter);
                $response = ( !empty($raw_tickets) ? $raw_tickets : __("Nothing to see here. Great work!","kanzu-support-desk") );
		echo json_encode($response);
		 
		die();// IMPORTANT: don't leave this out
	}
	/**
	 * Filters tickets based on the view chosen
	 */
	public function filter_ticket_view($filter=""){
		$tickets = new TicketsController();		
		$tickets_raw = $tickets->getTickets($filter);
                //Process the tickets for viewing on the view. Replace the username and the time with cleaner versions
                foreach ( $tickets_raw as $ksd_ticket) {
                    $this->format_ticket_for_viewing($ksd_ticket);
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
		$status = ( $assign_ticket->assignTicket( $_POST['tkt_id'],$_POST['tkt_assign_assigned_to'],$_POST['ksd_current_user_id'] ) ? __("Updated","kanzu-support-desk") : __("Failed","kanzu-support-desk") );
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
        public function log_new_ticket($channel,$title,$description,$customer_name,$customer_email,$assign_to,$severity,$status){
            	$tO = new stdClass(); 
                $tO->tkt_subject    	     = $title;
                $tO->tkt_initial_message 	 = $description;
                $tO->tkt_description 	 = $description;
                $tO->tkt_channel     	 = $channel;
                $tO->tkt_severity     	 = $severity;
                $tO->tkt_status 	 	 = $status;

                $TC = new TicketsController();
               $response = $TC->logTicket( $tO );
               return ( $response > 0 ) ? True : False;
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

