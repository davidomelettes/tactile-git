<?php
class HolidayrequestCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Holidayrequest');
			//$this->_tablename="holiday_requestsoverview";
			
		}
	
		
		
}
?>
