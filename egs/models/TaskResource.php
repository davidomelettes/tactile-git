<?php
class TaskResource extends DataObject {

	protected $defaultDisplayFields = array('id','resource');

	function __construct() {
		parent::__construct('task_resources');
		$this->idField='id';
		
		$this->validateUniquenessOf(array('resource_id','task_id'),'You cannot duplicate a resource against a task');
 		$this->belongsTo('Resource', 'resource_id', 'resource');
 		$this->belongsTo('Task', 'task_id', 'task'); 
		$this->orderby = 'resource';
		$this->orderdir = 'asc';


	}


}
?>
