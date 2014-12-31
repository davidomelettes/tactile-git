<?php

class Expense extends DataObject {
	public function __construct() {
		parent::__construct('expenses');
		
		$this->validateUniquenessOf(array("expense_ref"));
		$this->_autohandlers['expense_ref'] = new CompanyUniqueReferenceHandler('expenses', 'expense_ref');
		
		$this->belongsTo('Project', 'project_id', 'project');
		$this->belongsTo('Opportunity', 'opportunity_id', 'opportunity');
		$this->belongsTo('Employee', 'employee_id', 'employee');
	}
	
	public function getPersonName() {
		$db = DB::Instance();
		$query = 'SELECT p.firstname || \' \' || p.surname FROM person p LEFT JOIN employees e ON (e.person_id=p.id) WHERE e.id=' . $db->qstr($this->employee_id) . ' AND p.usercompanyid = ' . $db->qstr(EGS_COMPANY_ID);
		$name = $db->GetOne($query);
		return $name;
	}
}