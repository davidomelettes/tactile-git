<?php
class MFDept extends DataObject {

	function __construct() {
		parent::__construct('mf_depts');
		$this->idField='id';
		
		$this->identifierField='dept_code || \'- \' ||dept';
 		$this->validateUniquenessOf('dept_code'); 

	}


}
?>
