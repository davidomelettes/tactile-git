<?php

require_once 'Service/Xero/Entity/Contact.php';
require_once 'Service/Xero/Entity/Invoice/LineItems.php';
require_once 'Service/Xero/Entity/Invoice/Payments.php';
class Service_Xero_Entity_Invoice extends Service_Xero_Entity_Abstract {
	
	const TYPE_RECEIVABLE = 'ACCREC';
	const TYPE_PAYABLE = 'ACCPAY';
	
	protected $_properties = array(
		'InvoiceType',
		'InvoiceID',
		'Contact',
		'InvoiceDate',
		'DueDate',
		'InvoiceNumber',
		'Reference',
		'TaxInclusive',
		'IncludesTax',
		'SubTotal',
		'TotalTax',
		'Total',
		'InvoiceStatus',
		'LineItems',
		'Payments',
		'FullyPaidOnDate',
		'AmountDue',
		'AmountPaid',
		'AmountCredited'
	);
	
	public function loadFromXml($xml) {
		$this->_data = array();
		$this->_simpleXml = (isset($xml->Invoice) ? $xml->Invoice : $xml);
		$this->_eat($this->_properties, $this->_simpleXml, $this->_data);
		$this->_data['Contact'] = new Service_Xero_Entity_Contact($this->_service);
		$this->_data['Contact']->loadFromXml($this->_simpleXml->Contact);
		$this->_data['LineItems'] = new Service_Xero_Entity_Invoice_LineItems($this->_service);
		$this->_data['LineItems']->loadFromXml($this->_simpleXml->LineItems);
		$this->_data['Payments'] = new Service_Xero_Entity_Invoice_Payments($this->_service);
		$this->_data['Payments']->loadFromXml($this->_simpleXml->Payments);
	}
	
	public function setContact($contact) {
		$this->_data['Contact'] = $contact;
	}
	
	public function toXml() {
		return html_entity_decode($this->_spew($this->_data, new SimpleXMLElement('<Invoice/>'))->asXML());
	}
	
	public function get($invoiceID) {
		$xr = $this->_service->xeroRequest('invoice', array('invoiceID' => $invoiceID));
		if ($xr->wasSuccessful()) {
			$this->loadFromXml($xr->getXml());
			return $this;
		} else {
			throw new Service_Xero_Exception($xr->getErrorMsg());
		}
		return false;
	}
	
	public function put() {
		$xr = $this->_service->xeroRequest('invoice', array(), Zend_Http_Client::PUT, $this->toXml());
		if ($xr->wasSuccessful()) {
			$this->loadFromXml($xr->getXml());
			return $this;
		} else {
			throw new Service_Xero_Exception($xr->getErrorMsg());
		}
		return false;
	}
	
	public function addLineItem($lineitem) {
		if (!$this->_data['LineItems'] instanceof Service_Xero_Entity_Invoice_LineItems) {
			$this->_data['LineItems'] = new Service_Xero_Entity_Invoice_LineItems($this->_service);
		}
		if (!$lineitem instanceof Service_Xero_Entity_Invoice_LineItems_LineItem) {
			throw new Service_Xero_Exception('Line Item must be of type Service_Xero_Entity_Invoice_LineItems_LineItem!');
		}
		$this->_data['LineItems']->addLineItem($lineitem);
	}
	
	public function getLineItems() {
		return $this->LineItems->LineItem;
	}
	
}
