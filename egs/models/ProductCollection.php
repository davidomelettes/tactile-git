<?php
class ProductCollection extends DataObjectCollection {
	
	public $field;
	
	function __construct($do='Product') {
		parent::__construct($do);
		$this->_tablename="productoverview";
		
	}
	
		
	function contains($field,$value) {
		echo key($this->_dataobjects);
	}
}
?>
