<?php
class PersonCollection extends DataObjectCollection {
	
	public $field;
	
	function __construct() {
		parent::__construct('Person');
		$this->_tablename="personoverview";
		
		$this->identifier='surname';
		$this->identifierField='fullname';
	}
	
	function load($sh,$c_query=null) {
		debug_print_backtrace();
		$db=DB::Instance();
		$qb=new QueryBuilder($db,$this->_doname);
		if($sh instanceof SearchHandler) {
			if ($this->_templateobject->isAccessControlled()) {
				if(isModuleAdmin()) {
					$cc = new ConstraintChain();
					$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
					$cc->add(new Constraint('id','=',EGS_COMPANY_ID),'OR');
					$sh->addConstraintChain($cc);
					$qb->setDistinct();
				}
				else {
					$cc = new ConstraintChain();
					$cc->add(new Constraint('usernameaccess', '=', EGS_USERNAME));
					$cc->add(new Constraint('owner','=',EGS_USERNAME),'OR');
					$cc2 = new ConstraintChain();
					$cc2->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
					$sh->addConstraintChain($cc);
					$sh->addConstraintChain($cc2);
					$qb->setDistinct();						
				}
			}
			$this->sh = $sh;
		}
		$this->_load($sh,$qb,$c_query);
	}	
		
}
?>
