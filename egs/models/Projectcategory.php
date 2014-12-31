<?php
class Projectcategory extends DataObject {

	function __construct() {
		parent::__construct('project_categories');
		$this->idField='id';
		
		$this->identifierField='name';
		
 		$this->validateUniquenessOf('id'); 

	}


}
?>
