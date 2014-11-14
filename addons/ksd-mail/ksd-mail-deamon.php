<?php
/**
 * Script to be scheduled for email ticket logging.
 *
 * @package   KSD_Mail
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

/** Make sure that the WordPress bootstrap has run before continuing. */
require( ABSPATH  . '/wp-load.php' );

require( KSD_PLUGIN_DIR .  'includes/libraries/Mail.php' );
require( KSD_PLUGIN_DIR .  'includes/libraries/Model.php' );
require( KSD_PLUGIN_DIR .  'includes/controllers/Tickets.php' );
require( KSD_PLUGIN_DIR .  'includes/controllers/Users.php' );
 
$MBox = new Kanzu_Mail();

if( !$MBox->connect() ) {

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
        $new_ticket = new stdClass(); 
        $new_ticket->tkt_subject         = $msg['headers']->subject;
        $new_ticket->tkt_message_excerpt = "New Ticket.";
        $new_ticket->tkt_message         =  $msg['text'];;
        $new_ticket->tkt_channel         = "EMAIL";
        $new_ticket->tkt_status          = "OPEN";
        $new_ticket->tkt_private_notes   = "Private notes";
        $new_ticket->tkt_tags            = "tag";
        $new_ticket->tkt_customer_rating = "1";

        //Get userid
        $userObj = new UsersController();
        $users = $userObj->getUsers("user_email = '$email'");
        $user_id = $users[0]->ID;

        $new_ticket->tkt_logged_by  = $user_id;
        $new_ticket->tkt_updated_by = $user_id;

        $TC = new TicketsController();
        $id = $TC->logTicket( $new_ticket );

        if( $id > 0){
                echo "New ticket id: $id\n";
                echo "Subject: " . $subject . "\n";
                echo "Added by: " . $users[0]->user_nicename . "\n";
                echo "Date:" . date() . "\n";
                echo "----------------------------------------------\n";                
        }
        
        $new_ticket = null;
        $TC = null;

}


$MBox->disconnect();
?>