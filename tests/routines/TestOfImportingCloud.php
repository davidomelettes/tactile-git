<?php
class TestOfImportingCloud extends UnitTestCase {
	
	function __construct() {
		echo "Running TestOfImportingCloud\n";
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
		EGS::setCompanyId(1);
		require_once 'Zend/Auth.php';
		require_once 'Zend/Auth/Storage/NonPersistent.php';
		$auth = Zend_Auth::getInstance();
		$auth->setStorage(new Zend_Auth_Storage_NonPersistent());
		$auth->getStorage()->write('greg//tactile');
		Omelette::setUserSpace('tactile');
		
		$db = DB::Instance();
		$query = 'DELETE FROM people WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM organisations WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->setupMailTransport();
	}
	
	function tearDown() {
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		
		unset($this->transport);
		Zend_Mail::clearDefaultTransport();
	}
	
	function testOfExtraction() {
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
		$filename = TEST_ROOT.'routines/fixtures/cloud1.csv';
		$importer = new ContactImporter($filename);
		$importer->setExtractor(new OutlookCSVExtractor());
		$importer->prepare();
		$errors = array();
		
		$importer->import($errors);
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM organisations';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 11);
		
		$query = 'SELECT count(*) FROM people';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 11);
		$this->assertEqual(count($errors), 0);
	}
	
	function testOfDelayedImporting() {
		$csv_file = TEST_ROOT . 'routines/fixtures/cloud_large1.csv';
		$csv_file_copy = TEST_ROOT . 'routines/fixtures/cloud_large1.csv.tmp';
		
		copy($csv_file, $csv_file_copy);
		
		$logger = new Zend_Log(new Zend_Log_Writer_Null());
		
		$task_data = array(
			'filename' => $csv_file_copy,
			'sharing'=>array(
				'read' => 'private',
				'write' => 'private'
			),
			'import_type' => 'client',
			'tags' => 'foo',
			'file_type' => 'cloud',
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
		$this->assertEqual($count, 192);
		
		if (file_exists($csv_file_copy)) {
			unlink($csv_file_copy);
		}
	}
	
}
