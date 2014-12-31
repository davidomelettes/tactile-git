<?php
class HolidayextradayCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Holidayextraday');
			//$this->_tablename="holiday_extra_daysoverview";
		}
	
		
		
}
?>
