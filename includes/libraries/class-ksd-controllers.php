<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @file      class-ksd-controller.php
 */

$plugindir = plugin_dir_path( __FILE__ );

class KSD_Controller 
{
	protected $_model = null;
	protected $_model_name = null;
	
	/*
	* Load model class if provided
	*/
	public function __construct(){
		if( $this->_model_name != ""){			
			include_once( KSD_PLUGIN_DIR. "includes/models/class-ksd-" . strtolower( $this->_model_name ) . "-model.php" );
			$classname = "KSD_".$this->_model_name . "_Model";
			$this->_model = new $classname();
		}
	}
}


class KSD_Users_Controller extends KSD_Controller 
{	
	public function __construct(){
		$this->_model_name = "Users";
		parent::__construct();
	}
	
	/*
	*Returns client object with specified id.
	*
	*@param  $client_id	ticket id
	*@return client Object
	*/
	public function get_user( $user_id = null){
		return $this->_model->get_user( $user_id );
	}
	
	/*
	*Returns all clients that through query
	*@param string $filter Everything after the WHERE clause. Uses placeholders %s and %d
        *@param Array $value_parameters The values to replace the placeholders
	*@return Array Array of objects
	*/
	public function get_users( $filter, $value_parameters ){
		return $this->_model->get_all( $filter,$value_parameters );
	}
	
	/*
	*Update user details
	*/
	public function update_user ( &$user ) {
		return $this->_model->update_user( $user );
	}
        
        /**
         * Get users with the specified roles
         * @param string $roles |-separated list of role names. e.g. administrator|author|editor
         * @return object
         */
        public function get_users_with_roles( $roles ){
           return $this->_model->get_users_with_roles( $roles ); 
        }
}

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
	* Update a ticket
	*
	*@param Object $ticket the Updated ticket
	*
	*/
	public function update_ticket( $ticket ){
		return $this->_model->update_ticket( $ticket );
	}
        
        /**
         * Update multiple tickets at a go
         * @param Array $tkt_IDs The ticket IDs
         * @param Array $update The fields and corresponding new values. Array keys are the fields
         * @return Object The updated tickets
         */
        public function bulk_update_ticket( $tkt_IDs, $update ){
                return $this->_model->bulk_update_tickets( $tkt_IDs, $update );
        }
        
        /**
         * Delete tickets in bulk
         * @param Array $tkt_IDs Array of ticket IDs to delete
         * @return Array  
         */
        public function bulk_delete_tickets($tkt_IDs) {
        return $this->_model->bulk_delete_tickets( $tkt_IDs );
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
	* Returns all tickets that through query
	*
        * @param String $query The Query to run on the table(s). Uses placeholders %s and %d
        * @param Array $value_parameters The values to replace the placeholders in $query
	* @return Array Array of objects
	*/
	public function get_tickets( $query = null, $value_parameters=array() ){                   
               return $this->_model->get_all( $query, $value_parameters );               		
	}
	
	public function get_tickets_n_reply_cnt($filter, $value_parameters){
		return $this->_model->get_all_and_reply_cnt( $filter , $value_parameters);
	}
        
	/**
	 * Delete the ticket with the specified ID
	 * @param int $ticket_id Ticket ID
	 */
	 public function delete_ticket( $ticket_id ){
		$where = array ('tkt_id'=>$ticket_id);
		return $this->_model->delete_ticket( $where );
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
         * Get ticket count by status
         * @return Array of Objects
         */
        public function get_ticket_count_by_status() {
            return $this->_model->get_ticket_count_by_status();
        }
    /**
         * Run a custom query
         * @param type $query The query to run
         */
        public function exec_query($query){
            return $this->_model->exec_query( $query);
        }
        
       /**
        * Before imposing a LIMIT clause to a query to get the tickets needed in the tickets view,
        * we run that query against the Db and count the number of rows. This is essential for
        * pagination of the returned tickets
        * @param String $filter The Query to run on the table. Uses placeholders %s and %d
        * @param Array  $value_parameters The values to replace the placeholders in $filter
        * @return type
        */
        public function get_pre_limit_count( $filter, $value_parameters ){
           return  $this->_model->get_pre_limit_count( $filter,$value_parameters );
        }
        
        /**
         * Returns the total number of tickets in each ticket filter category.
         * 
         * @param int $user_id
         * @param int $recency
         * @return 
         */
        public function get_filter_totals( $user_id, $recency){
             return  $this->_model->get_filter_totals( $user_id,$recency );
        }
        
        /**
         * Sets the table name to be used by the model class
         * 
         * @param type $tablename
         */
        public function set_tablename( $tablename ){
            $this->_model->set_tablename( $tablename );
        }
}