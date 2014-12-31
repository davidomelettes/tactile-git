<?php
class CountryCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Country');
			$this->_tablename="countriesoverview";
			
		}
	
		
		
}
?>
