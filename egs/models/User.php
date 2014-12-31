<?php
class User extends DataObject {
	protected $defaultDisplayFields=array('person'=>'Person');
	protected $raw_password;
	function __construct() {
		parent::__construct('users');
		$this->idField='username';
		$this->identifierField='username';
		$this->orderby='username';
		$this->isHandled('lastcompanylogin');
		$this->belongsTo('Person','person_id','person');
		$this->validateUniquenessOf('person_id');
		$this->assignAutoHandler('password',new PasswordGenerationHandler());
	}
	
	public function isField($value, $depth=1)  {
		if ($value == 'usercompanyid') {
			return true;
		}
		else {
			return parent::isField($value,$depth);
		}
	}
	
	function getAll(ConstraintChain $cc=null,$ignore_tree=false) {
		$db=DB::Instance();
		$tablename=$this->_tablename;
		if (empty($cc)) {
			$cc = new ConstraintChain();
		}
		$collection_name='UserCollection';
		$coln = new $collection_name;
		$tablename=$coln->_tablename;
		$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		$query = 'SELECT '.$this->idField.', '.$this->getIdentifier().' FROM '.$tablename;
		$constraint = $cc->__toString();
		if ($constraint !== false) {
			$query .= ' WHERE '. $constraint;
		}
		$query .= ' ORDER BY username';
		$results=$db->GetAssoc($query);
		if($this->idField==$this->getIdentifier()) {
			foreach($results as $key=>$nothing) {
				$results[$key]=$key;
			}
		}
		return $results;
	}	
	
	public function load($clause, $override=false) {
		return parent::load($clause,true);
	}
	
	function loadBy($field,$value=null,$tablename=false) {
		$db=&DB::Instance();
		if($field instanceof SearchHandler) {
			$sh = $field;
			$sh->setLimit(1);
			$qb=new QueryBuilder($db);
			$query=$qb->select($sh->fields)
				->from($this->_tablename)
				->where($sh->constraints)
				->orderby($sh->orderby,$sh->orderdir)
				->limit($sh->perpage,$sh->offset)->__toString();
		}
		else {
			if($field instanceof ConstraintChain) {
				$where = $field->__toString();
			}
			elseif(!is_array($field)&&!is_array($value))
				$where=$field.'='.$db->qstr($value);
			elseif(!(is_array($field)&&is_array($value))) {
				throw new Exception('Error: $fieldname and $value must be of same type, array or string');
			}
			else {
				$where='1=1';
				for($i=0;$i<count($field);$i++) {
					if ((!$tablename) && (($this->getField($field[$i])->type == 'numeric') || (substr($this->getField($field[$i])->type,0,3) == 'int')) && ($value[$i] == ''))
						$where .= ' AND '.$field[$i].'=null';
					else
						$where.=' AND '.$field[$i].'='.$db->qstr($value[$i]);
				}

			}
			$where.=' AND usercompanyid='.$db->qstr(EGS::getCompanyId());
			$query='SELECT * FROM useroverview WHERE '.$where;
		}
		$row=$db->GetRow($query);
		if($row===false)
			die("Error in loadby: ".$db->ErrorMsg().$query);
		if(count($row)>0) {
			$this->_data=$row;
			return $this->load($row[$this->idField]);
		}
		return false;
	}
	
	function getCount() {
		$db=&DB::Instance();
		$tablename='useroverview';
		if ($this->isAccessControlled()) {
			if($constraint=='')
				$constraint=' WHERE ';
			else
				$constraint.=' AND ';
			$constraint.='usernameaccess='.$db->qstr(EGS_USERNAME);
			$collection_name=get_class($this).'Collection';
			$coln = new $collection_name;
			$tablename=$coln->_tablename;
		}
		if($this->isField('usercompanyid')) {
			if($constraint=='')
				$constraint=' WHERE ';
			else
				$constraint.=' AND ';
			$constraint.='usercompanyid='.$db->qstr(EGS_COMPANY_ID);
		}
		$query = 'SELECT count(*) FROM '.$tablename;
		
		if ($constraint <> '') {
			$query .= $constraint;
		}
		$count=$db->GetOne($query);
		return $count;
	}	
	
	/**
	 * @param $user mixed either a User model, or a username
	 * @param $roles array an array of role-ids
	 *
	 * Put a user into one or more roles
	 */
	public static function setRoles($user,$roles) {
		if(!$user instanceof User) {
			$username=$user;
			$user = new User();
			$user=$user->load($username);
			if($user===false) {
				return false;
			}
		}
		else {
			$username = $user->username;
		}
		$db=DB::Instance();
		$db->StartTrans();
		$query="delete from hasrole where username=".$db->qstr($username).' AND roleid IN (SELECT id FROM roles WHERE usercompanyid='.$db->qstr(EGS_COMPANY_ID).')';
		$db->Execute($query);
		$errors=array();
		foreach($roles as $role) {
			$ob = DataObject::Factory(array('roleid'=>$role, 'username'=>$username), $errors, 'HasRole');
			if($ob===false || false === $ob->save()) {
				$db->FailTrans();
				$db->CompleteTrans();
				return false;
			}
		}
		$db->CompleteTrans();
		return true;
	}
	
	
	/**
	 *[ @param $password string ]
	 * @return string
	 * Set, and optionally generate (default), a password for the User
	 * Passwords are between 6 and 8 characters, and are purely alphanumeric
	 * The function returns the password (unhashed)
	 * @see User;:setPassword()
	 */
	public function setPassword($password=null) {
		if($password===null||$password=='') {
			$characters = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9');
			shuffle($characters);
			$password = '';
			$length=mt_rand(6,8);
			$password = substr(join($characters),0,$length);
		}
		self::updatePassword($password,$this->username);
		return $password;
	}
	
	
	function setRawPassword($password) {
		$this->raw_password=$password;
	}
	
	function getRawPassword() {
		return $this->raw_password;
	}
	
	/**
	 * @param $password string
	 * @param $username string
	 * @return boolean
	 *
	 * Takes a username and a password, and updates the user's password accordingly.
	 * The password is md5'd before being inserted.
	 */
	public static function updatePassword($password,$username) {
		$db=DB::Instance();
		$user_data = array('username'=>$username,'password'=>md5($password));
		return($db->Replace('users',$user_data,'username',true)!==false);
	}
	
	public static function getOtherUsers() {
		$db=DB::Instance();
		$query = 'SELECT username, username AS val FROM useroverview WHERE username<>'.$db->qstr(EGS_USERNAME).' AND usercompanyid='.EGS_COMPANY_ID;
		return $db->GetAssoc($query);
	}
	
	public static function getPersonName($username) {
		$db = DB::Instance();
		$query = 'SELECT p.firstname || \' \' || p.surname FROM people p LEFT JOIN users u ON (u.person_id=p.id) WHERE u.username='.$db->qstr(EGS_USERNAME).' AND p.usercompanyid='.$db->qstr(EGS_COMPANY_ID);
		$name = $db->GetOne($query);
		return $name;
	}		
}
?>
