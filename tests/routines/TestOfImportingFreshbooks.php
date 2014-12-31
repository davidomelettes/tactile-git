<?php

class TestOfImportingFreshbooks extends UnitTestCase {
	
	function __construct() {
		echo "Running TestOfImportingFreshbooks\n";
	}
	
	function setup() {
		parent::setup();
		
		global $injector;
		$injector = new Phemto();
		$injector->register('Prettifier');
		$injector->register('OmeletteModelLoader');
		
		EGS::setUsername('greg//tactile');
		require_once 'Zend/Auth.php';
		require_once 'Zend/Auth/Storage/NonPersistent.php';
		$auth = Zend_Auth::getInstance();
		$auth->setStorage(new Zend_Auth_Storage_NonPersistent());
		$auth->getStorage()->write('greg//tactile');
		EGS::setCompanyId(1);
		
		Omelette::setUserSpace('tactile');
		$db = DB::Instance();
		$query = 'DELETE FROM people WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM organisations WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		require_once 'Zend/Http/Client.php';
		require_once 'Zend/Http/Client/Adapter/Test.php';
		$this->_testClient = new Zend_Http_Client();
		$this->_testAdapter = new Zend_Http_Client_Adapter_Test();
		$this->_testClient->setAdapter($this->_testAdapter);
		require_once 'Service/Freshbooks.php';
		Service_Freshbooks::setDefaultHttpClient($this->_testClient);
	}
	
	public function tearDown() {
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		Service_Freshbooks::setDefaultHttpClient(null);
	}

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

	public function testOfExtraction() {
		$filename = TEST_ROOT . 'freshbooks_fixtures/fb_client_list_import.xml';
		$file_object = new SPLFileObject($filename);
		
		$importer = new ContactImporter($filename);
		require_once 'Zend/Log/Writer/Stream.php';
		//$logger = new Zend_Log(new Zend_Log_Writer_Stream('php://output'));
		//$importer->setLogger($logger);
		
		$service = new Service_Freshbooks('x', 'y');
		$extractor = new FreshbooksExtractor(null, $service);
		$importer->setExtractor($extractor);
		
		$this->_testAdapter->setResponse($this->responseFromFixture('fb_client_list_import_prepare')->asString());
		$importer->prepare();
		
		$errors = array();
		$success = $importer->import($errors);
		$this->assertTrue($success);
		
		$db = DB::Instance();
		$newest_id = $db->getOne("SELECT id FROM organisations ORDER BY created DESC");
		
		$org = new Tactile_Organisation();
		$org->load($newest_id);
		$this->assertEqual($org->name, 'XYZ Corp');
		$this->assertEqual($org->address->street1, '123 Fake St.');
	}

}
