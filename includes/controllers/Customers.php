<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @file	  Controller.php
 */

include_once( KSD_PLUGIN_DIR. "includes/libraries/Controller.php");

class Kanzu_Customers_Controller extends Kanzu_Controller 
{	
	public function __construct(){
		$this->_model_name = "Customers";
		parent::__construct();
	}
        
       /*
	* Add a new customer to the Db
	*
	*@param $reply reply object to log
	*/
	public function addCustomer( &$customer ){
		return $this->_model->addCustomer( $customer );
	}
	
	/*
	*Returns customer object with specified id.
	*
	*@param  $customer_id	ticket id
	*@return customer Object
	*/
	public function getCustomer( $customer_id ){
		return $this->_model->getCustomer( $customer_id);
	}
	
        /**
         * Get customer by their email address
         * @param string $email_address The customer's email address
         */
        public function get_customer_by_email( $email_address ){            
            return $this->_model->get_customer_by_email( $email_address );
        }
        
        /**
         * Get customer email address by Ticket ID
         */
        public function get_customer_by_ticketID( $tkt_id ){
            return $this->_model->get_customer_by_ticketID( $tkt_id );
        }
        
	/*
	*Returns all customers that through query
	*
	*@return Array Array of objects
	*/
	public function getCustomers( $filter ){
		return $this->_model->getAll( $filter);
	}
	
	/*
	* Disable customer account
	*
	* @param int $customer_id 
	*/
	public function disableAccount( $customer_id ){
		$cO = new stdClass();
		$cO->cust_id = $customer_id;
		$cO->new_account_status = "DISABLED";
		$this->_model->updateCustomer( $cO );
	}
	
	/*
	* Enable customer account
	*/
	public function enableAccount( $customer_id ){
		$cO = new stdClass();
		$cO->cust_id = $customer_id;
		$cO->new_account_status = "ENABLED";
		$this->_model->updateCustomer( $cO );
	}
	
	public function deleteCustomer( $customer_id ){
		
		//Delete from customer table
		//Delete from wp usertable
		//Delete tickets
		//delete replies
	}
}
?>