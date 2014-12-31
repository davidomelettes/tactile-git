<?php
class DynamicsectioncriteriaCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Dynamicsectioncriteria');
			$this->_tablename="dynamic_section_criteriaoverview";
			
		}
	
		
		
}
?>
