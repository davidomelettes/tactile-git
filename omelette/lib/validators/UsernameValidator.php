<?php

/**
 * Responsible for checking that saving the user won't take the account past its user-limit
 */
class UsernameValidator implements FieldValidation {

	protected $_msg = "Usernames cannot contain the following characters: (#)";
	protected $_valid_username_regex = '/^[^#]+$/';
	
	/**
	 * 
	 * @param DataField $field 
	 * @param Array &$errors 
	 * @see ModelValidation::test()
	 */
	function test(DataField $field, Array &$errors=array()) {
		$value = $field->value;
		if (empty($value)) {
			return $value;
		}
		if (preg_match($this->_valid_username_regex, $value)) {
			return $value;
		}

		$errors[$field->name] = $this->_msg;
		return false;
	}
}
