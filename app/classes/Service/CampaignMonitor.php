<?php

require_once 'Zend/Http/Client.php';

class Service_CampaignMonitor {
	
	/**
	 * The API's endpoint. This is the HTTPS version.
	 *
	 */
	const ENDPOINT = 'https://secure.createsend.com/api/api.asmx/';
	
	/**
	 * @var Zend_Http_Client
	 */
	protected $_httpClient;
	
	/**
	 * The API Key to use for all calls
	 *
	 * @var String
	 */
	protected $_apiKey;
	
	/**
	 * @var Service_CampaignMonitor_Response
	 */
	protected $_lastResponse;
	
	/**
	 * Construct with the user's API Key
	 * @param String $apiKey
	 */
	public function __construct($apiKey) {
		$this->_apiKey = $apiKey;
	}
	
	/**
	 * Get the list of Clients attached to the API-Key
	 *
	 * @return Array|boolean
	 */
	public function userGetClients() {
		$response = $this->makeCall('User.GetClients');
		if($response->getCode() === Service_CampaignMonitor_Response::SUCCESS) {
			require_once 'Service/CampaignMonitor/Client.php';
			$clients = array();
			foreach($response->getXml()->Client as $client) {
				$clients[] = Service_CampaignMonitor_Client::fromXml($client);
			}
			return $clients;
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * Returns new Active subscribers for a list, since the given date
	 * @see CampaignMonitor::subscribersGet()
	 *
	 * @param String $listId
	 * @param Date $date A date as 'Y-m-d H:i:s'
	 * @return Array|boolean
	 */
	public function subscribersGetActive($listId, $date) {
		return $this->subscribersGet('Active', $listId, $date);
	}
	
	/**
	 * Returns the subscribers who unsubscribed from the List after the given date
	 * @see CampaignMonitor::subscribersGet()
	 *
	 * @param String $listId
	 * @param Date $date A date as 'Y-m-d H:i:s'
	 * @return Array|boolean
	 */
	public function subscribersGetUnsubscribed($listId, $date) {
		return $this->subscribersGet('Unsubscribed', $listId, $date);
	}
	
	/**
	 * Returns the subscribers who had email-bounces after the given date
	 * @see CampaignMonitor::subscribersGet()
	 *
	 * @param String $listId
	 * @param Date $date A date as 'Y-m-d H:i:s'
	 * @return Array|boolean
	 */
	public function subscribersGetBounced($listId, $date) {
		return $this->subscribersGet('Bounced', $listId, $date);
	}
	
	/**
	 * Helper method for the various subscribersGetXXXXX methods (Active, Unsubscribed etc.)
	 *
	 * @param String $type The type of subscribers to return
	 * @param String $listId The ID of the List
	 * @param Date $date Date as 'Y-m-d H:i:s', get subscribers since this date
	 * @return Array|boolean
	 */
	public function subscribersGet($type, $listId, $date) {
		$response = $this->makeCall('Subscribers.Get' . $type, array(
			'ListID' => $listId,
			'Date' => $date
		));
		if($response->getCode() === Service_CampaignMonitor_Response::SUCCESS) {
			require_once 'Service/CampaignMonitor/Subscriber.php';
			$subscribers = array();
			foreach($response->getXml()->Subscriber as $list) {
				$subscribers[] = Service_CampaignMonitor_Subscriber::fromXml($list);
			}
			return $subscribers;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Get the list of lists for the specified clientId
	 *
	 * @param String $clientId
	 * @return Array|boolean
	 */
	public function clientGetLists($clientId) {
		$response = $this->makeCall('Client.GetLists', array('ClientID' => $clientId));
		if($response->getCode() === Service_CampaignMonitor_Response::SUCCESS) {
			require_once 'Service/CampaignMonitor/List.php';
			$lists = array();
			foreach($response->getXml()->List as $list) {
				$lists[] = Service_CampaignMonitor_List::fromXml($list);
			}
			return $lists;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Add a new subscriber to a list
	 *
	 * @param String $listId
	 * @param String $email
	 * @param String $name optional
	 * @return boolean
	 */
	public function subscriberAdd($listId, $email, $name = '') {
		$response = $this->makeCall('Subscriber.Add', array(
			'ListID' => $listId,
			'Email' => $email,
			'Name' => $name
		));
		return $response->getCode() === Service_CampaignMonitor_Response::SUCCESS;
	}
	
	/**
	 * Unsubscribe an email address from a list
	 *
	 * @param String $listId The CM ListID
	 * @param String $email The subscriber's email address
	 * @return boolean
	 */
	public function subscriberUnsubscribe($listId, $email) {
		$response = $this->makeCall('Subscriber.Unsubscribe', array(
			'ListID' => $listId,
			'Email' => $email
		));
		return $response->getCode() === Service_CampaignMonitor_Response::SUCCESS;
	}
	
	/**
	 *
	 * @param String $method The API method name e.g. 'User.GetClients'
	 * @param Array $args optional Any additional arguments the method accepts
	 * @return Service_CampaignMonitor_Response
	 */
	public function makeCall($method, $args = array()) {
		require_once 'Service/CampaignMonitor/Response.php';
		$client = $this->getHttpClient();
		$client->setUri($this->_getUrl($method));
		
		$client->setParameterGet('ApiKey', $this->_apiKey);
		
		foreach($args as $key => $val) {
			$client->setParameterGet($key, $val);
		}
		
		try {
			$response = $client->request();
		}
		catch(Zend_Http_Client_Exception $e) {
			return false;
		}
		$cm_response = $this->_lastResponse = new Service_CampaignMonitor_Response($response);
		return $cm_response;
	}
	
	/**
	 * Return the URL to use for the supplied method
	 *
	 * @param String $method
	 * @return String
	 */
	protected function _getUrl($method) {
		return self::ENDPOINT . $method;
	}
	
	/**
	 *
	 * @return Zend_Http_Client
	 */
	public function getHttpClient() {
		if(!isset($this->_httpClient)) {
			$this->_httpClient = new Zend_Http_Client();
		}
		return $this->_httpClient;
	}
	
	/**
	 * Set the instance of Http_Client to use
	 *
	 * @param Zend_Http_Client $httpClient
	 */
	public function setHttpClient(Zend_Http_Client $httpClient) {
		$this->_httpClient = $httpClient;
	}
	
	/**
	 *
	 * @return Service_CampaignMonitor_Response
	 */
	public function getLastResponse() {
		return $this->_lastResponse;
	}
}

