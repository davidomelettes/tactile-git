<?php

class AlphaNumericValidator implements FieldValidation {
	
	private $message_stub='%s must contain an alphanumeric value';
	
	public function __construct($stub = null) {
		if (!empty($stub)) {
			$this->message_stub = $stub;
		}
	}
	
	function test(DataField $field,Array &$errors=array()) {
		if(ctype_alnum($field->value)!==false) {
			return $field->value;
		}
		$errors[$field->name]=sprintf($this->message_stub,$field->tag);
		return false;
	}
}
