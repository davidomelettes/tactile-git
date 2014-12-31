<?php
class TestOfImportingCSV extends UnitTestCase {
	
	function __construct() {
		echo "Running TestOfImportingCSV\n";
	}
	
	function loadFixtures($name) {
		$path = TEST_ROOT.'routines/fixtures/'.$name.'.yml';
		$fixtures = Spyc::YAMLLoad($path);
		$this->fixtures = $fixtures;
	}
	
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
	
	function setupMailTransport() {
		Mock::generate('Zend_Mail_Transport_Abstract','MockZend_Mail_Transport_Abstract',array('_sendMail'));
		$this->transport = new MockZend_Mail_Transport_Abstract();		
		Zend_Mail::setDefaultTransport($this->transport);
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
		$query = "DELETE FROM users WHERE username != 'greg//tactile'";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM people WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM organisations WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM custom_fields';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->setupMailTransport();
	}
	
	public function tearDown() {
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		
		unset($this->transport);
		Zend_Mail::clearDefaultTransport();
	}
	
	function testOfExtraction() {
		$db = DB::Instance();
		
		$headings = array(
			'Company',
			'Business Street',
			'Business Street 2',
			'Business City',
			'Business State',
			'Business Postal Code',
			'Business Phone',
			'Title',
			'First Name',
			'Last Name',
			'Job Title'
		);
		
		$row = array(
			'Test Company Ltd.',
			'12 Some Road',
			'Some bit',
			'Someville',
			'Someshire',
			'SO12 3FG',
			'0121 345 4345',
			'Mr',
			'Greg',
			'Jones',
			'Chief Mechanic'
		);
		
		$extractor = new OutlookCSVExtractor();
		
		$result = $extractor->extract($row, $headings);
		
		$this->assertTrue(is_array($result));
		$this->assertEqual(count($result), 2);
		
		$company_data = $result[0];
		$person_data = $result[1];
		
		$expected_company = array(
			'name'=>$row[0],
			'addresses'=>array(
				array(
					'street1'=>$row[1],
					'street2'=>$row[2],
					'town'=>$row[3],
					'county'=>$row[4],
					'postcode'=>$row[5]
				)
			),
			'phones'=>array(
				array(
					'contact'=>$row[6]
				)
			)
		);
		$this->assertEqual($company_data, $expected_company);
		
		$expected_person = array(
			'title'=>$row[7],
			'firstname'=>$row[8],
			'surname'=>$row[9],
			'jobtitle'=>$row[10]
		);
		$this->assertEqual($person_data, $expected_person);
	}
	
	function testOfImporting() {
		$filename = TEST_ROOT.'routines/fixtures/test1.csv';
		
		$importer = new ContactImporter($filename);
		$importer->setExtractor(new OutlookCSVExtractor());
		$importer->prepare();
		$errors = array();
		$importer->import($errors);
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM organisations';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 2);
		
		$query = 'SELECT count(*) FROM people';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 2);

