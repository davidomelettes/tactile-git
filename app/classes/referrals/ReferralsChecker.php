<?php

/**
 * Base for referrals checking
 * 
 * @author jas
 */
class ReferralsChecker {

	/**
	 * The account to interrogate
	 *
	 * @var OmeletteAccount
	 */
	protected $account;
	
	/**
	 * The referrals, once calculated
	 *
	 * @var String
	 */
	protected $referrals;
	
	/**
	 * Constructor
	 * Takes a OmeletteAccount instance
	 * 
	 * @param OmeletteAccount $account
	 */
	function __construct(OmeletteAccount $account) {
		$this->account = $account;
	}
	
	public function getTotalReferrals() {
		if(!isset($this->referrals)) {
			$this->referrals['total'] = $this->calculateTotalReferrals();		
		}
		return $this->referrals['total'];
	}
	
	private function calculateTotalReferrals() {
		$db = DB::Instance();
		$query = 'SELECT count(id) FROM tactile_accounts acc WHERE signup_code='.$db->qstr($this->account->site_address);

		return $db->GetOne($query);
	}
	
	public function getFreeReferrals() {
		if(!isset($this->referrals['free'])) {
			$this->referrals['free'] = $this->calculateFreeReferrals();		
		}
		return $this->referrals['free'];
	}
	
	private function calculateFreeReferrals() {
		$db = DB::Instance();
		$query = 'SELECT count(acc.id) FROM tactile_accounts acc, account_plans plan WHERE plan.id=acc.current_plan_id AND plan.cost_per_month=0 AND acc.signup_code='.$db->qstr($this->account->site_address);

		return $db->GetOne($query);
	}
	
	public function getPaidReferrals() {
		if(!isset($this->referrals['paid'])) {
			$this->referrals['paid'] = $this->calculatePaidReferrals();		
		}
		return $this->referrals['paid'];
	}
	
	private function calculatePaidReferrals() {
		$db = DB::Instance();
		$query = 'SELECT count(acc.id) FROM tactile_accounts acc, account_plans plan WHERE plan.id=acc.current_plan_id AND plan.cost_per_month>0 AND acc.signup_code='.$db->qstr($this->account->site_address);

		return $db->GetOne($query);
	}
	
	public function getStatement() {
		if(!isset($this->referrals['statement'])) {
			$this->referrals['statement'] = $this->calculateStatement();		
		}
		return $this->referrals['statement'];
	}
	
	private function calculateStatement() {
		$db = DB::Instance();
		$query = 'SELECT date_part(\'year\', pay.created) AS year, date_part(\'month\', pay.created) AS month, floor(sum(pay.amount)*0.2) AS total FROM tactile_accounts acc, payment_records pay WHERE (pay.type=\'FULL\' OR pay.type=\'RELEASE\' OR pay.type=\'REPEAT\') AND pay.account_id=acc.id AND acc.signup_code='.$db->qstr($this->account->site_address).' GROUP BY date_part(\'year\', pay.created), date_part(\'month\', pay.created) ORDER BY year, month ';
		return $db->GetAll($query);
	}
}

?>

