<?php

require_once 'Service/Xero.php';
require_once 'Service/Xero/Entity/Exception.php';
abstract class Service_Xero_Entity_Abstract {

	/**
	 * Xero service
	 *
	 * @var Service_Xero
	 */
	protected $_service;
	
	/**
	 * An array containing the names of the properties the response can contain
	 *
	 * @var array
	 */
	protected $_properties = array();
	
	/**
	 * XML response
	 *
	 * @var SimpleXMLElement
	 */
	protected $_simpleXml;
	
	/**
	 * Response interpreted as Associative array
	 *
	 * @var Array
	 */
	protected $_data = array();

	public function __construct($service) {
		$this->_service = $service;
		foreach ($this->_properties as $property) {
			$this->_data[$property] = null;
		}
	}
	
	public function loadFromXml($xml) {
		$this->_data = array();
		$this->_simpleXml = $xml;
		$this->_eat($this->_properties, $this->_simpleXml, $this->_data);
	}
	
	protected function _eat($properties, $xml, &$destination) {
		foreach ($properties as $index => $property) {
			if (is_array($property)) {
				if (count($xml->$index) > 1) {
					foreach ($xml->$index as $node) {
						$this->_eat($property, $node, $destination[$index][]);
					}
				} else {
					$this->_eat($property, $xml->$index, $destination[$index]);
				}
			} else {
				if (is_array($xml)) {
					foreach ($xml->$property as $node) {
						$destination[$property][] = (string) $node;
					}
				} elseif (isset($xml->$property)) {
					$destination[$property] = (string) $xml->$property;
				}
			}
		}
	}
	
	public function is_assoc($data) {
		return (is_array($data) && 0 !== count(array_diff_key($data, array_keys(array_keys($data)))));
	}
	
	public function _spew($data, $xml) {
		if (!$xml instanceof SimpleXMLElement) {
			//throw new Service_Xero_Entity_Exception('Expecting instance of SimpleXMLElement: ' . print_r($xml,1));
			return;
		}
		if ($data instanceof Service_Xero_Entity_Abstract) {
			$data = $data->getData();
		}
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				if ($this->is_assoc($value)) {
					$xml->addChild($key);
					$this->_spew($value, $xml->$key);
				} else {
					foreach ($value as $k => $v) {
						$node = $xml->addChild($key);
						$this->_spew($v, $node);
					}
				}
			} else {
				if (!is_null($value)) {
					if ($value instanceof Service_Xero_Entity_Abstract) {
						$xml->addChild($key);
						$this->_spew($value->getData(), $xml->$key);
					} else {
						$dirty = (string) $value;
						if (mb_detect_encoding($dirty) !== 'UTF-8') {
							$dirty = iconv(mb_detect_encoding($dirty), 'UTF-8//TRANSLIT', $dirty);
						}
						$clean = empty($dirty) ? $dirty : "<![CDATA[".htmlentities($dirty)."]]>";
						$xml->addChild($key, $clean);
					}
				}
			}
		}
		return $xml;
	}
	
	public function __get($key) {
		return $this->_access($key);
	}
	
	private function _access($key) {
		if (!in_array($key, $this->_properties)) {
			throw new Service_Xero_Entity_Exception("Invalid property requested: " . $key);
		}
		return !empty($this->_data[$key]) ? $this->_data[$key] : '';
	}
	
	public function set($key, $value) {
		if (!in_array($key, $this->_properties)) {
			throw new Service_Xero_Entity_Exception("Invalid property set: " . $key);
		}
		$this->_data[$key] = $value;
		return $this;
	}
	
	public function getData() {
		return $this->_data;
	}
	
}

