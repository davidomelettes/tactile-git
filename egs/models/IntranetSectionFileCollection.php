<?php
class IntranetSectionFileCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('IntranetSectionFile');
			$this->_tablename="intranet_section_filesoverview";
			
		}
	
		
		
}
?>
