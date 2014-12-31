<?php

class TactileEmailAddress extends DataObject {

	public function __construct($tablename='tactile_email_addresses') {
		parent::__construct($tablename);
	}
	
	public function is_verified() {
		$when = $this->verified_at;
		return !empty($when);
	}
	
	public function verify($code) {
		if ($this->is_verfied()) {
			return true;
		}
		if ($this->verify_code === $code) {
			$this->send = 't';
			$this->verified_at = date('Y-m-d H:i:s');
			return $this->save();
		} else {
			return false;
		}
	}
	
	public function sendVerificationEmail() {
		if (!$this->is_verified()) {
			$user = CurrentlyLoggedInUser::Instance();
			$account = $user->getAccount();
			
			$verifyChars = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890');
			shuffle($verifyChars);
			$this->verify_code = implode('', array_slice($verifyChars, 0, 16));
			
			if (FALSE !== $this->save()) {
				$mail = new Omelette_Mail('verify_email');
				$mail->getView()->set('site_address', $account->site_address);
				$mail->getView()->set('email', $this);
				$mail->getMail()->addTo($this->email_address);
				$mail->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME);
				$mail->setSubject('Verify your Email Address');
				try {
					$mail->send();
				} catch (Zend_Mail_Transport_Exception $e) {
					return false;
				}
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function canSendWith() {
		$user = CurrentlyLoggedInUser::Instance();
		if (!$this->is_verified() || !$this->is_send()) {
			return false;
		}
		$role = new Role();
		if (FALSE === $role->load($this->role_id)) {
			return false;
		}
		if (!$user->getModel()->hasRole($role)) {
			return false;
		}
		return true;
	}
	
	public function canEdit() {
		$user = CurrentlyLoggedInUser::Instance();
		
		$role = new Role();
		$role->load($this->role_id);
		return $user->getModel()->hasRole($role);
	}
	
	public function isShared() {
		$role = new Role();
		$role->load($this->role_id);
		return $role->id === Omelette::getUserSpaceRole()->id; 
	}
	
}
