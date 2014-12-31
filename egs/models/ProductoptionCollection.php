<?php
class ProductoptionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Productoption');
			$this->_tablename="product_options";
			
		$this->view='';
		}
	
		
		
}
?>