		$this->assertEqual(count($errors), 0);
		if (!empty($errors)) {
			print_r($errors);
		}
	}
	
	function testOfImportingMoreHeadings() {
		$filename = TEST_ROOT.'routines/fixtures/test3.csv';
		
		$importer = new ContactImporter($filename);
		$importer->setExtractor(new OutlookCSVExtractor());
		$importer->prepare();
		$errors = array();
		
		$importer->import($errors, 'Organisation');
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM organisations';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 2);
		
		$query = 'SELECT count(*) FROM people';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 2);

		$this->assertEqual(count($errors), 0);
		if (!empty($errors)) {
			print_r($errors);
		}
	}
	
	function testOfImportingDifferentHeadings() {
		$csv_mappings = array(
			'person' => array(
				'firstname'	=> '9',
				'surname'	=> '10',
				'jobtitle'	=> '11'
			),
			'personaddress' => array (
			),
			'personcontact' => array(
			),
			'organisation' => array(
				'name'		=> '0'
			),
			'organisationaddress' => array(
				'street1'	=> '1',
				'street2'	=> '2',
				'street3'	=> '3',
				'town'		=> '4',
				'county'	=> '5',
				'postcode'	=> '6'
			),
			'organisationcontact' => array(
				'phones'	=> '7'
			)
		);
		$filename = TEST_ROOT.'routines/fixtures/test2.csv';
		
		$importer = new ContactImporter($filename);
		$importer->setExtractor(new OutlookCSVExtractor($csv_mappings));
		$importer->prepare();
		$errors = array();
		
		$importer->import($errors, 'Organisation');
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM organisations';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 2);
		
		$query = 'SELECT count(*) FROM people';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 2);

		$this->assertEqual(count($errors), 0);
		if (!empty($errors)) {
			print_r($errors);
		}
	}
	
	function testWithDataRowHavingFewerColumnsThanThereAreHeadings() {
		$file = new SplFileObject(TEST_ROOT.'routines/fixtures/missing_cols.csv');
		$extractor = new OutlookCSVExtractor();
		$people = array();
		$companies = array();
		while(false != ($line = $extractor->iterate($file))) {
			list($people[], $companies[]) = $extractor->extract($line);
		}
		$this->assertEqual(count($people), 1);
		$this->assertEqual(count($companies), 1);
	}
	
	function testOfDelayedImporting() {
		$csv_file = TEST_ROOT . 'routines/fixtures/large.csv';
		$csv_file_copy = TEST_ROOT . 'routines/fixtures/large.csv.tmp';
		copy($csv_file, $csv_file_copy);
		$logger = new Zend_Log(new Zend_Log_Writer_Null());
		
		$task_data = array(
			'filename' => $csv_file_copy,
			'sharing'=>array(
				'read' => 'private',
				'write' => 'private'
			),
			'tags' => 'foo',
			'file_type' => 'csv',
			'task_type' => 'DelayedContactImport',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile'
		);
		$task_storage = new DelayedTaskTemporaryStorage();
		$task_storage->write($task_data);
		DelayedTask::setDefaultStorage($task_storage);
		$task = new DelayedContactImport(true, $logger);
		
		$task->load(0, FALSE);
		
		$task->execute();
		
		$db = DB::Instance();
		$query = "SELECT count(*) FROM people";
		$count = $db->GetOne($query);
		$this->assertEqual($count, 58);
		
		if (file_exists($csv_file_copy)) {
			unlink($csv_file_copy);
		}
		
		$query = 'SELECT * FROM people WHERE surname=\'Stubbington\'';
		$p_row = $db->GetRow($query);
		$this->assertEqual($p_row['title'], 'Mrs');
		$query = 'SELECT * FROM organisations WHERE name=\'Action Marketing\'';
		$c_row = $db->GetRow($query);
		$this->assertEqual($c_row['id'], $p_row['organisation_id']);
		$query = 'SELECT * FROM organisation_addresses WHERE organisation_id = '  . $db->qstr($p_row['organisation_id']);
		$a_row = $db->getRow($query);
		$this->assertEqual($a_row['main'], TRUE);
		$this->assertEqual($a_row['street1'], '458a Ashingdon Road');
		$this->assertEqual($a_row['town'], 'Rochford');
		$this->assertEqual($a_row['postcode'], 'SS4 3ET');
		$this->assertEqual($a_row['country_code'], 'GB');
	}
	
	function testOfDelayedImportingWithCustomFields() {
		// Load custom field fixtures
		$this->loadFixtures('custom_fields');
		$this->saveFixtureRows('custom_fields', 'custom_fields');
		$this->saveFixtureRows('custom_field_options', 'custom_field_options');
		
		// Prepare delayed task
		$csv_file = TEST_ROOT . 'routines/fixtures/test_custom_importing.csv';
		$csv_file_copy = TEST_ROOT . 'routines/fixtures/test_custom_importing.csv.tmp';
		copy($csv_file, $csv_file_copy);
		$logger = new Zend_Log(new Zend_Log_Writer_Null());
		
		$task_data = array(
			'csv_mappings' => array(
				'person' => array(
					'firstname' => 1,
					'surname' => 2,
				),
				'personcustom' => array(
					100 => array(
						'index' => 3
					),
					200 => array(
						'index' => 4
					),
					300 => array(
						'index' => 5
					),
					400 => array(
						'index' => 6,
						'autocreate' => 'on'
					)
				),
				'organisation' => array(
					'name' => 0
				),
			),
			'filename' => $csv_file_copy,
			'sharing'=>array(
				'read' => 'private',
				'write' => 'private'
			),
			'tags' => 'foo',
			'file_type' => 'csv',
			'task_type' => 'DelayedContactImport',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile'
		);
		$task_storage = new DelayedTaskTemporaryStorage();
		$task_storage->write($task_data);
		DelayedTask::setDefaultStorage($task_storage);
		$task = new DelayedContactImport(true, $logger);
		
		// Execute task
		$task->load(0, FALSE);
		$task->execute();
		
		if (file_exists($csv_file_copy)) {
			unlink($csv_file_copy);
		}
		
		// Examine results
		$db = DB::Instance();
		$count = $db->GetOne("SELECT count(*) FROM people");
		$this->assertEqual($count, 5); // User + 4 rows
		$count = $db->GetOne("SELECT count(*) FROM organisations");
		$this->assertEqual($count, 4); // Account + 3
		
		$boris = new Tactile_Person();
		$david= new Tactile_Person();
		$eve = new Tactile_Person();
		$roger = new Tactile_Person();
		$boris->load($db->getOne("SELECT id FROM people WHERE firstname = 'Boris' AND surname = 'McTesty' AND usercompanyid = '1'")) or die("Failed to load boris!");
		$david->load($db->getOne("SELECT id FROM people WHERE firstname = 'David' AND surname = 'Smith' AND usercompanyid = '1'")) or die("Failed to load david!");
		$eve->load($db->getOne("SELECT id FROM people WHERE firstname = 'Eve' AND surname = 'Green' AND usercompanyid = '1'")) or die("Failed to load eve!");
		$roger->load($db->getOne("SELECT id FROM people WHERE firstname = 'Roger' AND surname = 'Brown' AND usercompanyid = '1'")) or die("Failed to load roger!");
		
		$bacon_id = $db->getOne("SELECT id FROM custom_field_options WHERE field_id = '400' AND value = 'Bacon'");
		
		// Boris
		$values = $boris->getCustomValues();
		$map = array();
		foreach ($values as $value) {
			$map[$value->field_id]['value'] = $value->value;
			$map[$value->field_id]['option'] = $value->option;
			$map[$value->field_id]['enabled'] = $value->enabled;
		}
		$expected = array(
			200 => array(
				'value' => '20',
				'option' => '',
				'enabled' => 'f'
			),
			300 => array(
				'value' => '',
				'option' => '',
				'enabled' => 'f'
			),
			400 => array(
				'value' => '',
				'option' => '100',
				'enabled' => 'f'
			)
		);
		$this->assertEqual($map, $expected);
		
		// David
		$values = $david->getCustomValues();
		$map = array();
		foreach ($values as $value) {
			$map[$value->field_id]['value'] = $value->value;
			$map[$value->field_id]['option'] = $value->option;
			$map[$value->field_id]['enabled'] = $value->enabled;
		}
		$expected = array(
			200 => array(
				'value' => '21',
				'option' => '',
				'enabled' => 'f'
			),
			300 => array(
				'value' => '',
				'option' => '',
				'enabled' => 't'
			),
			400 => array(
				'value' => '',
				'option' => '200',
				'enabled' => 'f'
			)
		);
		$this->assertEqual($map, $expected);
		
		// Eve
		$values = $eve->getCustomValues();
		$map = array();
		foreach ($values as $value) {
			$map[$value->field_id]['value'] = $value->value;
			$map[$value->field_id]['option'] = $value->option;
			$map[$value->field_id]['enabled'] = $value->enabled;
		}
		$expected = array(
			100 => array(
				'value' => 'Lord of the Rings',
				'option' => '',
				'enabled' => 'f'
			),
			200 => array(
				'value' => '22',
				'option' => '',
				'enabled' => 'f'
			),
			300 => array(
				'value' => '',
				'option' => '',
				'enabled' => 'f'
			),
			400 => array(
				'value' => '',
				'option' => $bacon_id,
				'enabled' => 'f'
			)
		);
		$this->assertEqual($map, $expected);
		
		// Roger
		$values = $roger->getCustomValues();
		$map = array();
		foreach ($values as $value) {
			$map[$value->field_id]['value'] = $value->value;
			$map[$value->field_id]['option'] = $value->option;
			$map[$value->field_id]['enabled'] = $value->enabled;
		}
		$expected = array(
			200 => array(
				'value' => '23',
				'option' => '',
				'enabled' => 'f'
			),
			300 => array(
				'value' => '',
				'option' => '',
				'enabled' => 't'
			),
			400 => array(
				'value' => '',
				'option' => $bacon_id,
				'enabled' => 'f'
			)
		);
		$this->assertEqual($map, $expected);
	}
	
	public function testDanishCharactersInMacintoshEncodedFile() {
		$csv_file = TEST_ROOT.'routines/fixtures/danish.csv';
		$csv_file_copy = TEST_ROOT . 'routines/fixtures/danish.csv.tmp';
		copy($csv_file, $csv_file_copy);
		$logger = new Zend_Log(new Zend_Log_Writer_Null());
		
		$task_data = array(
			'csv_mappings' => array(
				'person' => array(
					'firstname' => 0,
					'surname' => 1,
				),
				'organisation' => array(
					'name' => 3
				),
			),
			'sharing'=>array(
				'read' => 'private',
				'write' => 'private'
			),
			'tags' => "Import20XXMMDD",
			'filename' => $csv_file_copy,
			'file_type' => 'csv',
			'task_type' => 'DelayedContactImport',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile'
		);
		$task_storage = new DelayedTaskTemporaryStorage();
		$task_storage->write($task_data);
		DelayedTask::setDefaultStorage($task_storage);
		$task = new DelayedContactImport(true, $logger);
		
		// Execute task
		$task->load(0, FALSE);
		$task->execute();
		if (file_exists($csv_file_copy)) {
			unlink($csv_file_copy);
		}
		
		$db = DB::Instance();
		$query = 'SELECT surname FROM people WHERE firstname = ' . $db->qstr('Dines Juhl');
		$surname = $db->getOne($query);
		$this->assertEqual($surname, 'Barsøe');
		
		$query = 'SELECT count(*) FROM organisations WHERE name = ' . $db->qstr('Lantmännen Unibake');
		$count = $db->getOne($query);
		$this->assertEqual($count, 1);
	}
	
	public function testJapaneseCharacters()
	{
		$csvFilePath = TEST_ROOT.'routines/fixtures/jp.csv';
		$file = new SPLFileObject($csvFilePath);
		$extractor = new OutlookCSVExtractor();
		
		$line = $file->fgetcsv();
		$japanese = $line[6];
		$cleaned = $extractor->cleanValue($japanese);
		
		$this->assertEqual($japanese, '菊地　俊一');
		$this->assertEqual($cleaned, '菊地　俊一');
	}
	
}
