<?php
/**
 * Holds all installation & deactivation-related functionality.  
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
 global $wpdb;
 
 class TicketsModel{
	private $_tablename = "";
	private $_id = "tkt_id";
	private $_formats = array(
		'tkt_title' => '%s', 
		'tkt_initial_message'=> '%s',
		'tkt_description' 	 => '%s' , 
		'tkt_channel' 		 => '%s',
		'tkt_status' 		 => '%s',
		'tkt_logged_by' 	 => '%s',  
		'tkt_severity' 		 => '%s', 
		'tkt_resolution' 	 => '%s', 
		'tkt_time_logged' 	 => '%s', 
		'tkt_time_updated' 	 => '%s', 
		'tkt_private_notes'  => '%s',
		'tkt_tags' 			 => '%s',
		'tkt_customer_rating' => '%d'
	);
	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_tickets";	
	}
	
	/*
	*Get user object
	*
	*@param userid
	*/
	public function getTicket( $id ){
		$results = $wpdb->get_results( 'SELECT * FROM '. $this->_tablename .' WHERE '. $this->_id .' = ' . $id, OBJECT );
		return $results;
	}
	
	/*
	*Get all from users (kanzu-users) from wp users table
	*
	*@param $filter SQL filter 
	*/
	public  function getAll( $filter = "" ){
	
	}
 
	/*
	*Add user to 
	*
	*
	*/
	public function addTicket( &$ticket ){
		global $wpdb;
		
		$data = array();
		$format = array();
		foreach( $ticket as $key => $value) {
			$data[$key] = $value;
			array_push($format,$this->_formats[$key]);
		}
		$wpdb->show_errors();
		$wpdb->insert( $this->_tablename, $data, $format );
		return $wpdb->insert_id;
	}
	
	/*
	*Add user to 
	*
	*@param $id User id
	*/
	public function deleteTicket(  &$ticket ){
		global $wpdb;
		$table = $this->_tablename;
		$where = array();
		$where_format = array();
		foreach( $ticket as $key => $value) {
			$where[$key] = $value;
			array_push($where_format,$this->_formats[$key]);
		}
		
		$wpdb->show_errors();
		$wpdb->delete( $table, $where, $where_format = null );
		
		return True;
		 
	}
	
	
	/*
	*Save/update 
	*
	* *new_* for new value
	*/
	public function updateTicket( &$ticket ){
		global $wpdb;
		$table = $this->_tablename;
		$data = array();
		$where = array();
		$format = array();
		$where_format = array();
		foreach( $ticket as $key => $value) {
			array_push($format,$this->_formats[$key]);
			$pfx = substr($key,0,4); #new_
			if( $pfx == "new_"){
				$data[ substr($key,4)] = $value;
			}else{
				$where[$key] = $value;
				array_push($where_format,$this->_formats[$key]);
			}
		}
		$wpdb->show_errors();
		$wpdb->update( $table, $data, $where, $format, $where_format); 
		return True;
	}
 }
 
 
 ?>
