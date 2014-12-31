<?php
class WHLocationCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('WHLocation');
			$this->_tablename="wh_locationsoverview";
			
		}
	
		
		
}
?>
