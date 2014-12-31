<?php
class Holidayextraday extends DataObject {
	protected $defaultDisplayFields=array('num_days'=>'Number of Days','reason'=>'Reason','authorisedby'=>'Authorised By','created'=>'Authorised On');
	function __construct() {
		parent::__construct('holiday_extra_days');
		$this->idField='id';
		
		//$this->_autohandlers['authorised_on']=new CurrentTimeHandler();
 		$this->belongsTo('Entitlement', 'entitlement_period_id', 'entitlement');
 		$this->belongsTo('Employee', 'employee_id', 'employee');
 		$this->belongsTo('User', 'authorisedby', 'authorisedby_user'); 
		$this->_autohandlers['authorisedby']=new CurrentUserHandler(false,'EGS_USERNAME');
	}


}
?>
