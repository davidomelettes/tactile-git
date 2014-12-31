<?php
class TaskCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Task');
			$this->_tablename="tasksoverview";
			
		$this->identifierField='name';
		}
	
		
		
}
?>
