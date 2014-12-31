<?php
class Omelette_User extends User {
	protected $defaultDisplayFields = array('username','person','enabled','is_admin');
	
	/**
	 * Contains any looked-up 'enabled' queries to make things a bit quicker
	 * - this can be filled by calling Omelette_User::FillEnabledCache()
	 * @var Array
	 */
	protected static $enabled_cache = array();
	
	public function __construct() {
		parent::__construct();
		$this->addValidator(new AtLeastOneAdminValidator());
		$this->getField('last_login')->blockValidator('DateValidator');
		$this->getField('terms_agreed')->blockValidator('DateValidator');
		
		$this->setNotSettable('last_login');
		$this->setNotSettable('terms_agreed');
		
		$this->assignAutoHandler('password',new Omelette_PasswordHandler());
		
		$this->addValidator(new UserLimitValidator('user_limit'));
		$this->getField('username')->addValidator(new UsernameValidator());
	}
	
	/**
	 * Intercepts getAll to firstly add the enabled=true constraint, and then modify the returned results
	 * to strip the userspace part
	 *
	 * @param ConstraintChain optional $cc
	 * @return Array
	 */
	function getAll($cc = null) {
		if(is_null($cc)) {
			$cc = new ConstraintChain();
		}
		$cc->add(new Constraint('enabled','=',true));
		$all = parent::getAll($cc);
		$new_all=array();
		foreach($all as $key=>$val) {
			$new_all[$key] = str_replace('//'.Omelette::getUserSpace(),'',$val);
		}
		return $new_all;
	}
	
	function __get($var) {
		$val = parent::__get($var);
		if($var=='username') {
			$val=str_replace('//'.Omelette::getUserSpace(),'',$val);
		}
		return $val;
	}
	
	function getRawUsername() {
		return $this->username.'//'.Omelette::getUserSpace();
	}
	
	public static function getUserRole($user) {
		$role = new Role();
		if(is_string($user)) {
			$exists=$role->loadBy('name',$user);
		}
		else {
			$exists=$role->loadBy('name',$user->getRawUsername());
		}
		if($exists===false) {
			$role_data=array('name'=>$user->getRawUsername());
			$errors=array();
			$role = DataObject::Factory($role_data,$errors,'Role');
			$role->save();
		}
		return $role;
	}
	
	public static function setRoles($user,$role_ids=array()) {
		$role_ids[] = Omelette::getUserSpaceRole()->id;
		$role_ids[] = Omelette_User::getUserRole($user)->id;
		return parent::setRoles($user->getRawUsername(),$role_ids);
	}
	
	public function load($id) {
		$id = str_replace('//'.Omelette::getUserSpace(),'',$id).'//'.Omelette::getUserSpace();
		return parent::load($id);
	}
	
	public function generateDropBoxKey(){
		$characters = str_split('abcdefghijkmnopqrstuvwxyz23456789');
		shuffle($characters);
		$length=12;
		$result = false;
		$db = DB::Instance();
		while($result === false){
			$dropboxkey = '';
			$dropboxkey = substr(join($characters),0,$length);
			$query = 'select count(*) from users where dropboxkey = \'' . $dropboxkey . '\'';
			if($db->getOne($query) < '1'){
				$result = true;	
			}
		}
		return $dropboxkey;
	}
	
	public function generateWebKey($salt='') {
		$result = false;
		$db = DB::Instance();
		while ($result === false) {
			$key = md5($salt . microtime());
			// Ensure this key does not exist already
			$query = "select count(*) from users where webkey = '" . $key . "'";
			if ($db->getOne($query) < 1) {
				$result = true;
			}
		}
		
		return $key;
	}
	
	public static function generateApiToken($salt='') {
		$db = DB::Instance();
		$exists = true;
		while ($exists) {
			$token = sha1($salt . microtime());
			$sql = "SELECT count(*) FROM users where api_token = " . $db->qstr($token);
			if ($db->getOne($sql) < 1) {
				$exists = false;
			}
		}
		return $token;
	}
	
	function save($debug=false) {
		$db = DB::Instance();
		$db->StartTrans();
		$this->username = $this->username.'//'.Omelette::getUserSpace();
		$success = parent::save($debug);
		if($success===false) {
			$db->FailTrans();
			$db->CompleteTrans();
			return false;
		}
		
		$uca = array(
			'username'=>$this->getRawUsername(),
			'organisation_id'=>EGS::getCompanyId(),
			'enabled'=>true
		);
		$success=$db->Replace('user_company_access',$uca,array('username','organisation_id'),true);
		$db->CompleteTrans();
		return $success;
	}
	
	public function getEmail() {
		$db = DB::Instance();
		$query = 'SELECT e.contact AS email FROM person_contact_methods e WHERE type=\'E\' AND main AND person_id = '.$db->qstr($this->person_id);
		$email = $db->GetOne($query);
		return $email;
	}
	
