<?php
class ProductattributeCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Productattribute');
			$this->_tablename="product_attributes";
			
		}
	
		
		
}
?>
