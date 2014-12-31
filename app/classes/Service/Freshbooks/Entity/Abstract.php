<?php

/**
 * Base for all response entities (clients, invoices etc.)
 *
 */
abstract class Service_Freshbooks_Entity_Abstract {
	
	/**
	 * An array containing the names of the properties the response can contain
	 *
	 * @var array
	 */
	protected $_properties = array();
	
	/**
	 * An array containing the response data
	 *
	 * @var array
	 */
	protected $_data = array();
	
	/**
	 * Indicates whether the response was a direct 'get' (then true) or part of a 'list' (false)
	 *
	 * @var boolean
	 */
	protected $_isFull = false;
	
	/**
	 * @var Service_Freshbooks
	 */
	protected $_service;
	
	/**
	 * @param SimpleXmlElement|array $xml
	 * @param Service_Freshbooks $service
	 */
	public function __construct($values, Service_Freshbooks $service) {
		if($values instanceof SimpleXMLElement) {
			foreach($this->_properties as $key) {
				if(isset($values->$key)) {
					$this->set($key, (string)$values->$key);
				}
			}
		}
		else if(is_array($values)) {
			$this->setData($values);
		}
		
		$this->_service = $service;
	}
	
	/**
	 * Get a particular key from the response data, will throw exception if key isn't valid for the response
	 * returns null if the key's valid but value is empty
	 *
	 * @param string $key
	 * @return string
	 */
	public function get($key) {
		if(!in_array($key, $this->_properties)) {
			require_once 'Service/Freshbooks/Entity/Exception.php';
			throw new Service_Freshbooks_Entity_Exception("Invalid property requested: " . $key);
		}
		if(!isset($this->_data[$key])) {
			return null;
		}
		return $this->_data[$key];
	}
	
	/**
	 * Set a value, throws exception if key isn't valid for entity-typ
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value) {
		if(!in_array($key, $this->_properties)) {
			require_once 'Service/Freshbooks/Entity/Exception.php';
			throw new Service_Freshbooks_Entity_Exception("Invalid property to set: " . $key);
		}
		$this->_data[$key] = $value;
	}
	
	/**
	 * Set data from an assoc array
	 *
	 * @param array $values
	 */
	public function setData($values) {
		foreach($values as $key => $value) {
			$this->set($key, $value);
		}
	}
	
	/**
	 * Magic __get for direct property access
	 * @see self::get()
	 *
	 * @param string $key
	 * @return string
	 */
	public function __get($key) {
		return $this->get($key);
	}
	
	/**
	 * @return Service_Freshbooks
	 */
	public function getService() {
		return $this->_service;
	}
	
	/**
	 * Return the response data as an assoc array
	 *
	 * @return array
	 */
	public function getData() {
		return $this->_data;
	}
	
	/**
	 * Set that the client is 'full', i.e. was got through a 'get' and not part of a list
	 *
	 * @param boolean $isFull
	 */
	public function setIsFull($isFull = true) {
		$this->_isFull = $isFull;
	}
	
	public function addEntity($key, $entity) {
		$this->_data[$key] = $entity->toArray();
	}
	
}

