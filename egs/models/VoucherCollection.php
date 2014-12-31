<?php
class VoucherCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Voucher');
			$this->_tablename="store_vouchersoverview";
			
		}
	
		
		
}
?>
