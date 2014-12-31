<?php
abstract class InputControl extends FormControl {
	public $name, $type;
	function __construct(DataField $field) {
		$this->_data=$field;
		$this->extractData();
	}
	function render($additional='') {
		$html="{input type='{$this->type}'  attribute='{$this->name}' {$this->getClassNameString()}}\n";
		return $html;
	}

}
?>
