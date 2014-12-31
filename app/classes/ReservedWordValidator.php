<?php
/**
 *
 * @author gj
 */
class ReservedWordValidator implements FieldValidation {

	private $words=array();
	private $message = '%s contains a reserved word, please choose another value';
	
	public function __construct($words,$message=null) {
		if($message!==null) {
			$this->message = $message;	
		}
		$this->words = $words;
	}
	
	public function test(DataField $field,Array &$errors = array()) {
		foreach($this->words as $word) {
			$re = '#'.$word.'#i';			
			if(preg_match($re,trim($field->value))!==0) {
				$errors[$field->name] = sprintf($this->message,$field->tag);
				return false;
			}
		}
		return $field->value;
	}
	
}
?>