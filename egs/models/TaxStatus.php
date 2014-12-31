<?php
class TaxStatus extends DataObject {

	function __construct() {
		parent::__construct('tax_statuses');
		$this->idField='id';
		$this->identifierField = 'description';
		 

	}


}
?>
