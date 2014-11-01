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

if ( ! class_exists( 'Kanzu_Support_Front' ) ) :
    
class Kanzu_Support_Front {
    
    public function __construct() {
        
        //@TODO Display this only if the 'show support tab'option is selected
        $this->generate_new_ticket_form();
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_front_styles' ) );
    }
    
    private function generate_new_ticket_form(){
        include_once( KSD_PLUGIN_DIR . '/includes/admin/views/html-front-new-ticket.php' );
    }
    
    	/**
	 * Register and enqueue front-specific style sheet.
	 *
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_front_styles() {	
		wp_enqueue_style( KSD_SLUG .'-front-styles', plugins_url( '../../assets/css/front-kanzu-support-desk.css', __FILE__ ), array(), KSD_VERSION );
        }
}
endif;
return new Kanzu_Support_Front();
?>
