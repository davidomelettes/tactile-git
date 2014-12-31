<?php
class SalesPersonCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('SalesPerson');
			$this->_tablename = 'sales_people_overview';

			
		}
	
		
		
}
?>
