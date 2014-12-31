<?php
class Service_CampaignMonitor_Client {
	
	protected $_clientId, $_name = '';
	
	public function __construct($clientId, $name = '') {
		$this->_clientId = $clientId;
		$this->_name = $name;
	}
	
	/**
	 *
	 * @param SimpleXmlElement $xml
	 * @return Service_CampaignMonitor_Client
	 */
	public static function fromXml(SimpleXmlElement $xml) {
		$clientId = (string) $xml->ClientID;
		$name = (string) $xml->Name;
		$client = new Service_CampaignMonitor_Client($clientId, $name);
		return $client;
	}
	
	public function getClientId() {
		return $this->_clientId;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Get the lists for this ClientID
	 *
	 * @param Service_CampaignMonitor $service
	 * @return array|boolean An array of Service_CampaignMonitor_List objects or false
	 */
	public function getLists(Service_CampaignMonitor $service) {
		$lists = $service->clientGetLists($this->getClientId());
		return $lists;
	}
	
}
