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
                
                //Register backgroup process
                add_action( 'ksd_run_deamon', array( $this, 'check_mailbox' )  );
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
        
        
        public function check_mailbox(){

            //Get last run time
            $run_freq = (int) get_option('ksd_mail_check_freq') ; //in minutes
            $last_run = (int) get_option('ksd_mail_lastrun_time'); //saved as unix timestamp
            $now = (int) date( 'U' );
            $interval = $now - $last_run ;

            if ( $interval  < ( $run_freq * 60 ) ){
                unlink( $pid_file);
                _e( ' Run interval has not passed.' ); //@TODO: Add run log instead.
                return;
            }

            //Update last run time.
            update_option( 'ksd_mail_lastrun_time', date( 'U' ) ) ;


            $m_box = new Kanzu_Mail();

            if ( ! $m_box->connect() ) {

                    _e( "Can not connect to mailbox.", "ksd-mail" );
                    exit;
            }

            $count = $m_box->numMsgs();

            $TC = new TicketsController();

            for ( $i=1; $i <= $count; $i++)
            {

                    $msg=array();
                    $msg = $m_box->getMessage($i);

                    $mail_mailbox = $msg['headers']->from[0]->mailbox;
                    $mail_host    = $msg['headers']->from[0]->host;
                    $email        = $mail_mailbox . "@" . $mail_host;
                    $subject      = $msg['headers']->subject;



                    //Get userid
                    $userObj = new UsersController();
                    $users = $userObj->getUsers("user_email = '$email'");
                    //TODO: Add check if user is not registered. send email notification.
                    $user_id = $users[0]->ID;

                    //Checi if this is a new ticket before logging it.
                    $value_parameters   = array();
                    $filter             = " tkt_subject = %s AND tkt_status = %d AND tkt_logged_by = %d ";
                    $value_parameters[] = $subject ;
                    $value_parameters[] = 'OPEN' ;
                    $value_parameters[] = $user_id ;

                    $tc = $TC->get_tickets( $filter, $value_parameters );

                    if ( count($tc) == 0  ){

                        $new_ticket = new stdClass(); 
                        $new_ticket->tkt_subject         = $msg['headers']->subject;
                        $new_ticket->tkt_message_excerpt = "New Ticket.";
                        $new_ticket->tkt_message         =  $msg['text'];;
                        $new_ticket->tkt_channel         = "EMAIL";
                        $new_ticket->tkt_status          = "OPEN";
                        $new_ticket->tkt_private_notes   = "Private notes";
                        $new_ticket->tkt_logged_by       = $user_id;
                        $new_ticket->tkt_updated_by      = $user_id;

                        $id = $TC->logTicket( $new_ticket );

                        if( $id > 0){
                                echo _e( "New ticket id: $id\n") ;
                                echo _e( "Subject: " . $subject . "\n" ) ;
                                echo _e( "Added by: " . $users[0]->user_nicename . "\n" ) ;
                                echo _e( "Date:" . date() . "\n" ) ;
                                echo _e( "----------------------------------------------\n") ;		
                        }

                        $new_ticket = null;

                    }else{//Reply

                    } 


            }


            $m_box->disconnect();

        }//eof:
        
        
}
endif;

return new KSD_Mail_Admin();

