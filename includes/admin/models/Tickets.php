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
 
 global $wpdb;
 
 class UsersModel(){
	$_tablename = ""
	
	/*
	*Get user object
	*
	*@param userid
	*/
	public getUser( int $id ){
		
	}
	
	/*
	*Get all from users (kanzu-users) from wp users table
	*
	*@param $filter SQL filter 
	*/
	public  getAll( $filter = "" ){
	
	}
 
	/*
	*Add user to 
	*
	*
	*/
	public addUser( var &$userObj ){
	
	}
	
	/*
	*Add user to 
	*
	*@param $id User id
	*/
	public deleteUser( int $id ){
	
	}
	
	
	/*
	*Save/update 
	*
	*/
	public saveUser( &$userObject ){
	
	}
 }
 
 
 ?>
