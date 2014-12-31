<?php

require_once 'Zend/Rest/Client.php';

require_once 'Service/Xero/Exception.php';
require_once 'Service/Xero/Response.php';

require_once 'Service/Xero/Entity/Abstract.php';
require_once 'Service/Xero/Entity/Contact.php';
require_once 'Service/Xero/Entity/Invoice.php';
require_once 'Service/Xero/Entity/Contacts.php';
require_once 'Service/Xero/Entity/Invoices.php';

class Service_Xero extends Zend_Service_Abstract {
	
	const ENDPOINT_TEST = 'https://networktest.xero.com/api.xro/1.0/';
	const ENDPOINT_PRODUCTION = 'https://network.xero.com/api.xro/1.0/';
	
	protected $_providerKey;
	protected $_customerKey;
	protected $_production;
	
	/**
	 * @param String $providerKey Key specific to 3rd party service (us)
	 * @param String $customerKey Key specific to Xero customer, generated specifically for our service
	 * @param Boolean $production Whether to use test server or not
	 */
	public function __construct($providerKey, $customerKey, $production=true) {
		$this->_providerKey = (string) $providerKey;
		$this->_customerKey = (string) $customerKey;
		$this->_production = (boolean) $production;
	}

	/**
	 * Submits HTTP request to Xero's API via REST
	 *
	 * @param String $path Path portion of API request URL
	 * @param Array $parameters Key => Value paired parameters for request
	 * @param String $method GET, POST, PUT
	 * @return Service_Xero_Response
	 */
	public function xeroRequest($path, $parameters=array(), $method=Zend_Http_Client::GET, $body=null) {
		if (!is_array($parameters)) {
			throw new Service_Xero_Exception('Expected Array of parameters for request!');
		}
		
		$client = $this->getHttpClient();
		$client->setConfig(array('maxredirects'=>0));
		
		$client->resetParameters()
			->setUri(($this->_production ? self::ENDPOINT_PRODUCTION : self::ENDPOINT_TEST) . $path)
			->setMethod($method);
		switch ($method) {
			case Zend_Http_Client::PUT:
			case Zend_Http_Client::GET: {
				$client->setParameterGet('apiKey', $this->_providerKey);
				$client->setParameterGet('xeroKey', $this->_customerKey);
				foreach ($parameters as $key => $value) {
					if ($value !== '') {
						$client->setParameterGet($key, $value);
					}
				}
				break;
			}
			case Zend_Http_Client::POST: {
				$client->setParameterGet('apiKey', $this->_providerKey);
				$client->setParameterGet('xeroKey', $this->_customerKey);
				foreach ($parameters as $key => $value) {
					if ($value !== '') {
						$client->setParameterPost($key, $value);
					}
				}
				break;
			}
		}
		
		if (!is_null($body)) {
			$client->setRawData($body);
		}
		
		try {
			$httpResponse = $client->request();
		} catch (Zend_Http_Client_Exception $e) {
			throw new Service_Xero_Exception("Communication failure with Xero: " . $e->getMessage());
		}
		$xeroResponse = new Service_Xero_Response($httpResponse);
		return $xeroResponse;
	}
	
}
