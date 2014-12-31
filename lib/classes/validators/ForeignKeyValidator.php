<?php

class ForeignKeyValidator implements FieldValidation {

	
	public function __construct($name) {
		$this->modelname = $name;
	}
	
	/**
	 * 
	 * @param DataField $field 
	 * @param Array &$errors 
	 * @return mixed 
	 * @see FieldValidation::test()
	 */
	public function test(DataField $field, array &$errors = array()) {
		$value = $field->value;
		if(empty($value)) {
			return $value;
		}
		$model = DataObject::Construct($this->modelname);
		$model = $model->load($field->value);
		if($model!==false) {
			return $field->value;
		}
		$errors[$field->name] = "Invalid ID specified";
		return false;
	}
}

?>
