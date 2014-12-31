<?php
class MFWorkorderCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('MFWorkorder');
			$this->_tablename="mf_workordersoverview";
			
		}
	
		
		
}
?>
