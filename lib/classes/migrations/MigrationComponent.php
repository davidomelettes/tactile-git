<?php

abstract class MigrationComponent implements YAMLable, SQLable {
	public $mig_type;
	protected $db;
	abstract function __construct();
	
	function setDB(&$db) {
		$this->db=$db;
	}
}

?>