<?php
class CalendarShareCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('CalendarShare');
			$this->_tablename="calendar_shares";
			
		}
	
		
		
}
?>
