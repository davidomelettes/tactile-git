<?php

class AccessObject{

	public $id;
	public $permissions;
	public $companyPermissions;
	public $tree=array();
	public $allPermissions=null;
	public $checked;
	public $roles=array();
	

	private function __construct($username) {
		if(!defined('EGS_COMPANY_ID'))
			return false;
		$this->id = $username;
		if(!$this->load() || isset($_GET['companyselector'])) {
			$this->getAllPermissions();
			$this->setRoles();
			$this->setPermissions();	
		}
	}

	public static function &Instance($username=null) {
		static $accessobject;
		if($accessobject==null) {
			if($username==null) {
				throw new Exception('AccessObject needs a username, at least on the first access');
			}
			$accessobject=new AccessObject($username);		
		}
		return $accessobject;
	}

	public function save() {
		$_SESSION['permissions'] = serialize($this);
	}

	public function load() {
		if(!isset($_SESSION['permissions'])) {
			return false;
		}
		$access = unserialize($_SESSION['permissions']);
		if($this->id !== $access->id) {
			return false;
		}

		$this->permissions = $access->permissions;
		$this->companyPermissions = $access->companyPermissions;
		$this->tree = $access->tree;
		$this->allPermissions = $access->allPermissions;
		$this->checked = $access->checked;
		$this->roles = $access->roles;
		return true;

	}

		

	/**
	 * Sets the list of permissions from the database
	 * 
	 * @return	boolean	if the function correctly gets the list of permissions it returns true else false
 	 *
	 * @todo	this function
	 */
	public function setPermissions() {
		$db =&DB::Instance();

		$super =false;
		
		//someone with no roles can't have access to anything
		if(empty($this->roles)) {
			return false;
		}
		
		//put together the query that gets the permissions that the different roles might have access to
		$query='SELECT DISTINCT p.* FROM permissions p JOIN haspermission hp ON (p.id=hp.permissionsid) LEFT JOIN roles r ON (hp.roleid = r.id) WHERE';
		$query.=' r.usercompanyid=' . EGS_COMPANY_ID . ' AND (0=1';
		foreach($this->roles as $role) {
			$query.=' OR ( hp.roleid = '.$role.') ';
		}
		$query.=') ORDER BY position, permission';
		$user_permissions = $db->Execute($query) or die($db->ErrorMsg());
		
		$mod_permissions=array();
		$permissions=array();
		foreach($user_permissions as $permission) {
			if($permission['type']=='m')
				$mod_permissions[$permission['permission']]=$permission;
			else
				$permissions[$permission['permission']]=$permission;
		}
		//we need to make sure that roles haven't been given access to things that the company doesn't have access to
		$query='SELECT co.* FROM companypermissionsoverview co JOIN permissions p ON (co.permissionid=p.id) WHERE usercompanyid='.$db->qstr(EGS_COMPANY_ID).' ORDER BY position';
		$company_permissions = $db->Execute($query) or die($db->ErrorMsg);
		$this->permissions=array();
		
		foreach($company_permissions as $permission) {
			if(isset($mod_permissions[$permission['permission']])) {
				$this->permissions[$permission['permission']]=$mod_permissions[$permission['permission']];
			}
		}
		$this->permissions=$this->permissions+$permissions;
		$this->tree = $this->getPermissionTree($this->permissions);
		$sort = false;
		if(isset($this->allPermissions)) {
			if(is_array($this->tree)) {
				foreach($this->tree as $key=>$item) {
					if(!isset($this->tree[$key]['title']) || $this->tree[$key]['title']=='') {
						$sort = true;
						foreach($this->allPermissions as $per) {
							if($per['name'] == $item['name']) {
								$this->tree[$key]['title']=$per['title'];
								$this->tree[$key]['position']=$per['position'];
								$this->tree[$key]['display']=$per['display'];
							}
						}
					}
				}
			}
			if($sort) {
				usort($this->tree, array('PermissionTree','compare'));
			}
			
		}
		return true;
	}

	/**
	 * Get the list of permissions from the database
	 * 
	 * @return	boolean	if the function correctly gets the list of permissions it returns true else false
 	 *
	 * @todo	this function
	 */
	public function setRoles() {

		$db=&DB::Instance();
		$query= 'SELECT roleid FROM hasrole WHERE username='.$db->qstr($this->id);
		
		$results = $db->Execute($query);
	
		if(!$results) {
			return false;
		}
		$this->roles = array();
		foreach($results as $role) {
			$this->roles[$role['roleid']]=$role['roleid'];
		}
		
		return true;

	}



