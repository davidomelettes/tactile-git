<?php

require_once 'Service/Freshbooks.php';
require_once 'Service/Freshbooks/Response/Client/List.php';

class FreshbooksExtractor {
	
	/**
	 * @var Service_Freshbooks_Response_Client_List
	 */
	protected $_response;
	
	protected $_clients;
	
	/**
	 * @var Service_Freshbooks
	 */
	protected $_fb;
	
	protected $_countries = array();
	
	protected $_country_default = 'GB';
	
	/**
	 *
	 * @param Service_Freshbooks_Response_Client_List [optional] $response
	 */
	public function __construct(Service_Freshbooks_Response_Client_List $response = null, Service_Freshbooks $service = null) {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$this->_fb = is_null($service) ? new Service_Freshbooks(Tactile_AccountMagic::getValue('freshbooks_account'), Tactile_AccountMagic::getValue('freshbooks_token')) : $service;
		$country_model = new Country();
		$this->_countries = array_flip($country_model->getAll());
		
		if(!is_null($response)) {
			$this->_response = $response;
			$this->_clients = $this->_response->getClients();
		}
	}
	
	/**
	 * Counts the number of lines in the file
	 *
	 * @param SPLFileObject $file
	 * @return int
	 */
	public function countRecords($file) {
		$this->_init($file);
		return $this->_response->getTotalRecords();
	}
	
	public function iterate($file) {
		$this->_init($file);
		list(,$return) = each($this->_clients);
		if(is_null($return)) {
			return false;
		}
		return $return;
	}
	
	public function extract(Service_Freshbooks_Entity_Client $entry = null) {
		$entry->getFullDetails();
		$person_data = array(
			'firstname' => $entry->get('first_name'),
			'surname' => $entry->get('last_name'),
			'emails' => array(
				array(
					'contact' => $entry->get('email'),
					'name' => 'Main'
				)
			)
		);
		foreach(array('home_phone', 'work_phone', 'mobile') as $key) {
			$number = $entry->get($key);
			if(!empty($number)) {
				$person_data['phones'][] = array(
					'contact' => $number,
					'name' => ucwords(str_replace('_',' ', $key))
				);
			}
		}
		$company_data = array(
			'name' => $entry->get('organization'),
			'freshbooks_id' => $entry->get('client_id'),
			'description' => $entry->get('notes')
		);
		
		$address_data = $this->_addressConvert($entry->getPrimaryAddress());
		$company_data['addresses'][] = $address_data;
		
		return array($company_data, $person_data);
	}
	
	public function _init(SPLFileObject $file) {
		if(!is_null($this->_response)) {
			return;
		}
		$http = new Zend_Http_Response(200, array(), file_get_contents($file->getPathname()));
		$this->_response = new Service_Freshbooks_Response_Client_List($http, $this->_fb);
		
		//check if the feed we grabbed contained everything, if not then grab everything and use that instead
		$page = $this->_response->getPage();
		while($this->_response->hasMorePages()) {
			$query = $this->_fb->newClientQuery('list');
			$query->addParam('page', ++$page);
			$query->addParam('per_page', $this->_response->getPerPage());
			$new_response = $this->_fb->execute($query);
			$this->_response->merge($new_response);
		}
		$this->_clients = $this->_response->getClients();
	}
	
	protected function _addressConvert($fb_address) {
		$map = array(
			'city' => 'town',
			'state' => 'county',
			'code' => 'postcode'
		);
		foreach($map as $from => $to) {
			$fb_address[$to] = $fb_address[$from];
			unset($fb_address[$from]);
		}
		$fb_address['country_code'] = isset($this->_countries[$fb_address['country']]) ?
			$this->_countries[$fb_address['country']] : $this->_country_default;
		unset($fb_address['country']);

		return $fb_address;
	}
}
