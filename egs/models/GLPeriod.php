<?php
class GLPeriod extends DataObject {

	function __construct() {
		parent::__construct('glperiods');
		$this->idField='id';
		$this->identifierField = 'year || \' - Period \' || period';	
		$this->orderby='period';
		$this->validateUniquenessOf(array('year','period'));
	}




}
?>
