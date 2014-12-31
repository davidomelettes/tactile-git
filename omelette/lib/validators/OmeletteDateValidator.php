<?php
class OmeletteDateValidator implements DateValidation {

	function test(DataField $field, Array &$errors=array()) {
		$value = $field->value;
		if(empty($value)) {
			return $value;
		}

		$formats = array('d/m/Y'=>'%d/%m/%y', 'm/d/Y'=>'%m/%d/%y');
		$user_format = EGS::getDateFormat();

		if(strptime($field->value,$formats[$user_format])!==false) {
				return fix_date($field->value);
		}
		if(strptime($field->value, '%Y-%m-%d')!==false || strptime($field->value, '%Y-%m-%d %H:%M:%S')!==false) {
			return $field->value;
		}
		$errors[$field->name] = "Invalid date specified for {$field->tag}, should be of the form {$user_format}";
		return false;
	}
	
}
?>