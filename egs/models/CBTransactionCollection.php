<?php
class CBTransactionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('CBTransaction');
			//$this->_tablename="cb_transactionsoverview";
			
		}
	
		
		
}
?>
