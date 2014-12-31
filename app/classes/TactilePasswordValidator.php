<?php
/**
 *
 * @author gj
 */
class TactilePasswordValidator extends PasswordValidator {
	public function test(DataField $field,Array &$errors=array()) {
		return $field->value;
	}
}
?>