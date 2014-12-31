<?php

class EmailTemplate extends DataObject {
	
	public function __construct($tablename='email_templates') {
		parent::__construct($tablename);
	}
	
	public function asJson() {
		$json = array();
		
		$string_fields = array('name', 'subject', 'body');
		$int_fields = array('id');
		
		foreach ($string_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : (string) $value);
		}
		foreach ($int_fields as $field) {
			$value = $this->$field; 
			$json[$field] = ((is_null($value) || '' === $value) ? null : (int) $value);
		}
		
		return json_encode($json);
	}
	
}
