<?php
class Resourcetemplate extends DataObject {
	
	protected $defaultDisplayFields=array('person', 'name', 'resource_type', 'standard_rate', 'overtime_rate', 'quantity', 'cost');

	public function __construct() {
		parent::__construct('resource_templates');
		$this->idField='id';
		$this->identifierField = 'person';
		
		$this->orderby = 'person,name';
		$this->orderdir = 'asc';
		
		$this->belongsTo('Person', 'person_id', 'person');
		$this->belongsTo('Resourcetype', 'resource_type_id', 'resource_type');
		
		$this->validateUniquenessOf(array('name','person_id','usercompanyid'), 'There is already a template of that name.');
	}
}
?>