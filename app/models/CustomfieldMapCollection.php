<?php

class CustomfieldMapCollection extends DataObjectCollection {

	
	public function __construct($model='CustomfieldMap') {
		parent::__construct($model);
		$this->_tablename='custom_field_map_overview';
	}
	
	public function asJson(){
		$data = array();
		foreach($this as $field){
			$data[]=$field->toArray();
		}
		return json_encode($data);
	}
	
}
