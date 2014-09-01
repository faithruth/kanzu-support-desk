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
	
	//private $_obj = new stdClass(); 
	
	public function __construct(){
	
	
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
		$table = "{$wpdb->prefix}kanzusupport_tickets";
		$data = array();
		$format = array();
		foreach( $ticket as $key => $value) {
			//print "$key => $value\n";
			$data[$key] = $value;
			array_push($format,$this->_formats[$key]);
		}
		global $wpdb;
		$wpdb->show_errors();
		$wpdb->insert( $table, $data, $format );
		return $wpdb->insert_id;
	}
	
	/*
	*Add user to 
	*
	*@param $id User id
	*/
	public function deleteTicket( int $id ){
	
	}
	
	
	/*
	*Save/update 
	*
	*/
	public function updateTicket( &$userObject ){
	//$wpdb->insert( $table, $data, $format );
	}
 }
 
 
 ?>
