<?php
require_once 'Zend/Registry.php';
require_once 'Service/Freshbooks.php';

class TestOfFreshbooksLibrary extends UnitTestCase {
	
	protected $account, $token;
	
	
	public function __construct() {
		echo "Running ".get_class($this)."\n";
		parent::UnitTestCase();
	}
	/**
	 * @var Service_Freshbooks
	 */
	protected $fb;
	
	function setup() {
		parent::setup();
		if(Zend_Registry::isRegistered('cache')) {
			$cache = Zend_Registry::get('cache');
			/* @var $cache Zend_Cache_Core */
			$cache->setOption('caching', false);
		}
		$this->account = 'gregcorp';
		$this->token = 'dfe06af6cea4a9508f4208583c6bf319';
		$this->fb = new Service_Freshbooks($this->account, $this->token);
	}
	
	function teardown() {
		if(Zend_Registry::isRegistered('cache')) {
			/* @var $cache Zend_Cache_Core */
			$cache = Zend_Registry::get('cache');
			$cache->clean();
		}
		parent::teardown();
	}
	
	/**
	 * @return Zend_Http_Client_Adapter_Test
	 */
	function setupTestHttpClient($fb = null) {
		if(is_null($fb)) {
			$fb = $this->fb;
		}
		require_once 'Zend/Http/Client/Adapter/Test.php';
		$client = new Zend_Http_Client();
		$adapter = new Zend_Http_Client_Adapter_Test();
		$client->setAdapter($adapter);
		
		$fb->setHttpClient($client);
		return $adapter;
	}
	
	/**
	 * @param string $fixture The name of the fixture
	 * @param int $code HTTP status code, default=200
	 * @return Zend_Http_Response
	 */
	function responseFromFixture($fixture, $code = 200) {
		$default_headers = array(
			  'Date' => date('r'),
			  'Server' => 'Apache',
			  'Cache-control' => 'private',
			  'Vary' => 'Accept-Encoding',
			  'Content-length' => '340',
			  'Connection' => 'close',
			  'Content-type' => 'application/xml',
		);
		$file = TEST_ROOT . 'freshbooks_fixtures/'. $fixture . '.xml';
		$response = new Zend_Http_Response($code, $default_headers, file_get_contents($file));
		return $response;
	}
	
	function testInstantiation() {
		$this->assertIsA($this->fb, 'Service_Freshbooks');
	}
	
	function testGeneratingClientQueriesWithValidMethod() {
		foreach(array('list', 'get', 'create') as $method) {
			$query = $this->fb->newClientQuery($method);
			$this->assertIsA($query, 'Service_Freshbooks_Query_Client');
			$this->assertEqual($query->asXmlString(), trim(file_get_contents(TEST_ROOT.'freshbooks_fixtures/client.' . $method . '.xml')));
		}
	}
	
	function testClientListWithParameters() {
		$query = $this->fb->newClientQuery('list');
		$query->addParam('per_page', 12);
		$query->addParam('page', 2);
		$this->assertEqual($query->asXmlString(), trim(file_get_contents(TEST_ROOT.'freshbooks_fixtures/client.list-params.xml')));
	}
	
	function testClientGetWithParameters() {
		$query = $this->fb->newClientQuery('get');
		$query->addParam('client_id', 12);
		$this->assertEqual($query->asXmlString(), trim(file_get_contents(TEST_ROOT.'freshbooks_fixtures/client.get-params.xml')));
	}
	
	function testGeneratingInvoiceQueriesWithValidMethod() {
		foreach(array('list', 'get') as $method) {
			$query = $this->fb->newInvoiceQuery($method);
			$this->assertIsA($query, 'Service_Freshbooks_Query_Invoice');
		}
	}
	
	function testGeneratingInvoiceQueryWithInvalidMethod() {
		$this->expectException(new Service_Freshbooks_Exception('Unknown method: foo'));
		$query = $this->fb->newInvoiceQuery('foo');
	}
	
	function testGeneratingClientQueryWithInvalidMethod() {
		$this->expectException(new Service_Freshbooks_Exception('Unknown method: bar'));
		$query = $this->fb->newInvoiceQuery('bar');
	}
	
	function testNoConnectionIsHandledNicely() {
		$adapter = $this->setupTestHttpClient();
		$response_404 = new Zend_Http_Response(404, array());
		$adapter->setResponse($response_404);
		
		$query = $this->fb->newClientQuery('get');
		$fb_response = $this->fb->execute($query);
		$this->assertIsA($fb_response, 'Service_Freshbooks_Response');
		$this->assertFalse($fb_response->isValid());
	}
	
