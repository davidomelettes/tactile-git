<?php
/**
 * Thin wrapper for the XML response - extend to give more power to specific response-types
 *
 * @author gj
 */
class Service_Freshbooks_Response {
	
	const STATUS_FAIL = 'fail';
	const STATUS_OK = 'ok';
	
	/**
	 * @var Zend_Http_response
	 */
	protected $_httpResponse;
	
	/**
	 * @param SimpleXmlElement 
	 */
	protected $_xmlElement;
	
	/**
	 * @var Service_Freshbooks
	 */
	protected $_service;
	
	/**
	 * @param Zend_Http_Response $httpResponse
	 */
	public function __construct(Zend_Http_Response $httpResponse, Service_Freshbooks $service) {
		$this->_httpResponse = $httpResponse;
		$this->_service = $service;
		$this->_xmlElement = @simplexml_load_string($httpResponse->getBody());
		if(false !== $this->_xmlElement) {
			$this->_init();
		}
	}
	
	/**
	 * Called at the end of __construct, allows extensions to do stuff
	 *
	 */
	protected function _init() {}
	
	/**
	 * Returns whether the request itself was successful (i.e. HTTP request worked, and correct URL)
	 *
	 * @return unknown
	 */
	public function isValid() {
		return $this->_xmlElement !== false;
	}
	
	/**
	 * Return the status of the response - 'ok' or 'fail'
	 *
	 * @return string
	 */
	public function getStatus() {
		return (string)$this->_xmlElement['status'];
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
	 * @return Service_Freshbooks
	 */
	public function getService() {
		return $this->_service;
	}
	
	/**
	 * Return the Error Message that forms the response when status=fail
	 *
	 * @return string
	 */
	public function getErrorMsg() {
		if($this->getStatus() !== self::STATUS_FAIL) {
			return false;
		}
		return (string)$this->_xmlElement->error;
	}
	
	/**
	 * @return Zend_Http_Response
	 */
	public function getHttpResponse() {
		return $this->_httpResponse;
	}
	
	/**
	 * Before serializing, need to remove the need for the SimpleXmlElement
	 * (return only the properties to serialize)
	 *
	 * @return array
	 */
	public function __sleep() {
		if($this->isValid()) {
			$this->_xmlString = $this->_xmlElement->asXml();
		}
		else {
			$this->_xmlString = '';
		}
		return array('_xmlString', '_httpResponse', '_service');
	}
	
	/**
	 * Then, on unserialization, we need to put the SimpleXmlElement back
	 *
	 */
	public function __wakeup() {
		$this->_xmlElement = @simplexml_load_string($this->_xmlString);
		if(false !== $this->_xmlElement) {
			$this->_init();	
		}
	}
	
}
