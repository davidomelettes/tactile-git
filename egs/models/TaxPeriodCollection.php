<?php
class TaxPeriodCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('TaxPeriod');
			$this->_tablename="taxperiods";
			$this->_identifierField = "description";
		}
	
		
		
}
?>
