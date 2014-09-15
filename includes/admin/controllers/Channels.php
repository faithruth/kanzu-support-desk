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

class ChannelsController extends Kanzu_Controller 
{	
	public function __construct(){
		$this->_model_name = "Channels";
		parent::__construct();
	}
	
	/*
	*Add new channel
	*
	*@param $channel channel object to log
	*/
	public function addChannel(&$channel){
		return $this->_model->addChannel( $channel);
		//TODO: Send email reply after logging reply in db, or do this in view
	}
	
	/*
	*Update channel
	*
	*@param $channel_id Channel id 
	*
	*/
	public function updateChannel( &$channel ){
		$this->_model->updateChannel( $channel );
	}

	/*
	*Delete Channel
	*
	*@param $channel_id Channel id 
	*
	*/
	public function deleteChannel(int $channel_id ){
		$cO = new stdClass();
		$cO->chan_id = $Channel_id;
		$this->_model->deleteChannel( $cO );	
	}

	
	/*
	*Returns Channel with specified id.
	*
	*@param  $channel_id	Channel id
	*@return Channel Object
	*/
	public function getChannel(int $channel_id){
		return $this->_model->getChannel( $channel_id);
	}
	
	/*
	*Returns all Channels  
	*
	*@return Array of Channel objects
	*/
	public function getChannels( $query = ""){
		return $this->_model->getAll( $query);
	}
}
?>