<?php
class NumericControl extends TextControl {
		
	function __construct($field) {
		$this->addClassName('numeric');
		parent::__construct($field);
	}
	
}
?>