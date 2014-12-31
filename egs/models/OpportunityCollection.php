<?php
class OpportunityCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Opportunity');
			$this->_tablename="opportunities_overview";
			$this->identifierField='name';
			
		$this->view='';
		}
	
		
		
}
?>