	function testRequestWithBadAccountIsHandledNicely() {
		$bad_fb = new Service_Freshbooks('foo', 'bar');
		
		$adapter = $this->setupTestHttpClient($bad_fb);
		$response = $this->responseFromFixture('fb_bad_account');
		$adapter->setResponse($response);
		
		$query = $bad_fb->newClientQuery('get');
		$fb_response = $bad_fb->execute($query);
		$this->assertIsA($fb_response, 'Service_Freshbooks_Response');
		$this->assertFalse($fb_response->isValid());
	}
	
	function testRequestWithBadTokenIsHandledNicely() {
		$bad_fb = new Service_Freshbooks($this->account, 'bar');
		
		$adapter = $this->setupTestHttpClient($bad_fb);
		$response = $this->responseFromFixture('fb_bad_token');
		$adapter->setResponse($response);
		
		$query = $bad_fb->newClientQuery('get');
		$fb_response = $bad_fb->execute($query);
		$this->assertIsA($fb_response, 'Service_Freshbooks_Response');
		$this->assertTrue($fb_response->isValid());
		$this->assertEqual($fb_response->getStatus(), Service_Freshbooks_Response::STATUS_FAIL);
	}
	
	function testValidResponseForClientList() {
		$adapter = $this->setupTestHttpClient();
		$response_client_list = $this->responseFromFixture('fb_client_list');
		$adapter->setResponse($response_client_list);
		$query = $this->fb->newClientQuery('list');
		$response = $this->fb->execute($query);
		
		$this->assertTrue($response->isValid());
		$this->assertEqual($response->getStatus(), Service_Freshbooks_Response::STATUS_OK);
		$this->assertIsA($response, 'Service_Freshbooks_Response_Client_List');
		$clients = $response->getClients();
		$this->assertTrue(is_array($clients));
		$this->assertEqual(count($clients), 1);
		
		$client = current($clients);
		$this->assertIsA($client, 'Service_Freshbooks_Entity_Client');
		$this->assertEqual($client->get('first_name'), 'Jane');
		$this->assertEqual($client->get('last_name'), 'Doe');
	}
	
	function testValidResponseForClientGet() {
		$adapter = $this->setupTestHttpClient();
		$response_client_list = $this->responseFromFixture('fb_client_get');
		$adapter->setResponse($response_client_list);
		$query = $this->fb->newClientQuery('get');
		$response = $this->fb->execute($query);
		
		$this->assertTrue($response->isValid());
		$this->assertEqual($response->getStatus(), Service_Freshbooks_Response::STATUS_OK);
		$this->assertIsA($response, 'Service_Freshbooks_Response_Client_Get');
		
		$client = $response->getClient();
		$this->assertIsA($client, 'Service_Freshbooks_Entity_Client');
		$this->assertEqual($client->get('first_name'), 'Jane');
		$this->assertEqual($client->get('last_name'), 'Doe');
		$this->assertEqual($client->get('credit'), '123.45');
		$this->assertEqual($client->get('url'), 'https://sample.freshbooks.com/client/12345-1-98969');
		
		$address = array(
			'street1' => '123 Fake St.',
			'street2' => 'Unit 555',
			'city' => 'New York',
			'state' => 'New York',
			'country' => 'United States',
			'code' => '553132'
		);
		$this->assertEqual($address, $client->getPrimaryAddress());
		
		$this->assertEqual(array(), array_filter(array_values($client->getSecondaryAddress())));
	}
	
	public function testUpgradingClientFromList() {
		$adapter = $this->setupTestHttpClient();
		$response_client_list = $this->responseFromFixture('fb_client_list');
		$adapter->setResponse($response_client_list);
		
		$response_client_list = $this->responseFromFixture('fb_client_get');
		$adapter->addResponse($response_client_list->asString());
		
		$query = $this->fb->newClientQuery('list');
		$response = $this->fb->execute($query);
		
		$this->assertTrue($response->isValid());
		$this->assertEqual($response->getStatus(), Service_Freshbooks_Response::STATUS_OK);
		$this->assertIsA($response, 'Service_Freshbooks_Response_Client_List');
		$clients = $response->getClients();
		$this->assertTrue(is_array($clients));
		$this->assertEqual(count($clients), 1);
		/* @var $client Service_Freshbooks_Entity_Client */
		$client = current($clients);
		$this->assertNull($client->get('url'));
		
		$client->getFullDetails();
		
		$this->assertEqual($client->get('first_name'), 'Jane');
		$this->assertEqual($client->get('last_name'), 'Doe');
		$this->assertEqual($client->get('credit'), '123.45');
		$this->assertEqual($client->get('url'), 'https://sample.freshbooks.com/client/12345-1-98969');
		
		$address = array(
			'street1' => '123 Fake St.',
			'street2' => 'Unit 555',
			'city' => 'New York',
			'state' => 'New York',
			'country' => 'United States',
			'code' => '553132'
		);
		$this->assertEqual($address, $client->getPrimaryAddress());
	}
	
