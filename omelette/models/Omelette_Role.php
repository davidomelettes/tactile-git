<?php
class Omelette_Role extends Role {

	public function __construct() {
		parent::__construct();
		$this->getField('name')->setFormatter(new OmeletteRoleFormatter());
		//$this->getField('name')->addValidator(new AlphaNumericValidator());
	}

	function getAll() {
		$all = parent::getAll();
		foreach($all as $key=>$val) {
			if(false!==(strpos($val,('//'.Omelette::getUserSpace())))) {
			//if($val=='//'.USER_SPACE||$val==EGS_USERNAME) {
				unset($all[$key]);
				continue;
			}
			$all[$key] = str_replace('//'.Omelette::getUserSpace(),'',$val);
		}
		return $all;
	}
	
	public static function getRolesAndUsers($exclude_current=false) {
		$db = DB::Instance();
		$query = 'SELECT id, name FROM roles WHERE usercompanyid = '.$db->qstr(EGS::getCompanyId());
		$roles = $db->GetAssoc($query);
		foreach($roles as $id=>$role) {
			if($exclude_current&&$role==EGS::getUsername()) {
				unset($roles[$id]);
			}
			else {
				$roles[$id] = str_replace('//'.Omelette::getUserSpace(),'',$role);
			}
			if(empty($roles[$id])) {
				unset($roles[$id]);
			}
		}
		return $roles;
	}
	
	public function setUsernames($usernames=array()) {
		$db = DB::Instance();
		$db->StartTrans();
		$query = 'DELETE FROM hasrole WHERE roleid='.$db->qstr($this->id);
		$success = $db->Execute($query);
		if($success===false) {
			return false;
		}
		$success = $this->addUsernames($usernames);
		if($success===false) {
			return false;
		}
		$db->CompleteTrans();
		return true;
	}
	
	public function addUsernames($usernames) {
		$data = array('roleid'=>$this->id);
		$errors=array();
		foreach($usernames as $username) {
			$data['username'] = $username;
			$hasrole = DataObject::Factory($data,$errors,'HasRole');
			if($hasrole===false || $hasrole->save()===false) {
				return false;
			}
		}
		return true;
	}
	
	public function getUsernames() {
		$db = DB::Instance();
		$query = 'SELECT u.username FROM users u JOIN hasrole hr ON (u.username=hr.username) 
			WHERE hr.roleid='.$db->qstr($this->id);
		$rows = $db->GetCol($query);
		return $rows;
	}
	
	public function getMembers() {
		$db = DB::Instance();
		$query = 'SELECT u.* FROM useroverview u JOIN hasrole hr ON (u.username=hr.username) 
			WHERE hr.roleid='.$db->qstr($this->id);
		$rows = $db->GetArray($query);
		$users = new UserCollection();
		foreach($rows as $user_data) {
			$user = DataObject::Construct('User');
			$user->_data = $user_data;
			$user->load($user_data['username']);
			$users->add($user);
		}
		return $users;
	}
	
	public function get_name() {
		return 'Role';
	}
	
}
?>