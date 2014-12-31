<?php
class Person extends DataObject {
	protected $defaultDisplayFields=array('fullname'=>'Name','company'=>'Company','phone'=>'Phone','mobile'=>'Mobile','email'=>'Email');
	function __construct() {
		parent::__construct('people');
		$this->idField='id';

		$this->orderby='surname';

		$this->identifier='surname';
		$this->identifierField='firstname || \' \' || surname';
 		$this->belongsTo('Organisation', 'organisation_id', 'organisation');
 		$this->belongsTo('User', 'owner', 'person_owned_by');
 		$this->belongsTo('User', 'alteredby', 'last_altered_by');
 		$this->belongsTo('User', 'assigned_to', 'person_assigned_to');
		

 		$this->actsAsTree('reports_to');
		$this->belongsTo('Person','reports_to','person_reports_to');
		$this->setConcatenation('fullname',array('title','firstname','middlename','surname','suffix'));

		$this->hasMany('Opportunity','opportunities');
		$this->hasMany('Project','projects');
		$this->hasMany('Activity','activities');
		$this->hasMany('PersonNote','notes');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','T'));
		$this->setAlias('phone','Personcontactmethod',$cc,'contact');
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','E'));
		$this->setAlias('email','Personcontactmethod',$cc,'contact');
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','F'));
		$this->setAlias('fax','Personcontactmethod',$cc,'contact');
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$cc->add(new Constraint('type','=','M'));
		$this->setAlias('mobile','Personcontactmethod',$cc,'contact');
		
		$this->setAccessControlled(true);
		
		$this->getField('jobtitle')->tag=prettify('job_title');
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
		$cms = new PersoncontactmethodCollection();
		$sh = new SearchHandler($cms,false);
		$sh->extract();
		$sh->addConstraint(new Constraint('type','=',$type));
		$sh->addConstraint(new Constraint('person_id','=',$this->id));
		$cms->load($sh);
		return $cms;
	}
	
	public function getMobileNumbers() {
		return $this->getContactMethods('M');
	}
	
	public function getEmailAddresses() {
		return $this->getContactMethods('E');
	}

}
?>
