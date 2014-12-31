<?php
class CurrencyCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct($do = null) {
		parent::__construct($do);
		$this->_tablename="currencyoverview";
			
		$this->identifierField='currency';
		$this->orderby='currency';
		}
	
		
		
}
?>
