<?php

require_once 'Service/Xero/Entity/Contact/Phones/Phone.php';
class Service_Xero_Entity_Contact_Phones extends Service_Xero_Entity_Abstract {
	
	protected $_properties = array(
		'Phone'
	);
	
	public function loadFromXml($xml) {
		$this->_data = array();
		$this->_simpleXml = $xml;
		$this->_eat($this->_properties, $this->_simpleXml, $this->_data);
		$this->_data['Phone'] = array();
		if (isset($this->_simpleXml->Phone)) {
			foreach ($this->_simpleXml->Phone as $phone) {
				$phones_phone = new Service_Xero_Entity_Contact_Phones_Phone($this->_service);
				$phones_phone->loadFromXml($phone);
				$this->_data['Phone'][] = $phones_phone;
			}
		}
	}
	
	public function current() {
		return current($this->_data['Phone']);
	}
	
	public function next() {
		return next($this->_data['Phone']);
	}
	
	public function key() {
		return key($this->_data['Phone']);
	}
	
	public function valid() {
		return (FALSE !== $this->current());
	}
	
	public function rewind() {
		return reset($this->_data['Phone']);
	}
	
	public function addPhone($phone) {
		if (!$phone instanceof Service_Xero_Entity_Contact_Phones_Phone) {
			throw new Service_Xero_Exception('Phone must be of type Service_Xero_Entity_Contact_Phones_Phone!');
		}
		$this->_data['Phone'][] = $phone;
	}
	
}
