<?php

require_once ('Zend/Log/Writer/Abstract.php');

/**
 * A Log-Writer for use with Omelette/EGS Databases
 * @author gj
 * @package Logging
 */
class Omelette_DBLogWriter extends Zend_Log_Writer_Abstract {

	/**
	 * As with Zend_Log_Writer_db, provide a database, a table and a column-mapping
	 * 
	 * @param DB $db
	 * @param String $table The name of the table to log to
	 * @param Array $column_map The assoc array mapping columnname=>log-parts
	 */
	function __construct($db, $table, $column_map) {
		$this->db = $db;
		$this->table = $table;
		$this->column_map=$column_map;
	}

	/**
	 * Formatting is not possible on this writer
	 */
	public function setFormatter($formatter) {
		throw new Zend_Log_Exception(get_class() . ' does not support formatting');
	}

	/**
	 * 
	 * @param array  $event  log data event 
	 * @return void 
	 * @see Zend_Log_Writer_Abstract::_write()
	 */
	protected function _write($event) {
		if ($this->db === null) {
            throw new Zend_Log_Exception('Database adapter instance has been removed by shutdown');
        }
		if($this->column_map === null) {
            $dataToInsert = $event;
        } else {
            $dataToInsert = array();
            foreach($this->column_map as $columnName => $fieldKey) {
            	if(!empty($event[$fieldKey])) {
                	$dataToInsert[$columnName] = $this->db->qstr($event[$fieldKey]);
            	}
            }
        }
        //$this->db->Replace($this->table,$dataToInsert,'id',true);
        $this->db->Replace($this->table,$dataToInsert,'id');
	}
	
	public static function factory($config) {
		
	}
}

?>
