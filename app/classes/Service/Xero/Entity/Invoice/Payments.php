<?php

require_once 'Service/Xero/Entity/Invoice/Payments/Payment.php';
class Service_Xero_Entity_Invoice_Payments extends Service_Xero_Entity_Abstract {
	
	protected $_properties = array(
		'Payment'
	);
	
	public function loadFromXml($xml) {
		$this->_data = array();
		$this->_simpleXml = $xml;
		$this->_eat($this->_properties, $this->_simpleXml, $this->_data);
		$this->_data['Payment'] = array();
		if (isset($this->_simpleXml->Payment)) {
			foreach ($this->_simpleXml->Payment as $payment) {
				$payments_payment = new Service_Xero_Entity_Invoice_Payments_Payment($this->_service);
				$payments_payment->loadFromXml($payment);
				$this->_data['Payment'][] = $payments_payment;
			}
		}
	}
	
	public function current() {
		return current($this->_data['Payment']);
	}
	
	public function next() {
		return next($this->_data['Payment']);
	}
	
	public function key() {
		return key($this->_data['Payment']);
	}
	
	public function valid() {
		return (FALSE !== $this->current());
	}
	
	public function rewind() {
		return reset($this->_data['Payment']);
	}
	
}
