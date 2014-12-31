<?php

/**
 * Responsible for checking the number of enabled users for a particular account
 * 
 * @author gj
 */
class UserUsageChecker extends UsageCheckerAbstract {

	public function calculateUsage() {
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM users u 
			JOIN user_company_access uca ON (u.username=uca.username)
			WHERE u.enabled AND uca.organisation_id = '.$db->qstr($this->account->organisation_id);
		$count = $db->GetOne($query);
		return $count;
	}
}

?>