	function testClientCreateFromEntityQuery() {
		$query = $this->fb->newClientQuery('create');
		
		$client_data = array(
			'first_name' => 'John',
			'last_name' => 'Smith',
			'organization' => 'Tesco',
			'email' => 'john@example.com',
			'username' => 'johnsmith',
			'password' => 'password',
			'work_phone' => '0121 234 5480',
			'home_phone' => '01234 438 384',
			'mobile' => '',
			'fax' => '0138 447 374',
			'notes' => 'Some text here',
			'p_street1' => '12 Some Road',
			'p_street2' => 'Someburb',
			'p_city' => 'Someville',
			'p_state' => 'Someshire',
			'p_country' => 'United Kingdom',
			'p_code' => 'SO12 3JF',
			's_street1' => '37 Someother Road',
			's_street2' => '',
			's_city' => 'Otherville',
			's_state' => 'Othershire',
			's_country' => 'United States',
			's_code' => '90210'
		);
		
		$client = new Service_Freshbooks_Entity_Client($client_data, $this->fb);
		$query->addEntity($client, 'client');
		$this->assertEqual($query->asXmlString(), trim(file_get_contents(TEST_ROOT.'freshbooks_fixtures/client.create-params.xml')));
	}
	
	function testCreatingClientWithBadFieldsErrors() {
		require_once 'Service/Freshbooks/Entity/Exception.php';
		$data = array(
			'first_name' => 'Peter',
			'foo' => 'bar',
			'baz' => 'fool'
		);
		$this->expectException(new Service_Freshbooks_Entity_Exception('Invalid property to set: foo'));
		$client = new Service_Freshbooks_Entity_Client($data, $this->fb);
	}
	
	function testPagingAndMergingOfClients() {
		$adapter = $this->setupTestHttpClient();
		$response_client_list = $this->responseFromFixture('fb_client_list_1');
		$adapter->setResponse($response_client_list);
		
		$response_client_list2 = $this->responseFromFixture('fb_client_list_2');
		$adapter->addResponse($response_client_list2->asString());
		
		$query = $this->fb->newClientQuery('list');
		$query->addParam('per_page', 1);
		$query->addParam('page', 1);
		
		/* @var $response Service_Freshbooks_Response_Client_List */
		$response = $this->fb->execute($query);
		$this->assertIsA($response, 'Service_Freshbooks_Response_Client_List');
		$this->assertEqual(count($response->getClients()), 1);
		$this->assertTrue($response->hasMorePages());
		
		$query = $this->fb->newClientQuery('list');
		$query->addParam('per_page', 1);
		$query->addParam('page', 2);
		/* @var $response2 Service_Freshbooks_Response_Client_List */
		$response2 = $this->fb->execute($query);
		$this->assertIsA($response2, 'Service_Freshbooks_Response_Client_List');
		$this->assertEqual(count($response2->getClients()), 1);
		$this->assertFalse($response2->hasMorePages());
		
		$response->merge($response2);
		$this->assertEqual(count($response->getClients()), 2);
		$this->assertFalse($response->hasMorePages());
	}
	
	function testGeneratingEstimateQueriesWithValidMethod() {
		foreach(array('list', 'get') as $method) {
			$query = $this->fb->newEstimateQuery($method);
			$this->assertIsA($query, 'Service_Freshbooks_Query_Estimate');
		}
	}
	
	function testEstimateList() {
		$query = $this->fb->newEstimateQuery('list');
		$this->assertEqual($query->asXmlString(), trim(file_get_contents(TEST_ROOT.'freshbooks_fixtures/estimate.list.xml')));
	}
	
	function testEstimateListWithParams() {
		$query = $this->fb->newEstimateQuery('list');
		$query->addParam('per_page', 12);
		$query->addParam('page', 2);
		$this->assertEqual($query->asXmlString(), trim(file_get_contents(TEST_ROOT.'freshbooks_fixtures/estimate.list-params.xml')));
	}
	
	function testEstimateGet() {
		$query = $this->fb->newEstimateQuery('get');
		$query->addParam('estimate_id', '00000000001');
		$this->assertEqual($query->asXmlString(), trim(file_get_contents(TEST_ROOT.'freshbooks_fixtures/estimate.get.xml')));
	}
}
