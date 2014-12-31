<?php
class PLTransactionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('PLTransaction');
			//$this->_tablename="sltransactionsoverview";
			
		}
	
		
		
}
?>
