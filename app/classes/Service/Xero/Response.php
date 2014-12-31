<?php

class Service_Xero_Response {
	
	const STATUS_OK = 'OK';
	const STATUS_OK_WITH_ERRORS = 'OK (with errors)';
	
	/**
	 * @var Zend_Http_response
	 */
	protected $_httpResponse;
	
	/**
	 * @param SimpleXmlElement 
	 */
	protected $_xmlElement;
	
	/**
	 * @param Array
	 */
	protected $_data;
	
	/**
	 * @param Zend_Http_Response $httpResponse
	 */
	public function __construct(Zend_Http_Response $httpResponse) {
		$this->_httpResponse = $httpResponse;
		$this->_xmlElement = @simplexml_load_string($httpResponse->getBody());
	}
	
	/**
	 * Return the SimpleXml representation of the response-body
	 *
	 * @return SimpleXmlElement
	 */
	public function getXml() {
		return $this->_xmlElement;
	}
	
	/**
	 * Return the status of the response - 'OK' or 'fail'
	 *
	 * @return string
	 */
	public function getStatus() {
		return (string) $this->_xmlElement->Status;
	}
	
	/**
	 * Returns whether the request itself was successful (i.e. HTTP request worked, and correct URL)
	 *
	 * @return Boolean
	 */
	public function isValid() {
		return $this->_xmlElement !== false;
	}
	
	/**
	 * Returns whether the request received was a 'success' or not
	 * 
	 * @return Boolean
	 */
	public function wasSuccessful() {
		return ($this->isValid() && $this->getStatus() === self::STATUS_OK);
	}
	
	/**
	 * Return the Error Message that forms the response was not successful
	 *
	 * @return string
	 */
	public function getErrorMsg() {
		if ($this->wasSuccessful()) {
			return false;
		}

		if (!$this->isValid()) {
			return $this->_httpResponse->getStatus() . ' ' . $this->_httpResponse->getMessage();
		}
		$errors = array();
		$xml = $this->getXml();
		if (!$xml instanceof SimpleXmlElement) {
			throw new Service_Xero_Exception('Failed to parse XML from response!');
		} else {
			foreach ($xml->xpath('//Errors/Error/Description/text()') as $error) {
				$errors[] = (string) $error;
			}
			return (!empty($errors) ? (implode(', ', $errors)) : 'Unsuccessful request');
		}
	}
	
}
