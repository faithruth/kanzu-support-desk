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

class ClientsController extends Kanzu_Controller 
{	
	public function __construct(){
		$this->_model_name = "Clients";
		parent::__construct();
	}
	
	/*
	*Returns client object with specified id.
	*
	*@param  $client_id	ticket id
	*@return client Object
	*/
	public function getClient(int $client_id){
		return $this->_model->getClient( $client_id);
	}
	
	/*
	*Returns all clients that through query
	*
	*@return Array Array of objects
	*/
	public function getClients( $filter ){
		return $this->_model->getAll( $filter);
	}
	
	/*
	* Disable client account
	*
	* @param int $client_id 
	*/
	public function disableAccount( int $client_id){
		$cO = new stdClass();
		$cO->cust_id = $client_id;
		$cO->new_account_status = "DISABLED";
		$this->_model->updateClient( $cO );
	}
	
	/*
	* Enable client account
	*/
	public function enableAccount( int $client_id){
		$cO = new stdClass();
		$cO->cust_id = $client_id;
		$cO->new_account_status = "ENABLED";
		$this->_model->updateClient( $cO );
	}
	
	public function deleteClient( $client_id ){
		
		//Delete from customer table
		//Delete from wp usertable
		//Delete tickets
		//delete replies
	}
}
?>