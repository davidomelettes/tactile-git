<?php
class ProjectCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Project');
			$this->_tablename="projectsoverview";
			
		$this->identifierField='name';
		}
	
		
		
}
?>
