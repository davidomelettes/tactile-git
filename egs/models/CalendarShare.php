<?php
class CalendarShare extends DataObject {

	function __construct() {
		parent::__construct('calendar_shares');
		$this->idField='id';
		
		
 		$this->belongsTo('User', 'owner', 'owner');
 		$this->belongsTo('User', 'username', 'username'); 

	}


}
?>
