<?php

class TestOfPublicController extends ControllerTest {
	
	function setup() {
		parent::setup();
		// Don't log in!
		//$this->setDefaultLogin();
		$db = DB::Instance();
		
		$this->loadFixtures('public');
	}
	
	function testPublicControllerAccess() {
		$this->setURL('public');
		$this->app->go();
		$this->genericPageTest();
		
		// Should be at the login screen
		$this->assertEqual($this->view->get('templateName'), $this->makeTemplatePath('login/index'));
	}
	
	function testPublicControllerCalendarAccess() {
		$this->saveFixtureRows('person_with_calendar', 'people');
		$this->saveFixtureRows('user_with_calendar', 'users');
		$this->saveFixtureRows('user_with_calendar_company_access', 'user_company_access');
		
		$this->setURL('public/icalendar/');
		$_GET['key'] = 'c0343a3b18daf310b8156552e9850cac';
		$this->app->go();
		// Should see a vcalendar
		$this->assertEqual($this->view->get('templateName'), $this->makeTemplatePath('public/icalendar'));
		$this->assertPattern('/^BEGIN:VCALENDAR/', $this->view->output);
		$this->assertPattern('/END:VCALENDAR$/', $this->view->output);
	}
	
	function testPublicControllerCalendarAccessWithBadKey() {
		$this->saveFixtureRows('person_with_calendar', 'people');
		$this->saveFixtureRows('user_with_calendar', 'users');
		$this->saveFixtureRows('user_with_calendar_company_access', 'user_company_access');
		
		$this->setURL('public/icalendar/?key=xxx');
		$this->app->go();
		
		$this->assertNoPattern('/^BEGIN:VCALENDAR/', $this->view->output);
	}
	
	function testVCalendarActivitiesToTodos() {
		$this->saveFixtureRows('person_with_calendar', 'people');
		$this->saveFixtureRows('user_with_calendar', 'users');
		$this->saveFixtureRows('user_with_calendar_company_access', 'user_company_access');
		
		// Load activities
		$this->saveFixtureRows('calendar_activities', 'tactile_activities');
		
		$this->setURL('public/icalendar/?key=c0343a3b18daf310b8156552e9850cac');
		$this->app->go();
		
		// Should see a vcalendar
		$this->assertPattern('/^BEGIN:VCALENDAR/', $this->view->output);
		$this->assertPattern('/END:VCALENDAR$/', $this->view->output);
		
		$this->assertPattern('/SUMMARY:Activity 1/', $this->view->output);
		$this->assertPattern('/SUMMARY:Activity 2/', $this->view->output);
		$this->assertPattern('/SUMMARY:Activity 3/', $this->view->output);
		$this->assertPattern('/COMPLETED:20070101T000000/', $this->view->output);
		$this->assertNoPattern('/SUMMARY:Activity 5/', $this->view->output);
		$this->assertPattern('/SUMMARY:Activity 6/', $this->view->output);
		$this->assertNoPattern('/SUMMARY:Activity 7/', $this->view->output);
		
		$this->assertPattern("/BEGIN:VTODO\r\nDTSTAMP:\d{8}T\d{6}\r\nSUMMARY:Activity 1\r\nEND:VTODO/", $this->view->output);
	}
	
	function testVCalendarActivitiesWithEscaping() {
		$this->saveFixtureRows('person_with_calendar', 'people');
		$this->saveFixtureRows('user_with_calendar', 'users');
		$this->saveFixtureRows('user_with_calendar_company_access', 'user_company_access');
		
		$db = DB::Instance();
		$query = "INSERT INTO tactile_activities (name, description, assigned_to, assigned_by, owner, alteredby, usercompanyid) VALUES ('Activity 7', 'With \r\nnewline', 'calendar//tactile', 'calendar//tactile', 'calendar//tactile', 'calendar//tactile', '1')";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->setURL('public/icalendar/?key=c0343a3b18daf310b8156552e9850cac');
		$this->app->go();
		
		$this->assertPattern('/^BEGIN:VCALENDAR/', $this->view->output);
		$this->assertPattern('/END:VCALENDAR$/', $this->view->output);
		$this->assertPattern("/BEGIN:VTODO\r\nDTSTAMP:\d{8}T\d{6}\r\nSUMMARY:Activity 7\r\nDESCRIPTION:With \\\\nnewline\r\nEND:VTODO/", $this->view->output);
	}
	
