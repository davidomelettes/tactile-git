<?php

require_once 'Service/Freshbooks/Entity/Abstract.php';
require_once 'Service/Freshbooks/Entity/Line.php';

class Service_Freshbooks_Entity_Estimate extends Service_Freshbooks_Entity_Abstract {
	
	/**
	 * For converting between the numbers FB gives back when it's broken and string-statuses
	 * @var array
	 */
	protected static $_statuses = array(
		1 => 'draft',
		2 => 'sent',
		4 => 'replied',
		5 => 'accepted',
		6 => 'invoiced'
	);
	
	/**
	 * The properties that an estimate can have
	 * @var array
	 */
	protected $_properties = array(
		'estimate_id',
		'number',
		'client_id',
		'organization',
		'status',
		'amount',
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
		'discount'
	);
	
	/**
	 * An array containing the data for any of the lines that make up the estimate
	 * @var array
	 */
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
			throw new Service_Freshbooks_Entity_Exception("Only full estimates have lines");
		}
		return $this->_lines;
	}
	
	/**
	 * Estimates returned as part of a list don't contain as much detail as those accessed directly,
	 * this is a simple way to get the rest of the data
	 *
	 */
	public function getFullDetails() {
		if($this->_isFull) {
			return;
		}
		$query = new Service_Freshbooks_Query_Estimate('get');
		$query->addParam('estimate_id', $this->get('estimate_id'));
		$response = $this->getService()->execute($query);
		$estimate = $response->getEstimate();
		$this->_data = $estimate->getData();
		unset($this->_data['lines']);
		$this->_lines = $estimate->getLines();
		$this->setIsFull();
	}
	
	/**
	 * Return the Estimate as an assoc array, including the lines (hence the overriding)
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
	
	public function get($key) {
		$value = parent::get($key);
		
		/*
		 * @TODO
		 * Bug with Freshbooks means that for estimate.list, the 'status' value is coming back as a number
		 * so we'll translate it ourselves
		 *  - remove if this stops being the case!
		 */
		if($key == 'status' && is_numeric($value)) {
			if(isset(self::$_statuses[$value])) {
				return self::$_statuses[$value];
			}
		}
		return $value;
	}
}
