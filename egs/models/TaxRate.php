<?php
class TaxRate extends DataObject {

	function __construct() {
		parent::__construct('taxrates');
		$this->idField='id';
		
		
 		$this->validateUniquenessOf('taxrate'); 
		$this->identifierField = 'description';
	}


}
?>
