<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2015 Kanzu Code
 * @since     1.2.1
 *
 * class-ksd-log-model.php
 */
 
include_once( KSD_PLUGIN_DIR. "includes/libraries/class-ksd-model.php");

 class KSD_Log_Model extends KSD_Model{
	
	public function __construct(){
		global $wpdb;
                
		$this->_tablename = $wpdb->prefix . "kanzusupport_log";	
		$this->_id = "log_id";
			
		$this->_formats = array(
		'log_id' 			=> '%d', 
		'log_name'	 		=> '%s',
		'log_msg'	 		=> '%s' , 
		'log_type' 			=> '%s',
		'log_date' 	 		=> '%s'
		);
	}
	
	/*
	*Get log object
	*
	*@param userid
	*/
	public function get_log( $id ){
		return parent::get_row($id);
	}
	
	/*
	*Get all from the log table
	*
	*@param string $filter Everything after the WHERE clause. Uses placeholders %s and %d
        *@param Array $value_parameters The values to replace the placeholders
	*/
	public  function get_all( $filter = "",$value_parameters=array() ){
		return parent::get_all($filter,$value_parameters);
	}
 
	/**
	* Add log 
	*/
	public function add_log( &$obj ){
		return parent::add_row( $obj );
	}
	
	/*
	*
	*@param Logs object.
	*/
	public function delete_log(  &$obj ){
		return parent::delete_row( $obj );
	}

 }
 
 
 ?>