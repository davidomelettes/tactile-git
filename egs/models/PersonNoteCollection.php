<?php
class PersonNoteCollection extends DataObjectCollection {

	function __construct() {
		parent::__construct('PersonNote');
		$this->_tablename='person_notesoverview';
	}
}	
?>