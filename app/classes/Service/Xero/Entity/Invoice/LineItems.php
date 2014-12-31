<?php

require_once 'Service/Xero/Entity/Invoice/LineItems/LineItem.php';
class Service_Xero_Entity_Invoice_LineItems extends Service_Xero_Entity_Abstract {
	
	protected $_properties = array(
		'LineItem'
	);
	
	public function loadFromXml($xml) {
		$this->_data = array();
		$this->_simpleXml = $xml;
		$this->_eat($this->_properties, $this->_simpleXml, $this->_data);
		$this->_data['LineItem'] = array();
		if (isset($this->_simpleXml->LineItem)) {
			foreach ($this->_simpleXml->LineItem as $lineitem) {
				$lineitems_lineitem = new Service_Xero_Entity_Invoice_LineItems_LineItem($this->_service);
				$lineitems_lineitem->loadFromXml($lineitem);
				$this->_data['LineItem'][] = $lineitems_lineitem;
			}
		}
	}
	
	public function current() {
		return current($this->_data['LineItem']);
	}
	
	public function next() {
		return next($this->_data['LineItem']);
	}
	
	public function key() {
		return key($this->_data['LineItem']);
	}
	
	public function valid() {
		return (FALSE !== $this->current());
	}
	
	public function rewind() {
		return reset($this->_data['LineItem']);
	}
	
	public function addLineItem($lineitem) {
		if (!$lineitem instanceof Service_Xero_Entity_Invoice_LineItems_LineItem) {
			throw new Service_Xero_Exception('Line Item must be of type Service_Xero_Entity_Invoice_LineItems_LineItem!');
		}
		$this->_data['LineItem'][] = $lineitem;
	}
	
}
