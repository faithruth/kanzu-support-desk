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

include_once( KSD_PLUGIN_DIR. "includes/libraries/Controller.php");

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
	public function getReply($reply_id){
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