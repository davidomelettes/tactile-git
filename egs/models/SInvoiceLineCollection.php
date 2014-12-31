<?php
class SInvoiceLineCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('SInvoiceLine');
			//$this->_tablename="si_linesoverview";
			
		}
	
		
		
}
?>
