<?php
require_once 'Service/Entanet.php';
class VoipController extends Controller {
	
	public function make_call() {
		$user = CurrentlyLoggedInUser::Instance();
		$this->setTemplateName('save');
		$account = $user->getAccount();
		if(!$account->isEntanetEnabled()) {
			sendTo('');
			return;
		}
		$ent = new Service_Entanet($account->entanet_domain, $account->entanet_code);
		try {
			$result = $ent->dialExtension(
				$user->getModel()->getEntanetExtension(),
				Service_Entanet::normalizeNumber($this->_data['number'])
			);
			if($result) {
				Flash::Instance()->addMessage("Dialling - your extension will shortly ring");
				return;
			}
			else {
				Flash::Instance()->addError($ent->getLastResponse()->getReturnValue());
				return;
			}
		}
		catch(Service_Entanet_Exception $e) {
			Flash::Instance()->addError("Problem talking to Entanet");
			return;
		}
	}
	
	public function check_calls() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		if(!$account->isEntanetEnabled()) {
			sendTo();
			return;
		}
		$extension = $user->getModel()->getEntanetExtension();
		if(empty($extension)) {
			Flash::Instance()->addError("No Extension associated with your username");
			sendTo();
			return;
		}

		$ent = new Service_Entanet($account->entanet_domain, $account->entanet_code);
		try {
			$result = $ent->getCallerInfo($extension);
		}
		catch(Service_Entanet_Exception $e) {
			$this->logger->warn("Problem talking to Entanet:");
			$this->logger->warn($e->getMessage());
			Flash::Instance()->addError("There was a problem talking with Entanet");
			return;
		}
		catch(Service_Entanet_Response_Exception $e) {
			$this->logger->crit("Unknown response from Entanet:");
			$this->logger->crit($e->getMessage());
			Flash::Instance()->addError("There was a problem talking with Entanet");
			return;
		}
		
		if($result->wasSuccessful()) {
			$calls = $result->getCallerDetails();
			$matches = array();
			foreach($calls as $i => $call) {
				$matched = false;
				
				$people = Tactile_Person::findByPhoneNumber($call['from']);
				foreach($people as $person) {
					$matched = true;
					$matches[] = array(
						'id' => $person['id'],
						'firstname' => $person['firstname'],
						'surname' => $person['surname'],
						'organisation_id' => $person['organisation_id'],
						'organisation' => $person['organisation'],
						'phone' => $call['from'],
						'type' => 'person',
						'ringing' => $call['type']
					);
				}
				$orgs = Tactile_Company::findByPhoneNumber($call['from']);
				foreach($orgs as $org) {
					$matched = true;
					$matches[] = array(
						'id' => $org['id'],
						'name' => $org['name'],
						'phone' => $call['from'],
						'type' => 'company',
						'ringing' => $call['type']
					);
				}
				if($matched === false) {
					$matches[] = array(
						'type' => 'nomatch',
						'phone' => $call['from'],
						'ringing' => $call['type']
					);
				}
			}
			$this->view->set('matches', $matches);
			return;
		}
		else {
			switch($ent->getLastResponse()->getReturnValue()) {
				case Service_Entanet_Response::NO_CALLS:
					$msg = 'Your extension is not currently receiving any calls';
					break;
				default:
					$msg = $ent->getLastResponse()->getReturnValue();
			}
			Flash::Instance()->addError($msg);
			return;
		}
	}
	
}
