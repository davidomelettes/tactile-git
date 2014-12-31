<?php

require_once 'Service/Freshbooks/Query.php';

/**
 * Wrapper for client.* queries
 *
 */
class Service_Freshbooks_Query_Client extends Service_Freshbooks_Query {
	
	protected static $_methods = array(
		'create',
		'update',
		'get',
		'delete',
		'list'
	);
	
	/**
	 * The particular method being accessed
	 *
	 * @var string
	 */
	protected $_method;
	
	/**
	 *
	 * @param string $method
	 */
	public function __construct($method) {
		if(!in_array($method, self::$_methods)) {		
			throw new Service_Freshbooks_Exception("Unknown method: " . $method);
		}
		$this->_method = $method;
		parent::__construct('client.' . $method);
	}
	
	/**
	 * Detemine which response-wrapper to use based on the method being used
	 *
	 * @param Zend_Http_Response $httpResponse
	 * @param Service_Freshbooks $service
	 * @return Service_Freshbooks_Response
	 */
	public function getResponseWrapper($httpResponse, $service) {
		switch($this->_method) {
			case 'list':
				require_once 'Service/Freshbooks/Response/Client/List.php';
				return new Service_Freshbooks_Response_Client_List($httpResponse, $service);
			case 'get':
				require_once 'Service/Freshbooks/Response/Client/Get.php';
				return new Service_Freshbooks_Response_Client_Get($httpResponse, $service);
			default:
				return parent::getResponseWrapper($httpResponse, $service);
		}
		
	}
	
	public function addEntity(Service_Freshbooks_Entity_Abstract $entity, $key) {
		$entity_node = $this->_dom->createElement($key);
		foreach($entity->getData() as $att_key => $value) {
			$attribute_node = $this->_dom->createElement($att_key, $value);
			$entity_node->appendChild($attribute_node);
		}
		$this->_requestNode->appendChild($entity_node);
	}
	
	
}
