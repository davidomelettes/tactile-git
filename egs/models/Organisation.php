<?php
if(file_exists(MODEL_ROOT.'CompanyLookups.php'))
	require_once MODEL_ROOT.'CompanyLookups.php';
else if(file_exists(CORE_MODEL_ROOT.'CompanyLookups.php'))
	require_once CORE_MODEL_ROOT.'CompanyLookups.php';
class Organisation extends DataObject {
	protected $defaultDisplayFields=array('name','accountnumber','town','phone','website');
	function __construct() {
		parent::__construct('organisations');
		$this->idField='id';
		$this->orderby='name';
		$this->identifier='name';
		$this->identifierField='name';

 		$this->validateUniquenessOf('accountnumber');
 		$this->belongsTo('User', 'owner', 'company_owner');
 		$this->belongsTo('User', 'assigned_to', 'company_assigned');
 		$this->belongsTo('User', 'alteredby', 'company_alteredby');
		$this->belongsTo('Organisation', 'parent_id', 'parent');
		$this->addValidator(new DistinctValidator(array('id','parent_id'), 'Account cannot be it\'s own parent'));
 		$this->actsAsTree('parent_id');
		$this->setParent();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','T'));
		$this->setAlias('phone','Organisationcontactmethod',$cc,'contact');
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','E'));
		$this->setAlias('email','Organisationcontactmethod',$cc,'contact');
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','F'));
		$this->setAlias('fax','Organisationcontactmethod',$cc,'contact');
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','W'));
		$this->setAlias('website','Organisationcontactmethod',$cc,'contact');
		$this->hasMany('Person','people');
		$this->hasMany('Opportunity','opportunities');
		$this->hasMany('Project','projects');
		$this->hasMany('Activity','activities');
		$this->hasMany('CompanyNote','notes');
		
		//$this->getField('website')->setFormatter(new URLFormatter());
		
		//if chosen, don't make accountnumber compulsory
		///*
		if(defined('EGS_USERNAME')) {
			/*$userPreferences = UserPreferences::instance(EGS_USERNAME);
			$autoGenerate = $userPreferences->getPreferenceValue('auto-account-numbering', 'contacts');
			if(!empty($autoGenerate) && $autoGenerate == 'on') {
				//$this->getField('accountnumber')->not_null=false;
				$this->assignAutoHandler('accountnumber',new AccountNumberHandler(true));
			}
			else {*/
				$this->getField('accountnumber')->setnotnull();
			//}
		}
		//*/
		$this->setAccessControlled(true);
	}

	public function createAccountNumber($companyname=null) {
		if (empty($companyname)) {
			$companyname = $this->name;
		}
		// Make an acronym based on the name
		$letters=array();
		$words = explode(' ', $companyname);
		$len = count($words) < 3 ? 2 : 1;
		foreach ($words as $word) {
			$word = mb_substr($word, 0, $len, "UTF-8");
			// Make sure we don't end up with non-ascii characters in accountnumbers
			$word = iconv("UTF-8", "ASCII//TRANSLIT", $word);
			array_push($letters, $word);
		}
		$accnum = strtoupper(implode($letters));
		// Now add a number to the end until an untaken one is found
		$i=1;
		$testaccnum=$accnum.sprintf("%02s",$i);
		while(!$this->isValidAccountNumber($testaccnum)) {
			$i++;
			$testaccnum=$accnum.sprintf("%02s",$i);
		}
		return $testaccnum;

	}

	public static function makeCompany() {
		if(defined('PRODUCTION')&&PRODUCTION&&HAS_APC) {
			if(false===($company=apc_fetch(FILE_ROOT.'company_blank'))) {
				$company =DataObject::Construct('Company');
				apc_store(FILE_ROOT.'company_blank',serialize($company));
			}
			else {
				$company = unserialize($company);
			}	
			return $company;
		}
		$company = DataObject::Construct('Company');
		return $company;
	}

	private function isValidAccountNumber($testaccnum) {
		$db = DB::instance();
		$query='SELECT COUNT(*) FROM organisations WHERE accountnumber = ' . $db->qstr($testaccnum) . ' AND usercompanyid = ' . $db->qstr(EGS::getCompanyId());
		$count = $db->GetOne($query);
		if ($count === "0") {
			return true;
		} else {
			return false;
		}
	}

	function getCount() {
		$db=&DB::Instance();
		$tablename=$this->_tablename;
		if ($this->isAccessControlled()) {
			$constraint=' WHERE ';
			$constraint.='usernameaccess='.$db->qstr(EGS_USERNAME);
			$constraint.= ' or owner='.$db->qstr(EGS_USERNAME);
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
	
	public function getPhoneNumbers() {
		return $this->getContactMethods('T');
	}
	
	private function getContactMethods($type) {
		$cms = new OrganisationcontactmethodCollection();
		$sh = new SearchHandler($cms,false);
		$sh->extract();
		$sh->addConstraint(new Constraint('type','=',$type));
		$sh->addConstraint(new Constraint('organisation_id','=',$this->id));
		$cms->load($sh);
		return $cms;
	}
	
	public function getFaxNumbers() {
		return $this->getContactMethods('F');
	}
	
	public function getEmailAddresses() {
		return $this->getContactMethods('E');
	}
	
	public static function checkAccess($type,$company) {
		if(!($type=='read'||$type=='write')) {
			return false;
		}
		if(empty($company)){
			return true;
		}
		if(!$company instanceof Organisation) {
			$id = $company;
			$company = DataObject::Construct('Organisation');
			$company->load($id);
		}
		
		if($company->owner==EGS::getUsername()) {
			return true;
		}
		
		$db = DB::Instance();
		$sql = "SELECT count(o.id) FROM organisations o
			JOIN organisation_roles orgr ON orgr.organisation_id = o.id
			JOIN hasrole hr ON hr.roleid = orgr.roleid
			WHERE hr.username = " . $db->qstr(EGS::getUsername()) . "
			AND orgr.$type = TRUE
			AND o.id = " . $db->qstr($company->id);
		$result = $db->getOne($sql);
		if ($result === FALSE) {
			throw new Exception('Check access query failed: ' .$db->errormsg());
		}
		return ($result>0);		
	}
	
}
?>
