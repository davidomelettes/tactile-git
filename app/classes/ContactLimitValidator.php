<?php

/**
 *
 */
class ContactLimitValidator implements ModelValidation {

	protected $msg = 'Unable to save as it will take you past the limit of contact (companies & people) in your account';
	
	/**
	 * 
	 * @param DataObject $do 
	 * @param Array &$errors 
	 * @see ModelValidation::test()
	 */
	function test(DataObject $do, array &$errors) {
		AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
		
		//we only need to check if we're adding something new, editing will pass
		$before = DataObject::Construct($do->get_name());
		$before = $before->load($do->getId());

		if($before !==false) {
			return $do;
		}
		
		//otherwise we need to check
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$checker = new LimitChecker(new ContactUsageChecker($account), $account->getPlan());
		if(false !== $checker->isWithinLimit('contact_limit')) {
			return $do;
		}
		$errors[] = $this->msg;
		return false;	
	}
}

?>
