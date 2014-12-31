<?php

require_once 'Service/Xero/Entity/Contact/Addresses/Address.php';
class Service_Xero_Entity_Contact_Addresses extends Service_Xero_Entity_Abstract {
	
	protected $_properties = array(
		'Address'
	);
	
	public function loadFromXml($xml) {
		$this->_data = array();
		$this->_simpleXml = $xml;
		$this->_eat($this->_properties, $this->_simpleXml, $this->_data);
		$this->_data['Address'] = array();
		if (isset($this->_simpleXml->Address)) {
			foreach ($this->_simpleXml->Address as $address) {
				$addresses_address = new Service_Xero_Entity_Contact_Addresses_Address($this->_service);
				$addresses_address->loadFromXml($address);
				$this->_data['Address'][] = $addresses_address;
				
			}
		}
	}
	
	public function current() {
		return current($this->_data['Address']);
	}
	
	public function next() {
		return next($this->_data['Address']);
	}
	
	public function key() {
		return key($this->_data['Address']);
	}
	
	public function valid() {
		return (FALSE !== $this->current());
	}
	
	public function rewind() {
		return reset($this->_data['Address']);
	}
	
	public function addAddress($address) {
		if (!$address instanceof Service_Xero_Entity_Contact_Addresses_Address) {
			throw new Service_Xero_Exception('Address must be of type Service_Xero_Entity_Contact_Addresses_Address!');
		}
		$this->_data['Address'][] = $address;
	}
	
}
