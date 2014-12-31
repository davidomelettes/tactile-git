<?php
class WHStore extends DataObject {

	function __construct() {
		parent::__construct('wh_stores');
		$this->idField='id';
		$this->identifierField="store_code ||'-'|| description";		
		
 		$this->validateUniquenessOf('store_code'); 

	}


}
?>
