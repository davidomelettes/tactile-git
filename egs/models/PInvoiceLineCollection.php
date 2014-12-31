<?php
class PInvoiceLineCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('PInvoiceLine');
			//$this->_tablename="pi_linesoverview";
			
		}
	
		
		
}
?>
