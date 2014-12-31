<?php
class TestOfPreferencesController extends ControllerTest {
	
	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$db = DB::Instance();
		
	}
	
	function testPageLoads() {
		$this->setURL('preferences');
		
		$this->app->go();
		
		$this->genericPageTest();
	}
	
	function testChangingPassword() {
		$this->transport->expectNever('send');
		
		$this->setURL('preferences/change_password');
		
		$_POST = array(
			'current_password'=>'password',
			'new_password'=>'password2',
			'new_password_again'=>'password2'
		);
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$user = DataObject::Construct('User');
		$user->load('greg//tactile');
		
		$this->assertEqual($user->password, md5('password2'));
	}
	
	function testChangingWithNoCurrentPassword() {
		$this->setURL('preferences/change_password');
		
		$_POST = array(
			'new_password'=>'password2',
			'new_password_again'=>'password2'
		);
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$user = DataObject::Construct('User');
		$user->load('greg//tactile');
		
		$this->assertEqual($user->password, md5('password'));
	}
	
	function testChangingWithWrongCurrentPassword() {
		$this->setURL('preferences/change_password');
		
		$_POST = array(
			'current_password'=>'wrong',
			'new_password'=>'password2',
			'new_password_again'=>'password2'
		);
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$user = DataObject::Construct('User');
		$user->load('greg//tactile');
		
		$this->assertEqual($user->password, md5('password'));
	}
	
	function testChangingWithDifferentConfirmPassword() {
		$this->setURL('preferences/change_password');
		
		$_POST = array(
			'current_password'=>'password',
			'new_password'=>'password2',
			'new_password_again'=>'password3'
		);
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$user = DataObject::Construct('User');
		$user->load('greg//tactile');
		
		$this->assertEqual($user->password, md5('password'));
	}
	
	
	function testChangingEmailPreferencesBothYes() {
		$this->setURL('preferences/email_preferences/');
		
		$_POST = array(
			'email_prefs'=>array(
				'activity_reminder'=>'yes',
				'activity_notification'=>'yes'
			)
		);
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$prefs = EmailPreference::getAll('greg//tactile');
		$this->assertTrue($prefs['activity_reminder']);
		$this->assertTrue($prefs['activity_notification']);
		
		$db = DB::Instance();
		$query = 'SELECT send FROM email_preferences';
		$expected = array(true,true);
		$actual = $db->GetCol($query);
		
		$this->assertEqual($expected, $actual);
	}
	
	function testChangingOneEmailPreferenceToNo() {
		$this->setURL('/preferences/email_preferences/');
		
		$_POST = array(
			'email_prefs'=>array(
				'activity_reminder'=>'yes',
				'activity_notification'=>'no'
			)
		);
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$prefs = EmailPreference::getAll('greg//tactile');
		$this->assertTrue($prefs['activity_reminder']);
		$this->assertFalse($prefs['activity_notification']);
	}
	
	function testChangingBothEmailPreferencesToNo() {
		$this->setURL('/preferences/email_preferences/');
		
		$_POST = array(
			'email_prefs'=>array(
				'activity_reminder'=>'no',
				'activity_notification'=>'no'
			)
		);
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$prefs = EmailPreference::getAll('greg//tactile');
		$this->assertFalse($prefs['activity_reminder']);
		$this->assertFalse($prefs['activity_notification']);
	}
	
	function testChangingDateFormat() {
		$this->setURL('preferences/change_datetime');
		$_POST = array(
			'date_format'=>'mdy'
		);
		
		$this->app->go();
		$this->checkSuccessfulSave();
		
		$user = DataObject::Construct('User');
		$user->load('greg//tactile');
		
		$this->assertEqual('m/d/Y', $user->date_format);
	}
	
	function testWithInvalidDateFormat() {
		$this->setURL('preferences/change_datetime');
		$_POST = array(
			'date_format'=>'goo'
		);
		
		$this->app->go();
		$this->checkSuccessfulSave();
		
		$user = DataObject::Construct('User');
		$user->load('greg//tactile');
		
		$this->assertEqual('d/m/Y', $user->date_format);
	}
	
	function testChangingTimezone() {
		$this->setURL('preferences/change_datetime');
		$_POST = array(
			'timezone'=>'Pacific/Samoa'
		);
		
		$this->app->go();
		$this->checkSuccessfulSave();
		
		$user = DataObject::Construct('User');
		$user->load('greg//tactile');
		
		$this->assertEqual('Pacific/Samoa', $user->timezone);
	}
	
	function testInvalidTimezone() {
		$this->setURL('preferences/change_datetime');
		$_POST = array(
			'timezone'=>'Sheffield'
		);
		
		$this->app->go();
		$this->checkUnsuccessfulSave();
		
		$user = DataObject::Construct('User');
		$user->load('greg//tactile');
		
		$this->assertEqual('Europe/London', $user->timezone);
	}
	
	function tearDown() {
		$db = DB::Instance();
		$query = 'DELETE FROM email_preferences';
		$db->Execute($query) or die($db->ErrorMsg());
		
		
		
		parent::tearDown();
	}
	
}
?>