<?php
class TaxStatusCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('TaxStatus');
			$this->_tablename="tax_statusesoverview";
			
		}
	
		
		
}
?>
