<?php
class IPValidator implements FieldValidation {
	private $message_stub='Invalid IP Address provided';

	function test(DataField $field,Array &$errors=array()) {
		if(ip2long($field->value)!==false) {
			return $field->value;
		}
		
		$errors[$field->name]=sprintf($this->message_stub,$field->tag);
		return false;
	}
}
?>
