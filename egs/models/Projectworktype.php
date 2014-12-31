<?php
class Projectworktype extends DataObject {

	function __construct() {
		parent::__construct('project_work_types');
		$this->idField='id';
		
		$this->identifierField='title';
 		$this->actsAsTree('parent_id'); 

	}


}
?>
