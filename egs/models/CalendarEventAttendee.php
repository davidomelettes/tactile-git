<?php
class CalendarEventAttendee extends DataObject {

	function __construct() {
		parent::__construct('calendar_event_attendees');
		$this->idField='id';
		
		
 		$this->belongsTo('Event', 'calendar_event_id', 'calendar');
 		$this->belongsTo('Person', 'person_id', 'person'); 

	}


}
?>
