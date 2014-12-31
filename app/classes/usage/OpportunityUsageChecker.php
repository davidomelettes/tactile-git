<?php

/**
 *
 */
class OpportunityUsageChecker extends UsageCheckerAbstract {

	/**
	 * 
	 * @see UsageCheckerAbstract::getUsage()
	 */
	public function calculateUsage() {
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM opportunities o
			WHERE o.usercompanyid='.$db->qstr($this->account->organisation_id) . '
			AND NOT archived';
		$count = $db->GetOne($query);
		return $count;
	}
}
