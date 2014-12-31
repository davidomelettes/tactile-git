<?php
class TaxRateCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('TaxRate');
			$this->_tablename="taxrates";
			
		}
	
		
		
}
?>
