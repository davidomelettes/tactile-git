<?php
class SLTransactionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('SLTransaction');
			//$this->_tablename="sltransactionsoverview";
			
		}
	
		
		
}
?>
