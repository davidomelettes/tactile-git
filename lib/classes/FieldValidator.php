<?php
/**
 * An object to help perform validation on fields
 * should probably provide both PHP validation, and some way to  apply js client-side validation
 * but that might belong elsewhere...
 *
 **/
//some constants:

	define('VALIDATE_IS_NUMERIC','is_numeric');
	define('VALIDATE_IS_NOT_NULL','!empty');



class FieldValidator implements FieldValidation {

	

	/**
	 * Constructor
	 * Initialise a Validator with a condition.
	 * A condition is either a defined function name, that takes a single argument (the value to be tested) or a PCRE 
	 * (take note of static class variables!)
	 *
	 * @param	$condition	string	Either a function name, or a PCRE
	 */
	function __construct($condition) {
		$this->_condition=$condition;
	}
	
	function test(DataField $field,Array &$errors=array()) {
		
		$negate = false;
		if($this->_condition[0] ==='!')
		{
			$negate = true;
			$this->_condition = substr($this->_condition,1);
		}
		if(is_callable($this->_condition)) {
			$test=call_user_func($this->_condition,$val);
			if($test!==$negate){		
				return $val;
			}
			return false;
		}
		if(isset($this->condition) && !empty($this->condition)){
			$test=preg_match($this->condition,$val);
			if($test!==false){
				return $val;
			}
			$errors[] = 'error';
		}
		return false;
	}

}
?>
