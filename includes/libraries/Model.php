<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
//@TODO Add $wpdb->prepare to wrap the queries
 class Kanzu_Model{
	protected $_tablename = "";
	protected $_id = "";
	protected $_formats = array(
		''=>''
	);
	
	public function __construct(){
	}
	
	public function exec_query( $query ){
		global $wpdb;
		return $wpdb->get_results( $query, OBJECT );
	}
	
	/*
	*Get single row object 
	*
	*@param userid
	*/
	public function get_row( $id ){
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT * FROM '. $this->_tablename .' WHERE '. $this->_id .' = ' . $id, OBJECT );
		
		return ( count($results) > 0 ) ? $results[0]: null;
	}
        
        /**
         * Get a single variable from the database
         * 
         * @param String $variable The variable you'd like to retrieve. e.g. count(tkt_id),sum(tkt_id), etc
         * @param String $where The WHERE clause
         */
        
        public function get_var( $variable, $where="" ) {
            global $wpdb;
            return $wpdb->get_var( "SELECT ".$variable." FROM ". $this->_tablename." ".$where );
        }
	
	/*
	*Get all from rows from table.
	*
	*@param $filter SQL filter. Everything after the WHERE key word
	*/
	public  function get_all( $filter = "" ){
		global $wpdb;
		$where = ( $filter == "" || $filter == null ) ? "" : " WHERE " . $filter ;
                $results = $wpdb->get_results( 'SELECT * FROM '. $this->_tablename . ' '. $where , OBJECT );
		return $results;
	}
 
	/*
	*Add row to table. 
	*
	*@param 
	*/
	public function add_row( &$rowObject ){
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
	* @return Number of rows deleted or false 
	*/
	public function delete_row(  &$rowObject ){
		global $wpdb;
		$table = $this->_tablename;
		$where = array();
		$where_format = array();
		foreach( $rowObject as $key => $value) {
			$where[$key] = $value;
			array_push($where_format,$this->_formats[$key]);
		}	
		return $wpdb->delete( $table, $where, $where_format = null ); ;
		 
	}
	

	/*
	*Save/update row(s)
	* @return The number of rows updated or false
	* *new_* for new value
	*/
	public function update_row( &$rowObject ){
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
		return $wpdb->update( $table, $data, $where, $format, $where_format); 		 
	}
	
	public function get_obj(){
		return (new stdClass());
	}
        
        public function get_count( $filter="", $table=""){
            global $wpdb;
                
            $where = ( $filter != "" )? " WHERE " : "" ;
            $table = ( $table != "" )? $table : $this->_tablename ;
            $filter = " SELECT * FROM $table $where $filter ";
            
            $query = " SELECT COUNT(*) AS count FROM ( $filter ) t";
            $obj = $wpdb->get_results( $query, OBJECT );
            return $obj[0]->count;
            return $query;
            
                
        }
 }
 
 ?>
