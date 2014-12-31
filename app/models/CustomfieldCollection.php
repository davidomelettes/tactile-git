<?php

class CustomfieldCollection extends DataObjectCollection {
	
	public function __construct($model='Customfield') {
		parent::__construct($model);
		$this->_tablename='custom_fields';
	}
	
	public function asJson(){
		$data = array();
		foreach($this as $field){
			$data[]=$field->toArray();
		}
		return json_encode($data);
	}
	
}
