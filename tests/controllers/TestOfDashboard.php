<?php

class TestOfDashboard extends ControllerTest {
	
	function setup() {
		parent::setup();
		$db = DB::Instance();
		
		$this->setDefaultLogin();
		$this->loadFixtures('dashboard');
		
		// Fix created timestamps to make them recent
		// Younger <= => Older
		$update_created = array('opportunity', 'activity', 'org_note', 'email', 'other_user_note', 'file');
		$i = 1;
		foreach ($update_created as $fixtures) {
			foreach ($this->fixtures[$fixtures] as &$fixture) {
				switch ($fixtures) {
					case 'email':
						$fixture['received'] = date('Y-m-d H:i:s', strtotime('-' . $i * 5 . ' seconds'));
						break;
					case 'org_note':
					case 'other_user_note':
						$fixture['lastupdated'] = date('Y-m-d H:i:s', strtotime('-' . $i * 5 . ' seconds'));
						break;
				}
				$fixture['created'] = date('Y-m-d H:i:s', strtotime('-' . $i * 5 . ' seconds'));
				$i++;
			} 
		}
		
		$query = 'DELETE FROM organisations WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		$query = 'DELETE FROM opportunities WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		$query = 'DELETE FROM tactile_activities WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		$query = 'DELETE FROM notes WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		$query = 'DELETE FROM emails WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		$query = 'DELETE FROM s3_files WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		$query = 'DELETE FROM tactile_magic';
		$db->Execute($query) or die($db->ErrorMsg());
	}
	
	function testBeingLoggedInShowsDashboard() {
		$this->setDefaultLogin();
		$this->app->go();
		$this->genericPageTest();
		$this->assertEqual($this->app->getControllerName(),'dashboard');
		$this->assertEqual($this->view->get('layout'), 'default');		
		$this->assertFalse($this->view->get('flash')->hasErrors());
		$this->assertEqual($this->view->get('templateName'), $this->makeTemplatePath('tactile/index'));
		
		$timeline = $this->view->get('activity_timeline');
		$this->assertFalse($timeline == false);
		$this->assertIsA($timeline, 'Timeline');
	}
	
	function testDashboardNotesEmails() {
		$_GET['view'] = 'notes_emails';
		
		$this->saveFixtureRows('organisation', 'organisations');
		$this->saveFixtureRows('person', 'people');
		$this->saveFixtureRows('opportunity_status', 'opportunitystatus');
		$this->saveFixtureRows('opportunity', 'opportunities');
		$this->saveFixtureRows('activity', 'tactile_activities');
		
		$this->saveFixtureRows('org_note', 'notes');
		$this->saveFixtureRows('greg_contact_method_email', 'person_contact_methods');
		$this->saveFixtureRows('person_contact_method_email', 'person_contact_methods');
		$this->saveFixtureRows('email', 'emails');
		$this->saveFixtureRows('file', 's3_files');
		
		$this->saveFixtureRows('other_user_person', 'people');
		$this->saveFixtureRows('other_user', 'users');
		$this->saveFixtureRows('other_user_note', 'notes');

		$this->app->go();
		$this->genericPageTest();
		
		$timeline = $this->view->get('activity_timeline');
		$this->assertFalse($timeline == false);
		$this->assertIsA($timeline, 'Timeline');
		
		$headings = $timeline->pluck(array('name', 'title', 'subject', 'filename'));
		$expected = array('Basic Note', 'Basic Email', 'Other Note');
		$this->assertEqual($headings, $expected);
	}

	function testDashboardNotesEmailsActs() {
		$_GET['view'] = 'notes_emails_acts';
		
		$this->saveFixtureRows('organisation', 'organisations');
		$this->saveFixtureRows('person', 'people');
		$this->saveFixtureRows('opportunity_status', 'opportunitystatus');
		$this->saveFixtureRows('opportunity', 'opportunities');
		$this->saveFixtureRows('activity', 'tactile_activities');
		
		$this->saveFixtureRows('org_note', 'notes');
		$this->saveFixtureRows('greg_contact_method_email', 'person_contact_methods');
		$this->saveFixtureRows('person_contact_method_email', 'person_contact_methods');
		$this->saveFixtureRows('email', 'emails');
		$this->saveFixtureRows('file', 's3_files');
		
		$this->saveFixtureRows('other_user_person', 'people');
		$this->saveFixtureRows('other_user', 'users');
		$this->saveFixtureRows('other_user_note', 'notes');
		
		// Complete the activity
		$db = DB::Instance();
		$sql = "UPDATE tactile_activities SET completed = now() WHERE id = '100'";
		$db->execute($sql);
		
		$this->app->go();
		$this->genericPageTest();
		
		$timeline = $this->view->get('activity_timeline');
		$this->assertFalse($timeline == false);
		$this->assertIsA($timeline, 'Timeline');
		
		$headings = $timeline->pluck(array('name', 'title', 'subject', 'filename'));
		$expected = array('Basic Activity', 'Basic Note', 'Basic Email', 'Other Note', );
		$this->assertEqual($headings, $expected);
	}
	
