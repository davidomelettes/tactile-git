<?php
require_once 'Gdata/Contacts/Feed.php';
class TestOfImportingGdata extends UnitTestCase {
	
	function __construct() {
		echo "Running TestOfImportingGdata\n";
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
	}
	
	public function tearDown() {
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
	}
	
	function testExtractionFromFile() {
		$filename = TEST_ROOT.'routines/fixtures/test1.xml';
		$file = new SPLFileObject($filename);
		
		$extractor = new GDataExtractor();
		$gdata = $extractor->iterate($file);
		$result = $extractor->extract($gdata);
		
		$this->assertTrue(is_array($result));
		$this->assertEqual(count($result), 2);
		
		$company_data = $result[0];
		$person_data = $result[1];
		
		$expected_company = false;
		$expected_person = array(
			'firstname' => 'Fred',
			'surname' => 'Bloggs',
			'phones'=>array(
				array(
					'contact' => '0121 234 5434',
					'name' => 'Home'
				)
			),
			'addresses' => array(
				array(
					'name' => 'Home',
					'main' => true,
					'street1' => "12 Place Road\n      Testville, United States"
				)
			)
		);
		
		$this->assertEqual($company_data, $expected_company);
		$this->assertEqual($person_data, $expected_person);
		
	}
	
	function testExtractionFromFeed() {
		$filename = TEST_ROOT.'routines/fixtures/test1.xml';
		$file = new SPLFileObject($filename);
		
		$feed = new Gdata_Contacts_Feed();
		$feed->transferFromXML(file_get_contents($filename));
		
		$extractor = new GDataExtractor($feed);
		$gdata = $extractor->iterate($file);
		$result = $extractor->extract($gdata);
		
		$this->assertTrue(is_array($result));
		$this->assertEqual(count($result), 2);
		
		$company_data = $result[0];
		$person_data = $result[1];
		
		$expected_company = false;
		$expected_person = array(
			'firstname' => 'Fred',
			'surname' => 'Bloggs',
			'phones'=>array(
				array(
					'contact' => '0121 234 5434',
					'name' => 'Home'
				)
			),
			'addresses' => array(
				array(
					'name' => 'Home',
					'main' => true,
					'street1' => "12 Place Road\n      Testville, United States"
				)
			)
		);
		
		$this->assertEqual($company_data, $expected_company);
		$this->assertEqual($person_data, $expected_person);
	}
	
	function testWithMoreDetails() {
		
		$filename = TEST_ROOT.'routines/fixtures/test2.xml';
		$file = new SPLFileObject($filename);
		
		$feed = new Gdata_Contacts_Feed();
		$feed->transferFromXML(file_get_contents($filename));
		
		$extractor = new GDataExtractor($feed);
		$gdata = $extractor->iterate($file);
		$result = $extractor->extract($gdata);
		
		$this->assertTrue(is_array($result));
		$this->assertEqual(count($result), 2);
		
		$company_data = $result[0];
		$person_data = $result[1];
		
		$expected_company = array('name'=>'Acme Corp Ltd.', 'created'=>$company_data['created']);
		
		$expected_person = array(
			'firstname' => 'Fred',
			'surname' => 'Bloggs',
			'jobtitle' => 'The Boss',
			'phones'=>array(
				array(
					'contact' => '0121 234 5434',
					'name' => 'Home'
				)
			),
			'emails' => array(
				array(
					'contact' => 'fred@example.com',
					'name' => 'Other'
				),
				array(
					'contact' => 'work@example.com',
					'name' => 'Work'
				)
			)			
		);
		
		$this->assertEqual($company_data, $expected_company);
		$this->assertEqual($person_data, $expected_person);
	}
	
}
?>