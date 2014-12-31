<?php
class ProjectIssueCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('ProjectIssue');
			$this->_tablename="project_issuesoverview";
			
		}
	
		
		
}
?>
