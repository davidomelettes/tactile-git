<?php
class ProductoptioncategoryCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Productoptioncategory');
			$this->_tablename="product_option_categories";
			
		}
	
		
		
}
?>
