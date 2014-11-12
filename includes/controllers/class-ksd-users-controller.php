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

include_once( KSD_PLUGIN_DIR. "includes/libraries/class-ksd-controller.php");

class KSD_Users_Controller extends KSD_Controller 
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
	public function get_user( $user_id = null){
		return $this->_model->get_user( $user_id);
	}
	
	/*
	*Returns all clients that through query
	*
	*@return Array Array of objects
	*/
	public function get_users( $filter ){
		return $this->_model->get_all( $filter );
	}
	
	/*
	*Update user details
	*/
	public function update_user(&$user){
		return $this->_model->update_user( $user );
	}
	
	
}

?>