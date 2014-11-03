<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @file	  Users.php
 * @version   1.0
 */

include_once( KSD_PLUGIN_DIR. "includes/libraries/Controller.php");

class UsersController extends Kanzu_Controller 
{	
	public function __construct(){
		$this->_model_name = "Users";
		parent::__construct();
	}
	
	/*
	*Returns client object with specified id.
	*
	*@param  $client_id	ticket id
	*@return client Object
	*/
	public function getUser( $user_id = null){
		return $this->_model->getUser( $user_id);
	}
	
	/*
	*Returns all clients that through query
	*
	*@return Array Array of objects
	*/
	public function getUsers( $filter ){
		return $this->_model->getAll( $filter );
	}
	
	/*
	*Update user details
	*/
	public function updateUser(&$user){
		return $this->_model->updateUser( $user );
	}
	
	
}

?>