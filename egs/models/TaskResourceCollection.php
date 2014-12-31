<?php
class TaskResourceCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('TaskResource');
			$this->_tablename = 'task_resources_overview';
			
		}
	
		
		
}
?>
