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
 
$plugindir = plugin_dir_path( __FILE__ );

//$DS=DIRECTORY_SEPARATOR;
//$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
//include_once( $plugindir. $DS . "admin" . $DS."libs".$DS."Model.php");
include_once (KANZU_PLUGIN_ADMIN_DIR . KANZU_DS . "models" . KANZU_DS . "Users.php" );

 class ClientsModel extends UsersModel{

	private $wp_formats = array();
	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_customers";	
		$this->_id = "cust_id";
			
		$this->_formats = array(
		'cust_id' 			 	=> '%d', 
		'cust_user_id' 			=> '%d',
		'cust_firstname'	 	=> '%s',
		'cust_lastname'	 		=> '%s' , 
		'cust_company_name' 	=> '%s',
		'cust_phone_number' 	=> '%s',
		'cust_about' 	 		=> '%s',
		'cust_creation_date' 	=> '%s',
		'cust_created_by' 	 	=> '%d'
		);
	}
	
	/*
	*Get user object
	*
	*@param customerid
	*/
	public function getClient( $id ){
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
	public function addClient( &$obj ){
		return parent::addRow( $obj );
	}
	
	/*
	*
	*@param client object.
	*/
	public function deleteClient(  &$obj ){
		return parent::deleteRow( $obj );
	}
	

	/*
	* Save/update 
	*@param client object
	* *new_* for new value
	*/
	public function updateClient( &$obj ){
		return parent::updateRow( $obj );
	}
 }
 
 
 ?>