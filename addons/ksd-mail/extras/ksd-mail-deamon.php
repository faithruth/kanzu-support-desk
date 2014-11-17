<?php
/**
 * Retrieves new mail and logs a ticket 
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

error_reporting(-1);

if( php_sapi_name() !== 'cli' ) {
     die("Must be run from commandline.");
}

function find_wordpress_base_path() {
    $dir = dirname(__FILE__);
    do {
        //it is possible to check for other files here
        if( file_exists($dir."/wp-config.php") ) {
            return $dir;
        }
    } while( $dir = realpath("$dir/..") );
    return null;
}

if ( null === ( $wp_base  = find_wordpress_base_path()."/" ) ){
    die( 'This file should be located inside a wordpress installation.' );
}

define ( 'BASE_PATH', $wp_base );
define ('WP_USE_THEMES', false);
global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
require (BASE_PATH . 'wp-load.php');

require ( KSD_PLUGIN_DIR .  'includes/controllers/class-ksd-tickets-controller.php' );
require ( KSD_PLUGIN_DIR .  'includes/controllers/class-ksd-users-controller.php' );
require ( KSD_MAIL_DIR . '/class-ksd-mail.php' ); 


//create pid file to ensure only one instance of this script runs at a time.;
$pid_file = KSD_MAIL_EXTRAS . '/pids/ksd_mail.pid';
if ( file_exists( $pid_file ) ){
    $pid_arr = file( $pid_file ); 
    die(  'File ' . $pid_file . ' already exists. The script is already running with pid '
                  . $pid_arr[0] . "\n" );
}

//Create pid file.
$pid = getmypid();
$fh = fopen( $pid_file , "w") 
      or die( "Unable to create pid file! Check permissions on " . KSD_MAIL_EXTRAS . "/pids\n" );
fwrite($fh, $pid);
fclose($fh);

//Get last run time
$run_freq = (int) get_option('ksd_mail_check_freq') ; //in minutes
$last_run = (int) get_option('ksd_mail_lastrun_time'); //saved as unix timestamp
$now = (int) date( 'U' );
$interval = $now - $last_run ;

if ( $interval  < ( $run_freq * 60 ) ){
    unlink( $pid_file);
    die( ' Run interval has not passed.' ); //@TODO: Add run log instead.
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
                    echo "New ticket id: $id\n";
                    echo "Subject: " . $subject . "\n";
                    echo "Added by: " . $users[0]->user_nicename . "\n";
                    echo "Date:" . date() . "\n";
                    echo "----------------------------------------------\n";		
            }

            $new_ticket = null;
            
        }else{//Reply
            
        } 


}


$m_box->disconnect();


//Delete pid file.
unlink( $pid_file );

?>
