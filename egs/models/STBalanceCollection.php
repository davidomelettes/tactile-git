<?php
class STBalanceCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('STBalance');
			$this->_tablename="st_balancesoverview";
			
		}
	
		
		
}
?>
