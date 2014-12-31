<?php
class PersonNote extends DataObject {

	protected $defaultDisplayFields = array('title','note','created');

	function __construct() {
		parent::__construct('person_notes');
		$this->belongsTo('Person','person_id','person');
	}
}	
?>