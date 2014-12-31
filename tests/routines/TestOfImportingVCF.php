<?php

class TestOfImportingVCF extends UnitTestCase {
	
	function __construct() {
		echo "Running TestOfImportingVCF\n";
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
	
	function testOfExtraction() {
		$filename = TEST_ROOT.'routines/fixtures/test1.vcf';
		$file = new SPLFileObject($filename);
		
		$extractor = new VCardExtractor();
		$vcard = $extractor->iterate($file);
		$result = $extractor->extract($vcard);
		
		$this->assertTrue(is_array($result));
		$this->assertEqual(count($result), 2);
		
		$company_data = $result[0];
		$person_data = $result[1];
		
		$expected_company = array(
			'name'			=> 'Coca Cola, Inc.',
			'description'	=> null,
			'addresses'		=> array(
				array(
					'street1'		=> 'Mail Stop 9',
					'street2'		=> 'Suite 105',
					'street3'		=> '1001 Pop Road',
					'town'			=> 'Denver',
					'county'		=> 'CO',
					'postcode'		=> '80301',
					'country_code'	=> 'GB',
				)
			),
			'phone'			=> array(
				'contact'		=> '01234567890'
			),
			'mobile'		=> array(
				'contact'		=> null
			),
			'fax'			=> array(
				'contact'		=> '439-344-9543'
			),
			'email'			=> array(
				'contact'		=> null
			),
			'created'		=> $company_data['created']
		);
		
		$expected_person = array(
			'title'			=> null,	
			'firstname'		=> 'Jane',
			'middlename'	=> null,
			'surname'		=> 'Hatfield',
			'jobtitle'		=> 'VP Marketing',
			'dob'			=> null,
			'description'	=> null,
			'addresses'		=> array(
				array(
					'street1'		=> '98 Crater Drive',
					'street2'		=> null,
					'street3'		=> null,
					'town'			=> 'Denver',
					'county'		=> 'CO',
					'postcode'		=> '80301',
					'country_code'	=> 'GB',
				)
			),
			'phone'			=> array(
				'contact'		=> '09876543210'
			),
			'mobile'		=> array(
				'contact'		=> '439-344-4323'
			),
			'fax'			=> array(
				'contact'		=> null
			),
			'email'			=> array(
				'contact'		=> 'jhatfield@cocacola.com'
			)
		);
		
		$this->assertEqual($company_data, $expected_company);
		$this->assertEqual($person_data, $expected_person);
	}
	
	function testOfImportingVCF() {
		$filename = TEST_ROOT.'routines/fixtures/test1.vcf';
		
		$importer = new ContactImporter($filename);
		$importer->setExtractor(new VCardExtractor());
		$importer->prepare();
		
		$errors = array();
		
		$importer->import($errors);
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM organisations';
		$count = $db->GetOne($query);
		// Should have the default company and the imported one
		$this->assertEqual($count, 2);
		
		$query = 'SELECT count(*) FROM people';
		$count = $db->GetOne($query);
		// Should have the default user and the imported one
		$this->assertEqual($count, 2);

		$this->assertEqual(count($errors), 0);
	}
	
	function testOfImportingVCFMissingEndTag() {
		$filename = TEST_ROOT.'routines/fixtures/vcard_missing_end.vcf';
		
		$importer = new ContactImporter($filename);
		$importer->setExtractor(new VCardExtractor());
		$importer->prepare();
		
		$errors = array();
		
		$importer->import($errors);
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM organisations';
		$count = $db->GetOne($query);
		// Should have the default company and the imported one
		$this->assertEqual($count, 2);
		
		$query = 'SELECT count(*) FROM people';
		$count = $db->GetOne($query);
		// Should have the default user and the imported one
		$this->assertEqual($count, 2);

		$this->assertEqual(count($errors), 0);
	}
	
	function testHandlingQuotedPrintableEncoding() {
		$filename = TEST_ROOT.'routines/fixtures/quoted_printable.vcf';
		
		$file = new SPLFileObject($filename);
		
		$extractor = new VCardExtractor();
		$vcard = $extractor->iterate($file);
		$result = $extractor->extract($vcard);
		
		$this->assertTrue(is_array($result));
		$this->assertEqual(count($result), 2);
		
		$company_data = $result[0];
		$person_data = $result[1];
		
		$expected_person = array(
			'title'			=> null,	
			'firstname'		=> 'Jane',
			'middlename'	=> null,
			'surname'		=> 'Hatfield',
			'jobtitle'		=> null,
			'dob'			=> null,
			'description'	=> null,
			'addresses'		=> array(
				array(
					'street1'		=> "Mail Stop 9\r\nNewline",
					'street2'		=> 'Suite 105',
					'street3'		=> '1001 Pop Road',
					'town'			=> 'Denver',
					'county'		=> 'CO',
					'postcode'		=> '80301',
					'country_code'	=> 'GB',
				)
			),
			'phone'			=> array(
				'contact'		=> '09876543210'
			),
			'mobile'		=> array(
				'contact'		=> null
			),
			'fax'			=> array(
				'contact'		=> null
			),
			'email'			=> array(
				'contact'		=> 'jhatfield@cocacola.com'
			)
		);
		
		$this->assertEqual($person_data, $expected_person);
	}
	
	public function testNonUTF8Data() {
		$filename = TEST_ROOT.'routines/fixtures/german.vcf';
		
		$file = new SPLFileObject($filename);
		
		$extractor = new VCardExtractor();
		while ($vcard = $extractor->iterate($file)) {
			$results = $extractor->extract($vcard);
			if (is_array($results)) {
				list ($company_data, $person_data) = $results;
				if ($person_data['surname'] == 'Gotterbarm') {
					$this->assertTrue(preg_match('/München/', $person_data['description']));
				}
			}
		}
	}
	
}

