<?php
class Employee extends DataObject {
	protected $defaultDisplayFields=array('person'=>'person','employee_number'=>'employee_number','start_date'=>'start_date','finished_date'=>'finished_date');
	function __construct() {
		parent::__construct('employees');
		$this->idField='id';
		$this->identifierField = 'person_id';
		
		
 		$this->belongsTo('Person', 'person_id', 'person');
 		$this->belongsTo('User', 'alteredby', 'alteredby'); 
		$this->belongsTo('Company', 'company_id', 'company');
	}


}
?>
