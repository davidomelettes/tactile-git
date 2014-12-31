<?php
class BankAccountCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('BankAccount');
			$this->_tablename="bankaccountoverview";
			$this->_identifierField = "acctref";
		}
	
		
		
}
?>
