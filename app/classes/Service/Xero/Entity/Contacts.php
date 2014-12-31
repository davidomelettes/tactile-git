<?php

require_once 'Service/Xero/Entity/Contact.php';
class Service_Xero_Entity_Contacts extends Service_Xero_Entity_Abstract implements Iterator {

	const TYPE_ALL = 'all';
	const TYPE_CUSTOMERS = 'customers';
	const TYPE_SUPPLIERS = 'suppliers';
	const SORT_NAME = 'name';
	const SORT_EMAIL = 'emailaddress';
	const DIR_DESC = 'desc';
	const DIR_ASC = 'asc';

	protected $_properties = array(
		'Contact',
	);
	
	public function _loadFromXml($xml) {
		$this->_data = array();
		$this->_simpleXml = (isset($xml->Contacts) ? $xml->Contacts : $xml);
		$this->_eat($this->_properties, $this->_simpleXml, $this->_data);
		$this->_data['Contact'] = array();
		foreach ($this->_simpleXml->Contact as $xml) {
			$contact = new Service_Xero_Entity_Contact($this->_service);
			$contact->loadFromXml($xml);
			$this->_data['Contact'][] = $contact;
		}
	}
	
	public function get(
		$type = Service_Xero_Entity_Contacts::TYPE_ALL,
		$sortBy = Service_Xero_Entity_Contacts::SORT_NAME,
		$direction = Service_Xero_Entity_Contacts::DIR_ASC,
		$updatedAfter = ''
	) {
		$xr = $this->_service->xeroRequest('contacts',
			array('type' => $type, 'sortBy' => $sortBy, 'direction' => $direction, 'updatedAfter' => $updatedAfter));
		if ($xr->wasSuccessful()) {
			$this->loadFromXml($xr->getXml());
			return $this;
		} else {
			throw new Service_Xero_Exception($xr->getErrorMsg());
		}
		return false;
	}
	
	public function current() {
		return current($this->_data['Contact']);
	}
	
	public function next() {
		return next($this->_data['Contact']);
	}
	
	public function key() {
		return key($this->_data['Contact']);
	}
	
	public function valid() {
		return (FALSE !== $this->current());
	}
	
	public function rewind() {
		return reset($this->_data['Contact']);
	}
	
}
