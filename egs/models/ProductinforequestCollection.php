<?php
class ProductinforequestCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Productinforequest');
			$this->_tablename="store_product_information_requestsoverview";
			
		}
	
		
		
}
?>
