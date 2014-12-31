<?php

require_once 'Service/Entanet.php';

class EntanetController extends Controller {
	
	public function index() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$this->view->set('entanet_domain', $account->entanet_domain);
		$this->view->set('entanet_code', $account->entanet_code);
		
		$domain = $account->entanet_domain;
		if(!empty($domain)) {
			$users = DataObject::Construct('User');
			$this->view->set('users', $users->getAll());
	
			$extensions = $account->getEntanetExtensionMapping();
			$this->view->set('extensions', $extensions);
		}
	}
	
	public function setup() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$domain = $account->entanet_domain;
		if(!empty($domain)) {
			$this->_data['entanet_domain'] = $domain;
		}
		if(empty($this->_data['entanet_domain'])) {
			Flash::Instance()->addError("Domain is a required field");
		}
		if(empty($this->_data['entanet_code'])) {
			Flash::Instance()->addError("Security Code is a required field");
		}
		if(Flash::Instance()->hasErrors()) {
			sendTo('entanet');
			return;
		}
		
		$ent = new Service_Entanet($this->_data['entanet_domain'], $this->_data['entanet_code']);
		try {
			$result = $ent->getCallerInfo(500);
		}
		catch(Zend_Http_Exception $e) {
			Flash::Instance()->addError("There was a problem connecting to the Domain you provided");
			sendTo('entanet');
			return;
		}
		if(!$result->wasSuccessful() && $result->getReturnValue() != Service_Entanet_Response::NO_CALLS) {
			Flash::Instance()->addError($result->getReturnValue());
			sendTo('entanet');
			return;
		}
		
		$account->setEntanetDetails($this->_data['entanet_domain'], $this->_data['entanet_code']);
		Flash::Instance()->addMessage("Account Details Saved");
		sendTo('entanet');
	}
	
	public function assign_extensions() {
		if(empty($this->_data['extensions'])) {
			sendTo('entanet');
			return;
		}
		$extensions = array_filter($this->_data['extensions']);
		if($extensions != array_unique($extensions)) {
			Flash::Instance()->addError("Please make sure you only assign an extension to one user");
			sendTo('entanet');
			return;
		}
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$success = $account->updateEntanetExtensions($extensions);
		if($success) {
			Flash::Instance()->addMessage("Extensions Assigned Successfully");
			sendTo('entanet');
			return;
		}
		else {
			Flash::Instance()->addError("There was a problem assigning Extensions, try again");
			sendTo('entanet');
			return;
		}
	}
	
	function reset() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$success = $account->clearEntanetDetails();
		if($success) {
			Flash::Instance()->addMessage('Accounts Unlinked');
		}
		else {
			Flash::Instance()->adderror('Problem Unlinking Accounts');
		}
		sendTo('entanet');
		return;
	}
	
}
