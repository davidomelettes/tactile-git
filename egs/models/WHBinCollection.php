<?php
class WHBinCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('WHBin');
			$this->_tablename="wh_binsoverview";
			
		}
	
		
		
}
?>
