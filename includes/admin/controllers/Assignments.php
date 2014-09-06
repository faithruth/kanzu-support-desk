<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @file	  Assignments.php
 */

$plugindir = plugin_dir_path( __FILE__ );

$DS=DIRECTORY_SEPARATOR;
$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
include_once( $plugindir. $DS . "admin" . $DS."libs".$DS."Controller.php");

class AssignmentsController extends Kanzu_Controller 
{	
	public function __construct(){
		$this->_model_name = "Assignments";
		parent::__construct();
	}
	
	/*
	* Assign ticket
	*
	* @param $ticket_id Ticket ID
	* @param $assign_to ID of agent to assign ticket to
	* @param $assign_by ID of admin who assigned ticket
	* @param $notes 	Notes on ticket assignment
	*/
	public function assignTicket( int $ticket_id, int $assign_to,  int $assign_by, string $notes ){
		$aO                = $this->_model->getObj();
		$aO->assign_to     = $assign_to;
		$aO->assign_by 	   = $assign_by;
		$aO->assign_notes  = $notes;
		$aO->assign_tkt_id = $ticket_id;
		return $this->_model->addAssignment( $aO );
	}
	
	
	/*
	* Unassign ticket
	*
	* @param $ticket_id Ticket ID of ticket to unassign 
	*
	*/
	public function unassignTicket( int $ticket_id ){
		//TODO: Check if ticket is already assigned.
		$aO                = $this->_model->getObj();
		$aO->assign_tkt_id = $ticket_id;
		$this->_model->deleteAssignment( $aO );
	}

	/*
	* Assign ticket to different agent
	*
	* @param int 	$ticket_id Ticket ID of ticket to reassign
	* @param int 	$agent_id ID of agent to reassign ticket to 
	* @param int 	$assign_by ID of admin who reassigns 
	* @param string	$notes New notes if provided, else old notes will be maintained.
	*/
	public function reassignTicket(int $ticket_id, int $agent_id, int $assign_by, $notes = "" ){
		$aO                = $this->_model->getObj();
		$aO->assign_tkt_id = $ticket_id;
		$aO->new_assign_to = $agent_id;
		$aO->new_assign_by = $assign_by;
		if ( $notes != "" ){
			$aO->new_notes = $notes;
		}
		$this->_model->updateReply( $aO );
	}
}
?>