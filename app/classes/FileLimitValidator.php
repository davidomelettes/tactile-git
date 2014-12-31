<?php

/**
 *
 */
class FileLimitValidator implements ModelValidation {

	protected $msg = 'Uploading that file will take you over the file-space limit of your account';
	
	/**
	 * 
	 * @param DataObject $do 
	 * @param Array &$errors 
	 * @see ModelValidation::test()
	 */
	function test(DataObject $do, array &$errors) {
		AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
		
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$checker = new LimitChecker(new FileUsageChecker($account), $account->getPlan());
		if(false !== $checker->isWithinLimit('file_space', $do->size)) {
			return $do;
		}
		$errors[] = $this->msg;
		return false;
	}
}

?>
