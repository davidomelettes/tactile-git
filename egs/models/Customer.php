<?php
class Customer extends DataObject {

	protected $defaultDisplayFields = array('person','username','created');

	function __construct() {
		parent::__construct('customers');
		$this->idField='id';
		$this->validateUniquenessOf(array('website_id','username'),'This username has already been taken.');
		$this->identifierField='username';
		$this->orderby='username';
		
 		$this->belongsTo('Person', 'person_id', 'person'); 
		$this->setNotEditable('password');
		$this->assignAutoHandler('password',new PasswordGenerationHandler());
		$this->isHash('additional');
	}

	public function converttouser() {
		$db = &DB::Instance();
		$query = "insert into users (username, password, person_id) values (".$db->qstr($this->username).",".$db->qstr($this->password).",{$this->person_id})";
		$db->Execute($query);
		return $this;
	}

	function __get($var) {
		if(!$this->isField($var,1)) {
			$person=new Person();
			if ($this->_loaded) {
				$person->load($this->person_id);
				return $person->$var;
			}
		}
		return parent::__get($var);
	}
	
	public function loadBy($one,$two=null,$three=false) {
		if (($two != null) && !is_array($one)) {
			$cc = new ConstraintChain();
			$cc->add(new Constraint($one,'=',$two));
			$cc->add(new Constraint('website_id','=',WEBSITE_ID));
			parent::loadBy($cc);
		}
		else
			parent::loadBy($one,$two,$three);		
	}
	
	function getAdditionalInformation() {
		$info=array();
		if(isset($this->hashes['additional'])&&is_array($this->hashes['additional'])) {
			foreach($this->hashes['additional'] as $key=>$hash) {
				$info[$key]=unserialize($hash);
			}
		}
		return $info;
	}

}
?>
