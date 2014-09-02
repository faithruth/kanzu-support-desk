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
	*@param $ticket_id ticket id of ticket to close
	*
	*/
	public function closeTicket(int $ticket_id ){
		$tO = new stdClass();
		$tO->tkt_id = $ticket_id;
		$tO->new_tkt_status = "CLOSE";
		$id = $this->_model->updateTicket( $tO );
	}

	/*
	*Returns ticket object with specified id.
	*
	*@param  $ticket_id	ticket id
	*@return ticket Object
	*/
	public function getTicket(int $ticket_id){
		return $this->_model->getTicket( $ticket_id);
	}
	
	/*
	*Returns all tickets that through query
	*
	*@return Array Array of objects
	*/
	public function getTickets( $query ){
		return $this->_model->getAll( $query);
	}
}
?>