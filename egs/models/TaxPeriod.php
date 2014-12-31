<?php
class TaxPeriod extends DataObject {

	function __construct() {
		parent::__construct('taxperiods');
		$this->idField='id';
		$this->identifierField='description';
		$this->orderby='year';
		$this->validateUniquenessOf(array('year','period'));	
	}


}
?>
