<?php
/**
 * Holds all installation & deactivation-related functionality.  
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

include_once( KSD_PLUGIN_DIR. "includes/libraries/Model.php");

 class UsersModel extends Kanzu_Model{
	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "users";	
		$this->_id = "user_id";
	}	
	/*
	*Get user object (WP_User)
	*
	*@param userid
	*/
	public function getUser( $id = null){
		if ( $id == null or $id == 0 ){
			return wp_get_current_user();
		}
		return get_user_by( 'id', $id );
	}
	
	/*
	*Get all from users (kanzu-users) from wp users table
	*
	*@param $filter SQL filter 
	*/
	public  function getAll( $filter = "" ){
		return parent::getAll($filter = "");
	}
 
	/*
	*Add users
	*
	*
	*/
	public function addUser( &$userObject ){
		$userdata = (array) $userObject;
		$user_id = wp_insert_user( $userdata ) ;
		
		return ( $user_id > 0 ) ? $user_id : -1 ;
	}
	
	/*
	*
	*@param $id User id
	*/
	public function deleteUser( int $id, $reassign = null){
		 wp_delete_user( $id, $reassign ); 
	}
	
	
	/*
	*
	* @param userObject
	*/
	public function updateUser( &$userObject ){
		$userdata = (array) $userObject;
		$user_id  = wp_update_user( $userdata );
		
		return ( $user_id > 0 ) ? $user_id : -1 ;
	}
 }
 
 
 ?>