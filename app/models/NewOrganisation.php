<?php

class NewOrganisation extends DataObject {
	
	public function __construct() {
		parent::__construct('organisations');
		$this->assignAutoHandler('accountnumber',new AccountNumberHandler(true));
	}
	
	/**
	 * Creates a new System Company for tactile
	 * @param TactileAccount $account
	 * @return NewCompany
	 */
	public static function create(TactileAccount $account) {
		$org_data = array(
			'name' => $account->company
		);
		$errors = array();
		$organisation = DataObject::Factory($org_data, $errors, 'NewOrganisation');
		if ($organisation === false || $organisation->save()===false) {
			return false;
		}
		
		$sys = new SystemCompany();
		$sys->organisation_id = $organisation->id;
		$sys->enabled = 't';
		$sys->save();
		
		return $organisation;
	}
	
	/**
	 * Generates a valid account number for a company
	 * - this is susceptible to a race condition
	 * @return String
	 */
	public function createAccountNumber() {
		$companyname = $this->name;
		// Make an acronym based on the name
		$letters=array();
		$words=explode(' ', $companyname);
		$len=1;
		if(count($words)<3) $len=2;
		foreach($words as $word) {
			$word = (substr($word, 0, $len));
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
	
	/**
	 * Returns true iff the supplied account number doesn't exist in the database
	 * @param String $testaccnum The account number to be tested
	 * @return Boolean
	 */
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
	
	public function addUser(NewUser $user) {
		$uca = new UserCompanyAccess();
		$uca->username = $user->username;
		$uca->organisation_id = $this->id;
		$uca->enabled = 't';
		return $uca->save();
	}
	
	public function giveEditAccess(Role $role) {
		$access = new CompanyRoleAccess();
		$access->organisation_id = $this->id;
		$access->roleid = $role->id; 
		$access->read='t';
		$access->write='t';
		return $access->save();
	}
	
	public function giveReadAccess(Role $role) {
		$access = new CompanyRoleAccess();
		$access->organisation_id = $this->id;
		$access->roleid = $role->id; 
		$access->read='t';
		$access->write='f';
		return $access->save();
	}
}
