<?php

require_once 'Service/Zendesk.php';

class ZendeskController extends Controller {
	public function index() {
		// zendesk_siteaddress
		// zendesk_email
		// zendesk_password
		
		
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$this->view->set('siteaddress', Tactile_AccountMagic::getValue('zendesk_siteaddress'));
		$this->view->set('email',       Tactile_AccountMagic::getValue('zendesk_email'));
		$this->view->set('password',    Tactile_AccountMagic::getValue('zendesk_password'));
	}
	
	function setup() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		
		if (empty($this->_data['siteaddress'])) {
			$zendesk_siteaddress = Tactile_AccountMagic::getValue('zendesk_siteaddress');
		} else {
			$zendesk_siteaddress = $this->_data['siteaddress'];
		}
		
		$zendesk_email = $this->_data['email'];
		$zendesk_password = $this->_data['password'];
		
		if(empty($zendesk_siteaddress) || empty($zendesk_email) || empty($zendesk_password)) {
			Flash::Instance()->addError("Please fill in all fields");
			sendTo('zendesk');
			return;
		}
		
		$zendesk_email = $this->_data['email'];
		$zendesk_password = $this->_data['password'];
		
		//catch people typing in the full URL
		$matched = preg_match('#^(?:https?://)?([^.]+).zendesk.com#i', $zendesk_siteaddress, $matches);
		if($matched) {
			$zendesk_siteaddress = $matches[1];
		}
		
		//then verify that we can connect and do something (invoices because they're admin-only)
		$zd = new Service_Zendesk($zendesk_siteaddress, $zendesk_email, $zendesk_password);

		if (!$zd->credentials_valid()) {
			Flash::Instance()->addError("There was a problem connecting to Zendesk with those account details");
		} else {
			$account = CurrentlyLoggedInUser::Instance()->getAccount();
			$success = $account->setZendeskDetails($zendesk_siteaddress, $zendesk_email, $zendesk_password);
			if($success) {
				Flash::Instance()->addMessage('Zendesk details saved');
			}
			else {
				Flash::Instance()->addError("Problem Saving");
			}
		}
		sendTo('zendesk');
		return;
	}
	
	function reset() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$success = $account->clearZendeskDetails();
		if($success) {
			Flash::Instance()->addMessage('Accounts Unlinked');
		}
		else {
			Flash::Instance()->adderror('Problem Unlinking Accounts');
		}
		sendTo('zendesk');
		return;
	}
}