	function testDashboardCustom() {
		$_GET['view'] = 'custom';
		
		$this->saveFixtureRows('organisation', 'organisations');
		$this->saveFixtureRows('person', 'people');
		$this->saveFixtureRows('opportunity_status', 'opportunitystatus');
		$this->saveFixtureRows('opportunity', 'opportunities');
		$this->saveFixtureRows('activity', 'tactile_activities');
		
		$this->saveFixtureRows('org_note', 'notes');
		$this->saveFixtureRows('greg_contact_method_email', 'person_contact_methods');
		$this->saveFixtureRows('person_contact_method_email', 'person_contact_methods');
		$this->saveFixtureRows('email', 'emails');
		$this->saveFixtureRows('file', 's3_files');
		
		$this->saveFixtureRows('other_user_person', 'people');
		$this->saveFixtureRows('other_user', 'users');
		$this->saveFixtureRows('other_user_note', 'notes');
		
		$this->saveFixtureRows('custom_timeline_prefs', 'tactile_magic');
		
		$this->app->go();
		$timeline = $this->view->get('activity_timeline');
		$this->assertIsA($timeline, 'Timeline');
		
		$headings = $timeline->pluck(array('name', 'title', 'subject', 'filename'));
		$expected = array('Basic Opportunity', 'Basic Activity', 'Basic Note', 'Basic Email', 'Other Note', 'foo.doc');
		$this->assertEqual($headings, $expected);
	}
	
	function testDashboardCustomNoEmailsMyNotes() {
		$_GET['view'] = 'custom';
		
		$this->saveFixtureRows('organisation', 'organisations');
		$this->saveFixtureRows('person', 'people');
		$this->saveFixtureRows('opportunity_status', 'opportunitystatus');
		$this->saveFixtureRows('opportunity', 'opportunities');
		$this->saveFixtureRows('activity', 'tactile_activities');
		
		$this->saveFixtureRows('org_note', 'notes');
		$this->saveFixtureRows('greg_contact_method_email', 'person_contact_methods');
		$this->saveFixtureRows('person_contact_method_email', 'person_contact_methods');
		$this->saveFixtureRows('email', 'emails');
		$this->saveFixtureRows('file', 's3_files');
		
		$this->saveFixtureRows('other_user_person', 'people');
		$this->saveFixtureRows('other_user', 'users');
		$this->saveFixtureRows('other_user_note', 'notes');
		
		$this->saveFixtureRows('custom_timeline_prefs_no_emails_my_notes', 'tactile_magic');
		
		$this->app->go();
		$timeline = $this->view->get('activity_timeline');
		
		$headings = $timeline->pluck(array('name', 'title', 'subject', 'filename'));
		$expected = array('Basic Opportunity', 'Basic Activity', 'Basic Note', 'foo.doc');
		$this->assertEqual($headings, $expected);
	}
	
	function textOverdueCountForAuckland() {
		$this->saveFixtureRows('overdue_tz_activities', 'tactile_activities');
		$date = date('Y-m-d', strtotime('-13 hours'));
		$time = date('H:i:s', strtotime('-13 hours'));
		DB::Instance()->Execute("UPDATE tactile_activities SET date = '$date', time = '$time'") or die('Failed to change timezone!');
		
		DB::Instance()->Execute("UPDATE users SET timezone = 'Pacific/Auckland'") or die('Failed to change timezone!');
		
		$this->setURL('activities/my_overdue');
		$this->app->go();
		
		$activities = $this->view->get('overdue_activities');
		$this->assertEqual($activities, 1);
		$activities = $this->view->get('today_activities');
		$this->assertEqual($activities, 1);
		$activities = $this->view->get('later_activities');
		$this->assertEqual($activities, 0);
		
		DB::Instance()->Execute("UPDATE users SET timezone = 'Europe/London'") or die('Failed to change timezone!');
	}
	
}
