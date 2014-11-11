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
        
        /**
         * Find a customer by their email address
         * @param string $email_address
         * @return Customer Object
         * 
         */
        public function get_customer_by_email( $email_address ){
            $query = "SELECT * FROM ".$this->_tablename." WHERE cust_email ='".$email_address."'";
            return parent::execQuery( $query );
        }
        
        /**
         * Get customer email by ticket ID
         */
        public function get_customer_by_ticketID( $tkt_id ){
            global $wpdb;
            $query = "SELECT C.cust_email,T.tkt_subject FROM `{$wpdb->prefix}kanzusupport_tickets` AS T JOIN ".$this->_tablename." AS C ON T.tkt_cust_id = C.cust_id WHERE T.tkt_id= ".$tkt_id;
            return parent::execQuery( $query );
        }
 
        /*
	* Add a new customer to the Db. 
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