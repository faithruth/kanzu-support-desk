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

 class UsersModel{
	
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
	
	}
 
	/*
	*Add user to 
	*
	*
	*/
	public function addUser( &$userObj ){
	
	}
	
	/*
	*Add user to 
	*
	*@param $id User id
	*/
	public function deleteUser( int $id ){
	
	}
	
	
	/*
	*Save/update 
	*
	*/
	public function updateUser( &$userObject ){
	
	}
 }
 
 
 ?>
