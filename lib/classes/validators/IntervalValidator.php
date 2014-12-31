<?php
class IntervalValidator implements FieldValidation {

	function test(DataField $field,Array &$errors=array()) {
		return $field->value;
	}	
	
}
?>