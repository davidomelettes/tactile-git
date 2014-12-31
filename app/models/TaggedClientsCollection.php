<?php
/**
 *
 * @author gj
 */
class TaggedOrganisationsCollection extends DataObjectCollection {
	
	
	public function __construct() {
		parent::__construct(new Company());
		$this->_tablename="tagged_companiesoverview";
	}
	
	function load($sh,$c_query=null) {
			$db=DB::Instance();
			$qb=new QueryBuilder($db,$this->_templateobject);
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
						$cc2->add(new Constraint('id','=',EGS_COMPANY_ID),'OR');
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
