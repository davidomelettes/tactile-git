<?php
class Service_CampaignMonitor_List {
	
	protected $_listId, $_name = '';
	
	public function __construct($listId, $name = '') {
		$this->_listId = $listId;
		$this->_name = $name;
	}
	
	/**
	 *
	 * @param SimpleXmlElement $xml
	 * @return Service_CampaignMonitor_Client
	 */
	public static function fromXml(SimpleXmlElement $xml) {
		$listId = (string) $xml->ListID;
		$name = (string) $xml->Name;
		$list = new Service_CampaignMonitor_List($listId, $name);
		return $list;
	}
	
	public function getListId() {
		return $this->_listId;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Return a list of all subscribers added to the list after a given date
	 *
	 * @param Service_CampaignMonitor $service
	 * @param Date $date As 'Y-m-d H:i:d'
	 * @return Array|boolean
	 */
	public function getActive(Service_CampaignMonitor $service, $date) {
		$subscribers = $service->subscribersGetActive($this->getListId(), $date);
		return $subscribers;
	}
	
	/**
	 * Return a list of all people unsibscribed from the list since a given date
	 *
	 * @param Service_CampaignMonitor $service
	 * @param Date $date
	 * @return Array|boolean
	 */
	public function getUnsubscribed(Service_CampaignMonitor $service, $date) {
		$subscribers = $service->subscribersGetUnsubscribed($this->getListId(), $date);
		return $subscribers;
	}
	
	/**
	 * Add a subscriber to the list
	 *
	 * @param Service_CampaignMonitor $service
	 * @param Service_CampaignMonitor_Subscriber $subscriber
	 * @return boolean
	 */
	public function addSubscriber(Service_CampaignMonitor $service, Service_CampaignMonitor_Subscriber $subscriber) {
		$success = $service->subscriberAdd(
			$this->getListId(), 
			$subscriber->getEmailAddress(), 
			$subscriber->getName()
		);
		return $success;
	}
}
