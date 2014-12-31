<?php
class Service_CampaignMonitor_Subscriber {
	
	protected $_emailAddress, $_name, $_date, $_state;
	
	protected $_customFields = array();
	
	public function __construct($emailAddress, $name, $date, $state) {
		$this->_emailAddress = $emailAddress;
		$this->_name = $name;
		$this->_date = $date;
		$this->_state = $state;
	}
	
	/**
	 *
	 * @param SimpleXmlElement $xml
	 * @return Service_CampaignMonitor_Client
	 */
	public static function fromXml(SimpleXmlElement $xml) {
		$emailAddress = (string) $xml->EmailAddress;
		$name = (string) $xml->Name;
		$date = (string) $xml->Date;
		$state = (string) $xml->State;
		$subscriber = new Service_CampaignMonitor_Subscriber($emailAddress, $name, $date, $state);
		
		foreach($xml->CustomFields->SubscriberCustomField as $customField) {
			$subscriber->setCustomField((string)$customField->Key, (string)$customField->Value);
		}
		return $subscriber;
	}
	
	public function getEmailAddress() {
		return $this->_emailAddress;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Set the value of a custom field
	 *
	 * @param String $key
	 * @param String $value
	 */
	public function setCustomField($key, $value) {
		$this->_customFields[$key] = $value;
	}
	
	/**
	 * Return the value of a custom field, or empty-string if not set
	 *
	 * @param String $key
	 * @return String
	 */
	public function getCustomField($key) {
		return isset($this->_customFields[$key]) ? $this->_customFields[$key] : '';
	}
	
}
