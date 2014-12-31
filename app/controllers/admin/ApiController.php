<?php

class ApiController extends Controller {

	public function index() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$this->view->set('api_enabled', $account->isAPIEnabled());
	}
	
	public function setup() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$flash = Flash::Instance();
		
		switch ($this->_data['api_access']) {
			case 'Disable API':
				if (FALSE !== $account->disableAPIAccess()) {
					$flash->addMessage('API Disabled');
				} else {
					$flash->addError('Error disabling API');
				}
				break;
			case 'Enable API':
				if (FALSE !== $account->enableAPIAccess()) {
					$flash->addMessage('API Enabled');
				} else {
					$flash->addError('Error enabling API');
				}
				break;
		}
		sendTo('api');
	}
	
}
