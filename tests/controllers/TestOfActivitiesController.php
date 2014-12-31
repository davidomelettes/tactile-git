<?php

class TestOfActivitiesController extends ControllerTest {

	function setup() {
		parent::setup();
		
		$this->setDefaultLogin();
		$db = DB::Instance();
		
		$query = 'DELETE FROM notes';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM users WHERE person_id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM organisations WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM tactile_activities';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->loadFixtures('activities');
	}
	
	function testOfIndexPage() {
		$this->setURL('activities');
		$this->app->go();
		$this->genericPageTest();
		
		$this->assertEqual($this->app->getControllerName(),'activitys');
		$this->assertEqual($this->view->get('templateName'),$this->makeTemplatePath('crm/tactile_activitys/index'));
		
		$activities = $this->view->get('activitys');
		$this->assertFalse($activities==false);
		$this->assertIsA($activities, 'Tactile_ActivityCollection');
		$this->assertEqual(count($activities),0);
	}
	
	function testNewActivityPage() {
		$this->setURL('activities/new');
		$this->app->go();
		$this->genericPageTest();
		
		$this->assertEqual($this->app->getControllerName(),'activitys');
		$this->assertEqual($this->view->get('templateName'),$this->makeTemplatePath('crm/tactile_activitys/new'));
		
		$this->assertIsA($this->view->get('Activity'), 'Tactile_Activity');
	}
	
	function testSaveActivityBasic() {		
		$_POST['Activity'] = $this->getFixture('basic');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$activity = DataObject::Construct('Activity');
		$this->assertEqual(count($activity->getAll()), 1);
		
		$activity = $activity->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($activity,'Tactile_Activity');
		
		$this->assertFixture($activity, 'basic', null, array('date_choice'));
		
		$this->assertEqual($activity->assigned_to, EGS::getUsername());
		$this->assertEqual($activity->assigned_by, EGS::getUsername());
		
		$this->assertNow($activity->created);
	}
	
	function testWithNoName() {
		$_POST['Activity'] = $this->getFixture('basic');
		unset($_POST['Activity']['name']);
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
	}
	
	function testWithNoPost() {
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$opp = DataObject::Construct('Activity');
		$this->assertEqual(count($opp->getAll()), 0);
	}
	
	function testCreatedIsIgnored() {
		$this->setURL('activities/save');
		$_POST['Activity'] = $this->getFixture('with_created_set');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		$act = DataObject::Construct('Activity');
		$this->assertEqual(count($act->getAll()), 1);
		
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($act,'Tactile_Activity');
		$this->assertEqual($act->name, $_POST['Activity']['name']);

		$this->assertNow($act->created);
	}
	
	function testWithQuotes() {
		$_POST['Activity'] = $this->getFixture('with_quotes');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$act = DataObject::Construct('Activity');
		$this->assertEqual(count($act->getAll()), 1);
		
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($act,'Tactile_Activity');
		
		$this->assertFixture($act, 'with_quotes', null, array('date_choice'));
		
		$this->assertFalse(strpos($act->name,'\\'));
		
		$this->assertEqual($act->assigned_to, EGS::getUsername());
		
		$this->assertNow($act->created);
	}
	
	function testAllMainFields() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		
		$_POST['Activity'] = $this->getFixture('all_main_fields');
		$_POST['Activity']['date_choice'] = 'date';
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$act = DataObject::Construct('Activity');
		$this->assertEqual(count($act->getAll()), 1);
		
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($act,'Tactile_Activity');
		
		$this->assertFixture($act, 'all_main_fields', null, array('date_choice'));
		
		$this->assertEqual($act->date, '2008-12-12');
	
