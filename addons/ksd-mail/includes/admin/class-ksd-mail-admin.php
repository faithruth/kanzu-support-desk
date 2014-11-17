<?php
/**
 * Admin side of KSD Mail
 *
 * @package   KSD_Mail
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Mail_Admin' ) ) :

class KSD_Mail_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;   


        /**
	 * Initialize the addon
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		//Add extra settings to the KSD Settings view
		add_action( 'ksd_display_settings', array( $this, 'show_settings' ) ); 
                
                //Add addon to KSD addons view 
                add_action( 'ksd_display_addons', array( $this, 'show_addons' ) );
                
                //Add help to KSD help view
                add_action( 'ksd_display_help', array( $this, 'show_help' ) );
                
                //Save settings
                add_filter( 'ksd_settings', array( $this, 'save_settings' ), 10, 2 );     
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
         * HTML to added to settings KSD settings form.
         * @param array $current_settings Array holding the current settings
         */
        public function show_settings( $current_settings ){
            include( KSD_MAIL_DIR . '/includes/admin/views/html-admin-settings.php' );
        }
        
        
        
        /**
         * HTML for KSD addons view
         */
        public function show_addons () {
            include( KSD_MAIL_DIR . '/includes/admin/views/html-admin-addons.php' );
        }
 
        /**
         * HTML for KSD Support/Help view
         */
        public function show_help () {
            include( KSD_MAIL_DIR . '/includes/admin/views/html-admin-help.php' );
        }
        
        /**
         * This saves the addon settings
         * @param array $current_settings The current KSD settings
         * @param array $new_settings $_POST values. If the array is empty then it's a reset of the settings
         */
        public function save_settings( $current_settings, $new_settings=array() ){
                //We add all our new settings into their own array to prevent key clashes
                $current_settings[KSD_Mail_Install::$ksd_options_name] = array();
                
                if ( count ($new_settings) == 0 ){//This is a 'Reset to Defaults' call
                   $current_settings[KSD_Mail_Install::$ksd_options_name] = KSD_Mail_Install::get_default_options();
                }
                else{
                    //Iterate through the new settings and save them as items in the array 
                    foreach ( KSD_Mail_Install::get_default_options() as $option_name => $default_value ) {
                        $current_settings[KSD_Mail_Install::$ksd_options_name][$option_name] = sanitize_text_field ( stripslashes ( $new_settings[$option_name] ) );
                    }
                }
                
                return $current_settings;               
        }
        

        
        
}
endif;

return new KSD_Mail_Admin();

