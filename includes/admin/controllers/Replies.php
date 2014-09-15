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
include_once( $plugindir. $DS . "admin" . $DS."libs".$DS."Controller.php");

class RepliesController extends Kanzu_Controller 
{	
	public function __construct(){
		$this->_model_name = "Replies";
		parent::__construct();
	}
	
	/*
	*Send new ticket
	*
	*@param $reply reply object to log
	*/
	public function addReply(&$reply){
		return $this->_model->addReply( $reply);
		//TODO: Send email reply after logging reply in db, or do this in view
	}
	
	/*
	*Update reply
	*
	*@param $reply_id Reply id 
	*
	*/
	public function updateReply( &$reply ){
		$this->_model->updateReply( $reply );
	}

	/*
	*Delete Reply
	*
	*@param $reply_id Reply id 
	*
	*/
	public function deleteReply(int $reply_id ){
		$rO = new stdClass();
		$rO->tkt_id = $reply_id;
		$this->_model->deleteReply( $rO );
	}

	
	/*
	*Returns Reply with specified id.
	*
	*@param  $reply_id	Reply id
	*@return Reply Object
	*/
	public function getReply(int $reply_id){
		return $this->_model->getReply( $reply_id);
	}
	
	/*
	*Returns all Replies that through query
	*
	*@return Array Array of objects
	*/
	public function getReplies( $query ){
		return $this->_model->getAll( $query);
	}
}
?>