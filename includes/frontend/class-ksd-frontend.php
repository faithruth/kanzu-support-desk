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

if ( ! class_exists( 'Kanzu_Support_FrontEnd' ) ) : 
    
class Kanzu_Support_FrontEnd {
    
    public function __construct() {
        
        //Enqueue styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
        //Add new form to the footer @TODO Display this only if the 'show support tab' option is selected
        add_action( 'wp_footer', array( $this , 'generate_new_ticket_form' ));
        //Handle AJAX
        add_action( 'wp_ajax_nopriv_ksd_log_new_ticket', array( $this, 'log_new_ticket' ));
    }
    
    public function generate_new_ticket_form(){
        include_once( KSD_PLUGIN_DIR .  'includes/frontend/views/html-frontend-new-ticket.php' );
    }
    
    	/**
	 * Register and enqueue front-specific style sheet.
	 * @TODO Change handles
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_frontend_styles() {	
		wp_enqueue_style( KSD_SLUG .'-frontend-css', KSD_PLUGIN_URL . 'assets/css/frontend-kanzu-support-desk.css' , array() , KSD_VERSION );
        }
        
        /**
         * Enqueue front-end scripts
         * Note that if a script is needed in both the front-end and admin side,
         * we load it from the front-end since that logic will always run
         * @since 1.0.0
         */
        public function enqueue_frontend_scripts() {	
            wp_enqueue_script( KSD_SLUG . '-frontend-js', KSD_PLUGIN_URL .  'assets/js/ksd-frontend.js' , array( 'jquery', 'jquery-ui-core' ), KSD_VERSION );
            wp_localize_script( KSD_SLUG . '-frontend-js', 'ksd_frontend' , array( 'ajax_url' => admin_url( 'admin-ajax.php') ) );
            //Validate the forms. This is used even by the admin side
            wp_enqueue_script( KSD_SLUG . '-validate', KSD_PLUGIN_URL . 'assets/js/jquery.validate.min.js' , array("jquery"), "1.13.0" ); 
        }
        
        /**
         * Log a new ticket. We use the backend logic
         */
        public function log_new_ticket(){
            $ksd_admin =  Kanzu_Support_Admin::get_instance();
            $ksd_admin->log_new_ticket();
        }
}
endif;

return new Kanzu_Support_FrontEnd();

