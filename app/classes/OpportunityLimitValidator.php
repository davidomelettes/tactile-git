<?php

/**
 *
 */
class OpportunityLimitValidator implements ModelValidation {

	protected $msg = 'Unable to save Opportunity as it will take you past the limit of open opportunities in your account';
	
	/**
	 * 
	 * @param DataObject $do 
	 * @param Array &$errors 
	 * @see ModelValidation::test()
	 */
	function test(DataObject $do, array &$errors) {
		
		AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
			
		$status_id = $do->status_id;
		if(empty($status_id)) {
			return $do;
		}
		if(!OpportunityStatus::StatusIsOpen($status_id)) {
			return $do;
		}
		
		$before = DataObject::Construct($do->get_name());
		$before->load($do->getId());
		if(Opportunitystatus::StatusIsOpen($before->status_id)) {
			return $do;
		}
				
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$checker = new LimitChecker(new OpportunityUsageChecker($account), $account->getPlan());
		if(false !== $checker->isWithinLimit('opportunity_limit')) {
			return $do;
		}
		$errors[] = $this->msg;
		return false;
	}
}

?>
