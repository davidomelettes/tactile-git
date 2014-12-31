<?php

class TestOfImportingShoeboxed extends UnitTestCase {
	
	function __construct() {
		echo "Running TestOfImportingShoeboxed\n";
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
		$filename = TEST_ROOT.'routines/fixtures/shoeboxed.xml';
		$file = new SPLFileObject($filename);
		
		$extractor = new ShoeboxedExtractor();
		$iteration = $extractor->iterate($file);
		$result = $extractor->extract($iteration);
		
		$this->assertTrue(is_array($result));
		$this->assertEqual(count($result), 2);
		
		$company_data = $result[0];
		$person_data = $result[1];
		
		$expected_company = array(
			'name'		=> 'Karl-Heinz Wilhelm',
			'street1'	=> '',
			'street2'	=> '',
			'town'		=> 'Berlin',
			'postcode'	=> '10719',
			'county'	=> '',
			'country'	=> 'Germany',
			'faxes'		=> array(
				array(
					'contact'	=> '8825552',
					'name'		=> 'Main'
				)
			)
		);
		$expected_person = array(
			'firstname'	=> '',
			'surname'	=> '',
			'jobtitle'	=> '',
			'emails'	=> array(
				array(
					'contact'	=> 'hv-wilhelm-gmbh@arcor.de',
					'name'		=> 'Main'
				)
			),
			'phones'	=> array(
				array(
					'contact'	=> '8813027',
					'name'		=> 'Main'
				)
			)
		);
		
		$this->assertEqual($company_data, $expected_company);
		$this->assertEqual($person_data, $expected_person);
		
	}
	
}
