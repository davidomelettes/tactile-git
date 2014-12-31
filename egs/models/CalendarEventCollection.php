<?php
class CalendarEventCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('CalendarEvent');
			$this->_tablename="calendar_events";
			
		}
	
		
		
}
?>
