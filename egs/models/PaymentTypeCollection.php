<?php
class PaymentTypeCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('PaymentType');
			$this->_tablename="sypaytypesoverview";
			
		}
	
		
		
}
?>
