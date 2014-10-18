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
//require(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php');
require(dirname(__FILE__) . '/admin/libs/Mail.php');


$kanzuserver_url    = 'mail.kanzucode.com'; //get_option('kanzu_supportemail')
$kanzueserver_login = 'support@kanzucode.com'; //get_option('mailserver_login')
$kanzuserver_pass   =  'b0GKn(Z7_LUi';
$kanzu_validate_certificate = 'no';//get_option('kanzu_validateSSL')

$MBox = new Kanzu_Mail();

echo "1\n";

if( !$MBox->connect('pop3', $kanzuserver_url, $kanzueserver_login, $kanzuserver_pass,$kanzu_validate_certificate) ){
	echo "Can not connect to mailbox.";
	exit;
}

$count = $MBox->numMsgs();

echo "Number of Unread msgs:". $count . "\n";

$msg=array();
$msg = $MBox->getMessage(1);

echo "HTML=====================================\n";
echo $msg['html'];
echo "TEXT=====================================\n";
echo $msg['text'];
echo "ATTACHMENTS=====================================\n";
echo print_r($msg['attachments']);

$MBox->disconnect();

?>
