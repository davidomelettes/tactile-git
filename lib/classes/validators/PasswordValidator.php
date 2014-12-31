<?php

class PasswordValidator implements FieldValidation {

	function test(DataField $field, Array &$errors=array()) {
		return md5($field->value);
	}
}
?>
