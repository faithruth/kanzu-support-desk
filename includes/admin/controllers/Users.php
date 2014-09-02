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

$plugindir = plugin_dir_path( __FILE__ );

$DS=DIRECTORY_SEPARATOR;
$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
include( $plugindir. $DS . "admin" . $DS."libs".$DS."Controller.php");

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
	public function getUser(int $user_id){
		return $this->_model->getClient( $user_id)
	}
	
	/*
	*Returns all clients that through query
	*
	*@return Array Array of objects
	*/
	public function getUsers( $query ){
		return $this->_model->getAll( $query);
	}
}
?>