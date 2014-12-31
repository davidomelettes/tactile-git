<?php
require_once 'Service/Freshbooks.php';

class FreshbooksController extends Controller {
	
	function index() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$this->view->set('accountname', Tactile_AccountMagic::getValue('freshbooks_account'));
		$this->view->set('token', Tactile_AccountMagic::getValue('freshbooks_token'));
		$this->view->set('show_coupon', (time() < strtotime('1st October 2009')));
	}
	
	function setup() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$fb_name = Tactile_AccountMagic::getValue('freshbooks_account');
		
		if((empty($fb_name) && empty($this->_data['accountname'])) || empty($this->_data['token'])) {
			Flash::Instance()->addError("Please fill in all fields");
			sendTo('freshbooks');
			return;				
		}
		if(!empty($this->_data['accountname'])) {
			$fb_name = $this->_data['accountname'];
			//catch people typing in the full URL
			$matched = preg_match('#^https?://([^.]+).freshbooks.com#i', $fb_name, $matches);
			if($matched) {
				$fb_name = $matches[1];
			}
		}
		
		//then verify that we can connect and do something (invoices because they're admin-only)
		$fb = new Service_Freshbooks($fb_name, $this->_data['token']);
		$query = $fb->newInvoiceQuery('list');
		try {
			$response = $fb->execute($query);
		}
		catch(Exception $e) {
			Flash::Instance()->addError("There was a problem connecting with FreshBooks");
			$this->logger->crit("Exception talking to Freshbooks: " . $e->getMessage());
			$this->logger->crit(print_r($_POST, true));
			sendTo('freshbooks');
			return;
		}
		
		if(!$response->isValid()) {
			Flash::Instance()->addError("There was a problem connecting with FreshBooks with that Account Name");
		}
		else if($response->getStatus() == Service_Freshbooks_Response::STATUS_FAIL) {
			$error = $response->getErrorMsg();
			switch($error) {
				case 'Not implemented.':
					$error = 'You provided a Staff access token. Integration with Tactile requires one from an Admin';
					break;
			}
			Flash::Instance()->addError($error);
		}
		else {
			/* @var $account TactileAccount */
			$account = CurrentlyLoggedInUser::Instance()->getAccount();
			$success = $account->setFreshbooksDetails($fb_name, $this->_data['token']);
			if($success) {
				Flash::Instance()->addMessage('FreshBooks details saved');
			}
			else {
				Flash::Instance()->addError("Problem Saving");
			}
		}
		sendTo('freshbooks');
		return;
	}
	
	
	function reset() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$success = $account->clearFreshbooksDetails();
		if($success) {
			Flash::Instance()->addMessage('Accounts Unlinked');
		}
		else {
			Flash::Instance()->adderror('Problem Unlinking Accounts');
		}
		sendTo('freshbooks');
		return;
	}
}
