<?php

/**
 * Tactile API
 * 
 * @package Tactile
 */

/**
 * Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';

/**
 * Zend_Uri_Http 
 */
require_once 'Zend/Uri/Http.php';

/**
 * Tactile_Api_Exception
 */
require_once 'Tactile/Api/Exception.php';

/**
 * Tactile_Api_Object
 */
require_once 'Tactile/Api/Object.php';

/**
 * Class wrapper for working with Tactile's API
 * 
 * @author de
 * @version 1.0.0
 * @package Tactile
 */
class Tactile_Api {
	
	const VERSION = '1.0.0';
	const DEFAULT_DOMAIN = 'tactilecrm.com';	
	const CONTENT_TYPE_JSON = 'application/json';
	
	/**
	 * User's API Token
	 *
	 * @var string
	 */
	private $_token;
	
	/**
	 * xxx.tactilecrm.com
	 *
	 * @var string
	 */
	private $_siteAddress;
	
	/**
	 * tactilecrm.com
	 *
	 * @var string
	 */
	private $_domain;

	/**
	 * HTTP Client used for performing API requests
	 *
	 * @var Zend_Http_Client
	 */
	protected $_client = null;

	/**
	 * API class constructor
	 *
	 * @param string $site
	 * @param string $token
	 * @param Zend_Http_Client $client
	 * @param string $host
	 */
	public function __construct($site, $token, $client=null, $host=null) {
		$this->_setSiteAddress($site);
		$this->_setApiToken($token);
		$this->_client = (!is_null($client) ? $client : $this->getClient());
		$this->_setHost(isset($host) ? $host : self::DEFAULT_DOMAIN);
	}
	
	/**
	 * Set the API Token 
	 *
	 * @param string $token
	 */
	protected function _setApiToken($token) {
		$this->_token = $token;
	}
	
	/**
	 * Set the site address (http://xxx.tactilecrm.com)
	 *
	 * @param string $site
	 */
	protected function _setSiteAddress($site) {
		$this->_siteAddress = $site;
	}
	
	/**
	 * Set the host part of the URI
	 *
	 * @param string $domain
	 */
	protected function _setHost($domain) {
		$this->_domain = $domain;
	}
	
	/**
	 * Returns the base URI for API queries
	 *
	 * @return Zend_Uri_Http
	 */
	protected function _uri() {
		$uri = Zend_Uri_Http::factory('http');
		$uri->setHost($this->_siteAddress . '.' . $this->_domain);
		$uri->setPath('/');
		$uri->setQuery(array('api_token' => $this->_token));
		return $uri;
	}
	
	/**
	 * Performs the API request and returns the body of the result
	 *
	 * @param string|Zend_Uri_Http $uri
	 * @param string $jsonBody
	 * @return string
	 */
	public function fetch($uri, $jsonBody=null) {
		/* @var $client Zend_Http_Client */
		$client = $this->getClient();
		$client->setUri($uri);
		
		if (isset($jsonBody)) {
			$client->setMethod(Zend_Http_Client::POST);
			$client->setHeaders('Content-type', self::CONTENT_TYPE_JSON);
			$client->setRawData($jsonBody);
		}
		
		/* @var $response Zend_Http_Response */
		$response = $client->request();
		
		if (!$response->isError()) {
			if (self::CONTENT_TYPE_JSON != $response->getHeader('Content-type')) {
				throw new Tactile_Api_Exception('Content-type mismatch! Expected ' .
					self::CONTENT_TYPE_JSON . ', received ' . $response->getHeader('Content-type'));
			} else {
				return $response->getBody();
			}
		} else {
			throw new Tactile_Api_Exception('Failed to fetch URI! ' .
				$response->getStatus() . ': ' . $response->getMessage());
		}
	}
	
	/**
	 * Wrapper function API requests, accepts an optional set of GET parameters 
	 *
	 * @param string $path
	 * @param array $query
	 * @param string $jsonBody
	 * @return stdClass
	 */
	public function apiRequest($path, $query=array(), $jsonBody=null) {
		$uri = $this->_uri();
		$uri->setPath($path);
		if (!empty($query)) {
			$query['api_token'] = $this->_token;
			$uri->setQuery($query);
		}
		$jsonResponse = $this->fetch($uri, $jsonBody);
		$decodedResponse = json_decode($jsonResponse);
		if (is_null($decodedResponse)) {
			throw new Tactile_Api_Exception('Failed to decode JSON response!'); 
		}
		
		return $decodedResponse;
	}
	
	/**
	 * Returns the HTTP client, constructing a new one if not already set
	 *
	 * @return Zend_HttpClient
	 */
	public function getClient() {
		return (!is_null($this->_client) ? $this->_client :
			new Zend_Http_Client(null, array('useragent' => 'Zend_Http_client (Tactile_Api::v'.self::VERSION.')'))); 
	}
	
	/**
	 * Returns the preferences set for the current User
	 *
	 * @return stdClass
	 */
	public function getPreferences() {
		return $this->apiRequest('/preferences/');
	}
	
