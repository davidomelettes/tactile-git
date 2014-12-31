<?php
class ActivityNote extends DataObject {
	
	protected $defaultDisplayFields=array('title'=>'Title','note'=>'Note');
	
	function __construct() {
		parent::__construct('activity_notes');
		$this->idField='id';
		
		
 		$this->belongsTo('Activity', 'activity_id', 'activity');

	}


}
?>
