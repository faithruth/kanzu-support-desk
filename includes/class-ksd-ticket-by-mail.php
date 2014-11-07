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

/** Make sure that the WordPress bootstrap has run before continuing. */
require( ABSPATH  . '/wp-load.php' );

require( KSD_PLUGIN_DIR .  'includes/libraries/Mail.php' );
require( KSD_PLUGIN_DIR .  'includes/libraries/Model.php' );
require( KSD_PLUGIN_DIR .  'includes/controllers/Tickets.php' );
require( KSD_PLUGIN_DIR .  'includes/controllers/Users.php' );


$kanzuserver_url    		= get_option('mail_server');
$kanzueserver_login 		= get_option('mail_account');
$kanzuserver_pass   		= get_option('mail_password');
$kanzu_mailbox   		= get_option('mail_mailbox');
$kanzu_serverport		= get_option('mail_port');
$kanzu_useSSL   		= get_option('mail_useSSL');
$kanzu_validate_certificate 	= get_option('mail_validate_certificate'); //If use_SSL is true
$kanzu_mail_protocol 		= get_option('mail_protocol'); // Can be imap or pop3
$protocol			= ( $kanzu_useSSL ? $kanzu_mail_protocol.'/ssl' : $kanzu_mail_protocol);

$MBox = new Kanzu_Mail();

if( !$MBox->connect( $protocol, $kanzuserver_url, $kanzueserver_login, $kanzu_serverport, 
		    $kanzuserver_pass, $kanzu_serverport, $kanzu_mailbox,$kanzu_validate_certificate ) ) {

	_e( "Can not connect to mailbox.", "kanzu-support-desk" );
	exit;
}

$count = $MBox->numMsgs();

for( $i=1; $i <= $count; $i++)
{

	$msg=array();
	$msg = $MBox->getMessage($i);

	$mail_mailbox = $msg['headers']->from[0]->mailbox;
	$mail_host    = $msg['headers']->from[0]->host;
	$email        = $mail_mailbox . "@" . $mail_host;
	$subject      = $msg['headers']->subject;

	//Create new ticket.
	$tO = new stdClass(); 
	$tO->tkt_subject	 = $msg['headers']->subject;
	$tO->tkt_message_excerpt = "New Ticket.";
	$tO->tkt_message 	 =  $msg['text'];;
	$tO->tkt_channel     	 = "EMAIL";
	$tO->tkt_status 	 = "OPEN";
	$tO->tkt_private_notes 	 = "Private notes";
	$tO->tkt_tags 	 	 = "tag";
	$tO->tkt_customer_rating = "1";

	//Get userid
	$userObj = new UsersController();
	$users = $userObj->getUsers("user_email = '$email'");
	$user_id = $users[0]->ID;

	$tO->tkt_logged_by  = $user_id;
	$tO->tkt_updated_by = $user_id;

	$TC = new TicketsController();
	$id = $TC->logTicket( $tO );

	if( $id > 0){
		echo "New ticket id: $id\n";
		echo "Subject: " . $subject . "\n";
		echo "Added by: " . $users[0]->user_nicename . "\n";
		echo "Date:" . date() . "\n";
		echo "----------------------------------------------\n";		
	}
	
	$tO = null;
	$TC = null;

}


$MBox->disconnect();

?>
