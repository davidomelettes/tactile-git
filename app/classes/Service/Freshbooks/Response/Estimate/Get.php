<?php
require_once 'Service/Freshbooks/Response.php';

/**
 * Wrapper for invoice.get requests
 *
 */
class Service_Freshbooks_Response_Estimate_Get extends Service_Freshbooks_Response {
	
	/**
	 * @var Service_Freshbooks_Entity_Estimate
	 */
	protected $_estimate;
	
	/**
	 * Creates an invoice Entity from the contents of the XML's 'invoice' element
	 */
	protected function _init() {
		require_once 'Service/Freshbooks/Entity/Estimate.php';
		$this->_estimate = new Service_Freshbooks_Entity_Estimate($this->_xmlElement->estimate, $this->getService());
		$this->_estimate->setIsFull();
	}
	
	/**
	 * @return Service_Freshbooks_Entity_Invoice
	 */
	public function getEstimate() {
		return $this->_estimate;
	}
	
	
	
}
