<?php
class CustomfieldOption extends DataObject {

	public function __construct() {
		parent::__construct('custom_field_options');
	}

	public function asJson() {
		$json = array();
		
		return json_encode(array('custom_field'=>$json));
	}
	
		
}
