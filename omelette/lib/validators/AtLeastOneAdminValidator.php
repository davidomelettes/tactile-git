<?php
/**
 *
 * @author gj
 */
class AtLeastOneAdminValidator implements ModelValidation {
	
	protected $msg = 'You must leave your account with at least one admin user';
	
	public function __construct($msg=null) {
		if($msg!==null) {
			$this->msg = $msg;
		}
	}
	
	public function test(DataObject $do,Array &$errors) {
		$db = DB::Instance();
		if($do->is_admin!=='true') {
			$query = 'SELECT count(*) FROM omelette_useroverview
				 WHERE is_admin AND usercompanyid='.$db->qstr(EGS::getCompanyId()).' AND username!='.$db->qstr($do->getRawUsername());
			$num_admins = $db->GetOne($query);
			if((int)$num_admins<1) {
				$errors[] = $this->msg;
				return false;
			}		
		}
		return $do;
	}
	
}
?>