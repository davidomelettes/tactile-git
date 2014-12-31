<?php
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Client/Adapter/Test.php';
require_once 'Service/Freshbooks.php';
//Mock::generate('Zend_Http_Client_Adapter_Test', 'Mock_Adapter');
class TestOfFreshbooksActions extends ControllerTest {
	
	/**
	 * @var Zend_Http_Client_Adapter_Test
	 */
	protected $_testAdapter;
	
	protected $_testClient;
	
	function setup() {
		parent::setup();
		$db = DB::Instance();
		$this->setDefaultLogin();

		$query = 'DELETE FROM tactile_accounts_magic';
		$db->execute($query) or die ($db->ErrorMsg());
		$query = "INSERT INTO tactile_accounts_magic (key, value, usercompanyid) VALUES ('freshbooks_account', 'meeple', '1')";
		$db->Execute($query) or die($db->ErrorMsg());
		$query = "INSERT INTO tactile_accounts_magic (key, value, usercompanyid) VALUES ('freshbooks_token', 'moople', '1')";
		//$query = "UPDATE tactile_accounts SET freshbooks_account = 'meeple', freshbooks_token='moople' WHERE id=1";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->_testClient = new Zend_Http_Client();
		$this->_testAdapter = new Zend_Http_Client_Adapter_Test();
		$this->_testClient->setAdapter($this->_testAdapter);
		Service_Freshbooks::setDefaultHttpClient($this->_testClient);
	}
	
	function teardown() {
		//clear it
		Service_Freshbooks::setDefaultHttpClient();
		$db = DB::Instance();
		$query = 'UPDATE organisations SET freshbooks_id = null';
		$db->execute($query) or die ($db->ErrorMsg());
		$query = 'DELETE FROM tactile_accounts_magic';
		$db->execute($query) or die ($db->ErrorMsg());
		parent::teardown();
	}
	
	function testOfGettingListOfOrganisations() {
		$this->setUrl('organisations/freshbooks_client_list');
		$this->setJSONRequest();
		$this->_testAdapter->setResponse($this->responseFromFixture('fb_client_list')->asString());
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$list = $this->view->get('clients');
		$this->assertEqual(count($list), 1);
		
		$this->assertEqual(array('13' => 'ABC Corp'), $list);
		
		$validJSON = json_decode(trim($this->view->output));
		$this->assertFalse(is_null($validJSON));
	}
	
	function testWhenListIsEmpty() {
		$this->setUrl('organisations/freshbooks_client_list');
		$this->setJSONRequest();
		$this->_testAdapter->setResponse($this->responseFromFixture('fb_empty_client_list')->asString());
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual($this->view->get('no_clients'), true);
		
		$validJSON = json_decode(trim($this->view->output));
		$this->assertFalse(is_null($validJSON));
		$this->assertEqual($validJSON->no_clients, true);
	}
	
	function testWhenGettingListErrors() {
		//not setting a response in the test-adapter will make it return an empty 404
		$this->setUrl('organisations/freshbooks_client_list');
		$this->setJSONRequest();
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$this->assertEqual(count($f->errors), 1);
		
		$validJSON = json_decode(trim($this->view->output));
		$this->assertFalse(is_null($validJSON));
	}
	
	function testGettingDetailsForFreshbooks() {
		$_GET['id'] = 1;
		$this->setUrl('organisations/details_for_freshbooks');
		$this->setAjaxRequest();
		$_SERVER['HTTP_ACCEPT'] = 'application/json'; // Now done via json
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertPattern('#^{"status":"success", "client":{"organization":"Default Company"#', $this->view->output);
	}
	
	function testGettingInvoiceListForOrganisation() {
		$db = DB::Instance();
		$query = 'UPDATE organisations SET freshbooks_id = 1 where id=1';
		$db->Execute($query);
		
		$this->_testAdapter->setResponse($this->responseFromFixture('fb_invoice_list'));
		
		$_GET['id'] = 1;
		$this->setUrl('organisations/freshbooks');
		$this->setAjaxRequest();
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertPattern('#<ul id="invoices"#', $this->view->output);
		
		$this->assertEqual(count($this->view->get('invoices')), 2);
	}
	
	function testGettingEmptyInvoiceListForOrganisation() {
		$db = DB::Instance();
		$query = 'UPDATE organisations SET freshbooks_id = 1 where id=1';
		$db->Execute($query);
		
		$this->_testAdapter->setResponse($this->responseFromFixture('fb_empty_invoice_list'));
		$_GET['id'] = 1;
		$this->setUrl('organisations/freshbooks');
		$this->setAjaxRequest();
		$this->app->go();
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertNoPattern('#<ul id="invoices"#', $this->view->output);
		$this->assertPattern('#No Invoices#i', $this->view->output);
		
		$this->assertEqual(count($this->view->get('invoices')), 0);
	}
	
