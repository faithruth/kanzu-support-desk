<?php
/**
 * Holds all installation & deactivation-related functionality.  
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

error_reporting(-1);

/** Make sure that the WordPress bootstrap has run before continuing. */
require(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php');
require(dirname(__FILE__) . '/admin/libs/Mail.php');
require(dirname(__FILE__) . '/admin/controllers/Tickets.php');
require(dirname(__FILE__) . '/admin/libs/Model.php');
require(dirname(__FILE__) . '/admin/controllers/Users.php');

$kanzuserver_protocol = "pop3/ssl";
$kanzuserver_url      = 'pop.gmail.com'; //get_option('kanzu_supportemail')
$kanzueserver_login   = 'ssegga@gmail.com'; //get_option('mailserver_login')
$kanzuserver_pass     =  '';

$MBox = new Kanzu_Mail();

if( !$MBox->connect($kanzuserver_protocol, $kanzuserver_url, $kanzueserver_login, $kanzuserver_pass) ){
	echo "Can not connect to mailbox.";
	exit;
}

$count = $MBox->numMsgs();

for( $i=1; $i <= $count; $++)
{

	$msg=array();
	$msg = $MBox->getMessage($i);

	$mail_mailbox = $msg['headers']->from[0]->mailbox;
	$mail_host    = $msg['headers']->from[0]->host;
	$email        = $mail_mailbox . "@" . $mail_host;
	$subject      = $msg['headers']->subject;

	//Create new ticket.
	$tO = new stdClass(); 
	$tO->tkt_title 		 = $msg['headers']->subject;
	$tO->tkt_initial_message = $msg['text'];
	$tO->tkt_description 	 =  "NEW TICKET";
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

	if( $id > 0)
	{
		echo "New ticket id: $id\n";
		echo "Title: " . $subject . "\n";
		echo "Added by: " . $users[0]->user_nicename . "\n";
		echo "Date:" . date() . "\n";
		echo "----------------------------------------------\n";
		
	}
	
	$tO = null;
	$TC = null;

}


$MBox->disconnect();

?>
