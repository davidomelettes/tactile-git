<?php
class HasPermission extends DataObject {

	function __construct() {
		$this->defaultDisplayFields = array('roleid'=>'Role ID','permissionsid'=>'Permission');
		parent::__construct('haspermission');
		$this->idField='id';
		$this->identifierField='roleid';
		
 		$this->belongsTo('Permission', 'permissionsid', 'role_permissionsid');
 		$this->belongsTo('Role', 'roleid', 'role_roleid'); 

	}


}
?>
