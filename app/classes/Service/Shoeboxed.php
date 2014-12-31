<?php

require_once 'Zend/Http/Client.php';
require_once 'Service/Shoeboxed/Exception.php';

class Service_Shoeboxed {

	const API_URL = 'https://api.shoeboxed.com/v1/ws/api.htm';
	const XMLNS = 'urn:sbx:apis:SbxBaseComponents';
	
	private $_appname;
	private $_appurl;
	private $_apparams;
	
	private $_apiToken;
	
	private $_user;
	private $_userToken;
	
	protected $_error = null;
	
	public function __construct($token, $appname, $appurl, $apparams) {
		$this->_apiToken = $token;
		$this->_appname = $appname;
		$this->_appurl = $appurl;
		$this->_apparams = $apparams;
	}
	
	public function getAuthUri() {
		$query_params = array(
			'SignIn'	=> 1,
			'appname'	=> $this->_appname,
			'appurl'	=> $this->_appurl,
			'apparams'	=> $this->_apparams
		);
		$location_uri = self::API_URL . '?' . http_build_query($query_params);
		return $location_uri;
	}
	
	public function setUserToken($user, $token) {
		$this->_user = $user;
		$this->_userToken = $token;
	}
	
	/**
	 * Single XML request to fetch a page of contacts
	 *
	 * @param int $page
	 * @return string
	 */
	public function getBusinessCardCallXML($page=1) {
		if (empty($this->_user) | empty($this->_userToken)) {
			throw new Service_Shoeboxed_Exception('No user or token! Must authenticate first!');
		}
		
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom_request = $dom->appendChild($dom->createElementNS(self::XMLNS, 'Request'));
		$dom_requester_credentials = $dom_request->appendChild($dom->createElement('RequesterCredentials'));
		$dom_requester_credentials->appendChild($dom->createElement('ApiUserToken', $this->_apiToken));
		$dom_requester_credentials->appendChild($dom->createElement('SbxUserToken', $this->_userToken));
		
		$dom_call = $dom_request->appendChild($dom->createElement('GetBusinessCardCall'));
		$dom_call_filter = $dom_call->appendChild($dom->createElement('BusinessCardFilter'));
		$dom_call_filter->appendChild($dom->createElement('Results', 200)); // 50, 100, 200
		$dom_call_filter->appendChild($dom->createElement('PageNo', $page));
		
		$xml = $dom->saveXML();
		
		return $xml;
	}
	
	/**
	 * Grabs all of the authenticated user's business card contacts, returns XML as string
	 *
	 * @return string
	 */
	public function getCards() {
		// Setup client
		$client = new Zend_Http_Client();
		$client->setUri(self::API_URL);
		$query = array('xml' => $this->getBusinessCardCallXML());
		$client->setParameterPost($query);
		
		// Do one call
		$response = $client->request(Zend_Http_Client::POST);
		if (!$response->isSuccessful()) {
			throw new Service_Shoeboxed_Exception('Error talking to Shoeboxed: '. $response->getMessage());
		}
		
		// Examine XML response
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML($response->getBody());
		$xp = new DOMXPath($dom);
		
		// Check for errors
		$error = $xp->evaluate('string(//Error/@description)');
		if (!empty($error)) {
			$this->_error = $error;
			return false;
		}
		
		$total = (int) $xp->evaluate('string(//BusinessCards/@count[1])');
		$received = (int) $xp->evaluate('count(//BusinessCard)');
		
		$page = 1;
		while ($total > $received) {
			// We need to get some more
			$page++;
			$query = array('xml' => $this->getBusinessCardCallXML($page));
			$client->setParameterGet($query);
			$response = $client->request();
			if (!$response->isSuccessful()) {
				throw new Service_Shoeboxed_Exception('Error talking to Shoeboxed: '. $response->getMessage());
			}
			
			$new_dom = new DOMDocument('1.0', 'UTF-8');
			$new_dom->loadXML($response->getBody());
			$new_xp = new DOMXPath($new_dom);
			
			$on_page = (int) $new_xp->evaluate('count(//BusinessCard)');
			if (0 == $on_page) {
				break;
			}
			$received += $on_page;
			$more_cards = $new_xp->query('//BusinessCard');
			foreach ($more_cards as $card) {
				$node = $dom->importNode($card, true);
				$dom->getElementsByTagName('BusinessCards')->item(0)->appendChild($node);
			}
		}
		
		// Return XML as string
		return $dom->saveXML();
	}
	
	public function getError()
	{
		return $this->_error;
	}
	
}
