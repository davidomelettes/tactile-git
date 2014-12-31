<?php
class TestOfDeleting extends ControllerTest {
	
	function setup() {
		parent::setup();
		$this->loadFixtures('deleting');
		$this->saveFixture('non_admin_person', 'people');
		$this->saveFixtureRows('non_admin_user', 'users');
		$this->saveFixtureRows('non_admin_access', 'user_company_access');
	}
	
	function tearDown() {
		
		parent::tearDown();
	}
	
	function testUserCanDeleteOwnActivity() {
		$this->setNonAdminLogin();
		$this->saveFixture('owned_activity', 'tactile_activities');

		$this->setUrl('activities/delete/100');
		$activity = DataObject::Construct('Activity');
		$activity = $activity->load(100);
		$this->assertIsA($activity, 'Tactile_Activity');
		
		$this->app->go();
		
		$this->assertFalse(Flash::Instance()->hasErrors());
		$this->assertEqual(count(Flash::Instance()->messages), 1);
		$activity = DataObject::Construct('Activity');
		$activity = $activity->load(100);
		$this->assertFalse($activity);
	}
	
	function testUserCantDeleteOthersActivity() {
		
		$this->setNonAdminLogin();
		$this->saveFixture('not_owned_activity', 'tactile_activities');

		$this->setUrl('activities/delete/200');
		$activity = DataObject::Construct('Activity');
		$activity = $activity->load(200);
		$this->assertIsA($activity, 'Tactile_Activity');
		
		$this->app->go();
		
		$this->assertTrue(Flash::Instance()->hasErrors());
		$this->assertEqual(count(Flash::Instance()->messages), 0);
		$activity = DataObject::Construct('Activity');
		$activity = $activity->load(200);
		$this->assertIsA($activity, 'Tactile_Activity');
	}
	
	function testAdminCanDeleteOthersActivity() {
		
		$this->setDefaultLogin();
		$this->saveFixture('not_admin_owned_activity', 'tactile_activities');

		$this->setUrl('activities/delete/300');
		/* @var $activity Tactile_Activity */
		$activity = DataObject::Construct('Activity');
		$activity = $activity->load(300);
		$this->assertIsA($activity, 'Tactile_Activity');
		
		$this->app->go();
		
		$this->assertFalse(Flash::Instance()->hasErrors());
		$this->assertEqual(count(Flash::Instance()->messages),1);
		$activity = DataObject::Construct('Activity');
		$activity = $activity->load(300);
		$this->assertFalse($activity);
	}
	
	function testUserCanDeleteOwnOpportunity() {
		
		$this->setNonAdminLogin();
		$this->saveFixture('owned_opportunity', 'opportunities');

		$this->setUrl('opportunities/delete/100');
		$opp = DataObject::Construct('Opportunity');
		$opp = $opp->load(100);
		$this->assertIsA($opp, 'Opportunity');
		
		$this->app->go();
		
		$this->assertFalse(Flash::Instance()->hasErrors());
		$this->assertEqual(count(Flash::Instance()->messages), 1);
		$opp = DataObject::Construct('Activity');
		$opp = $opp->load(100);
		$this->assertFalse($opp);
	}
	
	function testUserCantDeleteOthersOpportunity() {
		
		$this->setNonAdminLogin();
		$user = CurrentlyLoggedInUser::Instance();
		$this->assertFalse($user->isAdmin());		
		$this->saveFixture('not_owned_opportunity', 'opportunities');

		$this->setUrl('opportunities/delete/200');
		$opp = DataObject::Construct('Opportunity');
		$opp = $opp->load(200);
		$this->assertIsA($opp, 'Opportunity');
		
		$this->app->go();
		
		$this->assertTrue(Flash::Instance()->hasErrors());
		$this->assertEqual(count(Flash::Instance()->messages), 0);
		$opp = DataObject::Construct('Opportunity');
		$opp = $opp->load(200);
		$this->assertIsA($opp, 'Opportunity');
	}
	
	function testAdminCanDeleteOthersOpportunity() {
		
		$this->setDefaultLogin();
		$user = CurrentlyLoggedInUser::Instance();
		$this->saveFixture('not_admin_owned_opportunity', 'opportunities');

		$this->setUrl('opportunities/delete/300');
		/* @var $activity Tactile_Activity */
		$opp = DataObject::Construct('Opportunity');
		$opp = $opp->load(300);
		$this->assertIsA($opp, 'Opportunity');
		
		$this->app->go();
		
		$this->assertFalse(Flash::Instance()->hasErrors());
		$this->assertEqual(count(Flash::Instance()->messages),1);
		$opp = DataObject::Construct('Opportunity');
		$opp = $opp->load(300);
		$this->assertFalse($opp);
	}
	
	function setNonAdminLogin() {
		$this->_auth->getStorage()->write('nonadmin//tactile');
	}
	
}