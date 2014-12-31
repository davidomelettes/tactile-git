<?php
/**
 * Classes implementing this will be responsible for validating an entire model against some criteria
 * @author gj
 */
interface ModelValidation {

	/**
	 * Take a DataObject and test against some condition(s), returning the model on success or false otherwise.
	 * Adding a message to $errors is advisable
	 * @param DataObject $do
	 * @param Array &$errors
	 */
	function test(DataObject $do,Array &$errors);
}

?>
