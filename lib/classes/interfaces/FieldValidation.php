<?php
/**
 * Classes implementing this will be responsible for validating the value of the provided field, returning a value
 * (which may be different to that passed in!) on success, false otherwise. Adding an error messages to $errors is
 * advised
 * @author gj 
 */
interface FieldValidation{

	/**
	 * Takes a DataField and checks the value against some condition(s)
	 * 
	 * @param DataField $field
	 * @param Array &$errors
	 * @return mixed
	 */
	public function test(DataField $field,Array &$errors=array());

}
?>