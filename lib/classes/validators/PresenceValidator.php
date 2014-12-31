<?php

class PresenceValidator implements FieldValidation {
	private $message_stub='%s is a compulsory field, you must enter a value';
	
	protected $message;
	
	public function __construct($message = null) {
		if(!is_null($message)) {
			$this->message = $message;
		}
	}
	
	function test(DataField $field, Array &$errors=array()) {
		$value = $field->finalvalue;
		if(empty($value)&&$value!==0&&$value!=='0') {
			if (!$field->isHandled && !isset($errors[$field->name])) {
				if(is_null($this->message)) {
					$message=sprintf($this->message_stub,$field->tag);
				}
				else {
					$message = $this->message;
				}
				$errors[$field->name]=$message;
				return false;
			}
		}
		return $value;
	}
}
?>