	function testWhenGettingEmptyInvoiceListErrors() {
		$db = DB::Instance();
		$query = 'UPDATE organisations SET freshbooks_id = 1 where id=1';
		$db->Execute($query);
		
		$_GET['id'] = 1;
		$this->setUrl('organisations/freshbooks');
		$this->setAjaxRequest();
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$this->assertEqual(count($f->errors), 1);
		
		$this->assertNoPattern('#<ul id="invoices"#', $this->view->output);
		$this->assertPattern('#No Invoices#i', $this->view->output);
	}
	
	function testGettingEstimateListForOrganisation() {
		$db = DB::Instance();
		$query = 'UPDATE organisations SET freshbooks_id = 1 where id=1';
		$db->Execute($query);
		
		$this->_testAdapter->setResponse($this->responseFromFixture('fb_estimate_list'));
		
		$_GET['id'] = 1;
		$this->setUrl('organisations/freshbooks');
		$this->setAjaxRequest();
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertPattern('#<ul id="estimates"#', $this->view->output);
		
		$this->assertEqual(count($this->view->get('estimates')), 2);
	}
	
	function testGettingEstimatesAndInvoices() {
		define('MOO', true);
		$db = DB::Instance();
		$query = 'UPDATE organisations SET freshbooks_id = 1 where id=1';
		$db->Execute($query);
		
		$this->_testAdapter->setResponse($this->responseFromFixture('fb_invoice_list')->asString());
		$this->_testAdapter->addResponse($this->responseFromFixture('fb_estimate_list')->asString());
		
		$_GET['id'] = 1;
		$this->setUrl('organisations/freshbooks');
		$this->setAjaxRequest();
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertPattern('#<ul id="estimates"#', $this->view->output);
		$this->assertPattern('#<ul id="invoices"#', $this->view->output);
		
		$this->assertEqual(count($this->view->get('estimates')), 2);
		$this->assertEqual(count($this->view->get('invoices')), 2);
	}
	
	function testOfSettingUpIntegration() {
		$db = DB::Instance();
		$query = "DELETE FROM tactile_accounts_magic WHERE key like 'freshbooks%'";
		//$query = "UPDATE tactile_accounts SET freshbooks_account = null, freshbooks_token = null WHERE id=1";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->_testAdapter->setResponse($this->responseFromFixture('fb_invoice_list'));
		
		$_POST = array(
			'accountname' => 'goodname',
			'token' => 'goodtoken'
		);
		$this->setUrl('freshbooks/setup');
		$this->app->go();
		
		$query = "SELECT value from tactile_accounts_magic where key = 'freshbooks_account' and usercompanyid = '1'";
		$fb_account = $db->getOne($query);
		$query = "SELECT value from tactile_accounts_magic where key = 'freshbooks_token' and usercompanyid = '1'";
		$fb_token = $db->getOne($query);
		$row = $db->GetRow($query);
		$this->assertEqual(array(
			'freshbooks_account' => 'goodname',
			'freshbooks_token' => 'goodtoken'
		),array('freshbooks_account' =>$fb_account, 'freshbooks_token'=>$fb_token));
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testSettingUpIntegrationWithBadAccountName() {
		$db = DB::Instance();
		$query = "DELETE FROM tactile_accounts_magic WHERE key like 'freshbooks%'";
		//$query = "UPDATE tactile_accounts SET freshbooks_account = null, freshbooks_token = null WHERE id=1";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->_testAdapter->setResponse($this->responseFromFixture('fb_bad_account'));
		
		$_POST = array(
			'accountname' => 'badname',
			'token' => 'goodtoken'
		);
		$this->setUrl('freshbooks/setup');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$errors = $f->errors;
		$this->assertEqual(count($errors), 1);
		$this->assertPattern('#problem.+Account Name#', current($errors));
	}
	
	function testSettingUpIntegrationWithBadToken() {
		$db = DB::Instance();
		$query = "DELETE FROM tactile_accounts_magic WHERE key like 'freshbooks%'";
		//$query = "UPDATE tactile_accounts SET freshbooks_account = null, freshbooks_token = null WHERE id=1";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->_testAdapter->setResponse($this->responseFromFixture('fb_bad_token'));
		
		$_POST = array(
			'accountname' => 'goodname',
			'token' => 'badtoken'
		);
		$this->setUrl('freshbooks/setup');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$errors = $f->errors;
		$this->assertEqual(count($errors), 1);
		$this->assertPattern('#Authentication#', current($errors));
	}
	
	/**
	 * @param string $fixture The name of the fixture - filename minus extension
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
	
}
