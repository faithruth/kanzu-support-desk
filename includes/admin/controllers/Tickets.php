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

class TicketController extends Kanzu_Controller 
{
	
	public function __construct(){
		parent::_model_name = "Ticket";
		parent::__construct();
	}
	
	public function logTicket(&$ticket){
	
	}
	
	public function replyTicket(int $ticket_id ){
	
	}
	
	public function closeTicket(int $ticket_id ){
	
	}

	public function getTicket(&$ticket){
	
	}
}
?>