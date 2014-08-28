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
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;	
 

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

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( KSD_SLUG .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), KSD_VERSION );
		}

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

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( KSD_SLUG . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), KSD_VERSION );
		}

	}

	
	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . KSD_SLUG ) . '">' . __( 'Settings', KSD_SLUG ) . '</a>'
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
		add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this,$function),null,40);
		
		//Add the ticket pages. This syntax, __('Word','domain'), allows us to do localization
		$ticket_types = array();
		$ticket_types['ksd-my-unresolved']=__('My unresolved tickets','kanzu-support-desk');
		$ticket_types['ksd-all-tickets']=__('All tickets','kanzu-support-desk');
		$ticket_types['ksd-unassigned']=__('Unassigned tickets','kanzu-support-desk');
		$ticket_types['ksd-recently-updated']=__('Recently updated','kanzu-support-desk');
		$ticket_types['ksd-recently-resolved']=__('Recently resolved','kanzu-support-desk');
		$ticket_types['ksd-closed']=__('Closed','kanzu-support-desk');
		
		foreach ( $ticket_types as $submenu_slug => $submenu_title ) {
			$submenu_function = 'admin_menu_tickets';
			add_submenu_page($menu_slug, $page_title, $submenu_title, $capability, $submenu_slug, array($this,$submenu_function));
		}
		
		// Add submenu page with same slug as parent to ensure no duplicates
		 $sub_menu_title = __('Settings','kanzu-support-desk');
		add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, array($this,$function));

		// Now add the submenu page for Help
		$submenu_page_title = __('Kanzu Support Help','kanzu-support-desk');
		$submenu_title = __('Help','kanzu-support-desk');
		$submenu_slug = 'kanzu-support-help';
		$submenu_function = 'output_admin_menu_help';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,$submenu_function)); 
	
	}
	
	public function output_admin_menu_dashboard(){
		include('views/html-admin-dashboard.php');
	}

	public function output_admin_menu_settings() {
		if (!current_user_can('manage_options')) {
			wp_die('You do not have sufficient permissions to access this page.');
		}

    // Render the HTML for the Settings page or include a file that does
	}

	public function output_admin_menu_help() {
		if (!current_user_can('manage_options')) {
			wp_die('You do not have sufficient permissions to access this page.');		}

    // Render the HTML for the Help page or include a file that does
	
	}

	/** 
	 * Handle all the tickets requests
	 */
	 public function admin_menu_tickets($type){
	 
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

