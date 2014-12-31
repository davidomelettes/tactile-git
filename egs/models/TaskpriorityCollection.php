<?php
class TaskpriorityCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Taskpriority');
			$this->_tablename="task_prioritiesoverview";
			
		$this->identifierField='name';
		}
	
		
		
}
?>
