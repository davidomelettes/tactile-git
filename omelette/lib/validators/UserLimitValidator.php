<?php

/**
 * Responsible for checking that saving the user won't take the account past its user-limit
 */
class UserLimitValidator implements ModelValidation {

	protected $msg = "Unable to save User as it would take you past the limit of 'enabled' users for your account";
	
	/**
	 * 
	 * @param DataObject $do 
	 * @param Array &$errors 
	 * @see ModelValidation::test()
	 */
	function test(DataObject $do, array &$errors) {
		AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
		//if not enabled, then at worst we're keeping things the same
		if(!$do->is_enabled()) {
			return $do;
		}
		$before = DataObject::Construct($do->get_name());
		$before->load($do->getId());

		//if we're staying the same, then things are ok
		if($before->is_enabled()) {
			return $do;
		}
		
		//otherwise we need to check
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$checker = new LimitChecker(new UserUsageChecker($account), $account->getPlan());
		if(false !== $checker->isWithinLimit('user_limit')) {
			return $do;
		}
		$errors[] = $this->msg;
		return false;
	}
}

?>
