<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
 
include_once( KSD_PLUGIN_DIR. "includes/libraries/Model.php");

 class Kanzu_Assignments_Model extends Kanzu_Model{

	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_assignments";	
		$this->_id = "assign_id";
			
		$this->_formats = array(
		'assign_tkt_id'             => '%d', 
		'assign_assigned_to'        => '%d',
		'assign_date_assigned'      => '%s' , 
		'assign_assigned_by'        => '%d'
	);
	}
	
	/*
	*Get user object
	*
	*@param userid
	*/
	public function get_assignment( $id ){
		return parent::get_row($id);
	}
	
	/*
	*Get all from users (kanzu-users) from wp users table
	*
	*@param $filter SQL filter. Everything after the WHERE key word
	*/
	public  function get_all( $filter = "" ){
		return parent::get_all($filter = "");
	}
 
	/*
	*Add user to 
	*
	*
	*/
	public function add_assignment( &$obj ){
		return parent::add_row( $obj );
	}
	
	/*
	*Add user to 
	*
	*@param Ticket object.
	*/
	public function delete_assignment(  &$obj ){
		return parent::delete_row( $obj );
	}
	

	/*
	* Save/update 
	*@param ticket object
	* *new_* for new value
	*/
	public function update_assignment( &$obj ){
		return parent::update_row( $obj );
	}
 }
 
 
 ?>
