<?php
class PInvoiceCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('PInvoice');
			//$this->_tablename="pi_headeroverview";
			
		}
	
		
		
}
?>
