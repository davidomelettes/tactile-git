<?php
class TaskNoteCollection extends DataObjectCollection {

	function __construct() {
		parent::__construct('TaskNote');
		$this->_tablename='task_notesoverview';
	}
}	
?>
