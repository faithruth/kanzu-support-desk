<?php
/**
 * The tickets model
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
include_once( KSD_PLUGIN_DIR . '/includes/admin/libs/Model.php' );

 class TicketsModel extends Kanzu_Model{

	//@TODO Change tkt_description to tkt_message
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_tickets";	
		$this->_id = "tkt_id";
			
		$this->_formats = array(
		'tkt_id' 		 => '%d', 
		'tkt_subject' 		 => '%s', 
		'tkt_initial_message'	 => '%s',
		'tkt_description' 	 => '%s' , 
		'tkt_channel' 		 => '%s',
		'tkt_status' 		 => '%s',
		'tkt_logged_by' 	 => '%s',  
		'tkt_severity' 		 => '%s', 
		'tkt_resolution' 	 => '%s', 
		'tkt_time_logged' 	 => '%s', 
		'tkt_time_updated' 	 => '%s', 
		'tkt_private_notes'  	 => '%s',
		'tkt_tags' 		 => '%s',
		'tkt_customer_rating'    => '%d'
	);
	}
	
	/*
	*Get Tickets object
	*
	*@param Ticket ID
	*/
	public function getTicket( $id ){
		return parent::getRow($id);
	}
	
	/*
	*Get all from Tickets table
	*
	*@param $filter SQL filter. Everything after the WHERE key word
	*/
	public  function getAll( $filter = "" ){
		return parent::getAll($filter);
	}
 
	/*
	*Add Ticket to 
	*
	*
	*/
	public function addTicket( &$ticket ){
		return parent::addRow( $ticket );
	}
	
	/*
	*Add user to 
	*
	*@param Ticket object.
	*/
	public function deleteTicket(  &$ticket ){
		return parent::deleteRow( $ticket );
	}
	

	/*
	* Save/update 
	*@param ticket object
	* *new_* for new value
	*/
	public function updateTicket( &$ticket ){
		return parent::updateRow( $ticket );
	}
        
        
        public function execQuery( $query ){
		return parent::execQuery( $query );
	}
        
        public function get_dashboard_graph_statistics(){
            $query = 'SELECT COUNT(tkt_id) AS "ticket_volume",DATE(tkt_time_logged) AS "date_logged" FROM '.$this->_tablename.' GROUP BY date_logged;';
            return parent::execQuery( $query );
        }
        
        /**
         * Retrieve the summary statistics that show on the dashboard
         */
        //@TODO Optimize the retrieval of average response time
        public function get_dashboard_statistics_summary(){
            $summary_statistics = array();
             $response_time_query='SELECT TIMESTAMPDIFF(
                        SECOND , TICKETS.tkt_time_logged, REPLIES.rep_date_created ) AS time_difference
                        FROM wp_kanzusupport_tickets AS TICKETS
                        INNER JOIN `wp_kanzusupport_replies` AS REPLIES ON TICKETS.tkt_id = REPLIES.rep_tkt_id
                        WHERE TICKETS.tkt_status = "OPEN"
                        GROUP BY replies.rep_tkt_id';
             $summary_statistics["response_times"] = parent::execQuery( $response_time_query );
             
             $open_tickets_query = 'SELECT COUNT(tkt_id) AS open_tickets FROM '.$this->_tablename;
             $summary_statistics["open_tickets"] = parent::execQuery( $open_tickets_query );
             //@TODO Optimize this query
             $unassigned_tickets_query = 'SELECT COUNT(*) AS unassigned_tickets FROM ( SELECT T.tkt_id AS unassigned_ticketids
                        FROM `wp_kanzusupport_tickets` AS T
                        LEFT JOIN `wp_kanzusupport_assignment` AS A ON A.assign_tkt_id = T.tkt_id
                        WHERE A.`assign_assigned_to` = 0 GROUP BY A.`assign_assigned_to`) as temp';
             $summary_statistics["unassigned_tickets"]  = parent::execQuery( $unassigned_tickets_query );
              
             return $summary_statistics;
         }
         
         /**
          * Get all assigned tickets
          * @param String $filter Filter the assigned tickets.
          * @TODO Replace table names with variables
          */
         public function get_assigned_tickets( $filter = null ){
             $where = ( is_null( $filter ) ? " IS NULL " : $filter );
             $assigned_tickets_query = 'SELECT * 
                        FROM '.$this->_tablename.' AS T
                        INNER JOIN `wp_kanzusupport_assignment` AS A ON A.assign_tkt_id = T.tkt_id
                        WHERE A.`assign_assigned_to` '.$where;
             return parent::execQuery( $assigned_tickets_query );
         }
 }