	/**
	 * Load a user given their email address
	 * Returns false if no user is returned, or if multiple users are returned
	 * 
	 * @param String $email
	 * @return Omelette_User
	 */
	public function loadByEmail($email) {
		$db = DB::Instance();
		$query = 'SELECT DISTINCT u.* FROM users u JOIN person_contact_methods pcm 
				ON (u.person_id=pcm.person_id AND type=\'E\' AND lower(contact)='.$db->qstr(strtolower($email)).' AND u.username LIKE '.$db->qstr('%//'.Omelette::getUserSpace()).')';
		$rows = $db->GetArray($query);
		
		if(!is_array($rows) || count($rows)!=1) {
			return false;
		}
		$this->_data=$rows[0];
		$user = $this->load($rows[0]['username']);
		if($user===false) {
			return false;
		}
		return $user;
	}
	
	/**
	 * -over-ridden so as to use getRawUsername()
	 * Set, and optionally generate (default), a password for the User
	 * Passwords are between 6 and 8 characters, and are purely alphanumeric
	 * The function returns the password (unhashed)
	 * @return string
	 */
	public function setPassword($password=null) {
		if($password===null||$password=='') {
			$characters = str_split('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789');
			shuffle($characters);
			$password = '';
			$length=mt_rand(6,8);
			$password = substr(join($characters),0,$length);
		}
		self::updatePassword($password,$this->getRawUsername());
		$this->setRawPassword($password);
		return $password;
	}
	
	/**
	 * Returns true iff the supplied username corresponds to an 'enabled' user
	 * - this function caches within a request
	 * 
	 * @param String $username
	 * @return Boolean
	 */
	public static function UsernameIsEnabled($username) {
		if(!isset(self::$enabled_cache[$username])) {
			$user = new Omelette_User();
			$user->load($username);
			self::$enabled_cache[$username] = $user->is_enabled(); 
		}
		return self::$enabled_cache[$username];
	}
	
	/**
	 * Returns true iff the user is enabled
	 *
	 * @return Boolean
	 */
	public function is_enabled() {
		return $this->enabled == 't' || $this->enabled == 'true';
	}
	
	public function is_admin() {
		return $this->is_admin == 't' || $this->enabled == 'true';
	}
	
	public static function FillUserCache() {
		$db = DB::Instance();
		$query = 'SELECT username, enabled FROM useroverview WHERE usercompanyid='.$db->qstr(EGS::getCompanyId());
		self::$enabled_cache = $db->GetAssoc($query);
	}
	
	public static function Clean($username) {
		return array_shift(explode('//',$username));
	}

	public function canDelete() {
		return false;
	}
	
	function get_name() {
		return 'User';
	}
	
	public function hasRole($role) {
		$hr = new HasRoleCollection();
		$sh = new SearchHandler($hr, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('username', '=', $this->getRawUsername()));
		$hr->load($sh);
		return (FALSE !== $hr->contains('roleid', $role->id));
	}
	
	public function getDefaultPermissions($type) {
		switch ($type) {
			case 'read':
			case 'write':
				$permissions = Omelette_Magic::getValue($type.'_permissions', $this->getRawUsername(), 'everyone');
				break;
			default:
				throw new Exception('Not a permission type: ' . $type);
		}
		
		switch ($permissions) {
			case 'everyone':
			case 'private':
				return $permissions;
				break;
			default: {
				$owner_role_id = CurrentlyLoggedInUser::getUserRole(EGS::getUsername())->id;
				$role_ids = array($owner_role_id); // Owner must be able to read/write by default
				$ids = preg_split('/,/', $permissions);
				
				if ($type == 'read') {
					// Writers must also have read access
					$write_permissions = Omelette_Magic::getValue('write_permissions', $this->getRawUsername(), 'everyone');
					switch ($write_permissions) {
						case 'everyone':
							// Everyone can read
							return $this->getDefaultPermissions('write');
							break;
						case 'private':
							break;
						default:
							$write_role_ids = preg_split('/,/', $write_permissions);
							$role_ids = array_unique(array_merge($write_role_ids, $role_ids));
					}
				}
				
				foreach ($ids as $id) {
					$role = new Omelette_Role();
					if (FALSE !== $role->load($id)) {
						$role_ids[] = $id;
					}
				}
				return array_unique($role_ids);
			}
		}
	}
	
	public function getDefaultPermissionsString($type, $html=false) {
		switch ($type) {
			case 'read':
				$string = $html ? '<span class="read">Viewable</span> ' : 'Viewable ';
				break;
			case 'write':
				$string = $html ? '<span class="write">Editable</span> ' : 'Editable ';
				break;
			default:
				throw new Exception('Not a permission type: ' . $type);
		}
		
		$permissions = $this->getDefaultPermissions($type);
		switch ($permissions) {
			case 'everyone':
				$string .= ($html ? 'by everyone' : '<span class="permission">by everyone</span>'); 
				break;
			case 'private':
				$string .= ($html ? 'only by you' : '<span class="permission">only by you</span>');
				break;
			default:
				$roles = array();
				
				$string .= 'by ';
				$and_you = false;
				foreach ($permissions as $role_id) {
					$role_do = new Omelette_Role();
					if (FALSE !== $role_do->load($role_id)) {
						if ($role_do->name !== $this->getRawUsername()) {
							if (preg_match('/(.*)\/\//', $role_do->name, $matches)) {
								$role = $matches[1];
							} else {
								$role = $role_do->name;
							}
							$roles[] = $role;
						} else {
							$and_you = true;
						}
					}
				}
				$string .= implode(', ', $roles);
				if ($and_you) {
					$string .= ' and you';
				}
				break;
		}
		
		return $string;
	}
	
	public function hasFixedPermissions() {
		return Omelette_Magic::getAsBoolean('permissions_fixed', $this->getRawUsername(), 't', 'f');
	}
	
	public function delete() {
		return false;
	}
}
