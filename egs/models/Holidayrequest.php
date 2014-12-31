<?php
class Holidayrequest extends DataObject {
	protected $defaultDisplayFields=array('start_date'=>'Start Date','end_date'=>'End Date','num_days'=>'Number of Days','special_circumnstances'=>'Special Circumnstances','approved'=>'Approved','employee_notes'=>'Employee Notes','reason_declined'=>'Reason Declined','approved_by'=>'Approved By');	

	function __construct() {
		parent::__construct('holiday_requests');
		$this->idField='id';
		
		$this->orderby='created';
 		$this->belongsTo('Employee', 'employee_id', 'employee');
 		$this->belongsTo('User', 'approved_by', 'approved_by_user');
		//$this->_autohandlers['approved_by']=new CurrentUserHandler(false,'EGS_USERNAME');
		$this->belongsTo('Company', 'company_id', 'company');
	}

}
?>
