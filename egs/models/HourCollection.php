<?php
class HourCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Hour');
//			$this->_tablename="hoursoverview";
		}
	
		
		
}
?>
