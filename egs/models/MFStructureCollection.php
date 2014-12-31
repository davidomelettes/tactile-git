<?php
class MFStructureCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('MFStructure');
			$this->_tablename="mf_structuresoverview";
			
		}
	
		
		
}
?>
