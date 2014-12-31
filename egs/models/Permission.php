<?php
class Permission extends DataObject {

	function __construct() {
	$this->defaultDisplayFields=array('permission'=>'permission', 'description'=>'description' );
		parent::__construct('permissions');
		$this->idField='id';
		
		$this->setEnum('type',array('a'=>'Action','m'=>'Module', 'c'=>'Controller'));
		$this->orderby='permission';
		$this->identifierField='permission';
		
 		$this->validateUniquenessOf('permission');
	}


}
?>
