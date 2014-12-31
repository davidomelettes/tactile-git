<?php
class GLBalanceCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('GLBalance');
			$this->_tablename="glbalanceoverview";
			$this->_identifierField = "year";

		}
	
		
		
}
?>
