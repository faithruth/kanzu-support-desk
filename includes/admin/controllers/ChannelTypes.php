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

class ChannelTypesController extends Kanzu_Controller 
{	
	public function __construct(){
		$this->_model_name = "ChannelTypes";
		parent::__construct();
	}
	
	/*
	*Add new channel
	*
	*@param $channel channel object to log
	*/
	public function addChannelType(&$channelType){
		return $this->_model->addChannelType( $channelType);
		//TODO: Send email reply after logging reply in db, or do this in view
	}
	
	/*
	*Update channelType
	*
	*@param $channelType_id ChannelType id 
	*
	*/
	public function updateChannelType( &$channelType ){
		$this->_model->updateChannelType( $channelType );
	}

	/*
	*Delete Channel Type
	*
	*@param $channelType_id ChannelType id 
	*
	*/
	public function deleteChannelType(int $channelType_id ){
		$cTO = new stdClass();
		$cTO->chantype_id = $ChannelType_id;
		$this->_model->deleteChannelType( $cTO );
	}

	
	/*
	*Returns ChannelTypes with specified id.
	*
	*@param  $channelTypes_id	ChannelTypes id
	*@return Channel Object
	*/
	public function getChannelType(int $channel_id){
		return $this->_model->getChannelTypes( $channel_id);
	}
	
	/*
	*Returns all ChannelTypes  through query
	*
	*@return Array of Channel objects
	*/
	public function getChannelTypes( $query ){
		return $this->_model->getAll( $query);
	}
}
?>