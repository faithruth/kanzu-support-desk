<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @file	  Assignments.php
 */

 
include_once( KSD_PLUGIN_DIR. "includes/libraries/class-ksd-controller.php");

class KSD_Attachments_Controller extends KSD_Controller 
{	
	public function __construct(){
		$this->_model_name = "Attachments";
		parent::__construct();
	}
	
	/*
	* Attach item to a ticket
	*
	* @param $ticket_id Ticket ID
	* @param $url File URL
	* @param $size File size
	* @param $filename Filename
	*/
	public function add_attachment( $ticket_id, $url,  $size, $filename ){
		$aO                                 = $this->_model->get_obj();
		$aO->attach_url                 = $url;
		$aO->attach_size                = $size;
                $aO->attach_filename            = $filename;                
		$aO->attach_tkt_id = $ticket_id;
		return $this->_model->add_attachment( $aO );
	}


}