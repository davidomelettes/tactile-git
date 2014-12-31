<?php
class Service_Freshbooks_Query {
	
	/**
	 * @var DomDocument
	 */
	protected $_dom;
	
	/**
	 * @var DomElement
	 */
	protected $_requestNode;
	
	/**
	 * Specify the type of query that's required, in the form <entity>.<method> i.e. 'client.list'
	 *
	 * @param string $method
	 */
	public function __construct($method) {
		$this->_dom = new DOMDocument('1.0', 'utf-8');
		$this->_requestNode = $this->_dom->createElement('request');
		$this->_requestNode->setAttribute('method', $method);
		$this->_dom->appendChild($this->_requestNode);
	}
	
	/**
	 * Add a parameter to the query (an element in the XML body)
	 *  - doesn't replace existing parameter with same name
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function addParam($key, $value) {
		$param = $this->_dom->createElement($key, $value);
		$this->_requestNode->appendChild($param);
	}
	
	/**
	 * Return an XML string for the query
	 *
	 * @param boolean $prettyPrint Whether to pretty-print the XML (for debugging?)
	 * @return string
	 */
	public function asXmlString($prettyPrint = false) {
		if($prettyPrint) {
			$this->_dom->formatOutput = true;
		}
		$output = trim($this->_dom->saveXML());
		$this->_dom->formatOutput = false;
		return $output;
	}
	
	/**
	 * Returns an instance of the class to be used for responses
	 * (allows subclasses to prescribe more specific things)
	 *
	 * @param Zend_Http_Response $httpResponse
	 * @param Service_Freshbooks $service
	 * @return Service_Freshbooks_Response
	 */
	public function getResponseWrapper($httpResponse, $service) {
		require_once 'Service/Freshbooks/Response.php';
		return new Service_Freshbooks_Response($httpResponse, $service);
	}
	
}
