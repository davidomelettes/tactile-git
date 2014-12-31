<?php
require_once 'Service/Freshbooks.php';

/**
 * Helper for Freshbooks controller-methods, 
 * call functions statically,$this corresponds to the calling controller
 *
 */
class FreshbooksHelper {
	
	/**
	 * Contacts Freshbooks, grabs a list of Clients, takes out those that are already assigned to a Tactile org
	 * sends the array, sorted alphabetically, to the view as 'clients'
	 *
	 */
	public function client_list($start_page = 1, $per_page = 25, $max_pages = 99) {
		require_once 'Service/Freshbooks/Response/Client/List.php';
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$db = DB::Instance();
		$query = 'SELECT freshbooks_id FROM organisations WHERE usercompanyid = ' . $db->qstr(EGS::getCompanyId());
		$existing = $db->getCol($query);
		
		/* @var $cache Zend_Cache_Core */
		$cache = Zend_Registry::get('cache');
		$page = $start_page;
		$return = array();
		Tactile_AccountMagic::loadAll();
		do {
			$key = 'fbclientlist' . $account->organisation_id . '_' . $page . '_' . $per_page;
			if(false === ($response = $cache->load($key))) {
				$fb = new Service_Freshbooks(Tactile_AccountMagic::getValue('freshbooks_account'), Tactile_AccountMagic::getValue('freshbooks_token'));
				$query = $fb->newClientQuery('list');
				$query->addParam('page', $page);
				$query->addParam('per_page', $per_page);
				$response = $fb->execute($query);
				$cache->save($response, $key, array('freshbooks'), 60 * 5);
			}
			/* @var $response Service_Freshbooks_Response_Client_List */
			if(false === $response->isValid()) {
				Flash::Instance()->addError('Error communicating with FreshBooks, please try again');
				return false;
			}
			else if($response->getStatus() == Service_Freshbooks_Response::STATUS_FAIL) {
				Flash::Instance()->addError('FreshBooks Error: ' . $response->getErrorMsg());
				return false;
			}
			else {
				$clients = $response->getClients();
				
				foreach($clients as $client) {
					if(!in_array($client->client_id, $existing)) {
						$return[$client->client_id] = $client->organization;
					}
				}
			}
			$page++;
		}
		while($response->hasMorePages() && $page <= $max_pages);
		
		if(count($return) == 0) {
			$this->view->set('no_clients', true);
		}
		else {
			asort($return);
			$this->view->set('clients', $return);
		}
	}
	
	/**
	 * Grabs a list of invoices from FB for the required company, assigned to the view as 'invoices'
	 *
	 * @param Tactile_Company $company
	 */
	public function invoice_list($company, $start_page = 1, $per_page = 25, $max_pages = 99) {
		require_once 'Service/Freshbooks/Response/Invoice/List.php';
		$this->view->set('is_attached', true);
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		/* @var $cache Zend_Cache_Core */
		$cache = Zend_Registry::get('cache');
		$page = $start_page;
		$invoices = array();
		do {
			$key = 'invoicelist' . $company->id . '_' . $page . '_' . $per_page;
			if(false === ($response = $cache->load($key))) {
				$fb = new Service_Freshbooks(Tactile_AccountMagic::getValue('freshbooks_account'), Tactile_AccountMagic::getValue('freshbooks_token'));
				
				$query = $fb->newInvoiceQuery('list');
				$query->addParam('client_id', $company->freshbooks_id);
				$query->addParam('page', $page);
				$query->addParam('per_page', $per_page);
				
				/* @var $response Service_Freshbooks_Response */
				$response = $fb->execute($query);
				if(!$response->isValid()) {
					Flash::Instance()->addError('Problem communicating with FreshBooks, please try again');
					return false;
				}
				else if($response->getStatus() == Service_Freshbooks_Response::STATUS_FAIL) {
					Flash::Instance()->addError($response->getErrorMsg());
					return false;
				}
				$cache->save($response, $key, array('freshbooks'), 60 * 10);
			}
			/*@var $response Service_Freshbooks_Response */
			$invoices = array_merge($invoices, $response->getInvoices());
			$page++;
		}
		while($response->hasMorePages() && $page <= $max_pages);

		uasort($invoices, array('FreshbooksHelper', 'invoice_cmp'));
		$this->view->set('invoices', $invoices);
		$formatter = new PrettyTimestampFormatter();
		
		$last_updated = $formatter->format($response->getHttpResponse()->getHeader('Date'));
		$this->view->set('response_date', $last_updated);
	}
	
