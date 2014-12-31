<?php

require_once 'Service/Freshbooks/Entity/Abstract.php';

/**
 * Wrapper for an invidual client, either the basic version returned by client.list or the full version
 * from  client.get
 *
 */
class Service_Freshbooks_Entity_Client extends Service_Freshbooks_Entity_Abstract {
	
	const ADDRESS_PREFIX_PRIMARY = 'p';
	const ADDRESS_PREFIX_SECONDARY = 's';
	
	protected $_properties = array(
		'client_id',
		'username',
		'password',
		'first_name',
		'last_name',
		'organization',
		'email',
		'work_phone',
		'home_phone',
		'mobile',
		'fax',
		'p_street1',
		'p_street2',
		'p_city',
		'p_state',
		'p_country',
		'p_code',
		's_street1',
		's_street2',
		's_city',
		's_state',
		's_country',
		's_code',
		'notes',
		'credit',
		'url',
		'auth_url'
	);
	
	/**
	 * Shortcut for getting the primary address ('p_')
	 *
	 * @return array
	 */
	public function getPrimaryAddress() {
		return $this->getAddress(self::ADDRESS_PREFIX_PRIMARY);
	}
	
	/**
	 * Shortcut for getting the secondary address ('s_')
	 *
	 * @return array
	 */
	public function getSecondaryAddress() {
		return $this->getAddress(self::ADDRESS_PREFIX_SECONDARY);
	}
	
	/**
	 * Return an array containing the fields beginning with the specified prefix
	 * the keys of the returned array don't contain the prefix
	 *
	 * @param string $prefix
	 * @return array
	 */
	public function getAddress($prefix) {
		$address = array();
		foreach($this->_properties as $key) {
			if(substr($key, 0, 2) == $prefix . '_'){
				$address[substr($key, 2)] = $this->get($key);
			}
		}
		return $address;
	}
	
	
	/**
	 * Clients returned as part of a list don't contain as much detail as those accessed directly,
	 * this is a simple way to get the rest of the data
	 *
	 */
	public function getFullDetails() {
		if($this->_isFull) {
			return;
		}
		require_once 'Service/Freshbooks/Query/Client.php';
		$query = new Service_Freshbooks_Query_Client('get');
		$query->addParam('client_id', $this->get('client_id'));
		$client = $this->getService()->execute($query)->getClient();
		$this->_data = $client->getData();
		$this->setIsFull();
	}
	
	/**
	 * Helper for getting a client's invoices
	 *
	 * @return array
	 */
	public function getInvoices() {
		$query = new Service_Freshbooks_Query_Invoice('list');
		$query->addParam('client_id', $this->get('client_id'));
		$response = $this->getService()->execute($query);
		return $response->getInvoices();
	}
}
