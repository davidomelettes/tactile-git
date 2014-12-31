<?php
require_once 'Zend/Auth.php';
require_once 'Zend/Auth/Storage/NonPersistent.php';
require_once 'Zend/Mail.php';
require_once LIB_ROOT.'spyc/spyc.php';

abstract class ControllerTest extends UnitTestCase {
	
	protected $fixtures = array();
	
	/**
	 * @var TestView
	 */
	protected $view;
	
	/**
	 * @var Zend_Auth
	 */
	protected $_auth;
	
	public function __construct() {
		parent::UnitTestCase();
		echo "Running ".get_class($this)."\n";
	}
	
	/**
	 * The Injector instance
	 *
	 * @var Phemto
	 */
	protected $injector;
	
	function setup() {
		global $injector;
		$injector = new Phemto();
		Omelette::setUserSpace('tactile');
		$this->injector = $injector;
		
		$this->setupMailTransport();
		
		$this->view = new TestView($injector);
		$this->app = new Tactile($injector, $this->view);
		$injector->register(new Singleton('DummyRedirectHandler'));
		$injector->register(new Singleton('TestModelLoading'));
		$injector->register('NonSessionFlash');
		$this->_auth = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_NonPersistent());
//		ob_start();
		
	}
	
	function loadFixtures($name) {
		$path = TEST_ROOT.'controllers/fixtures/'.$name.'.yml';
		$fixtures = Spyc::YAMLLoad($path);
		$this->fixtures = $fixtures;
	}
	
	/**
	 * Insert a number of items from the fixtures list
	 * format example:
	 * crm_defaults:
	 *   company_statuses:
     *     id: 1
     *     name: Test Status
	 *   company_sources:
     *     id: 1
     *     name: Test Source
	 * Will insert one row into each of 'company_statuses' and 'company_sources'
	 * 
	 * @param String $name
	 */
	function saveMultiFixture($name, $ucid = 1) {
		$data = $this->fixtures[$name];
		foreach($data as $tablename => $fixture) {
			$db = DB::Instance();
			if(!isset($fixture['usercompanyid']) && $tablename!=='users' && $tablename!='user_company_access') {
				$fixture['usercompanyid'] = $ucid;
			}
			$res = $db->Replace($tablename, $fixture, ($tablename=='users')?'username':'id', true);
			if($res==false) {
				throw new Exception("Saving multi-fixture failed: ".$db->ErrorMsg());
			}
			$this->fixtures[$name][$tablename]['id'] = $db->get_last_insert_id();
		}
	}
	
	function saveFixture($name, $tablename = null) {
		$fixture = $this->fixtures[$name];
		$db = DB::Instance();
		try {
			$fixture['usercompanyid'] = EGS::getCompanyId();
		}
		catch(Exception $e) {}
		$success = $db->Replace($tablename, $fixture, 'id', true);
		if($success === false) {
			exit($db->ErrorMsg());
		}
		$this->fixtures[$name][$tablename]['id'] = $db->get_last_insert_id();
	}
	
	/**
	 * Will insert a number of rows into a single table given the fixture name
	 * example format:
	 * client_defaults: 
	 *   - 
	 *    id: 100
	 *    name: Client 100
	 *    accountnumber: CAAA
	 *    creditlimit: 12000
	 *    vatnumber: 143553459
	 *    companynumber: 23948399
	 *    employees: 15
	 *    website: www.tactilecrm.com
	 *    usercompanyid: 1
	 *    owner: greg//tactile
	 *    alteredby: greg//tactile
	 *
	 * @param String $fixture_name
	 * @param String $tablename
	 */
	function saveFixtureRows($fixture_name, $tablename) {
		$db = DB::Instance();
		$fixture = $this->fixtures[$fixture_name];
		foreach($fixture as $row) {
			$res = $db->Replace($tablename, $row, 'id', true);
			if($res==false) {
				throw new Exception("Inserting fixture rows for $fixture_name into $tablename failed: ".$db->ErrorMsg());
			}
		}
	}
	
	function getFixture($name) {
		if(!isset($this->fixtures[$name])) {
			throw new Exception('Fixture name doesn\'t exist: '.$name);
		}
		return $this->fixtures[$name];
	}
	
	function assertFixture($model, $fixture, $sub = null, $ignore_fields=array()) {
		if(!$model instanceof DataObject) {
			$this->fail("Model isn't a model!");
			return;
		}
		$data = $this->fixtures[$fixture];
		foreach ($ignore_fields as $field) {
			if (isset($data[$field])) {
				unset($data[$field]);
			}
		}
		if(!is_null($sub)) {
			if(!isset($data[$sub])) {
				throw new Exception("Sub-fixture not found: ".$sub);
			}
			$data = $data[$sub];
		}
		foreach($data as $key=>$val) {
			$model_val = $model->$key;
			if(is_array($val) || $key[0] == '_') {
				continue;
			}
			$field = $model->getField($key);
			if($field->type == 'bool') {
				$val = ($val==true) ? 't' : 'f';
			}
			if($field->type == 'date') {
				$val = fix_date($val);
			}
			$this->assertEqual($model_val, $val, "Fixture test: $key was $val in the fixture, but model gives $model_val");
		}
	}
	
	function assertNow($datetime) {
		$this->assertTrue(abs(strtotime($datetime) - time()) < 1200, $datetime." isn't close enough to 'now' (".date('Y-m-d H:i:s').')');
	}
	
	function checkSuccessfulSave() {
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors(), 'Flash contains errors');
		if($f->hasErrors()) {
			print_r($f->errors);
		}
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect(), "The request isn't going to redirect when it should");
	}
	
	function checkUnsuccessfulSave() {
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		
		$this->assertTrue(count($f->errors) > 0);
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function setupMailTransport() {
		Mock::generate('Zend_Mail_Transport_Abstract','MockZend_Mail_Transport_Abstract',array('_sendMail'));
		$this->transport = new MockZend_Mail_Transport_Abstract();		
		Zend_Mail::setDefaultTransport($this->transport);
	}
	
	/**
	 * Checks that page starts and ends sensibly
	 *
	 */
	function genericPageTest() {
		$this->assertPattern('#^<!DOCTYPE#',trim($this->view->output));
		$this->assertPattern('#</html>$#',$this->view->output);		
	}
	
	function makeTemplatePath($name) {
		return STANDARD_TPL_ROOT. 'includes/'.$name.'.tpl';
	}
	
	function setURL($url) {
		$url = parse_url(ltrim($url, '/'));
		if(isset($url['query'])) {
			parse_str($url['query'], $_GET);
		}
		$_GET['url'] = $url['path'];
		
	}
	
	function setDefaultLogin($username='greg//tactile') {
		$this->_auth->getStorage()->write($username);
	}
	
	function setAjaxRequest() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$_SERVER['HTTP_ACCEPT'] = 'text/html';
		
		//we do this as currently the layout determination happens in View's constructor
		$this->view = new TestView($this->injector);
		$this->app = new Tactile($this->injector, $this->view);
	}
	
	function assertIsJsonObject($string) {
		$decoded = json_decode($string);
		$this->assertTrue(!is_null($decoded) && $decoded instanceof stdClass);
	}
	
	function setJSONRequest() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$_SERVER['HTTP_ACCEPT'] = 'application/json';
		//we do this as currently the layout determination happens in View's constructor
		$this->view = new TestView($this->injector);
		$this->app = new Tactile($this->injector, $this->view);		
	}

	function tearDown() {
		if(ob_get_level()>0) {
			@ob_end_clean();
		}
		
		if(Zend_Registry::isRegistered('cache')) {
			/* @var $cache Zend_Cache_Core */
			$cache = Zend_Registry::get('cache');
			$cache->clean();
		}
		SearchHandler::$perpage_default = 30;
		$f = Flash::Instance();
		$f->clear();
		$_GET = array();
		$_POST = array();
		$_SESSION = array();
		$this->_auth->clearIdentity();
		$db = DB::Instance();
		
		$query = 'DELETE FROM tag_map';
		$db->Execute($query);
		$query = 'DELETE FROM tags';
		$db->Execute($query);
		
		$query = 'DELETE from notes';
		$db->Execute($query) or die($db->ErrorMsg().$query);
		
		$query = 'UPDATE people SET usercompanyid=1, owner=\'greg//tactile\', alteredby=\'greg//tactile\'';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'UPDATE organisations SET usercompanyid=1, owner=\'greg//tactile\', alteredby=\'greg//tactile\'';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM user_company_access WHERE username IN (SELECT username FROM users WHERE person_id > 1)';
		$db->Execute($query) or die($db->ErrorMsg().$query);
		
		$query = 'DELETE FROM users WHERE person_id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM organisations WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg().$query);
		
		$query = 'DELETE FROM people WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg().$query);	
		
		$query = 'DELETE FROM person_contact_methods';
		$db->Execute($query) or die($db->ErrorMsg().$query);
		
		$query = 'DELETE FROM recently_viewed';
		$db->Execute($query) or die($db->ErrorMsg().$query);
		
		$query = 'DELETE FROM tactile_activities';
		$db->Execute($query) or die($db->ErrorMsg().$query);
		
		$query = 'DELETE FROM opportunities';
		$db->Execute($query) or die($db->ErrorMsg().$query);
		
		$query = 'UPDATE users SET password=md5(\'password\'), timezone=\'Europe/London\', date_format=\'d/m/Y\' WHERE username=\'greg//tactile\'';
		$db->Execute($query) or die($db->ErrorMsg().$query);
		
		$query = "DELETE FROM person_addresses";
		$db->Execute($query) or die($db->ErrorMsg() . $query);
		
		$query = "DELETE FROM organisation_addresses";
		$db->Execute($query) or die($db->ErrorMsg() . $query);

		$query = "INSERT INTO organisation_addresses (main, street1, town, county, postcode, country_code, organisation_id) VALUES ('true', '45 Acacia Avenue', 'Bananaville', 'Bananashire', 'BA1 3HT', 'GB', '1')";
		$db->Execute($query) or die($db->ErrorMsg() . $query);
		
		$query = 'DELETE FROM person_contact_methods';
		$db->Execute($query);
		
		$query = 'DELETE FROM organisation_contact_methods';
		$db->Execute($query);
		
		CurrentlyLoggedInUser::clear();
		EGS::setUsername(false);
		DB::Clear();
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		$_SERVER['HTTP_ACCEPT'] = 'text/html';
		unset($this->view);
		unset($this->app);
		$this->injector->reset();
	}
}
