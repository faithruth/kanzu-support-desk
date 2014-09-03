<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
 class Kanzu_Model{
	protected $_tablename = "";
	protected $_id = "";
	protected $_formats = array();
	
	public function __construct(){
	}
	
	public function execQuery( $query ){
		global $wpdb;
		return $wpdb->get_results( query, OBJECT );
	}
	
	/*
	*Get single row object 
	*
	*@param userid
	*/
	public function getRow( $id ){
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT * FROM '. $this->_tablename .' WHERE '. $this->_id .' = ' . $id, OBJECT );
		
		return ( count($results) > 0 ) ? $results[0]: null;
	}
	
	/*
	*Get all from rows from table.
	*
	*@param $filter SQL filter. Everything after the WHERE key word
	*/
	public  function getAll( $filter = "" ){
		$where = ( $filter == "" ) ? "" : " WHERE $filter" ;
		$results = $wpdb->get_results( 'SELECT * FROM '. $this->_tablename . ' '. $filter , OBJECT );
	}
 
	/*
	*Add row to table. 
	*
	*@param 
	*/
	public function addRow( &$rowObject ){
		global $wpdb;
		
		$data = array();
		$format = array();
		foreach( $rowObject as $key => $value) {
			$data[$key] = $value;
			array_push($format,$this->_formats[$key]);
		}
		$wpdb->insert( $this->_tablename, $data, $format );
		return $wpdb->insert_id;
	}
	
	/*
	*Add delete row/s 
	*
	*@param $rowObject 
	*/
	public function deleteRow(  &$rowObject ){
		global $wpdb;
		$table = $this->_tablename;
		$where = array();
		$where_format = array();
		foreach( $rowObject as $key => $value) {
			$where[$key] = $value;
			array_push($where_format,$this->_formats[$key]);
		}
		$wpdb->delete( $table, $where, $where_format = null );
		return True;
		 
	}
	

	/*
	*Save/update row(s)
	*
	* *new_* for new value
	*/
	public function updateRow( &$rowObject ){
		global $wpdb;
		$table = $this->_tablename;
		$data = array();
		$where = array();
		$format = array();
		$where_format = array();
		foreach( $rowObject as $key => $value) {
			$pfx = substr($key,0,4); #new_
			if( $pfx == "new_"){ //New value Record Update Pattern
				$newkey = substr($key,4);
				$data[ $newkey] = $value;
				array_push($format,$this->_formats[$newkey]);
			}else{
				$where[$key] = $value;
				array_push($where_format,$this->_formats[$key]);
			}
		}
		$wpdb->update( $table, $data, $where, $format, $where_format); 
		return True;
	}
	
	public function getObj(){
		return (new stdClass());
	}
 }
 
 ?>