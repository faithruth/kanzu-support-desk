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

		//Add settings to the KSD Settings view
		add_action( 'ksd_settings', array( $this, 'show_settings' ) );
                
                //Add addon to KSD addons view 
                add_action( 'ksd_addons', array( $this, 'show_addons' ) );
                
                //Add help to KSD help view
                add_action( 'ksd_support', array( $this, 'show_help' ) );
                
                //Save settings
                add_action( 'ksd_save_settings', array( $this, 'save_settings' ) );
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
         */
        public function show_settings(){
            include( KSD_MAIL_DIR . '/admin/views/html-admin-settings.php' );
        }
        
        
        
        /**
         * HTML for KSD addons view
         */
        public function show_addons () {
            include( KSD_MAIL_DIR . '/admin/views/html-admin-addons.php' );
        }
 
        /**
         * HTML for KSD Support/Help view
         */
        public function show_help () {
            include( KSD_MAIL_DIR . '/admin/views/html-admin-help.php' );
        }
        
        /**
         * This saves the addon settins
         * @param array $post_vars $_POST values
         */
        public function save_settings( $post_vars ){

                $settings = array();
                //Iterate through the new settings and save them. 
                foreach ( KSD_Mail_Install::get_default_options() as $option_name => $default_value ) {
                    $settings[$option_name] = sanitize_text_field ( stripslashes ( $_POST[$option_name] ) );
                }
                
                update_option ( KSD_Mail_Install::$ksd_options_name, $settings );
                
        }
        
        
        
}
endif;

return new KSD_Mail_Admin();

