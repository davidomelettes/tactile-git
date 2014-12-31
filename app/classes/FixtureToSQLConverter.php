<?php

class FixtureToSQLConverter {
	
	protected $column_info = array();
	
	public function __construct() {
		
	}
	
	public function setColumns($columns) {
		$this->column_info = $columns;
	}

	public function getColumns($tablename = null) {
		if(is_null($tablename)) {
			return $this->column_info;
		}
		if(!isset($this->column_info[$tablename])) {
			throw new Exception("getColumns: Invalid tablename: $tablename");
		}
		return $this->column_info[$tablename];
	}
	
	public function getSQL() {
		$sqls = array();
		foreach($this->column_info as $tablename => $columns) {
			$sql = 'INSERT INTO '.$tablename.' (';
			$sql .= implode(',', $columns);
			$sql .= ') VALUES (';
			$sql .= trim(str_repeat('?,', count($columns)), ',');
			$sql .= ')';
			
			$sqls[$tablename] = $sql;
		}
		return $sqls;
	}
}
?>