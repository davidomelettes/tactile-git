<?php
class OpportunityNoteCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('OpportunityNote');
			$this->_tablename="opportunity_notes";
			
		}
	
		
		
}
?>