	/**
	 * Returns the available options for User preferences
	 * 
	 * Also returns a list of enabled Users and Groups
	 *
	 * @return stdClass
	 */
	public function getPreferencesOptions() {
		return $this->apiRequest('/preferences/options/');
	}
	
	/**
	 * Fetches a single Organisation, by ID
	 *
	 * @param int $id
	 * @return stdClass
	 */
	public function getOrganisation($id) {
		return $this->apiRequest('/organisations/view/' . $id);
	}
	
	/**
	 * Fetches a single Person, by ID
	 *
	 * @param int $id
	 * @return stdClass
	 */
	public function getPerson($id) {
		return $this->apiRequest('/people/view/' . $id);
	}

	/**
	 * Fetches a single Opportunity, by ID
	 *
	 * @param int $id
	 * @return stdClass
	 */
	public function getOpportunity($id) {
		return $this->apiRequest('/opportunities/view/' . $id);
	}
	
	/**
	 * Fetches a single Activity, by ID
	 *
	 * @param int $id
	 * @return stdClass
	 */
	public function getActivity($id) {
		return $this->apiRequest('/activities/view/' . $id);
	}
	
	/**
	 * Fetches the timeline (notes & emails) attached to an Organisation
	 *
	 * @param int $id
	 * @return stdClass
	 */
	public function getOrganisationTimeline($id) {
		return $this->apiRequest('/organisations/timeline/' . $id);
	}
	
	/**
	 * Fetches the timeline (notes & emails) attached to a Person
	 *
	 * @param int $id
	 * @return stdClass
	 */
	public function getPersonTimeline($id) {
		return $this->apiRequest('/people/timeline/' . $id);
	}
	
	/**
	 * Fetches the timeline (notes & emails) attached to an Opportunity
	 *
	 * @param int $id
	 * @return stdClass
	 */
	public function getOpportunityTimeline($id) {
		return $this->apiRequest('/opportunities/timeline/' . $id);
	}
	
	/**
	 * Fetches the timeline (notes & emails) attached to an Activity
	 *
	 * @param int $id
	 * @return stdClass
	 */
	public function getActivityTimeline($id) {
		return $this->apiRequest('/activities/timeline/' . $id);
	}
	
	/**
	 * Fetches all the contact methods attached to an Organisation
	 *
	 * @param int $id
	 * @return stdClass
	 */
	public function getOrganisationContactMethods($id) {
		return $this->apiRequest('/organisations/contact_methods/' . $id);
	}
	
	/**
	 * Fetches all the contact methods attached to a Person
	 *
	 * @param int $id
	 * @return stdClass
	 */
	public function getPersonContactMethods($id) {
		return $this->apiRequest('/people/contact_methods/' . $id);
	}
	
	/**
	 * Fetch a list of Organisations
	 * 
	 * Pagination params:
	 * limit					=> number of records returned per page (1 <= x <= 100)
	 * page						=> offset records returned by (page-1 * limit)
	 * 
	 * Search params (use * for wildcard):
	 * name						=> search by name
	 * accountnumber			=> search by account number
	 * 
	 * Date/time params (accept any strtotime()-friendly format):
	 * updated_(before|after)	=> restrict by updated time
	 * created_(before|after)	=> restrict by creation time
	 * 
	 * @param array $query
	 * @return stdClass
	 */
	public function getOrganisations($query=array()) {
		return $this->apiRequest('/organisations/', $query);
	}
	
	/**
	 * Fetch a list of People
	 * 
	 * Pagination params:
	 * limit					=> number of records returned per page (1 <= x <= 100)
	 * page						=> offset records returned by (page-1 * limit)
	 * 
	 * Search params (use * for wildcard):
	 * firstname				=> search by first name only
	 * surname					=> search by surname only
	 * fullname					=> search by ($firstname.' '.$lastname)
	 * 
	 * Exact-match params:
	 * organisation_id			=> search by organisation
	 * 
	 * Date/time params (accept any strtotime()-friendly format):
	 * updated_(before|after)	=> restrict by updated time
	 * created_(before|after)	=> restrict by creation time
	 * 
	 * @param array $query
	 * @return stdClass
	 */
	public function getPeople($query=array()) {
		return $this->apiRequest('/people/', $query);
	}
	
	/**
	 * Fetch a list of Opportunitites
	 *
	 * Pagination params:
	 * limit					=> number of records returned per page (1 <= x <= 100)
	 * page						=> offset records returned by (page-1 * limit)
	 * 
	 * Search params (use * for wildcard):
	 * name						=> search by name
	 * 
	 * Date/time params (accept any strtotime()-friendly format):
	 * updated_(before|after)	=> restrict by updated time
	 * created_(before|after)	=> restrict by creation time
	 * 
	 * @param array $query
	 * @return stdClass
	 */
	public function getOpportunities($query=array()) {
		return $this->apiRequest('/opportunities/', $query);
	}
	
