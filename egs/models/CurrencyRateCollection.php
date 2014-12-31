<?php
class CurrencyRateCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('CurrencyRate');
			$this->_tablename="currencyrateoverview";
			
		}
	
		
		
}
?>
