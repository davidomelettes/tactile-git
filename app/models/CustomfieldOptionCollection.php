<?php

class CustomfieldOptionCollection extends DataObjectCollection {
	
	public function __construct($model='CustomfieldOption') {
		parent::__construct($model);
		$this->_tablename='custom_field_options';
	}
	
	public function asJson(){
		$data = array();
		foreach($this as $field){
			$data[]=$field->asJson();
		}
		return json_encode($data);
	}
	
}
