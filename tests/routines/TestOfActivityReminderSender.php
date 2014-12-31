<?php

class TestOfActivityReminderSender extends UnitTestCase {

	protected $fixtures = array();
	
	protected $routine = null;
	
	function __construct() {
		echo "Running TestOfActivityReminderSender\n";
	}

	function setupMailTransport() {
		Mock::generate('Zend_Mail_Transport_Abstract','MockZend_Mail_Transport_Abstract',array('_sendMail'));
		$this->transport = new MockZend_Mail_Transport_Abstract();		
		Zend_Mail::setDefaultTransport($this->transport);
	}

	function loadFixtures($name) {
		require_once LIB_ROOT.'spyc/spyc.php';
		$path = TEST_ROOT.'routines/fixtures/'.$name.'.yml';
		if (!file_exists($path)) {
			throw new Exeption('Failed to locate fixture file: ' . $path);
		}
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
	
	function setup() {
		parent::setup();
		
		global $injector;
		$injector = new Phemto();
		$injector->register('Prettifier');
		$injector->register('OmeletteModelLoader');
		
		require_once 'Zend/Log.php';
		require_once 'Zend/Log/Writer/Stream.php';
		$logger = null;
		//$logger = new Zend_Log(new Zend_Log_Writer_Stream('php://output'));
		require_once FILE_ROOT.'routines/hourly/ActivityReminderSender.php';
		$this->routine = new ActivityReminderSender($injector, array(), $logger);
		$this->routine->setSendTime('06:00:00');
		
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
		
		$query = 'DELETE FROM tactile_activities WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->loadFixtures('activities');
		
		$this->setupMailTransport();
	}
	
	public function tearDown() {
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		
		unset($this->transport);
		Zend_Mail::clearDefaultTransport();
	}
	
	public function testSimpleSendReminder() {
		$this->saveFixtureRows('basic_activities', 'tactile_activities');
		$this->saveFixtureRows('basic_contact_methods', 'person_contact_methods');
		
		$this->routine->setToday('2010-02-02');
		$this->routine->setTimeNow('06:00');
		
		$this->transport->expectOnce('send');
		$this->routine->go();
	}
	
	public function testSimpleSendReminderDoesNotSendWhenItShouldNot() {
		$this->saveFixtureRows('basic_activities_nosend', 'tactile_activities');
		$this->saveFixtureRows('basic_contact_methods', 'person_contact_methods');
		
		$this->routine->setToday('2010-02-02');
		$this->routine->setTimeNow('06:00');
		
		$this->transport->expectNever('send');
		$this->routine->go();
	}
	
	public function testSendReminderSendsAtCorrectTimeForUserInDifferentTimezone() {
		$this->saveFixtureRows('tz_person', 'people');
		$this->saveFixtureRows('tz_email_address', 'person_contact_methods');
		$this->saveFixtureRows('tz_user', 'users');
		$this->saveFixtureRows('tz_activities_send', 'tactile_activities');
		
		// Reminder should be send at 2010-07-19 06:00, -04:00
		$this->routine->setToday('2010-07-19');
		$this->routine->setSendTime('10:00');
		$this->routine->setTimeNow('10:00');
		
		$this->transport->expectOnce('send');
		$this->routine->go();
	}
	
	public function testSendReminderDoesNotSendTooEarlyForUserInDifferentTimezone() {
		$this->saveFixtureRows('tz_person', 'people');
		$this->saveFixtureRows('tz_email_address', 'person_contact_methods');
		$this->saveFixtureRows('tz_user', 'users');
		$this->saveFixtureRows('tz_activities_send', 'tactile_activities');
		
		// Reminder should be send at 2010-07-19 06:00, -04:00
		$this->routine->setToday('2010-07-19');
		$this->routine->setSendTime('10:00');
		$this->routine->setTimeNow('01:00');
		
		$db = DB::Instance();
		
		$this->transport->expectNever('send');
		$this->routine->go();
	}
	
}
