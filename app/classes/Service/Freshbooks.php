<?php
require_once 'Zend/Http/Client.php';
require_once 'Service/Freshbooks/Exception.php';

class Service_Freshbooks {
	
	const ENDPOINT = 'https://%s.freshbooks.com/api/2.1/xml-in';
	
	protected $_account, $_token;
	
	protected static $_defaultHttpClient;
	
	/**
	 * @var Zend_Http_Client
	 */
	protected $_httpClient;
	
	/**
	 * Enter description here...
	 *
	 * @param String $account The freshbooks account-name (x.freshbooks.com)
	 * @param string $token The API token for the user
	 */
	public function __construct($account, $token) {
		$this->_account = $account;
		$this->_token = $token;
	}
	
	/**
	 * Return the URL for the account being accessed
	 *
	 * @return string
	 */
	public function getUrl() {
		return sprintf(self::ENDPOINT, $this->_account);
	}
	
	/**
	 * @return Zend_Http_Client
	 */
	public function getHttpClient() {
		if(!is_null(self::$_defaultHttpClient)) {
			$this->_httpClient = self::$_defaultHttpClient;
		}
		if(is_null($this->_httpClient)) {
			$this->_httpClient = new Zend_Http_Client(null, array(
				'useragent' => 'Tactile CRM / Zend_Http_Client'			
			));
		}
		return $this->_httpClient;
	}
	
	public function setHttpClient(Zend_Http_Client $client) {
		$this->_httpClient = $client;
	}
	
	public function newQuery($method) {
		list($object, $method) = explode('.', $method);
		switch($object) {
			case 'client':
				require_once 'Service/Freshbooks/Query/Client.php';
				$query = new Service_Freshbooks_Query_Client($method);
				break;
			default:
				throw new Service_Freshbooks_Exception("Calling methods on unknown object: " . $object . '.' . $method);
		}
		return $query;
	}
	
	/**
	 * Return a new Query object for doing things with Clients
	 *
	 * @param string $method list/get/create etc.
	 * @return Service_Freshbooks_Query_Client
	 */
	public function newClientQuery($method) {
		require_once 'Service/Freshbooks/Query/Client.php';
		$query = new Service_Freshbooks_Query_Client($method);
		return $query;
	}
	
	/**
	 * Return a new Query object for doing things with Invoices
	 * 
	 * @param String $method
	 * @return Service_Freshbooks_Query_Invoice
	 */
	public function newInvoiceQuery($method) {
		require_once 'Service/Freshbooks/Query/Invoice.php';
		$query = new Service_Freshbooks_Query_Invoice($method);
		return $query;
	}
	
	/**
	 * Return a new Query object for doing things with Estimates
	 * 
	 * @param String $method
	 * @return Service_Freshbooks_Query_Estimate
	 */
	public function newEstimateQuery($method) {
		require_once 'Service/Freshbooks/Query/Estimate.php';
		$query = new Service_Freshbooks_Query_Estimate($method);
		return $query;
	}

	/**
	 * 
	 * @param Service_Freshbooks_Query $query
	 * @return Service_Freshbooks_Response
	 */
	public function execute($query) {
		$client = $this->getHttpClient();
		$client->setUri($this->getUrl());
		$client->setAuth($this->_token);
		$client->setRawData($query->asXmlString());
		
		$response = $client->request();
		$this->_lastResponse = $response;
		$fb_response = $query->getResponseWrapper($response, $this);
		return $fb_response;
	}
	
	/**
	 *
	 * @return Zend_Http_Response
	 */
	public function getLastResponse() {
		return $this->_lastResponse;
	}
	
	public static function setDefaultHttpClient(Zend_Http_Client $client = null) {
		self::$_defaultHttpClient = $client;
	}
	
}
