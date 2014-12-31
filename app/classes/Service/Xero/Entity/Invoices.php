<?php

require_once 'Service/Xero/Entity/Abstract.php';
class Service_Xero_Entity_Invoices extends Service_Xero_Entity_Abstract implements Iterator {

	protected $_properties = array(
		'Invoice',
	);
	
	public function loadFromXml($xml) {
		$this->_data = array();
		$this->_simpleXml = (isset($xml->Invoices) ? $xml->Invoices : $xml);
		$this->_eat($this->_properties, $this->_simpleXml, $this->_data);
		require_once 'Service/Xero/Entity/Invoice.php';
		$this->_data['Invoice'] = array();
		foreach ($this->_simpleXml->Invoice as $xml) {
			$invoice = new Service_Xero_Entity_Invoice($this->_service);
			$invoice->loadFromXml($xml);
			$this->_data['Invoice'][] = $invoice;
		}
	}
	
	public function get($modifiedSince = '') {
		$xr = $this->_service->xeroRequest('invoices', array('modifiedSince' => $modifiedSince));
		if ($xr->wasSuccessful()) {
			$this->loadFromXml($xr->getXml());
			return $this;
		} elseif ($xr->isValid()) {
			return false;
		} else {
			throw new Service_Xero_Exception($xr->getErrorMsg());
		}
	}
	
	public function current() {
		return current($this->_data['Invoice']);
	}
	
	public function next() {
		return next($this->_data['Invoice']);
	}
	
	public function key() {
		return key($this->_data['Invoice']);
	}
	
	public function valid() {
		return (FALSE !== $this->current());
	}
	
	public function rewind() {
		return reset($this->_data['Invoice']);
	}
	
}
