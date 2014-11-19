<?php
/**
 * Holds all installation & deactivation-related functionality.  
 * On activation, activate is called.
 * On de-activation, 
 * @package   KSD_Mail
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Mail_Install' ) ) :

class KSD_Mail_Install {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;
        

	/**
	 * Initialize the KSD Mail addon
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
 
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
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 */
	public static function activate() { 
            //Check for re-activation. Will later be used to check for upgrades
            $ksd_mail_settings   =   KSD_Mail::get_settings();            
            if ( isset ( $ksd_mail_settings['ksd_mail_version'] ) &&  $ksd_mail_settings['ksd_mail_version'] == KSD_MAIL_VERSION ) {//Bail out if it's a re-activation
                return;
            }
            self::set_default_options(); 	
	}
        
 
 
             private static function set_default_options() {                  
                
                 KSD_Mail::update_settings( self::get_default_options() );                    
            }
            
            /**
             * Get default settings
             */
            public static function get_default_options(){
             
                return  array (
                        /** KSD Version info ********************************************************/
                        'ksd_mail_version'                  => KSD_MAIL_VERSION,
                    
                        /** Mail Settings ************************************************************/
                        'ksd_mail_server'                   => 'mail.example.com',
                        'ksd_mail_account'                  => 'user@example.com',
                        'ksd_mail_check_freq'               => '30', //minutes
                        'ksd_mail_mailbox'                  => 'INBOX',//default mail box
                        'ksd_mail_password'                 => '',
                        'ksd_mail_protocol'                 => 'pop3',
                        'ksd_mail_port'                     => '110',
                        'ksd_mail_validate_certificate'     => 'NO',
                        'ksd_mail_useSSL'                   => 'NO',
                        'ksd_mail_lastrun_time'             => date( 'U' ),
                        
                        /** License Information ************************************************************/
                        'ksd_mail_license_key'              => '',
                        'ksd_mail_license_status'           => 'invalid'
                    );
            }
 
}

endif;

return new KSD_Mail_Install();
