<?php
class Migration implements YAMLable, SQLable {
	protected $data=array();
	protected $components=array();
	function __construct() {
	
	
	}

	function add(MigrationComponent $component) {
		$this->components[]=$component;	
		return $component;
	}
	
	function toArray() {
		foreach($this->components as $component) {
			$this->data[]=array('type'=>$component->mig_type)+$component->toArray();	
		}
		return $this->data;
	}
	
	function toSQL() {
		$sql="--outputting sql\n";
		$sql.="BEGIN;\n";
		foreach($this->components as $component) {
			$sql.=$component->toSQL();
		}
		$sql.="COMMIT;\n";
		return $sql;
	}
}
?>