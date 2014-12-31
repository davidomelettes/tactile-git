<?php

/**
 * Responsible for checking the number of companies and people owned by the account
 * 
 * @author gj
 */
class ContactUsageChecker extends UsageCheckerAbstract {

	public function calculateUsage() {
		$db = DB::Instance();
		$c_query = 'SELECT count(id) FROM organisations org WHERE usercompanyid='.$db->qstr($this->account->organisation_id);
		$companies = $db->GetOne($c_query);
		
		$p_query = 'SELECT count(id) FROM people p WHERE usercompanyid='.$db->qstr($this->account->organisation_id);
		$people = $db->GetOne($p_query);
		
		$total = $companies + $people;
		return $total;
	}
}

?>
