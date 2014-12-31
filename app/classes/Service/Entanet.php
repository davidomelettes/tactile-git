<?php

require_once 'Service/Entanet.php';

class Service_Entanet {

	const ENTANET_DOMAIN = '%s.managevoip.net';
	const API_PATH = '/api/';
	
	const PARAMETER_SECURITY_TOKEN = 'sec';
	const PARAMETER_DOMAIN = 'domain';
	const PARAMETER_EXTENSION = 'exten';
	const PARAMETER_NUMBER_TO_DIAL = 'ddi';
	
	protected static $_methods = array(
		'dialExtension' => 'dialExten.php',
		'getCallerInfo' => 'getExten.php'
	);
	
	/**
	 * The auth details for making requests
	 *
	 * @var String
	 */
	protected $_domain, $_securityToken;
	
	/**
	 * @var Zend_Http_Client
	 */
	protected $_httpClient;
	
	/**
	 * @var Service_Entanet_Response
	 */
	protected $_lastResponse;
	
	/**
	 *
	 * @param String $domain
	 * @param String $securityToken
	 */
	public function __construct($domain, $securityToken) {
		$this->_domain = $domain;
		$this->_securityToken = $securityToken;
	}
	
	/**
	 * Make a request to the dialExten method. Returns true if successful, false otherwise
	 *
	 * @param String $extension The extension making the call
	 * @param String $numberToDial The number you want to call
	 * @return boolean
	 */
	public function dialExtension($extension, $numberToDial) {
		$response = $this->sendRequest('dialExtension', array(
			self::PARAMETER_EXTENSION => $extension,
			self::PARAMETER_NUMBER_TO_DIAL => $numberToDial
		));
		return $response->wasSuccessful();
	}
	
	/**
	 * Make a request to the getExten method (who's calling).
	 *
	 * @param String $extension The extension to check
	 * @return Service_Entanet_Response
	 */
	public function getCallerInfo($extension) {
		$response = $this->sendRequest('getCallerInfo', array(
			self::PARAMETER_EXTENSION => $extension
		));
		return $response;
	}
	
	/**
	 * Send a request to the API
	 *
	 * @param String $method
	 * @param Array $args optional
	 * @return Service_Entanet_Response
	 */
	public function sendRequest($method, $args = array()) {
		if(!isset(self::$_methods[$method])) {
			require_once 'Service/Entanet/Exception.php';
			throw new Service_Entanet_Exception("Invalid Entanet Method: " . $method);
		}
		
		$client = $this->getHttpClient();
		$client->setParameterGet(self::PARAMETER_SECURITY_TOKEN, $this->_securityToken);
		$client->setParameterGet(self::PARAMETER_DOMAIN, $this->_makeDomain($this->_domain));
		
		foreach($args as $key => $value) {
			$client->setParameterGet($key, $value);
		}
		
		$client->setUri($this->getEndpointUri($method));
		
		$httpResponse = $client->request();
		if($httpResponse->isError()) {
			require_once 'Service/Entanet/Exception.php';
			throw new Service_Entanet_Exception("HTTP Request failed: " . $httpResponse->getStatus());
		}
		require_once 'Service/Entanet/Response.php';
		$this->_lastResponse = $response = new Service_Entanet_Response($httpResponse);
		return $response;
	}
	
	/**
	 * Return the API-endpoint for the current domain (different per customer)
	 *
	 * @param $method The method-key
	 * @return String
	 */
	public function getEndpointUri($method) {
		return  'http://' . $this->_makeDomain($this->_domain)
			. self::API_PATH
			. self::$_methods[$method];
	}
	
	/**
	 * Return the full domain name for the given subdomain-part
	 *
	 * @param String $domain
	 * @return String
	 */
	protected function _makeDomain($domain) {
		return sprintf(self::ENTANET_DOMAIN, $domain);
	}
	
	/**
	 * Setter for alternative HTTP-client
	 *
	 * @param Zend_Http_Client $httpClient
	 */
	public function setHttpClient(Zend_Http_Client $httpClient) {
		$this->_httpClient = $httpClient;
	}
	
	/**
	 * Get/create the HTTP-client for making calls
	 *
	 * @return Zend_Http_Client
	 */
	public function getHttpClient() {
		if(is_null($this->_httpClient)) {
			require_once 'Zend/Http/Client.php';
			$this->_httpClient = new Zend_Http_Client();
		}
		return $this->_httpClient;
	}
	
	/**
	 * Returns the most recent Entanet_Response
	 * 
	 * @return Service_Entanet_Response
	 */
	public function getLastResponse() {
		return $this->_lastResponse;
	}
	
	public static function normalizeNumber($number) {
		return preg_replace('#^440?#','0',preg_replace('#[^0-9]#','', $number));
	}
}
