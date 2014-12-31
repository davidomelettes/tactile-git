<?php
class PaymentTermCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('PaymentTerm');
	
		}
	
}
?>