	/**
	 * Fetch a list of Activities
	 *
	 * Pagination params:
	 * limit					=> number of records returned per page (1 <= x <= 100)
	 * page						=> offset records returned by (page-1 * limit)
	 * 
	 * Search params (use * for wildcard):
	 * name						=> search by name
	 * 
	 * Date/time params (accept any strtotime()-friendly format):
	 * updated_(before|after)	=> restrict by updated time
	 * created_(before|after)	=> restrict by creation time
	 * 
	 * @param array $query
	 * @return stdClass
	 */
	public function getActivities($query = array()) {
		return $this->apiRequest('/activities/', $query);
	}
	
	/**
	 * Fetch the list of Organisations belonging to the authenticated User
	 *
	 * Shares query parameters with getOrganisations() 
	 * 
	 * @param array $query
	 * @return stdClass
	 */
	public function getOrganisationsMine($query=array()) {
		return $this->apiRequest('/organisations/mine/', $query);
	}
	
	/**
	 * Fetch the list of People belonging to the authenticated User
	 *
	 * Shares query parameters with getPeople()
	 * 
	 * @param array $query
	 * @return stdClass
	 */
	public function getPeopleMine($query=array()) {
		return $this->apiRequest('/people/mine/', $query);
	}
	
	/**
	 * Fetch the list of Opportunities belonging to the authenticated User
	 *
	 * Shares query parameters with getOpportunities()
	 * 
	 * @param array $query
	 * @return stdClass
	 */
	public function getOpportunitiesMine($query=array()) {
		return $this->apiRequest('/opportunities/mine/', $query);
	}
	
	/**
	 * Fetch the list of Activities belonging to the authenticated User
	 *
	 * Shares query parameters with getActivities()
	 * 
	 * @param array $query
	 * @return stdClass
	 */
	public function getActivitiesMine($query = array()) {
		return $this->apiRequest('/activities/mine/', $query);
	}
	
	/**
	 * Returns key => value series of options for Organisations
	 * 
	 * Could be used to provide valid options for dropdowns or other form controls
	 * Sets are 'assigned', 'status', 'source', 'classification', 'rating', 'industry', 'type'
	 *
	 * @return stdClass
	 */
	public function getOrganisationOptions() {
		return $this->apiRequest('/organisations/options/');
	}
	
	/**
	 * Returns key => value series of options for People
	 * 
	 * Could be used to provide valid options for dropdowns or other form controls
	 * Sets are 'assigned', 'language'
	 *
	 * @return stdClass
	 */
	public function getPeopleOptions() {
		return $this->apiRequest('/people/options/');
	}
	
	/**
	 * Returns key => value series of options for Opportunities
	 * 
	 * Could be used to provide valid options for dropdowns or other form controls
	 * Sets are 'assigned', 'status', 'probability', 'type', 'source'
	 *
	 * @return stdClass
	 */
	public function getOpportunityOptions() {
		return $this->apiRequest('/opportunities/options/');
	}
	
	/**
	 * Returns key => value series of options for Activities
	 * 
	 * Could be used to provide valid options for dropdowns or other form controls
	 * Sets are 'assigned', 'class', 'date_choice', 'type'
	 *
	 * @return stdClass
	 */
	public function getActivityOptions() {
		return $this->apiRequest('/activities/options/');
	}
	
	/**
	 * Saves an Organisation
	 *
	 * @param Tactile_Api_Organisation $org
	 * @return stdClass
	 */
	public function saveOrganisation(Tactile_Api_Organisation $org) {
		return $this->apiRequest('/organisations/save/', array(), $org->asJson());
	}
	
	/**
	 * Saves a Person
	 *
	 * @param Tactile_Api_Person $person
	 * @return stdClass
	 */
	public function savePerson(Tactile_Api_Person $person) {
		return $this->apiRequest('/people/save/', array(), $person->asJson());
	}
	
	/**
	 * Saves an Opportunity
	 *
	 * @param Tactile_Api_Opportunity $opp
	 * @return stdClass
	 */
	public function saveOpportunity(Tactile_Api_Opportunity $opp) {
		return $this->apiRequest('/opportunities/save/', array(), $opp->asJson());
	}
	
	/**
	 * Saves an Activity
	 *
	 * @param Tactile_Api_Activity $act
	 * @return stdClass
	 */
	public function saveActivity(Tactile_Api_Activity $act) {
		return $this->apiRequest('/activities/save/', array(), $act->asJson());
	}
	
	/**
	 * Saves a Note against an Organisation, Person, Opportunity and/or Activity
	 *
	 * @param Tactile_Api_Note $note
	 * @return stdClass
	 */
	public function saveNote(Tactile_Api_Note $note) {
		return $this->apiRequest('/notes/save/', array(), $note->asJson());
	}
	
	public function tagOrganisation(Tactile_Api_Organisation $org, $tag){
		return $this->apiRequest('/organisations/add_tag/',array('id'=>$org->id, 'tag'=>$tag));
	}

	public function tagPerson(Tactile_Api_Person $person, $tag){
		return $this->apiRequest('/people/add_tag/',array('id'=>$person->id, 'tag'=>$tag));
	}
}
