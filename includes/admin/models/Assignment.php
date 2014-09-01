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
		$this->_tablename = $wpdb->prefix . "kanzusupport_assignment";	
		$this->_id = "assign_id";
			
		$this->_formats = array(
		'assign_tkt_id' 		 => '%d', 
		'assign_assigned_to'	 => '%d',
		'assign_date_assigned' 	 => '%s' , 
		'assign_assigned_by' 	 => '%d'
	);
	}
	
	/*
	*Get user object
	*
	*@param userid
	*/
	public function getAssignment( $id ){
		return parent::getRow($id);
	}
	
	/*
	*Get all from users (kanzu-users) from wp users table
	*
	*@param $filter SQL filter. Everything after the WHERE key word
	*/
	public  function getAll( $filter = "" ){
		return parent::getAll($filter = "");
	}
 
	/*
	*Add user to 
	*
	*
	*/
	public function addAssignment( &$obj ){
		return parent::addRow( $obj );
	}
	
	/*
	*Add user to 
	*
	*@param Ticket object.
	*/
	public function deleteAssignment(  &$obj ){
		return parent::deleteRow( $obj );
	}
	

	/*
	* Save/update 
	*@param ticket object
	* *new_* for new value
	*/
	public function updateAssignment( &$obj ){
		return parent::updateRow( $obj );
	}
 }
 
 
 ?>
