<?php
class MFResourceCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('MFResource');
			$this->_tablename="mf_resourcesoverview";
			
		}
	
		
		
}
?>
