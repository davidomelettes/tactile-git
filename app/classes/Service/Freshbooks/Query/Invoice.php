<?php

require_once 'Service/Freshbooks/Query.php';

/**
 * Wrapper for invoice.* queries
 *
 */
class Service_Freshbooks_Query_Invoice extends Service_Freshbooks_Query {
	
	/**
	 * All available/supported methods on invoice
	 *
	 * @var array
	 */
	protected static $_methods = array(
		'get',
		'list'
	);
	
	/**
	 * The method being used
	 *
	 * @var string
	 */
	protected $_method;
	
	/**
	 *
	 * @param string $method One from self::$_methods
	 */
	public function __construct($method) {
		if(!in_array($method, self::$_methods)) {		
			throw new Service_Freshbooks_Exception("Unknown method: " . $method);
		}
		$this->_method = $method;
		parent::__construct('invoice.' . $method);
	}
	
	/**
	 * Return the appropriate response-wrapper for the query
	 *
	 * @param Zend_Http_Response $httpResponse
	 * @param Service_Freshbooks $service
	 * @return Service_Freshbooks_Response
	 */
	public function getResponseWrapper($httpResponse, $service) {
		switch($this->_method) {
			case 'list':
				require_once 'Service/Freshbooks/Response/Invoice/List.php';
				return new Service_Freshbooks_Response_Invoice_List($httpResponse, $service);
			case 'get':
				require_once 'Service/Freshbooks/Response/Invoice/Get.php';
				return new Service_Freshbooks_Response_Invoice_Get($httpResponse, $service);
			default:
				return parent::getResponseWrapper($httpResponse, $service);
		}
		
	}
}
