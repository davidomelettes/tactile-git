<?php

class Tactile_EmailaddressesController extends Controller {

	protected $tactileemailaddress;
	
	function __construct($module=null,$view=null) {
		parent::__construct($module,$view);
		$this->uses('TactileEmailAddress');
	}
	
	function index() {
		
	}
	
	function _new() {
		
	}
	
	function save() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		
		$email_data = isset($this->_data['TactileEmailAddress']) ? $this->_data['TactileEmailAddress'] : array();
		$email_data['role_id'] = Omelette::getUserSpaceRole()->id; 
		
		if (isset($email_data['verify_code'])) {
			unset($email_data['verify_code']);
		}
		if (isset($email_data['verified_at'])) {
			unset($email_data['verified_at']);
		}
		$saver = new ModelSaver();
		$errors = array();
		
		try {
			$email = $saver->save($email_data, 'TactileEmailAddress', $errors);
		} catch (Exception $e) {
			$email = FALSE;
			$errors[] = 'Failed to save Email Address';
		}
		if (FALSE !== $email) {
			// Send verification email
			$email->sendVerificationEmail();
		} else {
			Flash::Instance()->addErrors($errors);
		}
	}
	
	function send_validation_email() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		
		$id = !empty($this->_data['id']) ? $this->_data['id'] : '';
		$address = new TactileEmailAddress();
		if (FALSE === $address->load($id) || !$address->canEdit()) {
			Flash::Instance()->addError("You don't have permission to do that.");
			sendTo('setup/email');
			return;
		}
		if ($address->sendVerificationEmail()) {
			Flash::Instance()->addMessage("Verification email sent.");
		} else {
			Flash::Instance()->addError("Error sending re-validation email. Please try again.");
		}
		sendTo('setup/email');
	}
	
	function delete() {
		ModelDeleter::delete($this->_uses['TactileEmailAddress'],'Address',array('setup', 'email'));
	}
	
	function view() {
		
	}
	
}
