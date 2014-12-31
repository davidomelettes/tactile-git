<?php
class SInvoiceCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('SInvoice');
			//$this->_tablename="si_headeroverview";
			
		$this->view='';
		}
	
		
		
}
?>
