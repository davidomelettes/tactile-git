<?php

require 'Service/Zendesk/Entity/Exception/TypeMismatch.php';

/**
 * Abstract base class for Zendesk entities
 */
abstract class Service_Zendesk_Entity_Abstract {
	/** Stores the original SimpleXMLElement object for later debugging */
	protected $_xml;
	protected $_entity_type;
	protected $_callable_properties = array();
	
	public function __construct(SimpleXMLElement $xml) {
		// if ($xml->getName() != $this->_entity_type) {
		// 	throw new Service_Zendesk_Entity_Exception_TypeMismatch("Got " . $xml->getName . " but expected " . $this->_entity_type);
		// }
		
		$this->_xml = $xml;
	}
	
	public function __get($property) {
		if (in_array($property, $this->_callable_properties)) {
			return $this->$property();
		} else {
			return $this->_xml->$property;
		}
	}
	
	protected function _getNode($name) {
		$node = $this->_xml->xpath($name);
		return $node[0];
	}
	
	public function __sleep() {
		$this->_xml = $this->_xml->asXML();
		
		return array (
			"\0*\0"."_xml",
			"\0*\0"."_entity_type",
			"\0*\0"."_callable_properties"
		);
	}
	
	public function __wakeup() {
		$this->_xml = simplexml_load_string($this->_xml);
	}
}