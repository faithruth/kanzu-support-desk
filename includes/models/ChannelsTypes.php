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
 
include_once( KSD_PLUGIN_DIR. "includes/libraries/Model.php");


 class ChannelTypesModel extends Kanzu_Model{

	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_channeltypes";	
		$this->_id = "chantype_id";
			
		$this->_formats = array(
		'chan_id' 			 => '%d', 
		'chan_chantype_id'	 => '%s',
		'chan_handle'	 	 => '%s' , 
		'chan_description' 	 => '%s'
		);
	}
	
	/*
	*Get user object
	*
	*@param userid
	*/
	public function getChannelType( $id ){
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
	public function addChannelType( &$obj ){
		return parent::addRow( $obj );
	}
	
	/*
	*
	*@param Channel object.
	*/
	public function deleteChannelType(  &$obj ){
		return parent::deleteRow( $obj );
	}
	

	/*
	* Save/update 
	*@param Channel object
	* *new_* for new value
	*/
	public function updateChannelType( &$obj ){
		return parent::updateRow( $obj );
	}
 }
 
 
 ?>
