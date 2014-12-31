<?php
class ProjectNoteCollection extends DataObjectCollection {

	function __construct() {
		parent::__construct('ProjectNote');
		$this->_tablename='project_notesoverview';
	}
}	
?>
