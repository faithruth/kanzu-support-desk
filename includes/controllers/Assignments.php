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

 
include_once( KSD_PLUGIN_DIR. "includes/libraries/Controller.php");

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
	public function assignTicket( $ticket_id, $assign_to,  $assign_by, $notes="" ){
		$aO                = $this->_model->getObj();
		$aO->assign_assigned_to     = $assign_to;
		$aO->assign_assigned_by 	   = $assign_by;
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
		$aO                = $this->_model->getObj();
		$aO->assign_tkt_id = $ticket_id;
		$this->_model->deleteAssignment( $aO );
	}

	/*
	* Assign ticket to different agent.  
        * NB: I DON'T THINK WE NEED THIS FUNCTION. ALL ASSIGNMENTS
        * ARE ADDED AS NEW ASSIGNMENTS FOR US TO BE ABLE TO TRACK RE-ASSIGNMENTS
	*
	* @param int 	$ticket_id Ticket ID of ticket to reassign
	* @param int 	$agent_id ID of agent to reassign ticket to 
	* @param int 	$assign_by ID of admin who reassigns 
	* @param string	$notes New notes if provided, else old notes will be maintained.
	*/
	public function reassignTicket($ticket_id, $agent_id, $assign_by, $notes = "" ){
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