<?php
class ProjectIssueStatusCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('ProjectIssueStatus');
			//$this->_tablename="project_issue_statusesoverview";
			
		}
	
		
		
}
?>