		$this->assertNow($act->created);
	}
	
	function testSavingWithType() {
		$this->saveFixtureRows('default_types', 'activitytype');
		$this->setURL('activities/save');
		
		$_POST['Activity'] = $this->getFixture('with_type');

		$this->app->go();

		$this->checkSuccessfulSave();
		
		$act = DataObject::Construct('Activity');
		$this->assertEqual(count($act->getAll()), 1);
		
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($act,'Tactile_Activity');
		
		$this->assertFixture($act, 'with_type', null, array('date_choice'));
	
		$this->assertNow($act->created);
	}
	
	function testAttachingToCompany() {
		$_POST['Activity'] = $this->getFixture('basic');
		$_POST['Activity']['organisation_id'] = 1;
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$activity = DataObject::Construct('Activity');
		$this->assertEqual(count($activity->getAll()), 1);
		
		$activity = $activity->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($activity,'Tactile_Activity');
		
		$this->assertFixture($activity, 'basic', null, array('date_choice'));
		
		$this->assertEqual($activity->assigned_to, EGS::getUsername());
		$this->assertEqual($activity->assigned_by, EGS::getUsername());
		
		$this->assertEqual($activity->organisation, 'Default Company');
		
		$this->assertNow($activity->created);
	}
	
	function testAddingNote() {
		
		$this->saveFixtureRows('default_activities', 'tactile_activities');
		$this->setJSONRequest();
		$this->setUrl('activities/save_note/?activity_id=100');
		$_POST = $this->getFixture('basic_note');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());

		$note = $this->view->get('note');
		$this->assertIsA($note,'Note');		
		$this->assertFixture($note, 'basic_note');		
		$this->assertEqual($note->activity_id,100);
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM notes';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 1);
	}
	
	function testBasicEditing() {
		$this->saveFixtureRows('default_activities', 'tactile_activities');
		$this->setURL('activities/edit/100');
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Activity');
		
		$this->assertIsA($model, 'Tactile_Activity');		
		$this->assertEqual($model->id, 100);		
		$this->assertTrue($model->canEdit());
	}
	
	function testEditingWithInvalidID() {
		$this->setURL('activities/edit/999');
		$this->app->go();
		
		$f = Flash::Instance();
		$r = $this->injector->instantiate('Redirection');
		
		$this->assertTrue($f->hasErrors());
		$this->assertTrue($r->willRedirect());		
	}
	
	function testSavingWithDMYDateFormat() {
		$db = DB::Instance();
		$query = 'UPDATE users SET date_format = \'m/d/Y\' WHERE username=\'greg//tactile\'';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$_POST['Activity'] = $this->getFixture('basic_with_mdy_date');
		$_POST['Activity']['date_choice'] = 'date';
		
		$this->setURL('activities/save');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$activity = DataObject::Construct('Activity');
		$this->assertEqual(count($activity->getAll()), 1);
		
		$activity = $activity->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($activity,'Tactile_Activity');
		
		$this->assertFixture($activity, 'basic_with_mdy_date', null, array('date_choice'));
		
		$this->assertEqual($activity->date, '2008-12-20');
		
		$this->assertEqual($activity->assigned_to, EGS::getUsername());
		$this->assertEqual($activity->assigned_by, EGS::getUsername());
	}
	
	function testSavingWithTimezoneSetToParis() {
		$db = DB::Instance();
		$query = 'UPDATE users SET timezone = \'Europe/Paris\' WHERE username=\'greg//tactile\'';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$_POST['Activity'] = array(
			'name'=>'Test With Timezone',
			'date'=>'20/02/2008',
			'date_choice'=>'date',
			'time_hours'=>'13',
			'time_minutes'=>'28'
		);
		$this->setURL('activities/save');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$activity = DataObject::Construct('Activity');
		$this->assertEqual(count($activity->getAll()), 1);
		
		$activity = $activity->loadBy('name', $_POST['Activity']['name']);
		$this->assertIsA($activity, 'Tactile_Activity');
		/* @var $activity Tactile_Activity */
		
		$this->assertEqual($activity->date, '2008-02-20');
		$this->assertEqual($activity->time, '12:28:00');		
	}
	
	function testWithDifferentTimezoneThatCausesDifferentDate() {
		$db = DB::Instance();
		$query = 'UPDATE users SET timezone = \'Europe/Paris\' WHERE username=\'greg//tactile\'';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$_POST['Activity'] = array(
			'name'=>'Test With Timezone',
			'date'=>'20/02/2008',
			'date_choice'=>'date',
			'time_hours'=>'00',
			'time_minutes'=>'28'
		);
		$this->setURL('activities/save');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$activity = DataObject::Construct('Activity');
		$this->assertEqual(count($activity->getAll()), 1);
		
		$activity = $activity->loadBy('name', $_POST['Activity']['name']);
		$this->assertIsA($activity, 'Tactile_Activity');
		/* @var $activity Tactile_Activity */
		
		$this->assertEqual($activity->date, '2008-02-19');
		$this->assertEqual($activity->time, '23:28:00');		
	}
	
	function testWithNegativeOffset() {
		$db = DB::Instance();
		$query = 'UPDATE users SET timezone = \'Atlantic/Azores\' WHERE username=\'greg//tactile\'';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$_POST['Activity'] = array(
			'name'=>'Test With Timezone',
			'date'=>'20/02/2008',
			'date_choice'=>'date',
			'time_hours'=>'13',
			'time_minutes'=>'28'
		);
		$this->setURL('activities/save');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$activity = DataObject::Construct('Activity');
		$this->assertEqual(count($activity->getAll()), 1);
		
		$activity = $activity->loadBy('name', $_POST['Activity']['name']);
		$this->assertIsA($activity, 'Tactile_Activity');
		/* @var $activity Tactile_Activity */
		
		$this->assertEqual($activity->date, '2008-02-20');
		$this->assertEqual($activity->time, '14:28:00');	
	}
	
	function testCloseToMidnightWithNegativeOffset() {
		$db = DB::Instance();
		$query = 'UPDATE users SET timezone = \'Atlantic/Azores\' WHERE username=\'greg//tactile\'';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$_POST['Activity'] = array(
			'name'=>'Test With Timezone',
			'date'=>'20/02/2008',
			'date_choice'=>'date',
			'time_hours'=>'23',
			'time_minutes'=>'28'
		);
		$this->setURL('activities/save');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$activity = DataObject::Construct('Activity');
		$this->assertEqual(count($activity->getAll()), 1);
		
		$activity = $activity->loadBy('name', $_POST['Activity']['name']);
		$this->assertIsA($activity, 'Tactile_Activity');
		/* @var $activity Tactile_Activity */
		
		$this->assertEqual($activity->date, '2008-02-21');
		$this->assertEqual($activity->time, '00:28:00');	
	}
	
	function testCompanyIDInURLGetsUsed() {
		$this->setURL('activities/new/?organisation_id=1');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$this->assertPattern('#id="activity_organisation" value="Default Company"#i', $this->view->output);
		$this->assertPattern('#id="activity_organisation_id" value="1"#i', $this->view->output);
		
	}
	
	function testPersonIDInURLGetsUsed() {
		$this->setURL('activities/new/?person_id=1');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$this->assertPattern('#id="activity_person" value="Greg Jones"#i', $this->view->output);
		$this->assertPattern('#id="activity_person_id" value="1"#i', $this->view->output);
	}
	
	function testBasicActivityEvent() {
		$_POST['Activity'] = $this->getFixture('basic_event');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$act = DataObject::Construct('Activity');
		$this->assertEqual(count($act->getAll()), 1);
		
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($act,'Tactile_Activity');
		
		// Can't assert as fixture, because form contains fields which do not map to model properties
		//$this->assertFixture($act, 'basic_event');
		
		$this->assertFalse(strpos($act->name,'\\'));
		
		$this->assertEqual($act->assigned_to, EGS::getUsername());
		
		$this->assertEqual($act->name, 'Event');
		$this->assertEqual($act->class, 'event');
		// Today
		$this->assertEqual($act->date, date('Y-m-d'));
		$this->assertEqual($act->end_date, date('Y-m-d'));
		$this->assertEqual($act->time, '12:00:00');
		$this->assertEqual($act->end_time, '13:00:00');
		
		$this->assertFalse($act->is_later());
		
		$this->assertNow($act->created);
	}
	
	function testBasicActivityEventInvalidTime() {
		$_POST['Activity'] = $this->getFixture('basic_event_invalid_time');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
	}
	
	function testBasicActivityEventTomorrow() {
		$_POST['Activity'] = $this->getFixture('basic_event_tomorrow');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$act = DataObject::Construct('Activity');
		$this->assertEqual(count($act->getAll()), 1);
		
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($act,'Tactile_Activity');
		
		$this->assertEqual($act->date, date('Y-m-d', strtotime('tomorrow')));
		$this->assertEqual($act->end_date, date('Y-m-d', strtotime('tomorrow')));
		$this->assertEqual($act->time, '12:00:00');
		$this->assertEqual($act->end_time, '13:00:00');
		
		$this->assertFalse($act->is_later());
	}
	
	function testBasicActivityEventTodayBadTimeRange() {
		$_POST['Activity'] = $this->getFixture('basic_event_today_bad_time_range');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
	}
	
	function testBasicActivityEventDate() {
		$_POST['Activity'] = $this->getFixture('basic_event_date');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$act = DataObject::Construct('Activity');
		$this->assertEqual(count($act->getAll()), 1);
		
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($act,'Tactile_Activity');
		
		$this->assertEqual($act->date, '2011-01-01');
		$this->assertEqual($act->end_date, '2011-01-01');
		$this->assertEqual($act->time, '12:00:00');
		$this->assertEqual($act->end_time, '13:00:00');
		
		$this->assertFalse($act->is_later());
	}
	
	function testBasicActivityEventDateNoTime() {
		$_POST['Activity'] = $this->getFixture('basic_event_date_no_time');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$act = DataObject::Construct('Activity');
		$this->assertEqual(count($act->getAll()), 1);
		
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($act,'Tactile_Activity');
		
		$this->assertEqual($act->date, '2011-01-01');
		$this->assertEqual($act->end_date, '2011-01-01');
		$this->assertEqual($act->time, '');
		$this->assertEqual($act->end_time, '');
		
		$this->assertFalse($act->is_later());
	}
	
	function testBasicActivityEventDateOnlyStartTime() {
		$_POST['Activity'] = $this->getFixture('basic_event_date_only_start_time');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$act = DataObject::Construct('Activity');
		$this->assertEqual(count($act->getAll()), 1);
		
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($act,'Tactile_Activity');
		
		$this->assertEqual($act->date, '2011-01-01');
		$this->assertEqual($act->end_date, '2011-01-02');
		$this->assertEqual($act->time, '12:00:00');
		$this->assertEqual($act->end_time, '');
		
		$this->assertFalse($act->is_later());
	}
	
	function testBasicActivityEventDateOnlyEndTime() {
		$_POST['Activity'] = $this->getFixture('basic_event_date_only_end_time');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$act = DataObject::Construct('Activity');
		$this->assertEqual(count($act->getAll()), 1);
		
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($act,'Tactile_Activity');
		
		$this->assertEqual($act->date, '2011-01-01');
		$this->assertEqual($act->end_date, '2011-01-02');
		$this->assertEqual($act->time, '');
		$this->assertEqual($act->end_time, '13:00:00');
		
		$this->assertFalse($act->is_later());
	}
	
	function testBasicActivityEventDateBadDateRange() {
		$_POST['Activity'] = $this->getFixture('basic_event_date_bad_date_range');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
	}
	
	function testBasicActivityEventLater() {
		$_POST['Activity'] = $this->getFixture('basic_event_later');
		
		$this->setURL('activities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$act = DataObject::Construct('Activity');
		$this->assertEqual(count($act->getAll()), 1);
		
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		$this->assertIsA($act,'Tactile_Activity');
		
		$this->assertTrue($act->is_later());
	}
	
	function testDownloadActivityAsVCalendar() {
		$this->saveFixtureRows('default_activities', 'tactile_activities');
		$this->setURL('activities/icalendar/100');
		$this->app->go();
		
		$this->assertPattern("|^BEGIN:VCALENDAR\r\nPRODID:-//omelett.es//Tactile//EN\r\nVERSION:2.0\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\nX-WR-TIMEZONE:".CurrentlyLoggedInUser::Instance()->getTimezoneString()."\r\nBEGIN:VTODO\r\nDTSTAMP:\d{8}T\d{6}\r\nSUMMARY:Default Activity\r\nEND:VTODO\r\nEND:VCALENDAR$|", $this->view->output);
		//$this->assertEqual("BEGIN:VCALENDAR\r\nPRODID:-//omelett.es//Tactile//EN\r\nVERSION:2.0\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\nX-WR-TIMEZONE:".CurrentlyLoggedInUser::Instance()->getTimezoneString()."\r\nBEGIN:VTODO\r\nDTSTAMP:\d{8}T\d{6}\r\nSUMMARY:Default Activity\r\nEND:VTODO\r\nEND:VCALENDAR", $this->view->output);
	}
	
	
	function testBasicActivityEventDateOnlyStartTimeThenEditAndSaveWithBothTimes() {
		$this->saveFixtureRows('event_activities', 'tactile_activities');
		
		$this->setURL('activities/edit/703');
		$this->app->go();
		
		$model = $this->view->get('Activity');
		/* @var $model Tactile_Activity */
		$this->assertIsA($model, 'Tactile_Activity');
		$this->assertEqual($model->id, 703);
		$this->assertEqual($model->name, 'Activity Event With Dates And Start Time');
		
		$_POST['Activity']['id'] = $model->id;
		$_POST['Activity']['name'] = $model->name;
		$_POST['Activity']['class'] = $model->class;
		$_POST['Activity']['date_choice'] = 'date';
		$_POST['Activity']['date'] = $model->date;
		$time = $model->time;
		preg_match('/(\d{2}):(\d{2}):(\d{2})/', $time, $matches);
		$_POST['Activity']['time_hours'] = $matches[1];
		$_POST['Activity']['time_minutes'] = $matches[2];
		$_POST['Activity']['end_date'] = $model->end_date;
		$_POST['Activity']['end_time_hours'] = '14';
		$_POST['Activity']['end_time_minutes'] = '00';
		$_POST['Activity']['assigned_to'] = 'greg//tactile';
		
		$this->setURL('activities/save');
		$this->app->go();
		$this->checkSuccessfulSave();
		
		$act = DataObject::Construct('Activity');
		$act = $act->loadBy('name',$_POST['Activity']['name']);
		
		$this->assertEqual($act->time, '12:00:00');
		$this->assertEqual($act->end_time, '14:00:00');
	}
	
	function testForMailToLinkError() {
		$this->saveFixtureRows('default_activities', 'tactile_activities');
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('person_contact_details', 'person_contact_methods');
		$db = DB::Instance();
		$query = 'UPDATE tactile_activities SET person_id=100 WHERE id=100';
		$db->Execute($query);
		$this->setURL('activities/view/100');
		$this->app->go();
		
		$this->genericPageTest();
		$this->assertNoPattern('#mailto:\$Person->email#i', $this->view->output);
	}
	
	function testDurationValidatorWorksForADifferentTimezone() {
		DB::Instance()->Execute("UPDATE users SET timezone = 'Pacific/Samoa'") or die('Failed to change timezone!');
		$_POST['Activity'] = array(
			'name'				=> 'Testing diffcult timezones',
			'class'				=> 'event',
			'date_choice'		=> 'today',
			'time_hours'		=> '22',
			'time_minutes'		=> '00',
			'end_time_hours'	=> '23',
			'end_time_minutes'	=> '30'
		);
		
		$this->setURL('activities/save');
		$this->app->go();
		$this->checkSuccessfulSave();
		
		DB::Instance()->Execute("UPDATE users SET timezone = 'Europe/London'") or die('Failed to change timezone!');
	}
	
	function testDurationValidatorWorksForADifferentTimezoneWhenSaving() {
		$this->saveFixtureRows('tz_complete_activity', 'tactile_activities');
		
		DB::Instance()->Execute("UPDATE users SET timezone = 'America/Argentina/Buenos_Aires'") or die('Failed to change timezone!');
		
		$_POST['Activity'] = array(
			'id' => '888',
			'name' => 'Complete This!',
			'class' => 'event',
			'date_choice' => 'date',
			'time_hours' => '15',
			'time_minutes' => '00',
			'date' => '12/10/2010',
			'end_time_hours' => '16',
			'end_time_minutes' => '00',
			'end_date' => '12/10/2010',
			'assigned_to' => 'greg//tactile'
		); 
		
		$this->setURL('activities/save');
		$this->app->go();
		$this->checkSuccessfulSave();
		
		DB::Instance()->Execute("UPDATE users SET timezone = 'Europe/London'") or die('Failed to change timezone!');
	}
	
	function testDurationValidatorWorksForADifferentTimezoneWhenCompleting() {
		$this->saveFixtureRows('tz_complete_activity', 'tactile_activities');
		
		DB::Instance()->Execute("UPDATE users SET timezone = 'America/Argentina/Buenos_Aires'") or die('Failed to change timezone!');
		
		$this->setURL('activities/complete/888');
		$this->app->go();
		$this->checkSuccessfulSave();
		
		DB::Instance()->Execute("UPDATE users SET timezone = 'Europe/London'") or die('Failed to change timezone!');
	}
	
	function testTodayOverdueIsOverdue() {
		$this->saveFixtureRows('overdue_tz_activities', 'tactile_activities');
		$date = date('Y-m-d', strtotime('-5 minutes'));
		$time = date('H:i:s', strtotime('-5 minutes'));
		DB::Instance()->Execute("UPDATE tactile_activities SET date = '$date', time = '$time'") or die('Failed to change timezone!');
		
		$this->setURL('activities/my_overdue');
		$this->app->go();
		
		$this->assertPattern('#<h2>\s*My Overdue Activities\s*</h2>#', $this->view->output);
		
		$activities = $this->view->get('activitys');
		$this->assertIsA($activities, 'Tactile_ActivityCollection');
		$this->assertEqual(count($activities),1);
		$names = $activities->pluck('name');
		$this->assertEqual($names, array('Today and Overdue'));
	}
	
	function testTodayOverdueIsToday() {
		$this->saveFixtureRows('overdue_tz_activities', 'tactile_activities');
		$date = date('Y-m-d', strtotime('-5 minutes'));
		$time = date('H:i:s', strtotime('-5 minutes'));
		DB::Instance()->Execute("UPDATE tactile_activities SET date = '$date', time = '$time'") or die('Failed to change timezone!');
		
		$this->setURL('activities/my_today');
		$this->app->go();
		
		$this->assertPattern('#<h2>\s*My Activities for Today\s*</h2>#', $this->view->output);
		
		$activities = $this->view->get('activitys');
		$this->assertIsA($activities, 'Tactile_ActivityCollection');
		$this->assertEqual(count($activities),1);
		$names = $activities->pluck('name');
		$this->assertEqual($names, array('Today and Overdue'));
	}
	
	function testAucklandTzOverduesCorrectly() {
		$this->saveFixtureRows('overdue_tz_activities', 'tactile_activities');
		$date = date('Y-m-d', strtotime('-13 hours'));
		$time = date('H:i:s', strtotime('-13 hours'));
		DB::Instance()->Execute("UPDATE tactile_activities SET date = '$date', time = '$time'") or die('Failed to change timezone!');
		
		DB::Instance()->Execute("UPDATE users SET timezone = 'Pacific/Auckland'") or die('Failed to change timezone!');
		
		$this->setURL('activities/my_overdue');
		$this->app->go();
		
		$activities = $this->view->get('activitys');
		$this->assertIsA($activities, 'Tactile_ActivityCollection');
		$this->assertEqual(count($activities),1);
		$names = $activities->pluck('name');
		$this->assertEqual($names, array('Today and Overdue'));
		
		DB::Instance()->Execute("UPDATE users SET timezone = 'Europe/London'") or die('Failed to change timezone!');
	}
}
