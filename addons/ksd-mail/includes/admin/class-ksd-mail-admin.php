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
        
        /** An instance of the mailbox
         * 
         * @since 1.1.3
         * @var object 
         */
        protected static $mailbox;


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

                //Check/test connection mail details.	
                add_action( 'wp_ajax_ksd_test_mail_connection', array( $this, 'test_mail_connection' ) );

                //Set-up actions
                $this->setup_actions( 'invalid' );

                //add action to ksd_mail_check hook. This has to be added here otherwise it won't run
                add_action( 'ksd_mail_check', array( $this, 'check_mailbox' ) );
                
                //Mark mail as read when the new ticket is logged successfully
                add_action( 'ksd_new_ticket_logged', array( $this, 'new_ticket_logged', 10, 2 ) );
                
                //Log errors as notices
                add_action( 'admin_notices', array ( $this,'show_errors') );
                
                 //Catch all email deamon errors. First define PHP constants to cater to lower PHP < 5.3
                if ( !defined( 'E_STRICT' ) )   define( 'E_STRICT', 2048 );
                if ( !defined( 'E_RECOVERABLE_ERROR' ) )    define( 'E_RECOVERABLE_ERROR', 4096 );
                if ( !defined( 'E_DEPRECATED' ) )   define( 'E_DEPRECATED', 8192 );
                if ( !defined( 'E_USER_DEPRECATED' ) )  define( 'E_USER_DEPRECATED', 16384 );
               set_error_handler( array( $this, "error_handler" ), 
                        E_ERROR ^ E_CORE_ERROR ^ E_COMPILE_ERROR ^ E_USER_ERROR ^
                        E_RECOVERABLE_ERROR ^  E_WARNING ^  E_CORE_WARNING ^ 
                        E_COMPILE_WARNING ^ E_USER_WARNING ^ E_NOTICE ^  E_USER_NOTICE ^ 
                        E_DEPRECATED    ^  E_USER_DEPRECATED    ^  E_PARSE);

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
            if ( KSD_Mail::is_KSD_active() ){
                include( KSD_MAIL_DIR . '/includes/admin/views/html-admin-settings.php' );
            }
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
                    //That done, deal with checkboxes. When unchecked, they aren't sent in $new_settings
                                    //For a checkbox, if it is unchecked then it won't be set in $_POST
                $checkbox_names = array( "ksd_mail_useSSL","ksd_mail_validate_certificate" );
                    //Iterate through the checkboxes and set the value to "no" for all that aren't set
                    foreach ( $checkbox_names as $checkbox_name ){
                        $current_settings[KSD_MAIL_OPTIONS_KEY][$checkbox_name] = ( !isset ( $new_settings[$checkbox_name] ) ? "no" : $new_settings[$checkbox_name] );
                    }     
                }
                    
                //Check connection
                if( $new_settings['ksd_mail_settings_changed'] == 'yes' ){
                    $this->check_connection( $new_settings[KSD_MAIL_OPTIONS_KEY], false ); 
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
        if ( ( empty ( $license_key ) || 'invalid' == $mail_settings['ksd_mail_license_status'] ) && KSD_Mail::is_KSD_active() ){
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
        

        /**
         * Handles mail plugin errors.
         * Currently, does nothing. Still requires further testing
         * @param int $errno   Error number 
         * @param string $errstr  Error message
         * @param string $errfile Error file
         * @param int $errline Line with error in the file
         * @since 1.3.0
         */
        public function error_handler( $errno, $errstr, $errfile, $errline ){

            $errorType = array (
                E_ERROR                => 'ERROR',
                E_CORE_ERROR           => 'CORE ERROR',
                E_COMPILE_ERROR        => 'COMPILE ERROR',
                E_USER_ERROR           => 'USER ERROR',
                E_RECOVERABLE_ERROR    => 'RECOVERABLE ERROR',
                E_WARNING              => 'WARNING',
                E_CORE_WARNING         => 'CORE WARNING',
                E_COMPILE_WARNING      => 'COMPILE WARNING',
                E_USER_WARNING         => 'USER WARNING',
                E_NOTICE               => 'NOTICE',
                E_USER_NOTICE          => 'USER NOTICE',
                E_DEPRECATED           => 'DEPRECATED',
                E_USER_DEPRECATED      => 'USER_DEPRECATED',
                E_PARSE                => 'PARSING ERROR'
           );
            
            if (array_key_exists($errno, $errorType)) {
                    $errname = $errorType[$errno];
                } else {
                    $errname = 'UNKNOWN ERROR';
            }
            
          /*  update_option( 'ksd_mail_log', array(
                'type' => $errname,
                'msg'  => $errstr,
                'line' => $errline,
                'file' => $errfile,
                'no' => $errno,
                'time' => date('Ymdhhmi')
                )
            );*/
        }

        
        /**
         * Display mail-related errors
         * @return type
         */

        public function show_errors ( ) {
            
            $opt = get_option('ksd_mail_log');
            
            //Ensure that the option exists first or isset
            if ( empty( $opt ) || !is_array( $opt) ) {
                return;                 
            }
          
            $errstr  = $opt['msg'];
            
            //Clear error 
            update_option( 'ksd_mail_log', array());     
            return;//@TODO We kill the execution here. Errors still need to first be properly sieved
            ob_start();?>
            <div class="error">
              <p>
                <?php printf( __( "Kanzu Support Desk Mail | %s", "kanzu-support-desk" ), $errstr ); ?>
              <p/>
            </div>
            <?php
            echo ob_get_clean();

        }
        
        /*
         * Checks mailbox for new tickets to log.
         * 
         */
            public function check_mailbox ( ) {     

            try{
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
                    
            $this->check_connection( $mail_settings, false );
            
            // Get some mail
            $mailsIds = self::$mailbox->searchMailBox( 'UNSEEN' );
            if( ! $mailsIds ) {
                //No email tickets.
                return;
            }

            foreach ( $mailsIds as $mailId ) {
                //$mailId = reset($mailsIds);
                $mail = self::$mailbox->getMail( $mailId );                     
                $new_ticket                         = new stdClass(); 
                $new_ticket->tkt_subject            = $mail->subject;
                $new_ticket->tkt_message            = ( !empty( $mail->textHtml ) ? $mail->textHtml : $mail->textPlain );
                $new_ticket->tkt_channel            = "EMAIL";
                $new_ticket->tkt_status             = "OPEN";
                $new_ticket->cust_email             = $mail->fromAddress;
                $new_ticket->cust_fullname          = $mail->fromName;
                $new_ticket->tkt_time_logged        = $mail->date;
                $new_ticket->addon_tkt_id           = $mailId;  //Used to inform the main plugin what add-on ID is used for this ticket. The main plugin
                                                                //sends back ticket logging results which we use to mark this as logged or not logged
                
                //Get one attachment for now.
                //TODO: iterate over all attachments and add them to the attachments field.
                //$attachments = array();
                //$attachments = $mail->getAttachments();
                //$new_ticket->tkt_attachments         = basename( $attachments[0]->filePath );
                
                //Log the ticket
                do_action( 'ksd_log_new_ticket', $new_ticket );
            }
            //Note that self::$mailbox disconnects the connection to the mailbox automatically in its destructor method
           }
           catch ( Exception $e  ) {
               $this->error_handler( $e->getCode() ,  $e->getMessage() , 'NaN', 'NaN');
           }

        }//eof:
        
        /**
         * Mark mail as read when tickets (both new and replies) are logged successfully
         * @param int $mail_id The mail ID
         * @param int $ticket_id The ticket ID in the database
         */
        public function new_ticket_logged( $mail_id, $ticket_id ){
            if( $ticket_id > 0 ){                            
                //Mark mail as read.   
                self::$mailbox->markMailAsRead( $mail_id );
            }
            
        }
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
                $response_message = __( 'An error occurred. Please retry','kanzu-support-desk' );
           
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
        
        
        /**
         * Check if connection succeeds with the current connection details
         * 
         * @param Array $mail_settings  An array of the current mail settings
         * @param boolean $isTest Whether or not this is a test connection
         * @return turns self::$mailbox into a connection resource on success and returns an array with $response['connected']=true|false
         * @since 1.1.0
         * @TODO If this connection fails and $isTest === false, find a way to inform the user ( i.e. for non-test traffic)
         */
        public function check_connection( $mail_settings = array(), $isTest = true ){
            
            //Connection details setup
            $the_mailbox="";
            //Append the ssl Flag if the user chose to always use SSL
            $mail_settings['ksd_mail_protocol'] = ( "yes" == $mail_settings['ksd_mail_useSSL'] ? $mail_settings['ksd_mail_protocol'].'/ssl' : $mail_settings['ksd_mail_protocol'] );

            //Cater for self-signed certificates
            if( "yes" == $mail_settings['ksd_mail_validate_certificate'] ) {
                $the_mailbox = "{" . 
                                $mail_settings['ksd_mail_server'] . ": " . 
                                $mail_settings['ksd_mail_port'] . "/" . 
                                $mail_settings['ksd_mail_protocol'] . "}" .
                                $mail_settings['ksd_mail_mailbox'];
            }
            else {
                $the_mailbox = "{" . 
                                $mail_settings['ksd_mail_server']. ":" .
                                $mail_settings['ksd_mail_port'] . "/" . 
                                $mail_settings['ksd_mail_protocol'] .
                                "/novalidate-cert}". 
                                $mail_settings['ksd_mail_mailbox'];    
            }
            
            $attachments_dir = KSD_MAIL_DIR . '/assets/attachments';
            //First thing: Make sure the IMAP extension is enabled
            if( ! extension_loaded( 'imap' ) ){
                return array ( 'connected' => false, 'response' => sprintf( __( 'Sorry, your host has not enabled the PHP IMAP extension. Please contact them or <a href="%s" target="_blank" >visit our documentation.</a> Thank you.', 'kanzu-support-desk' ), 'http://kanzucode.com/documentation' ) );
            }
            self::$mailbox = new KSD_Mail_ImapMailbox( $the_mailbox, $mail_settings['ksd_mail_account'], 
            $mail_settings['ksd_mail_password'], $attachments_dir , 'utf-8');

            try{
                self::$mailbox->getImapStream( true );
                if( $isTest ){ //If we were merely testing the connection settings, end the party here. The connection is closed in self::$mailbox's destructor
                    return array ( 'connected' => true, 'response' => __( "Woohoo! Your connection succeeded! Let's roll","kanzu-support-desk" ) );
                }
            }catch( Exception $e ) {
                //Suppress imap fatal errors
               // imap_alerts();
               // imap_errors();
               return array ( 'connected' => false, 'response' => __( "Sorry, your connection failed. Please change your settings and try again.","kanzu-support-desk" ) );
            }
        }
        
        /*
         * Test the user's current mail details through AJAX request.
         * @since 1.1.0
         */
        public function  test_mail_connection ( ) {
            if ( ! wp_verify_nonce( $_POST['ksd_mail_connection_nonce'], 'ksd-admin-nonce' ) ){
                die ( __('Busted!','kanzu-support-desk') );
            }
            $connection_response = $this->check_connection( $_POST );
            echo json_encode( $connection_response['response'] );
            die();//important otherwise output will have a 0 at the end         
        }
                    
}
endif;

return new KSD_Mail_Admin();

