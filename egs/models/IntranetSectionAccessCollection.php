<?php
class IntranetSectionAccessCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('IntranetSectionAccess');
			$this->_tablename="intranet_section_accessoverview";
			
		$this->view='';
		}
	
		
		
}
?>
