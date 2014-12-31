<?php
class Service_CampaignMonitor_Response {
	
	const SUCCESS = 0;
	
	const INVALID_EMAIL_ADDRESS = 1;	
	const INVALID_API_KEY = 100;
	const INVALID_LIST_ID = 101;
	const EMAIL_SUPPRESSED = 204;
	
	protected static $_errorMessages = array(
		self::INVALID_API_KEY => 'Invalid API Key',
		self::INVALID_EMAIL_ADDRESS => 'Invalid Email Address',
		self::INVALID_LIST_ID => 'Invalid List ID',
		self::EMAIL_SUPPRESSED => 'Email Address in suppression list'
	);
	
	/**
	 * @var Zend_Http_Response
	 */
	protected $_httpResponse;
	
	public function __construct(Zend_Http_Response $httpResponse) {
		$this->_httpResponse = $httpResponse;
		$this->_xmlElement = @simplexml_load_string($httpResponse->getBody());
	}
	
	/**
	 * Returns whether the request itself was successful (i.e. HTTP request worked, and correct URL)
	 *
	 * @return unknown
	 */
	public function isValid() {
		return $this->_xmlElement !== false;
	}
	
	/**
	 * Return the code of the response.
	 * Some responses, 'getXXX' don't have a code, so assume success
	 *
	 * @return string
	 */
	public function getCode() {
		if(!isset($this->_xmlElement->Code)) {
			return self::SUCCESS;
		}
		return (int)$this->_xmlElement->Code;
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
	 * Return the Error Message that forms the response when status=fail
	 *
	 * @return string
	 */
	public function getErrorMsg() {
		if($this->getCode() === self::SUCCESS) {
			return false;
		}
		return self::$_errorMessages[$this->getCode()];
	}
	
	/**
	 * @return Zend_Http_Response
	 */
	public function getHttpResponse() {
		return $this->_httpResponse;
	}
	
}

