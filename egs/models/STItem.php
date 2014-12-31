<?php
class STItem extends DataObject {

	function __construct() {
		parent::__construct('st_items');
		$this->idField='id';
		
		$this->identifierField='item_code || \'- \' ||description';
 		$this->validateUniquenessOf('item_code'); 

	}


}
?>
