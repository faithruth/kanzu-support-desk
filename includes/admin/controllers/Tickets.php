<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @file	  Controller.php
 */

$plugindir = plugin_dir_path( __FILE__ );

$DS=DIRECTORY_SEPARATOR;
$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
include( $plugindir. $DS . "admin" . $DS."libs".$DS."Controller.php");

class TicketsController extends Kanzu_Controller 
{	
	public function __construct(){
		$this->_model_name = "Tickets";
		parent::__construct();
	}
	
	public function logTicket(&$ticket){
		$id = $this->_model->addTicket( $ticket);
	}
	
	public function replyTicket(int $ticket_id ){
	
	}
	
	public function closeTicket(int $ticket_id ){
	
	}

	public function getTicket(&$ticket){
	
	}
}
?>