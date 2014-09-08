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
		add_action( 'wp_ajax_ksd_admin_ajax_action', array( $this, 'handle_admin_ajax_callback' ));

		
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
	 * @return    null    Return early if no settings page is registered.
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
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() { 
		
		wp_enqueue_script( KSD_SLUG . '-admin-script', plugins_url( '../../assets/js/admin-kanzu-support-desk.js', __FILE__ ), array( 'jquery','jquery-ui-core','jquery-ui-tabs','json2' ), KSD_VERSION ); 
		$ksd_admin_tab = (isset($_GET['page']) ? $_GET['page'] : "");	 //This determines which tab to show as active
		//Localization allows us to send variables to the JS script
		wp_localize_script(KSD_SLUG . '-admin-script','ksd_admin',array('admin_tab'=> $ksd_admin_tab,'ajax_url' => admin_url( 'admin-ajax.php'),'ksd_admin_nonce' => wp_create_nonce( 'ksd-admin-nonce' ),'ksd_tickets_url'=>admin_url( 'admin.php?page=ksd-tickets')));
		 
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
	 */
	public function output_admin_menu_dashboard(){
		$this->do_admin_includes();
                if( isset($_POST['ksd-submit']) ) {//If it's a form submission
                    //@TODO Switch this to AJAX        
                    $this->log_new_ticket("MANUAL",$_POST['subject'],$_POST['description'],$_POST['customer_name'],$_POST['customer_email'],$_POST['assign_to'],"OPEN");
                    wp_redirect(admin_url('admin.php?page=ksd-tickets'));
                    exit;
                }
				elseif( isset($_GET['action']) && isset($_GET['tkd_id']) ){//Currently used to edit/delete tickets
					if ( $_GET['action'] == "trash" ) {//Delete a ticket
						$tickets = new TicketsController();		
						$tickets->deleteTicket( array( 'tkt_id' =>$_GET['tkd_id'] ) );
						wp_redirect(admin_url('admin.php?page=ksd-tickets'));
						exit;
					}
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

	}
	/** 
	 * Handle AJAX callbacks. Currently used to sort tickets 
	 */
	public function handle_admin_ajax_callback() {		 
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
		echo json_encode($this->filter_ticket_view($filter));
		 
		die();// IMPORTANT: don't leave this out
	}
	/**
	 * Filters tickets based on the view chosen
	 */
	public function filter_ticket_view($filter=""){
		$tickets = new TicketsController();		
		$tickets_raw = $tickets->getTickets($filter);
                //Process the tickets for viewing on the view. Replace the username and the time with cleaner versions
                foreach ( $tickets_raw as $ticket_key => $ticket_value) {
                    //Replace the username
                    $users = new UsersController();
                    $ticket_value->tkt_logged_by = str_replace($ticket_value->tkt_logged_by,$users->getUser($ticket_value->tkt_logged_by)->user_nicename,$ticket_value->tkt_logged_by);
                    //Replace the date 
                    $ticket_value->tkt_time_logged = date('M d',strtotime($ticket_value->tkt_time_logged));
                }
                
                return $tickets_raw;
	}
        
        /**
         * Log new tickets
         * @param type $channel
         * @param type $title
         * @param type $description
         * @param type $customer_name
         * @param type $customer_email
         * @param type $assign_to
         * @param type $status
         * @return type
         */
        public function log_new_ticket($channel,$title,$description,$customer_name,$customer_email,$assign_to,$status){
            	$tO = new stdClass(); 
                $tO->tkt_title    	     = $title;
                $tO->tkt_initial_message 	 = $description;
                $tO->tkt_description 	 = $description;
                $tO->tkt_channel     	 = $channel;
                $tO->tkt_status 	 	 = $status;

                $TC = new TicketsController();
               $response = $TC->logTicket( $tO );
               return ( $response > 0 ) ? True : False;
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

