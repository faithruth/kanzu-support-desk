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
include_once( $plugindir. $DS . "admin" . $DS."libs".$DS."Controller.php");

class TicketsController extends Kanzu_Controller {	
	public function __construct(){
		$this->_model_name = "Tickets";
		parent::__construct();
	}
	
	/*
	*Logs new ticket
	*
	*@param $ticket ticket object to log
	*/
	public function logTicket(&$ticket){
		return $this->_model->addTicket( $ticket);
	}
	
	/*
	*Close ticket
	*
	*@param int $ticket_id ticket id of ticket to close
	*
	*/
	public function closeTicket($ticket_id ){
		$tO = new stdClass();
		$tO->tkt_id = $ticket_id;
		$tO->new_tkt_status = "CLOSE";
		$id = $this->_model->updateTicket( $tO );
	}
	
	/*
	* Change ticket status
	*
	*@param int $ticket_id ticket id of ticket to close
	*
	*/
	public function changeTicketStatus($ticket_id,$new_status ){
		$tO = new stdClass();
		$tO->tkt_id = $ticket_id;
		$tO->new_tkt_status = $new_status;
		$id = $this->_model->updateTicket( $tO );
	}

	/*
	*Returns ticket object with specified id.
	*
	*@param  int $ticket_id	ticket id
	*@return ticket Object
	*/
	public function getTicket($ticket_id){
		return $this->_model->getTicket( $ticket_id);
	}
	
	/*
	*Returns all tickets that through query
	*
	*@return Array Array of objects
	*/
	public function getTickets( $query = null){
		return $this->_model->getAll( $query);
	}
	
	/**
	 * Delete the ticket with the specified ID
	 * @param int $ticket_id Ticket ID
	 */
	 public function deleteTicket($ticket_id){
		$where = array ('tkt_id'=>$ticket_id);
		return $this->_model->deleteTicket( $where);
	}
}
?>