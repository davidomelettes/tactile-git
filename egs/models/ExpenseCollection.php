<?php
class ExpenseCollection extends DataObjectCollection {
		public $field;
		
		function __construct() {
			parent::__construct('Expense');
			$this->_tablename="expenses_overview";
		}
}
?>