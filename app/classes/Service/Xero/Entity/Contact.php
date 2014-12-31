<?php

require_once 'Service/Xero/Entity/Contact/Addresses.php';
require_once 'Service/Xero/Entity/Contact/Phones.php';
class Service_Xero_Entity_Contact extends Service_Xero_Entity_Abstract {
	
	const STATUS_ACTIVE = 'ACTIVE';
	const STATUS_DELETED = 'DELETED';
	
	protected $_properties = array(
		'ContactID',
		'ContactNumber',
		'ContactStatus',
		'Name',
		'EmailAddress',
		'Addresses',
		'Phones',
		'UpdatedDateUTC'
	);
	
	public function loadFromXml($xml) {
		$this->_data = array();
		$this->_simpleXml = (isset($xml->Contact) ? $xml->Contact : $xml);
		$this->_eat($this->_properties, $this->_simpleXml, $this->_data);
		$this->_data['Addresses'] = new Service_Xero_Entity_Contact_Addresses($this->_service);
		$this->_data['Addresses']->loadFromXml($this->_simpleXml->Addresses);
		$this->_data['Phones'] = new Service_Xero_Entity_Contact_Phones($this->_service);
		$this->_data['Phones']->loadFromXml($this->_simpleXml->Phones);
	}
	
	public function toXml() {
		return html_entity_decode($this->_spew($this->_data, new SimpleXMLElement('<Contact/>'))->asXML());
	}
	
	public function get($contactID, $contactNumber='') {
		$xr = $this->_service->xeroRequest('contact',
			array('contactID' => $contactID, 'contactNumber' => $contactNumber));
		if ($xr->wasSuccessful()) {
			$this->loadFromXml($xr->getXml());
			return $this;
		} else {
			throw new Service_Xero_Exception($xr->getErrorMsg());
		}
		return false;
	}
	
	public function put() {
		$xr = $this->_service->xeroRequest('contact', array(), Zend_Http_Client::POST, $this->toXml());
		if ($xr->wasSuccessful()) {
			$this->loadFromXml($xr->getXml());
			return $this;
		} else {
			throw new Service_Xero_Exception($xr->getErrorMsg());
		}
		return false;
	}
	
	public function addAddress($address) {
		if (!$this->_data['Addresses'] instanceof Service_Xero_Entity_Contact_Addresses) {
			$this->_data['Addresses'] = new Service_Xero_Entity_Contact_Addresses($this->_service);
		}
		if (!$address instanceof Service_Xero_Entity_Contact_Addresses_Address) {
			throw new Service_Xero_Exception('Address must be of type Service_Xero_Entity_Contact_Addresses_Address!');
		}
		$this->_data['Addresses']->addAddress($address);
	}
	
	public function addPhone($phone) {
		if (!$this->_data['Phones'] instanceof Service_Xero_Entity_Contact_Phones) {
			$this->_data['Phones'] = new Service_Xero_Entity_Contact_Phones($this->_service);
		}
		if (!$phone instanceof Service_Xero_Entity_Contact_Phones_Phone) {
			throw new Service_Xero_Exception('Address must be of type Service_Xero_Entity_Contact_Phones_Phone!');
		}
		$this->_data['Phones']->addPhone($phone);
	}
	
	public function getAddresses() {
		return $this->Addresses->Address;
	}
	
	public function getPhones() {
		return $this->Phones->Phone;
	}
	
}
