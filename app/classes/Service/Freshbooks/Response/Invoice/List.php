<?php
require_once 'Service/Freshbooks/Response/ListAbstract.php';

class Service_Freshbooks_Response_Invoice_List extends Service_Freshbooks_Response_ListAbstract {
	
	protected $_invoices = array();
	
	protected function _init() {
		require_once 'Service/Freshbooks/Entity/Invoice.php';
		if(isset($this->_xmlElement->invoices->invoice)) {
			foreach($this->_xmlElement->invoices->invoice as $invoice) {
				if(isset($invoice->invoice_id)) {
					$this->_invoices[] = new Service_Freshbooks_Entity_Invoice($invoice, $this->getService());
				}
			}
		}
	}
	
	/**
	 * Return an array of Service_Freshbooks_Entity_Invoice objects
	 *
	 * @return array
	 */
	public function getInvoices() {
		return $this->_invoices;
	}
	
	public function getListElement() {
		return $this->_xmlElement->invoices;
	}
	
}

