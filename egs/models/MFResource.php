<?php
class MFResource extends DataObject {

	function __construct() {
		parent::__construct('mf_resources');
		$this->idField='id';
		
		$this->identifierField='resource_code || \'- \' ||description';
 		$this->validateUniquenessOf('resource_code'); 

	}


}
?>
