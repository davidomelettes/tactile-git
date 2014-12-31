<?php

/**
 *
 */
class LimitChecker {

	
	/**
	 * Constructor
	 * 
	 * @param UsageCheckerAbstract $checker
	 * @param AccountPlan $plan
	 */
	function __construct(UsageCheckerAbstract $checker, $plan) {
		$this->checker = $checker;
		$this->plan = $plan;
	}
	
	/**
	 * Returns true iff adding $to_be_added new things will take the account over the plan's limit for the $criteria
	 *
	 * @param String $criteria The plan-property to be checked against
	 * @param Int optional $to_be_added The number of new things that are going to be added (Files for example will be in bytes, not units)
	 * @return Boolean
	 */
	public function isWithinLimit($criteria, $to_be_added = 1) {
		$allowance = $this->plan->$criteria;
		if($allowance==0) {
			return true;
		}
		$usage = $this->checker->getUsage();
		
		return $allowance - $usage >= $to_be_added;
	}
}

?>
