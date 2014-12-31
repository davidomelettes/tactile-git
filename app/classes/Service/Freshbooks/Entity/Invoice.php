<?php

require_once 'Service/Freshbooks/Entity/Abstract.php';
require_once 'Service/Freshbooks/Entity/Line.php';

class Service_Freshbooks_Entity_Invoice extends Service_Freshbooks_Entity_Abstract {
	
	protected $_properties = array(
		'invoice_id',
		'number',
		'client_id',
		'recurring_id',
		'organization',
		'status',
		'amount',
		'amount_outstanding',
		'paid',
		'date',
		'first_name',
		'last_name',
		'p_street1',
		'p_street2',
		'p_city',
		'p_state',
		'p_country',
		'p_code',
		'po_number',
		'notes',
		'terms',
		'discount',
		'url',
		'auth_url'
	);
	
	protected $_lines = array();
	
	/**
	 * @param SimpleXmlElement $xml
	 * @param Service_Freshbooks $service
	 */
	public function __construct(SimpleXmlElement $xml, Service_Freshbooks $service) {
		parent::__construct($xml, $service);
		if(isset($xml->lines)) {
			foreach($xml->lines->line as $line) {
				$this->_lines[] = new Service_Freshbooks_Entity_Line($line, $service);
			}
		}
	}
	
	/**
	 * Return an array containing the Invoice lines
	 *
	 * @return array
	 */
	public function getLines() {
		if(!$this->_isFull) {
			throw new Service_Freshbooks_Entity_Exception("Only full invoices have lines");
		}
		return $this->_lines;
	}
	
	/**
	 * Invoices returned as part of a list don't contain as much detail as those accessed directly,
	 * this is a simple way to get the rest of the data
	 *
	 */
	public function getFullDetails() {
		if($this->_isFull) {
			return;
		}
		$query = new Service_Freshbooks_Query_Invoice('get');
		$query->addParam('invoice_id', $this->get('invoice_id'));
		$invoice = $this->getService()->execute($query)->getInvoice();
		$this->_data = $invoice->getData();
		unset($this->_data['lines']);
		$this->_lines = $invoice->getLines();
		$this->setIsFull();
	}
	
	/**
	 * Return the Invoice as an assoc array, including the lines (hence the overriding)
	 *
	 * @return array
	 */
	public function getData() {
		$data = parent::getData();
		$line_data = array();
		foreach($this->_lines as $line) {
			$line_data[] = $line->getData();
		}
		$data['lines'] = $line_data;
		return $data;
	}
	
	public function getDate($format = 'd/m/Y') {
		return date($format, strtotime($this->get('date')));
	}
	
	public function isPartPaid() {
		return 0 < $this->get('amount_outstanding') && $this->get('amount_outstanding') < $this->get('amount');
	}
}
