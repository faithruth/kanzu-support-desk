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

include_once( KSD_PLUGIN_DIR . '/includes/admin/libs/Controller.php' );

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
		return $this->_model->updateTicket( $tO );
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
        *@param String $query The Query to run on the table(s)
        *@param String $check_ticket_assignments Whether the ticket assignments should also be checked
	*@return Array Array of objects
	*/
	public function getTickets( $query = null, $check_ticket_assignments ){
                if ( "yes" == $check_ticket_assignments ) { 
                    //@TODO Fix assignment check. A single ticket has multiple entries so the query should take that into account
                    $query.= " ORDER BY T.tkt_time_logged DESC  ";
                    return $this->_model->get_assigned_tickets( $query );
                }
                else{
                    $query.= " ORDER BY tkt_time_logged DESC ";
                    return $this->_model->getAll( $query );
                }
		
	}
	
	/**
	 * Delete the ticket with the specified ID
	 * @param int $ticket_id Ticket ID
	 */
	 public function deleteTicket($ticket_id){
		$where = array ('tkt_id'=>$ticket_id);
		return $this->_model->deleteTicket( $where);
	}
	
	/**
	 * Get the ticket volumes for display on the dashboard
	 */
	public function get_dashboard_graph_statistics(){	
		return $this->_model->get_dashboard_graph_statistics();
	}
        
        
        public function get_dashboard_statistics_summary(){
            return $this->_model->get_dashboard_statistics_summary();
        }
        /**
         * Run a custom query
         * @param type $query The query to run
         */
        public function execQuery($query){
            return $this->_model->execQuery( $query);
        }
        

}
?>
