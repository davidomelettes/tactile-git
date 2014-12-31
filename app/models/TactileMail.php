<?php

class TactileMail extends DataObject {
	
	const MAX_ATTEMPTS = 3;
	
	const FREE_FOOTER = "\nSent via Tactile CRM (http://www.tactilecrm.com)";
	
	public function __construct($tablename='mail_queue_send') {
		parent::__construct($tablename);
	}
	
	public function send($action='dropbox') {
		if ($this->attempts >= self::MAX_ATTEMPTS) {
			return false;
		}
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$mail = new Zend_Mail('UTF-8');
		
		$from = new TactileEmailAddress();
		if (FALSE === $from->load($this->from_id)) {
			return false;
		}
		if (!$from->canSendWith()) {
			return false;
		}
		
		$mail->addTo($this->to_address)
			->addBcc($user->getDropboxAddress($action))
			->setFrom($from->email_address, $from->display_name)
			->setReturnPath($from->email_address)
			->setSubject($this->subject)
			->setBodyText($this->body . ($account->is_free() ?
				(preg_match('/--/', $this->body) ? self::FREE_FOOTER : ("\n\n--".self::FREE_FOOTER)) : ''),
				'UTF-8'
			);

		try {
			require_once 'Zend/Mail/Transport/Sendmail.php';
			$transport = new Zend_Mail_Transport_Sendmail("-f {$from->email_address}");
			
			$mail->send($transport);
			$this->delete($this->id);
			return true;
		} catch (Zend_Mail_Transport_Exception $e) {
			$this->attempts = $this->attempts + 1;
			$this->save();
			return false;
		}
	}
	
}
