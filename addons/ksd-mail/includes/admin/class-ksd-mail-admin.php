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
                
                //Check for updates
                add_action( 'admin_init', array ( $this, 'do_updates' ), 0 );
                
                //Display admin notices
                add_action( 'admin_notices', array ( $this,'display_admin_notice') );
                
                //Set-up actions
                $this->setup_actions( 'invalid' );

                //add action to ksd_mail_check hook. This has to be added here otherwise it won't run
                add_action( 'ksd_mail_check', array( $this, 'check_mailbox' ) );
                
                //If the main plugin, KSD, gets deactivated
                add_action( 'ksd_deactivated', array( 'KSD_Mail_Install', 'ksd_deactivated' ), 2 , 1 );  //We give this a very high priority (2) to ensure
                                                                                        //that it runs earlier than all other add-on logic. That
                                                                                        //other add-on logic would fail to run on realizing that KSD isn't active                
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
         * HTML for KSD Support/Help view
         */
        public function show_help () {
            include( KSD_MAIL_DIR . '/includes/admin/views/html-admin-help.php' );
        }
        	
	/**
	 * Setup KSD Mail actions
         * @param string $action_types Can be 'valid' or 'invalid'
	 * @since    1.0.0
	 */
	private function setup_actions( $action_type ){	
            if ( 'invalid' === $action_type ){                
                //Display KSD mail license in a separate licenses tab
                add_filter( 'ksd_display_licenses', array( $this, 'display_licences' ) );     
                
                //Add JS
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
                
                //Handle AJAX callbacks
                add_action( 'wp_ajax_ksd_modify_license', array( $this, 'modify_license_status' ));                
                                
                //Save mail settings with the overall KSD settings
                add_filter( 'ksd_settings', array( $this, 'save_settings' ), 10, 2 );     
            }
            else{
                //Add extra settings to the KSD Settings view
		add_action( 'ksd_display_settings', array( $this, 'show_settings' ) ); 
                
                //Add help to KSD help view
                add_action( 'ksd_display_help', array( $this, 'show_help' ) );                

                //Register backgroup process
                add_action( 'ksd_run_deamon', array( $this, 'check_mailbox' )  );  
                                
                //To update wp_cron when settings are saved.
                add_action( 'ksd_settings_saved', array( $this, 'schedule_mail_check') );         

                //Schedule mail check
                $this->schedule_mail_check();

            }

	}
        
        /**
         * This saves the addon settings
         * @param array $current_settings The current KSD settings
         * @param array $new_settings The $_POST array submitted by the settings form. If the array is empty then it's a 'Reset to defaults' call
         */
        public function save_settings( $current_settings, $new_settings=array() ){
                //To eliminate key clashes with any add-on, we add all our new settings 
                //into their own array in $current_settings with key KSD_MAIL_OPTIONS_KEY        
                if ( count ( $new_settings ) == 0 ){//This is a 'Reset to Defaults' call. Populate the array with default settings
                   $current_settings[KSD_MAIL_OPTIONS_KEY] = KSD_Mail_Install::get_default_options();
                }
                else{    
                    //Iterate through the new settings and save them as items in the array 
                    foreach ( $current_settings[KSD_MAIL_OPTIONS_KEY] as $option_name => $default_value ) {                        
                        //If a setting exists in $new_settings, replace the corresponding value in $current_settings with it. 
                        //Otherwise, leave the $current_settings value as is
                        if ( isset ( $new_settings[$option_name] ) ){
                             $current_settings[KSD_MAIL_OPTIONS_KEY][$option_name] = sanitize_text_field ( stripslashes ( $new_settings[$option_name] ) );
                           }                       
                    }
                }
                return $current_settings;               
        }
        
        /**
         * Display active licenses in the settings tab. We display them under a 'Licenses' tab
         * @param type $current_settings The current KSD settings 
         */
        public function display_licences ( $current_settings ){

            $mail_settings = $current_settings[KSD_MAIL_OPTIONS_KEY];
            //Add an item to the licenses array. We add the name, license and the key name used to store it in the Db
            $mail_settings['licenses'][] = array (  "addon_name"                => "KSD Mail",
                                                    "license"                   => $mail_settings['ksd_mail_license_key'],
                                                    "license_db_key"            => 'ksd_mail_license_key',
                                                    "license_status"            => $mail_settings['ksd_mail_license_status'],
                                                    "license_status_db_key"     => 'ksd_mail_license_status'
                                                  );
            return $mail_settings;
        }
        
                
        public function do_updates() {
	// retrieve our license key from the DB 
        $mail_settings  =   KSD_Mail::get_settings();
	$license_key    = isset( $mail_settings[ 'ksd_mail_license_key' ] ) ? trim( $mail_settings[ 'ksd_mail_license_key' ] ) : null;   
        
        //Display notice if no license is set or if the license is invalid
        if ( empty ( $license_key ) || 'invalid' == $mail_settings['ksd_mail_license_status'] ){
            KSD_Mail::$ksd_mail_admin_notices = array(
                "error"=> __( "Kanzu Support Desk Mail | You need to provide a valid license key before this plugin can function","kanzu-support-desk" )
                );
        }else{
            //Set-up actions
            $this->setup_actions( 'valid' );
        
            //Run check only if transient is not set
            if ( false === get_transient( '_ksd_mail_license_last_check' ) ){
                set_transient( '_ksd_mail_license_last_check', date('U'), 7 * 60 * 60 * 24 );//Expires in a week. Didn't use constant WEEK_IN_SECONDS since it was
                                                                                            //added in WP 3.5+
                $this->do_license_modifications ( 'check_license', $license_key );
            }
        }
        $plugin_data    =   get_plugin_data( KSD_MAIL_PLUGIN_FILE );
	// setup the updater
	$ksd_updater = new KSD_Mail_Updater(  $plugin_data['AuthorURI'], KSD_MAIL_PLUGIN_FILE, array( 
			'version' 	=> KSD_MAIL_VERSION, 		// current version number
			'license' 	=> $license_key, 		// license key  
			'item_name'     => $plugin_data['Name'], 	// name of this plugin
			'author' 	=> $plugin_data['Author']       // author of this plugin
		)
	);

        }
        
        public function display_admin_notice() {
            if ( count ( KSD_Mail::$ksd_mail_admin_notices ) > 0 ){
                foreach ( KSD_Mail::$ksd_mail_admin_notices as $admin_notice_type => $admin_notice_message ){
                    $notice_body="<div class='{$admin_notice_type}'><p>";
                    $notice_body.=$admin_notice_message;
                    $notice_body.="</p></div>";
                    echo $notice_body;               
                }
            }
          }
        
                    
        /*
         * Checks mailbox for new tickets to log.
         * @TODO Display connection-related errors as admin notices
         */
            public function check_mailbox(){     
            //Get the settings
            $mail_settings  =   KSD_Mail::get_settings();
            
            //Get last run time 
            $run_freq = (int) $mail_settings['ksd_mail_check_freq'] ; //in minutes
            $last_run = (int) $mail_settings['ksd_mail_lastrun_time']; //saved as unix timestamp
            $now = (int) date( 'U' );
            $interval = $now - $last_run ;
            
            if ( $interval  < ( $run_freq * 60 ) ){               
                _e( ' Run interval has not passed.' ); //@TODO: Add run log instead.
                return;
            }
            
            //Update last run time.
            $mail_settings['ksd_mail_lastrun_time'] = date( 'U' );
            KSD_Mail::update_settings( $mail_settings );
                    
            $m_box = new KSD_Mail_Processor();

            if ( ! $m_box->connect() ) {
                    _e( "Can not connect to mailbox.", "ksd-mail" );//@TODO Display admin notice
                    return;
            }

            $count = $m_box->numMsgs();

            for ( $i=1; $i <= $count; $i++)
            {

                    $msg = array();
                    $msg = $m_box->getMessage($i);

                    $mail_mailbox = $msg['headers']->from[0]->mailbox;
                    $mail_host    = $msg['headers']->from[0]->host;                    
                    
                    $new_ticket                         = new stdClass(); 
                    $new_ticket->tkt_subject            = $msg['headers']->subject;
                    $new_ticket->tkt_message            = $msg['text'];;
                    $new_ticket->tkt_channel            = "EMAIL";
                    $new_ticket->tkt_status             = "OPEN";
                    $new_ticket->cust_email             = $mail_mailbox . "@" . $mail_host;
                    $new_ticket->cust_fullname          = $msg['headers']->from[0]->personal;
                    $new_ticket->tkt_time_logged        = $msg['headers']->MailDate;
                        
                    //Log the ticket
                    do_action( 'ksd_log_new_ticket', $new_ticket );

            }


            $m_box->disconnect();

        }//eof:
        
        /**
         * Enqueue scripts used by KSD Mail. We add them to the footer to 
         * make use of Kanzu Support's localized variables. We also list the Kanzu Support desk
         * script among the dependencies
         */
        public function enqueue_admin_scripts() { 
             wp_enqueue_script( KSD_MAIL_SLUG.'-admin-js', KSD_MAIL_PLUGIN_URL.'/assets/js/ksd-mail-admin.js', array( 'jquery','kanzu-support-desk-admin-js' ), KSD_MAIL_VERSION, true ); 
        }
        
        /**
         * Handle an AJAX request to change the license's status. We use this to activate
         * and deactivate licenses
         */
        public function modify_license_status(){
        if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                die ( __('Busted!','kanzu-support-desk') );                         
          }
          
          $response = $this->do_license_modifications( $_POST['license_action'],sanitize_text_field( $_POST['license'] ) );
          echo json_encode( $response );          
          die();//Important. Don't leave this out
        }
        
        /**
         * Make a remote call to Kanzu Code to activate/Deactivate/check license status
         * @param string $action The action to perform on the license. Can be 'activate_license','deactivate_license' and 'check_license'
         * @return boolean
         */
        private function do_license_modifications( $action, $license=null ) {
                $response_message = __( 'An error occured. Please retry','kanzu-support-desk' );
           
                 // retrieve the license from the database
		$mail_settings  = KSD_Mail::get_settings();
                if( is_null( $license ) ){
                    $license    =   trim( $mail_settings[ 'ksd_mail_license_key' ] );    	
                }   
                $plugin_data    =   get_plugin_data( KSD_MAIL_PLUGIN_FILE );
		// data to send in our API request
		$api_params = array( 
			'edd_action'    => $action, 
			'license' 	=> $license, 
			'item_name'     => urlencode( $plugin_data['Name'] ), // the name of our product in EDD
			'url'           => home_url()
		);
		
		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, $plugin_data['AuthorURI'] ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ){
			return $response_message;
                }
		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
                switch( $action ){
                    case 'activate_license':
                    case 'check_license':
                        if( $license_data->license == 'valid' ) {
                            $mail_settings[ 'ksd_mail_license_status' ] = 'valid';
                            $response_message = __('License successfully validated. Welcome to a super-charged Kanzu Support Desk!','kanzu-support-desk' );
                           }
                        else{//Invalid license
                            $mail_settings[ 'ksd_mail_license_status' ] = 'invalid';
                            $response_message = __( 'Invalid License. Please renew your license','kanzu-support-desk' );             
                        }
                        break;
                    case 'deactivate_license':
                        if( $license_data->license == 'deactivated' ) {
                            $mail_settings[ 'ksd_mail_license_status' ] = 'invalid';
                            $response_message = __( 'Your license has been deactivated successfully. Thank you.','kanzu-support-desk' );                            
                        }
                        break;
                }
                //Retrieve the license for saving
                $mail_settings[ 'ksd_mail_license_key' ] = $license;
                
                //Update the Db
                KSD_Mail::update_settings( $mail_settings );
                
                return $response_message;	 
        } 
        
        
        
        
        /**
         * Create new cron scedure
         */
        public function create_cron_schedule(){
            
            add_filter( 'cron_schedules',  array( $this, 'ksd_create_cron_schedule') ); 
            
        }
        

        /**
         * Hook into cron_schedules filter to create "KSDMailCheckInt" schedule
         * 
         * @param array $schedules
         */
        public function ksd_create_cron_schedule( $schedules ){
            $mail_settings  = KSD_Mail::get_settings();
                    
            $minutes = ( 0 == (int)$mail_settings[ 'ksd_mail_check_freq' ] ? 30 : (int)$mail_settings[ 'ksd_mail_check_freq' ] ); //Default value of 30
            
            $schedules[ 'KSDMailCheckInt' ] = array(
              'interval' => $minutes * 60, 
              'display' => __( 'KSD Mail Check Interval')
            );
            

            return $schedules;
        }
        
        /**
         * Delete the cron schedule
         */
        public function delete_cron_schedule(){
            
            //Unschedule all cron jobs attached to our 'ksd_mail_check' hook
            wp_clear_scheduled_hook( 'ksd_mail_check' );     
        }
        
        
        /**
         * Schedule mail check hook called "ksd_mail_check" on "KSDMailCheckInt"
         * cron schedule  
         */
        public function create_cron_hook(){
            //Schedule ksd_mail_check action hook. We first check if we've already defined 
            //a cron event
            if ( ! wp_next_scheduled( 'ksd_mail_check' ) ) {//No event defined. Define one     
                wp_schedule_event( time(), 'KSDMailCheckInt', 'ksd_mail_check');      
            } 
        }
        
        /**
         * Create cron schedule and schedule mail check 
         */
        public function schedule_mail_check(){
            $this->create_cron_schedule();
            $this->create_cron_hook();
        }   
                    
}
endif;

return new KSD_Mail_Admin();
