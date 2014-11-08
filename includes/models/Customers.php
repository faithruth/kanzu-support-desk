<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 *
 * Channels.php
 */
 
 
include_once ( KSD_PLUGIN_DIR . "includes/models/Users.php" );
include_once( KSD_PLUGIN_DIR. "includes/libraries/Model.php");

 class CustomersModel extends UsersModel{
	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_customers";	
		$this->_id = "cust_id";
			
		$this->_formats = array(
		'cust_id' 		=> '%d', 
		'cust_user_id'          => '%d',
		'cust_firstname'	=> '%s',
		'cust_lastname'	 	=> '%s' , 
                'cust_email'	 	=> '%s' , 
		'cust_company_name' 	=> '%s',
		'cust_phone_number' 	=> '%s',
		'cust_about' 	 	=> '%s',
		'cust_creation_date' 	=> '%s',
		'cust_created_by' 	=> '%d'
		);
	}
        
 
	
	/*
	*Get user object
	*
	*@param customerid
	*/
	public function getCustomer( $id ){
		return parent::getRow($id); ;
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
	*
	*/
	public function addCustomer( &$obj ){
		return parent::addRow( $obj );
	}
	
	/*
	*
	*@param client object.
	*/
	public function deleteCustomer(  &$obj ){
		return parent::deleteRow( $obj );
	}
	

	/*
	* Save/update 
	*@param client object
	* *new_* for new value
	*/
	public function updateCustomer( &$obj ){
		return parent::updateRow( $obj );
	}
 }
 
 
 ?>