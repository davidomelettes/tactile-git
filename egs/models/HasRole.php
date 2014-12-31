<?php
class HasRole extends DataObject {

	function __construct() {
		$this->defaultDisplayFields = array('roleid'=>'Role ID','username'=>'Username');
		parent::__construct('hasrole');
		$this->idField='id';
		
		$this->identifierField='roleid';
		
 		$this->belongsTo('Role', 'roleid', 'roles_roleid');
 		$this->belongsTo('User', 'username', 'users_username'); 

	}


}
?>
