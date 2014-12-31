<?php
class ProductfeatureCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Productfeature');
			$this->_tablename="product_features";
			
		}
	
		
		
}
?>
