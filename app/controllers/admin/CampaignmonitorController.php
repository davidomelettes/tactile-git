<?php

require_once 'Service/CampaignMonitor.php';

class CampaignmonitorController extends Controller {
	
	public function index() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$this->view->set('cm_key', Tactile_AccountMagic::getValue('cm_key'));
		$this->view->set('cm_client', Tactile_AccountMagic::getValue('cm_client'));
	}
	
	public function setup() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$cm_key = empty($this->_data['cm_key']) ? Tactile_AccountMagic::getValue('cm_key') : $this->_data['cm_key'];
		$cm_client_id = empty($this->_data['cm_client_id']) ? Tactile_AccountMagic::getValue('cm_client_id') : $this->_data['cm_client_id'];
		
		if (empty($cm_key)) {
			Flash::Instance()->addError("Please enter your API Key");
			sendTo('campaignmonitor');
			return;
		}
		if (empty($cm_client_id)) {
			Flash::Instance()->addError("Please choose a Client");
			sendTo('campaignmonitor');
			return;
		}
		
		// Make sure provided values are valid
		$cm = new Service_CampaignMonitor($cm_key);
		$clients = $cm->userGetClients();
		if (FALSE === $clients) {
			Flash::Instance()->addError("Failed to link with Client. Please check your API Key and try again.");
			sendTo('campaignmonitor');
			return;
		}
		$cm_client = null;
		foreach ($clients as $client) {
			if ($client->getClientId() == $cm_client_id) {
				$cm_client = $client->getName();
				break;
			}
		}
		if (!isset($cm_client)) {
			Flash::Instance()->addError("Failed to link with Client. Please check your API Key and try again.");
			sendTo('campaignmonitor');
			return;
		}

		if (FALSE === $account->setCampaignMonitorDetails($cm_key, $cm_client_id, $cm_client)) {
			Flash::Instance()->addError('Failed to save Campaign Monitor details');
		} else {
			Flash::Instance()->addMessage('Account linked successfully');
		}
		
		sendTo('campaignmonitor');
		return;
	}
	
	function get_clients() {
		if (!$this->view->is_json) {
			sendTo('campaignmonitor');
			return;
		}
		$cm_key = empty($this->_data['cm_key']) ? Tactile_AccountMagic::getValue('cm_key') : $this->_data['cm_key'];
		$cm = new Service_CampaignMonitor($cm_key);
		$clients = $cm->userGetClients();
		if (FALSE === $clients) {
			Flash::Instance()->addError('Failed to fetch Campaign Monitor client list. Please check you are using your "Account" API Key.');
		} else {
			$clients_data = array();
			foreach ($clients as $client) {
				$clients_data[$client->getClientId()] = $client->getName();
			}
			$this->view->set('clients', json_encode($clients_data));
		}
	}
	
	function get_lists() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		if (!$this->view->is_json || !$account->isCampaignMonitorEnabled()) {
			sendTo('campaignmonitor');
			return;
		}
		$cm = new Service_CampaignMonitor(Tactile_AccountMagic::getValue('cm_key'));
		$lists = $cm->clientGetLists(Tactile_AccountMagic::getValue('cm_client_id'));
		if (FALSE === $lists) {
			$this->view->set('status', 'failure');
		} else {
			$this->view->set('status', 'success');
			$json_lists = array();
			foreach ($lists as $list) {
				$json_lists[$list->getListId()] = $list->getName();
			}
			$this->view->set('lists', json_encode($json_lists));
		}
	}
	
	function unlink() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$success = $account->clearCampaignMonitorDetails();
		if ($success) {
			Flash::Instance()->addMessage('Accounts Unlinked');
		} else {
			Flash::Instance()->adderror('Problem Unlinking Accounts');
		}
		sendTo('campaignmonitor');
		return;
	}
}
