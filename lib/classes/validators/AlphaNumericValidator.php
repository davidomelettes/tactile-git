<?php
/**
 *
 * @author gj
 */
class AlphaNumericValidator implements FieldValidation {
	private $msg = '%s can only contain letters and numbers';
	
	public function __construct($msg=null) {
		if($msg!==null) {
			$this->msg = $msg;
		}
	}
	
	public function test(DataField $field,Array &$errors=array()) {
		$value = $field->value;
		if(empty($value)) {
			return $field->value;
		}
		$valid = ctype_alnum($field->value);
		if(!$valid) {
			$errors[$field->name] = sprintf($this->msg,$field->tag);
			return false;
		}
		return $field->value;
	}
}
?>