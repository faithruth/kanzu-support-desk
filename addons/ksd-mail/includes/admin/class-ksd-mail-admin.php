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
                

                //Register backgroup process
                add_action( 'ksd_run_deamon', array( $this, 'check_mailbox' )  );
                
                //Save mail settings with the overall KSD settings
                add_filter( 'ksd_settings', array( $this, 'save_settings' ), 10, 2 );   
                
                //Display KSD mail license in a separate licenses tab
                add_filter( 'ksd_display_licenses', array( $this, 'display_licences' ) );     

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
         * @param array $new_settings The $_POST array submitted by the settings form. If the array is empty then it's a 'Reset to defaults' call
         */
        public function save_settings( $current_settings, $new_settings=array() ){
                //To eliminate key clashes with any add-on, we add all our new settings 
                //into their own array in $current_settings with key KSD_Mail_Install::$ksd_options_name 
                $current_settings[KSD_Mail_Install::$ksd_options_name] = array();
                
                if ( count ( $new_settings ) == 0 ){//This is a 'Reset to Defaults' call. Populate the array with default settings
                   $current_settings[KSD_Mail_Install::$ksd_options_name] = KSD_Mail_Install::get_default_options();
                }
                else{
                    //Iterate through the new settings and save them as items in the array 
                    foreach ( $current_settings as $option_name => $default_value ) {
                        //If a setting exists in $new_settings, replace the corresponding value in $current_settings with it. 
                        //Otherwise, leave the $current_settings value as is
                        $current_settings[KSD_Mail_Install::$ksd_options_name][$option_name] = ( isset ( $new_settings[$option_name] ) ? sanitize_text_field ( stripslashes ( $new_settings[$option_name] ) ) : $current_settings[KSD_Mail_Install::$ksd_options_name][$option_name] );
                    }
                }
                
                return $current_settings;               
        }
        
        /**
         * Display active licenses in the settings tab. We display them under a 'Licenses' tab
         * @param type $current_settings The current KSD settings 
         */
        public function display_licences ( $current_settings ){
            $licences_array = array();
            $mail_settings = $current_settings[KSD_Mail_Install::$ksd_options_name];
            //Add an item to the licenses array. We add the name, license and the key name used to store it in the Db
            $mail_settings['licenses'][] = array (  "addon_name"                => "KSD Mail",
                                                    "license"                   => $mail_settings['ksd_mail_license_key'],
                                                    "license_db_key"            => 'ksd_mail_license_key',
                                                    "license_status"            => $mail_settings['ksd_mail_license_status'],
                                                    "license_status_db_key"     => 'ksd_mail_license_status'
                                                  );
            return $mail_settings;
        }
        

        /*
         * Checks mailbox for new tickets to log.
         */
        public function check_mailbox () {

            //Get last run time
            $run_freq = (int) get_option('ksd_mail_check_freq') ; //in minutes
            $last_run = (int) get_option('ksd_mail_lastrun_time'); //saved as unix timestamp
            $now = (int) date( 'U' );
            $interval = $now - $last_run ;

            if ( $interval  < ( $run_freq * 60 ) ){
                _e( ' Run interval has not passed.' ); //@TODO: Add run log instead.
                return;
            }

            //Update last run time.
            update_option( 'ksd_mail_lastrun_time', date( 'U' ) ) ;


            $m_box = new Kanzu_Mail();

            if ( ! $m_box->connect() ) {
                    _e( "Can not connect to mailbox.", "ksd-mail" );
                    return;
            }

            $count = $m_box->numMsgs();

            $TC = new KSD_Tickets_Controller();

            for ( $i=1; $i <= $count; $i++)
            {

                    $msg = array();
                    $msg = $m_box->getMessage($i);

                    $mail_mailbox = $msg['headers']->from[0]->mailbox;
                    $mail_host    = $msg['headers']->from[0]->host;
                    $email        = $mail_mailbox . "@" . $mail_host;
                    $subject      = $msg['headers']->subject;



                    //Get userid
                    $userObj = new KSD_Users_Controller();
                    $users   = array();
                    $users   = $userObj->get_users("user_email = '$email'");
                    //TODO: Add check if user is not registered. send email notification.
                    
                    if ( count( $users ) == 0 ){
                        throw new Exception ( __( "User with email account " . 
                                              $email ." doesn't exist.\n" ) , -3 );
                    }
                    $user_id = $users[0]->ID;

                    //Checi if this is a new ticket before logging it.
                    $value_parameters   = array();
                    $filter             = " tkt_subject = %s AND tkt_status = %d AND tkt_logged_by = %d ";
                    $value_parameters[] = $subject ;
                    $value_parameters[] = 'OPEN' ;
                    $value_parameters[] = $user_id ;

                    $tc = $TC->get_tickets( $filter, $value_parameters );

                    if ( count($tc) == 0  ){

                        $new_ticket                      = new stdClass(); 
                        $new_ticket->tkt_subject         = $msg['headers']->subject;
                        $new_ticket->tkt_message_excerpt = "New Ticket.";
                        $new_ticket->tkt_message         =  $msg['text'];;
                        $new_ticket->tkt_channel         = "EMAIL";
                        $new_ticket->tkt_status          = "OPEN";
                        $new_ticket->tkt_private_notes   = "Private notes";
                        $new_ticket->tkt_logged_by       = $user_id;
                        $new_ticket->tkt_updated_by      = $user_id;

                        $id = $TC->log_ticket( $new_ticket );

                        if( $id > 0){
                                echo __( "New ticket id" ) . ": $id\n"  ;
                                echo __( "Subject: ") . $subject . "\n" ;
                                echo __( "Added by: ") . $users[0]->user_nicename . "\n"  ;
                                echo  __( "Date:") . date() . "\n" ;
                                echo "----------------------------------------------\n" ;		
                        }

                        $new_ticket = null;

                    }else{//@TODO: Log Reply

                    } 

            }


            $m_box->disconnect();

        }//eof:
        
        
}
endif;

return new KSD_Mail_Admin();

