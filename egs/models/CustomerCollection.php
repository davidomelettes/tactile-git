<?php
class CustomerCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Customer');
			$this->_tablename="customeroverview";
			
		$this->identifierField='username';
		$this->orderby='username';
		}
	
		
		
}
?>
