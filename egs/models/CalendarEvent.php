<?php
class CalendarEvent extends DataObject {

	protected $defaultDisplayFields = array('summary','start_time','end_time');

	function __construct() {
		parent::__construct('calendar_events');
		$this->idField='id';
		$this->belongsTo('Person','person_id','person');
		$this->belongsTo('Company','company_id','company');
		
	}


}
?>
