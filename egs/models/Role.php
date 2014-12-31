<?php
class Role extends DataObject {

	function __construct() {
		$this->defaultDisplayFields = array('name'=>'Permission Name','description'=>'Description');
		parent::__construct('roles');
		$this->idField='id';
		$this->validateUniquenessOf('name');
		$this->identifierField='name';
		$this->orderby = 'name';
		
	}

	public function setPermissions($permission_ids) {
		$db = DB::Instance();
		$db->StartTrans();
		$query="delete from haspermission where roleid=".$db->qstr($this->id);
		$db->Execute($query);
		$errors=array();
		foreach($permission_ids as $id) {
			if(empty($id)) {
				continue;
			}
			$permission = DataObject::Factory(array('roleid'=>$this->id, 'permissionsid'=>$id), $errors, 'HasPermission');
			$permission->save();
		}
		return $db->CompleteTrans();
	}
	
	public function setAdmin($module_ids) {
		$db = DB::Instance();
		$db->StartTrans();
		$query = "delete from module_admins where role_id=".$db->qstr($this->id);
		$db->Execute($query);
		foreach ($module_ids as $key=>$admin) {
			$query = "insert into module_admins(role_id,module_name) values (".$db->qstr($this->id).",".$db->qstr($key).")";
			$db->Execute($query);
		}
		return $db->CompleteTrans();
	}
}
?>