	/**
	 * @todo	actually check
	 *
	 */
	public function companyPermission($modules) {
		if(empty($modules)) {
			return false;
		}
		if(count($modules)>1) {
			$check = array_shift($modules);
			if ($this->checkCompanyPermission($check))
				return true;
			else
				foreach ($modules as $module) {
					$check = $check . '-'. $module;
					if ($this->checkCompanyPermission($check))
						return true;		
				}
		}
		else {
			$check = strtolower($modules[0]);
		}
		if($this->checkCompanyPermission($check)) {
			return true;
		}
		
		return false;
		
	}

	public function checkCompanyPermission($module) {

		if(empty($this->companyPermissions)) {
			return true;
		}
		foreach($this->companyPermissions as $key=>$comp) {

			if($comp['permission'] == $module) {
				return true;
			}
		}
		
		return false;
	}



	/**
	 * Check in the list of permission to see if this user has access to the requested action
	 * 
	 * @param	string	the name of the action to be checked
	 * @param	string	the controller name
	 * @param	string	the module name
	 * @return	boolean	if has permission return true else return false
 	 *
	 */
	public function hasPermission($modules, $controller='', $action='') {
		$key = md5(serialize($modules).$controller.$action);
		if(!defined('PRODUCTION')||PRODUCTION!==true||!isset($_SESSION[$key])) {
			if(!is_array($modules)) {
				$modules=array($modules);
			}
			$controller = str_replace('controller', '', strtolower($controller));
			if ( (empty($controller)&&empty($action)) || (strtolower($controller)=='index'&&empty($action)) ||  (strtolower($controller)=='index' && strtolower($action) == 'index')) {
				foreach ($this->tree as $treenode) {
					if ($treenode['name'] == $modules[0]) {
						return true;
					}
				}
			}
			if($modules[0] == 'dashboard' || $modules[0] =='login' || trim($modules[0])=='') {
				return true;
			}
			
			if($this->check('egs')) {
				return true;
			}
			if(!$this->companyPermission($modules)) {
				return false;
			}
			$check = '';
			foreach($modules as $module) {
				$check.=$module;
				if($this->check($check)) {
					return true;
				}
				$check.='-';
			}
			if($controller !== '') {
				
				//if($controller !=='index') {
					$check.=$controller;
					if($this->check($check)) {
						return true;
					}
					
					$check.='-';
				//}
			}
			$check.=strtolower($action);
			$_SESSION[$key]= $this->check($check);
		}
		return $_SESSION[$key];
	}

	//a shortcut for looping over modules checking permission
	//returns true if the user has access to _any_ of the modules
	public function hasPermissionAny($modules) {
		if(!is_array($modules)) {
			return true;
		}
		foreach($modules as $module) {
			if($this->hasPermission($module)) {
				return true;
			}
		}
		return false;
	}

	//returns true iff the user has access to _all_ of the modules
	public function hasPermissionAll($modules) {
		foreach($modules as $module) {
			if(!$this->hasPermission($module))
				return false;
		}
		return true;
	}

	function check($check) {

		//echo "checking $check";
		if(isset($this->permissions[$check])) {
		//	echo "- returning true<br/>\n";
			return true;
		}
		//echo "- returning false<br/>\n";
		return false;
	}


	function getPermissionTree($result) {
						
		$permissionTree = new PermissionTree();
		$tree = array();
		if(!isset($result) || empty($result)) {
			return false;
		}
		foreach($result as $permission) {	
			$explode = explode('-',$permission['permission']);
			$tree = $permissionTree->makeTree($tree, $explode, $permission);
		}
		usort($tree, array('PermissionTree','compare'));
		return $tree;
		
	}

		
	public function getAllPermissions() {
		$db =DB::Instance();
		$query='SELECT * FROM permissions order by permission';
		$extra = $db->Execute($query);
		$temp = array();
		$query = "SELECT * FROM companypermissionsoverview where usercompanyid =".$db->qstr(EGS_COMPANY_ID);
		$comper = $db->GetAssoc($query);
		$this->companyPermissions = $comper;
		$permissions = $this->getPermissionTree($extra);
		$this->allPermissions = array();
		foreach($permissions as $per) {
			if(isset($per['id'])&&isset($comper[$per['id']])) {
				$this->allPermissions[] = $per;
			}
		}
		return $this->allPermissions;
	}
	
	


}
?>
