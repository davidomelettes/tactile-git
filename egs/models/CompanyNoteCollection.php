<?php
class CompanyNoteCollection extends DataObjectCollection {

	function __construct() {
		parent::__construct('CompanyNote');
		$this->_tablename='company_notesoverview';
	}
}	
?>