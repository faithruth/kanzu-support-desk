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


 class RepliesModel extends Kanzu_Model{

	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_replies";	
		$this->_id = "rep_id";
			
		$this->_formats = array(
		'rep_id' 			 	=> '%d', 
		'rep_tkt_id'	 		=> '%d',
		'rep_type'	 			=> '%d' , 
		'rep_is_cc' 			=> '%s',
		'rep_is_bcc' 	 		=> '%s',
		'rep_date_created' 		=> '%s',
		'rep_created_by' 	 	=> '%s',
		'rep_date_modified' 	=> '%s',
                'rep_message'    => '%s'
		);
	}
	
	/*
	*Get Replies object
	*
	*@param userid
	*/
	public function getReply( $id ){
		return parent::getRow($id);
	}
	
	/*
	*Get all from the replies table
	*
	*@param $filter SQL filter. Everything after the WHERE key word
	*/
	public  function getAll( $filter = "" ){
		return parent::getAll($filter);
	}
 
	/*
	*
	*/
	public function addReply( &$obj ){
		return parent::addRow( $obj );
	}
	
	/*
	*
	*@param Replies object.
	*/
	public function deleteReply(  &$obj ){
		return parent::deleteRow( $obj );
	}
	

	/*
	* Save/update 
	*@param Replies object
	* *new_* for new value
	*/
	public function updateReply( &$obj ){
		return parent::updateRow( $obj );
	}
 }
 
 
 ?>