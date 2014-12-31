<?php
require_once 'Service/Freshbooks/Response/ListAbstract.php';

class Service_Freshbooks_Response_Client_List extends Service_Freshbooks_Response_ListAbstract {
	
	/**
	 * An array of Service_Freshbooks_Entity_Client objects
	 * 
	 * @var array
	 */
	protected $_clients = array();
	
	/**
	 * Setup method for the Response, turns the XML into an array of Client Entities
	 * - resets the array before adding clients
	 */
	protected function _init() {
		require_once 'Service/Freshbooks/Entity/Client.php';
		$this->_clients = array();
		foreach($this->_xmlElement->clients->client as $client) {
			$this->_clients[] = new Service_Freshbooks_Entity_Client($client, $this->getService());
		}
	}
	
	/**
	 * Return an array of Service_Freshbooks_Entity_Client objects
	 *
	 * @return array
	 */
	public function getClients() {
		return $this->_clients;
	}
	
	/**
	 * Returns the DOM element that holds the list of clients
	 * (prescribed by Service_Freshbooks_Response_ListAbstract)
	 *
	 * @return DOMElement
	 */
	public function getListElement() {
		return $this->_xmlElement->clients;
	}
	
	/**
	 * Take the response from another client.list query and merge the results with this one
	 * - allows multiple 'pages' of results to be stuck together to be treated as if a single response
	 *
	 * @param Service_Freshbooks_Response_Client_List $new_response
	 */
	public function merge(Service_Freshbooks_Response_Client_List $new_response) {
		foreach($new_response->getClients() as $client) {
			$this->addClientToList($client);
		}
		$this->_init();
		$this->_xmlElement->clients['page'] = $new_response->getPage();
	}
	
	/**
	 * Take a Client entity-object and insert its values into the clients part of this result
	 * - used as part of merging, will need to handle fixing 'page' and calling _init() if used elsewhere
	 *
	 * @param Service_Freshbooks_Entity_Client $client
	 */
	public function addClientToList(Service_Freshbooks_Entity_Client $client) {
		$client_node = $this->_xmlElement->clients->addChild('client');
		foreach($client->getData() as $key => $value) {
			$value = preg_replace('/&(?!\w+;)/', '&amp;', $value); // AddChild() throws a warn on ampersands
			$client_node->addChild($key, $value);
		}
	}
	
}