	/**
	 * Compare invoices by date, to sort Descending. (callback)
	 *
	 * @param Service_Freshbooks_Entity_Invoice $a
	 * @param Service_Freshbooks_Entity_Invoice $b
	 * @return int
	 */
	public static function invoice_cmp($a, $b) {
		if($a->date == $b->date) {
			return 0;
		}
		return strtotime($a->date) < strtotime($b->date) ? 1 : -1;
	}
	
	public function client_create($company) {
		require_once 'Service/Freshbooks/Entity/Client.php';
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$fb = new Service_Freshbooks(Tactile_AccountMagic::getValue('freshbooks_account'), Tactile_AccountMagic::getValue('freshbooks_token'));
		
		/* @var $query Service_Freshbooks_Query */
		$query = $fb->newClientQuery('create');
		$fields = array(
			'organization',
			'first_name',
			'last_name',
			'email',
			'work_phone',
			'p_street1',
			'p_street2',
			'p_city',
			'p_state',
			'p_country',
			'p_code'
		);
		$client_data = array();
		foreach($fields as $fieldname) {
			if(!empty($this->_data[$fieldname])) {
				$client_data[$fieldname] = $this->_data[$fieldname];
			}
		}
		$client = new Service_Freshbooks_Entity_Client($client_data, $fb);
		$query->addEntity($client, 'client');
		$response = $fb->execute($query);
		if(!$response->isValid()) {
			Flash::Instance()->addError("Problem talking to FreshBooks, please retry");
		}
		else if($response->getStatus() !== Service_Freshbooks_Response::STATUS_OK) {
			Flash::Instance()->addError('FreshBooks Error: ' . $response->getErrorMsg());
		}
		else {
			$fb_id = $response->getXml()->client_id;
			$company->update($company->id, 'freshbooks_id', $fb_id);
			Flash::Instance()->addMessage("Organisation linked to FreshBooks");
		}
	}
	
	public function reset_link(Tactile_Organisation $org) {
		$success = $org->update($org->id, 'freshbooks_id', 'null');
		if($success) {
			/*@var $cache Zend_Cache_Core */
			$cache = Zend_Registry::get('cache');
			$key = 'invoicelist' . $org->id;
			$cache->remove($key);
			Flash::Instance()->addMessage('Link Reset');
		}
		else {
			Flash::Instance()->addError('Error Resetting Link');
		}
	}
	
	public function estimate_list($company, $start_page = 1, $per_page = 25, $max_pages = 99) {
		require_once 'Service/Freshbooks/Response/Estimate/List.php';
		$this->view->set('is_attached', true);
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		/* @var $cache Zend_Cache_Core */
		$cache = Zend_Registry::get('cache');
		$page = $start_page;
		$estimates = array();
		do {
			$key = 'estimatelist' . $company->id . '_' . $page . '_' . $per_page;
			if(false === ($response = $cache->load($key))) {
				$fb = new Service_Freshbooks(Tactile_AccountMagic::getValue('freshbooks_account'), Tactile_AccountMagic::getValue('freshbooks_token'));
				
				$query = $fb->newEstimateQuery('list');
				$query->addParam('client_id', $company->freshbooks_id);
				$query->addParam('page', $page);
				$query->addParam('per_page', $per_page);
				
				/* @var $response Service_Freshbooks_Response */
				$response = $fb->execute($query);
				if(!$response->isValid()) {
					Flash::Instance()->addError('Problem communicating with FreshBooks, please try again');
					return false;
				}
				else if($response->getStatus() == Service_Freshbooks_Response::STATUS_FAIL) {
					Flash::Instance()->addError($response->getErrorMsg());
					return false;
				}
				$cache->save($response, $key, array('freshbooks'), 60 * 10);
			}
			/*@var $response Service_Freshbooks_Response */
			$estimates = array_merge($estimates, $response->getEstimates());
			$page++;
		}
		while($response->hasMorePages() && $page <= $max_pages);
		uasort($estimates, array('FreshbooksHelper', 'invoice_cmp'));
		$this->view->set('estimates', $estimates);
		$formatter = new PrettyTimestampFormatter();
		
		$last_updated = $formatter->format($response->getHttpResponse()->getHeader('Date'));
		$this->view->set('response_date', $last_updated);
	}
}
