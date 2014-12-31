<?php
class STTransactionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('STTransaction');
			$this->_tablename="st_transactionsoverview";
			
		}
	
		
		
}
?>
