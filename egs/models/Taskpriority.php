<?php
class Taskpriority extends DataObject {

	function __construct() {
		parent::__construct('task_priorities');
		$this->idField='id';
		
		$this->identifierField='name';
		
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Project', 'project_id', 'project'); 

	}


}
?>
