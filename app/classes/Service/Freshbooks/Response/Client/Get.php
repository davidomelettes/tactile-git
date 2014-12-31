<?php
require_once 'Service/Freshbooks/Response.php';

class Service_Freshbooks_Response_Client_Get extends Service_Freshbooks_Response {
	
	/**
	 * @var Service_Freshbooks_Entity_Client
	 */
	protected $_client;
	
	protected function _init() {
		require_once 'Service/Freshbooks/Entity/Client.php';
		$this->_client = new Service_Freshbooks_Entity_Client($this->_xmlElement->client, $this->getService());
		$this->_client->setIsFull();
	}
	
	/**
	 * @return Service_Freshbooks_Entity_Client
	 */
	public function getClient() {
		return $this->_client;
	}
	
	
	
}
