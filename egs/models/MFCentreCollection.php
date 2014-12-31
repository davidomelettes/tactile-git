<?php
class MFCentreCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('MFCentre');
			$this->_tablename="mf_centresoverview";
			
		}
	
		
		
}
?>
