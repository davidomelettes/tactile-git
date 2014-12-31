<?php
class Resource extends DataObject {
protected $defaultDisplayFields = array('person','project_manager');
	function __construct() {
		parent::__construct('resources');
		$this->idField='id';
		$this->identifierField = 'person';
 		$this->validateUniquenessOf(array('person_id', 'project_id','usercompanyid'),'Resource already exists on this project');
 		$this->belongsTo('Person', 'person_id', 'person');
 		$this->belongsTo('Project', 'project_id', 'project');
		$this->belongsTo('Resourcetype', 'resource_type_id', 'resource_type');
		$this->orderby = 'person';
		$this->orderdir = 'asc';

	}


}
?>
