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

$DS=DIRECTORY_SEPARATOR;
$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
include_once( $plugindir. $DS . "admin" . $DS."libs".$DS."Model.php");


 class RepliesModel extends Kanzu_Model{

	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_replies";	
		$this->_id = "rep_id";
			
		$this->_formats = array(
		'rep_id' 			 	=> '%d', 
		'rep_tkt_id'	 		=> '%s',
		'rep_type'	 			=> '%s' , 
		'rep_is_cc' 			=> '%s',
		'rep_is_bcc' 	 		=> '%s',
		'rep_date_created' 		=> '%s',
		'rep_created_by' 	 	=> '%s',
		'rep_date_modified' 	=> '%s'
		);
	}
	
	/*
	*Get user object
	*
	*@param userid
	*/
	public function getReply( $id ){
		return parent::getRow($id);
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
	public function addReply( &$obj ){
		return parent::addRow( $obj );
	}
	
	/*
	*
	*@param Channel object.
	*/
	public function deleteReply(  &$obj ){
		return parent::deleteRow( $obj );
	}
	

	/*
	* Save/update 
	*@param Channel object
	* *new_* for new value
	*/
	public function updateReply( &$obj ){
		return parent::updateRow( $obj );
	}
 }
 
 
 ?>