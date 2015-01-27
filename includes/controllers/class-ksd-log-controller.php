<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2015 Kanzu Code
 * @file      class-ksd-log-controller.php
 * @since     1.2.1
 */

include_once( KSD_PLUGIN_DIR. "includes/libraries/class-ksd-controller.php");

class KSD_Log_Controller extends KSD_Controller 
{	
	public function __construct(){
		$this->_model_name = "Log";
		parent::__construct();
	}
	
	/*
	*Add new log entry
	*
	*@param $log Log object to add to db.
	*/
	public function add_log ( &$log ) {
		return $this->_model->add_log( $log);
	}

	/*
	*Delete Reply
	*
	*@param $log_id Log id 
	*
	*/
	public function delete_log ( int $log_id ){
		$rO = new stdClass();
		$rO->log_id = $log_id;
		$this->_model->delete_log( $rO );
	}

	
	/*
	*Returns log with specified id.
	*
	*@param  $log_id	log id
	*@return Log Object
	*/
	public function get_log($log_id){
		return $this->_model->get_log( $log_id);
	}
	
	/*
	*Returns all logs that through query
	*@param string $query The query. Uses placeholders %s and %d
        * @param Array $value_parameters The values to replace the placeholders
	*@return Array Array of objects
	*/
	public function get_logs( $query, $value_parameters ){
		return $this->_model->get_all( $query, $value_parameters);
	}
}
?>