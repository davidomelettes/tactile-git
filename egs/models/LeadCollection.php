<?php
class LeadCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Lead');
			$this->_tablename="companyoverview";
			
			$this->identifier='name';
			$this->identifierField='name';
		}
	
		
		
}
?>
