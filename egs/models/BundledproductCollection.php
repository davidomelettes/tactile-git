<?php
class BundledproductCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Bundledproduct');
//			$this->_tablename="products_in_bundlesoverview";
			
		}
	
		
		
}
?>