	function testAccessToVCalendarWhileLoggedIn() {
		// Log in
		$this->setDefaultLogin();
		
		$this->saveFixtureRows('person_with_calendar', 'people');
		$this->saveFixtureRows('user_with_calendar', 'users');
		$this->saveFixtureRows('user_with_calendar_company_access', 'user_company_access');
		
		// Access a bad calendar URL (use wrong key)
		$this->setURL('public/icalendar/?key=c0343a3b18daf310b8156552e9850cac');
		$this->app->go();
		
		// Should see our vCalendar
		$this->assertPattern('/^BEGIN:VCALENDAR/', $this->view->output);
		$this->assertPattern('/END:VCALENDAR$/', $this->view->output);
	}
	
	function testVCalendarWithActivityEvents() {
		$this->saveFixtureRows('person_with_calendar', 'people');
		$this->saveFixtureRows('user_with_calendar', 'users');
		$this->saveFixtureRows('user_with_calendar_company_access', 'user_company_access');
		
		// Load activities
		$this->saveFixtureRows('calendar_activities_events', 'tactile_activities');
		
		$this->setURL('public/icalendar/?key=c0343a3b18daf310b8156552e9850cac');
		$this->app->go();
		
		// Should see a vcalendar
		$this->assertPattern('/^BEGIN:VCALENDAR/', $this->view->output);
		$this->assertPattern('/END:VCALENDAR$/', $this->view->output);
		
		$this->assertPattern('/SUMMARY:Todo 1/', $this->view->output);
		$this->assertNoPattern('/SUMMARY:Todo 2/', $this->view->output);
		$this->assertPattern('/SUMMARY:Event 1/', $this->view->output);
		$this->assertPattern('/SUMMARY:Event 2/', $this->view->output);
		
		$this->assertPattern("/BEGIN:VEVENT\r\nDTSTAMP:\d{8}T\d{6}\r\nSUMMARY:Event 3\r\nDTSTART:20110101T100000\r\nDTEND:20110102T110100\r\nLOCATION:place\r\nDESCRIPTION:one day, one hour, one minute and one second\r\nEND:VEVENT/", $this->view->output);
		$this->assertPattern("/BEGIN:VEVENT\r\nDTSTAMP:\d{8}T\d{6}\r\nSUMMARY:Event 4\r\nDTSTART:20110201\r\nDTEND:20110203\r\nDESCRIPTION:just days, no time\r\nEND:VEVENT/", $this->view->output);
	}

	function testHTTPNotModifiedSince() {
		$this->saveFixtureRows('person_with_calendar', 'people');
		$this->saveFixtureRows('user_with_calendar', 'users');
		$this->saveFixtureRows('user_with_calendar_company_access', 'user_company_access');

		// Load activities
		$this->saveFixtureRows('http_modified_activities', 'tactile_activities');
		
		$this->setURL('public/icalendar/?key=c0343a3b18daf310b8156552e9850cac');
		$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'Thu, 21 Dec 2000 16:01:07 +0200';
		$this->app->go();
		
		$this->assertTrue(in_array('Status: 304 Not Modified', $this->view->getTestHeaders()));
		$this->assertEqual(trim($this->view->output), '');
	}
	
	function testHTTPModifiedSince() {
		$this->saveFixtureRows('person_with_calendar', 'people');
		$this->saveFixtureRows('user_with_calendar', 'users');
		$this->saveFixtureRows('user_with_calendar_company_access', 'user_company_access');

		// Load activities
		$this->saveFixtureRows('http_modified_activities', 'tactile_activities');
		
		$this->setURL('public/icalendar/?key=c0343a3b18daf310b8156552e9850cac');
		$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'Tue, 21 Dec 1999 16:01:07 +0200';
		$this->app->go();
		
		$this->assertFalse(in_array('Status: 304 Not Modified', $this->view->getTestHeaders()));
		$this->assertPattern('/^BEGIN:VCALENDAR/', $this->view->output);
		$this->assertPattern('/END:VCALENDAR$/', $this->view->output);
	}
	
}
