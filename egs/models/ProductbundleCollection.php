<?php
class ProductbundleCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Productbundle');
			
		$this->view='';
		}
	
		
		
}
?>
