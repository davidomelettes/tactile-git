<?php
/**
 * Form-processing helper for password changing, to reduce duplicate controller code
 *
 * @author gj
 */
class PasswordChanger {
	
	/**
	 * Change the password for a user, providing an array
	 * containing new_password, current_password and new_password_again keys
	 *
	 * @param CurrentlyLoggedInUser $user
	 * @param Array $form_data
	 * @return Boolean
	 */
	function changePassword($user, $form_data) {
		$flash = Flash::Instance();
		if(!empty($form_data['new_password'])) {
			if(empty($form_data['current_password'])) {
				$flash->addError('To change your password, first enter your current password');
			}
			else {
				if($user->password!==md5($form_data['current_password'])) {
					$flash->addError('The value you entered for \'Current Password\' is incorrect, try again');
				}
				else {
					if($form_data['new_password']!==$form_data['new_password_again']) {
						$flash->addError('The two new passwords you entered don\'t match, try again');
					}
				}
			}
			if(!$flash->hasErrors()) {
				$user_base = $user->getModel();
				$user_base->password = md5($form_data['new_password']);
				if($user_base->save()!==false) {
					RememberedUser::destroyAllMemories($user->getRawUsername());
					$flash->addMessage('Password changed successfully');
				}
				return true;
			}
			return false;
		} 
	}	
}
?>