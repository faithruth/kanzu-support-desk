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

class Kanzu_Replies_Controller extends Kanzu_Controller 
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
	public function add_reply(&$reply){
		return $this->_model->add_reply( $reply);
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
	public function delete_reply(int $reply_id ){
		$rO = new stdClass();
		$rO->tkt_id = $reply_id;
		$this->_model->delete_reply( $rO );
	}

	
	/*
	*Returns Reply with specified id.
	*
	*@param  $reply_id	Reply id
	*@return Reply Object
	*/
	public function get_reply($reply_id){
		return $this->_model->get_reply( $reply_id);
	}
	
	/*
	*Returns all Replies that through query
	*
	*@return Array Array of objects
	*/
	public function get_replies( $query ){
		return $this->_model->get_all( $query);
	}
}
?>