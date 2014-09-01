<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
$plugindir = plugin_dir_path( __FILE__ );

$DS=DIRECTORY_SEPARATOR;
$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
include( $plugindir. $DS . "admin" . $DS."libs".$DS."Model.php");


 class AssignmentModel extends Kanzu_Model{

	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_assignments";	
		$this->_id = "tkt_id";
			
		$this->_formats = array(
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
	}
	
	/*
	*Get user object
	*
	*@param userid
	*/
	public function getTicket( $id ){
		return parent::getRow($id);
	}
	
	/*
	*Get all from users (kanzu-users) from wp users table
	*
	*@param $filter SQL filter. Everything after the WHERE key word
	*/
	public  function getAll( $filter = "" ){
		return parent::getRow($filter = "");
	}
 
	/*
	*Add user to 
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
		return parent::updateTicket( $ticket );
	}
 }
 
 
 ?>
