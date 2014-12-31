<?php
require_once 'Service/Freshbooks/Response.php';

/**
 * Wrapper for invoice.get requests
 *
 */
class Service_Freshbooks_Response_Invoice_Get extends Service_Freshbooks_Response {
	
	/**
	 * @var Service_Freshbooks_Entity_Invoice
	 */
	protected $_invoice;
	
	/**
	 * Creates an invoice Entity from the contents of the XML's 'invoice' element
	 */
	protected function _init() {
		require_once 'Service/Freshbooks/Entity/Invoice.php';
		$this->_invoice = new Service_Freshbooks_Entity_Invoice($this->_xmlElement->invoice, $this->getService());
		$this->_invoice->setIsFull();
	}
	
	/**
	 * @return Service_Freshbooks_Entity_Invoice
	 */
	public function getInvoice() {
		return $this->_invoice;
	}
	
	
	
}
