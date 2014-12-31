<?php

class TestOfCrossAccountAttaching extends ControllerTest {
	
	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$db = DB::Instance();
		
		$query = 'DELETE FROM user_company_access WHERE username IN (SELECT username FROM users WHERE person_id > 1)';
		$db->Execute($query) or die(__LINE__.$db->ErrorMsg());
		
		$this->loadFixtures('clients');
		$this->loadFixtures('attaching');
		$this->saveMultiFixture('setup', 2);
		
		$query = 'UPDATE people SET usercompanyid=2, owner=\'other_user//othersite\' WHERE id=2';
		$db->Execute($query) or die($db->ErrorMsg());		
	}
	
	function testAssigningOpportunityToAUserFromADifferentAccount() {
		$this->setURL('opportunities/save');
		
		$_POST = $this->getFixture('opp_to_different_account');
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();		

	}
	
	function testAssigningActivityToAUserFromADifferentAccount() {
		$this->setURL('activities/save');
		
		$_POST = $this->getFixture('act_to_different_account');
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();		

	}
	
	function testAssigningCompanyToAUserFromADifferentAccount() {
		$this->setURL('organisations/save');
		
		$_POST = $this->getFixture('company_to_different_account');
		$this->app->go();
		$this->checkUnsuccessfulSave();		

	}
	
	function testAssigningPersonToAUserFromADifferentAccount() {
		$this->setURL('people/save');
		
		$_POST = $this->getFixture('person_to_different_account');
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();		

	}
	
	function testAttachingOppToCompanyFromDifferentAccount() {
		$this->setURL('opportunities/save');
		
		$_POST = $this->getFixture('opp_to_different_company');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();	
	}
	
	function testAttachingActivityToCompanyFromDifferentAccount() {
		$this->setURL('activities/save');
		
		$_POST = $this->getFixture('act_to_different_company');
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();	
	}
	
	function testAttachingPersonToCompanyFromDifferentAccount() {
		$this->setURL('people/save');
		
		$_POST = $this->getFixture('person_to_different_company');
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();	
	}
	
	function testAttachingOpportunityToPersonFromDifferentAccount() {
		$this->setURL('opportunities/save');
		
		$_POST = $this->getFixture('opp_to_different_person');
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();	
	}
	
	function testAttachingActivityToPersonFromDifferentAccount() {
		$this->setURL('activities/save');
		
		$_POST = $this->getFixture('act_to_different_person');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();	
	}
	
	function testAttachingActivityToOpportunityFromDifferentAccount() {
		$this->setURL('activities/save');
		
		$this->saveFixtureRows('default_opportunity', 'opportunities');
		
		$_POST = $this->getFixture('act_to_different_opportunity');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();	
	}
}

?>
