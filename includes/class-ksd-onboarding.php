<?php
/**
 * Walks a new user through the plugin
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @since 2.2.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Onboarding' ) ) :

class KSD_Onboarding {
    
        /**
	 * Instance of this class.
	 *
	 * @since    1.7.0
	 *
	 * @var      object
	 */
	protected static $instance = null;   
        
        /**
         * Option key used to store the current onboarding stage
         * @var string
         */
        private $ksd_current_stage_option_key = 'ksd_onboarding_current_stage';    
        
        
        /**
         * Onboarding current stage
         * @var array
         */
        private $ksd_current_stage_key;         
        
        /**
         * Onboarding stage details
         * @var array
         */
        private $ksd_stage_details = array();          
        
        

        /**
	 * Return an instance of this class.
	 *
	 * @since     1.7.0
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
    
        public function __construct(){

            if( isset( $_GET['ksd_getting_started'] ) ):
                add_action( 'admin_notices', array( $this, 'render_getting_started_banner' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_getting_started_scripts' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_getting_started_styles' ) );
            endif;    
        }

        /**
         * Render a getting started message
         * @return string
         */
        public function render_getting_started_banner(){
        	ob_start();
        		$this->render_getting_started_nav_menu();
        	$nav_menu = ob_get_clean();
			include_once( KSD_PLUGIN_DIR .  "templates/admin/notices/getting-started.php");  
        }        

        /**
         * Render the navigation menu shown at the bottom of every section in the
         * getting started walk-through
         */
        private function render_getting_started_nav_menu(){?>
		  <ul class="ksd-gs-navigation">
		      <li><button class="button button-secondary ksd-gs-nav ksd-gs-nav-prev"><?php _ex( 'Previous','navigation button to previous section', 'kanzu-support-desk' ); ?></button> </li>
		      <li><button class="button button-primary ksd-gs-nav"><?php _ex( 'Next','navigation button to next section', 'kanzu-support-desk' ); ?></button> </li>
		    </ul> 
        <?php
        }

        public function enqueue_getting_started_scripts(){
            wp_enqueue_script( KSD_SLUG . '-gs-js', KSD_PLUGIN_URL.'/assets/js/ksd-gs.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-tabs' ), KSD_VERSION );

        }

        public function enqueue_getting_started_styles(){
            wp_enqueue_style( KSD_SLUG .'-gs-css', KSD_PLUGIN_URL.'/assets/css/ksd-gs.css' );            
        }                                      
 
}
endif;

return new KSD_Onboarding();
