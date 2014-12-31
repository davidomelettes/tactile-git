<?php
class EmployeeCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Employee');
			$this->_tablename="employeeoverview";
			$this->identifierField='person';
		}
	
		
		
}
?>
