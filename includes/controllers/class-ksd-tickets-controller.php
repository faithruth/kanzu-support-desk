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

include_once( KSD_PLUGIN_DIR .  'includes/libraries/class-ksd-controller.php' );

class KSD_Tickets_Controller extends KSD_Controller {	
	public function __construct(){
		$this->_model_name = "Tickets";
		parent::__construct();
	}
	
	/*
	*Logs new ticket
	*
	*@param $ticket ticket object to log
	*/
	public function log_ticket(&$ticket){
		return $this->_model->add_ticket( $ticket);
	}
	
	/*
	*Close ticket
	*
	*@param int $ticket_id ticket id of ticket to close
	*
	*/
	public function close_ticket($ticket_id ){
		$tO = new stdClass();
		$tO->tkt_id = $ticket_id;
		$tO->new_tkt_status = "CLOSE";
		$id = $this->_model->update_ticket( $tO );
	}
	
	/*
	* Change ticket status
	* @TODO update_ticket should handle this
	*@param int $ticket_id ticket id of ticket to close
	*
	*/
	public function change_ticket_status($ticket_id,$new_status ){
		$tO = new stdClass();
		$tO->tkt_id = $ticket_id;
		$tO->new_tkt_status = $new_status;
		return $this->_model->update_ticket( $tO );
	}
        
      	/*
	* Update a ticket
	*
	*@param Object $ticket the Updated ticket
	*
	*/
	public function update_ticket( $ticket ){
		return $this->_model->update_ticket( $ticket );
	}

	/*
	*Returns ticket object with specified id.
	*
	*@param  int $ticket_id	ticket id
	*@return ticket Object
	*/
	public function get_ticket($ticket_id){
		return $this->_model->get_ticket( $ticket_id);
	}
        
	
	/*
	*Returns all tickets that through query
	*
        *@param String $query The Query to run on the table(s)
	*@return Array Array of objects
	*/
	public function get_tickets( $query = null ){                   
               return $this->_model->get_all( $query );               		
	}
	
	/**
	 * Delete the ticket with the specified ID
	 * @param int $ticket_id Ticket ID
	 */
	 public function delete_ticket( $ticket_id ){
		$where = array ('tkt_id'=>$ticket_id);
		return $this->_model->delete_ticket( $where);
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
        public function exec_query($query){
            return $this->_model->exec_query( $query);
        }
        
        public function get_count( $filter = "" ){
           return  $this->_model->get_count( $filter );
        }

}
?>
