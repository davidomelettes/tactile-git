<?php
require_once LIB_ROOT.'spyc/spyc.php';

class SetOfFixtures {
	
	protected $fixtures = array();
	
	public function __construct($filename) {
		$this->fixtures = Spyc::YAMLLoad($filename);
	}
	
	public function getParsedData() {
		return $this->fixtures;
	}
	
	public function bindAll($key, $val) {
		foreach($this->fixtures as $table=>$data) {
			$this->bind($table, $key, $val);
		}
	}
	
	public function bind($table, $key, $val) {
		if(!isset($this->fixtures[$table])) {
			throw new Exception("Invalid tablename specified for binding: $table");
		}
		foreach($this->fixtures[$table] as $i=>$row) {
			$this->fixtures[$table][$i][$key] = $val;
		}
	}
	
	public function getForColumns($tablename, $columns) {
		$rows = array();
		if(!isset($this->fixtures[$tablename])) {
			throw new Exception("Invalid tablename specified: $tablename");
		}
		
		foreach($this->fixtures[$tablename] as $row) {
			$row_data = array();
			foreach($columns as $col) {
				if(!isset($row[$col])) {
					$row[$col] = NULL;
				}
				if($row[$col] === true) {
					$row[$col] = 'true';
				}
				if($row[$col] === false) {
					$row[$col] = 'false';
				}
				$row_data[] = $row[$col];
			}
			$rows[] = $row_data;
		}
		return $rows;
	}
	
}
?>