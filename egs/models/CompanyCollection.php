<?php
class CompanyCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct($do='Company') {
			parent::__construct($do);
		$this->_tablename="companyoverview";
			
		$this->identifier='name';
		$this->identifierField='name';
		}

		function load($sh,$c_query=null) {
			$db=DB::Instance();
			$qb=new QueryBuilder($db,$this->_templateobject);
			if($sh instanceof SearchHandler) {
				if ($this->_templateobject->isAccessControlled()) {
 					if(isModuleAdmin()) {
						$cc = new ConstraintChain();
						$cc->add(new Constraint('usercompanyid','=',EGS::getCompanyId()));
						$cc->add(new Constraint('id','=',EGS::getCompanyId()),'OR');
						$sh->addConstraintChain($cc);
						$qb->setDistinct();
					}
					else {
						$cc = new ConstraintChain();
						$cc->add(new Constraint('usernameaccess', '=', EGS::getUsername()));
						$cc->add(new Constraint('owner','=', EGS::getUsername()),'OR');
						$cc2 = new ConstraintChain();
						$cc2->add(new Constraint('usercompanyid','=',EGS::getCompanyId()));
						$cc2->add(new Constraint('id','=',EGS::getCompanyId()),'OR');
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
