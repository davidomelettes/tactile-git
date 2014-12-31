<?php

class TestOfMassActions extends ControllerTest {

	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$db = DB::Instance();
		$this->loadFixtures('mass_actions');
	}
	
	function teardown() {
		$db = DB::Instance();
		
		$query = 'DELETE FROM organisation_roles WHERE organisation_id > 1';
		$db->Execute($query) or die($db->ErrorMsg().$query);

		$query = 'DELETE FROM roles WHERE id > 9';
                $db->Execute($query) or die($db->ErrorMsg().$query);
		
		parent::tearDown();
	}
	
	function testMergingOrganisationsByNonAdmin() {
		$db = DB::Instance();
		
		$this->saveFixtureRows('user_people', 'people');
		$this->saveFixtureRows('users', 'users');
		$this->saveFixtureRows('user_company_access', 'user_company_access');
		$this->saveFixtureRows('roles', 'roles');
		$this->saveFixtureRows('organisations', 'organisations');
		$this->saveFixtureRows('organisation_roles', 'organisation_roles');
		$this->saveFixtureRows('notes', 'notes');
		$this->saveFixtureRows('emails', 'emails');
		$this->saveFixtureRows('people', 'people');
		$this->saveFixtureRows('organisation_contact_methods', 'organisation_contact_methods');
		$this->saveFixtureRows('opportunities', 'opportunities');
		$this->saveFixtureRows('activities', 'tactile_activities');
		
		$this->setDefaultLogin('archie//tactile');
		
		$_POST = array(
			'mass_action'	=> 'merge',
			'master_id'		=> 100,
			'ids'			=> array('100', '200', '300')
		);
		$this->setURL('/organisations/mass_action');
		$this->app->go();
		
		$f = Flash::Instance();
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($f->hasErrors());
		$this->assertTrue($r->willRedirect());
		
		$org_ids = $db->getCol("SELECT organisation_id FROM notes order by title");
		$this->assertEqual($org_ids, array(100, 100, 100, 400));
		
		$org_ids = $db->getCol("SELECT organisation_id FROM emails order by subject");
		$this->assertEqual($org_ids, array(100));
		
		$org_ids = $db->getCol("SELECT organisation_id FROM people where id > 10 order by surname");
		$this->assertEqual($org_ids, array(100));
		
		$org_ids = $db->getCol("SELECT organisation_id FROM organisation_contact_methods order by contact");
		$this->assertEqual($org_ids, array(100, 100));
		
		$org_ids = $db->getCol("SELECT organisation_id FROM opportunities order by name");
		$this->assertEqual($org_ids, array(100));
		
		$org_ids = $db->getCol("SELECT organisation_id FROM tactile_activities order by name");
		$this->assertEqual($org_ids, array(100));
	}
	
	function testMergingOrganisationsByNonAdminIncludingOrgThatCannotBeMerged() {
		$db = DB::Instance();
		
		$this->saveFixtureRows('user_people', 'people');
		$this->saveFixtureRows('users', 'users');
		$this->saveFixtureRows('user_company_access', 'user_company_access');
		$this->saveFixtureRows('roles', 'roles');
		$this->saveFixtureRows('organisations', 'organisations');
		$this->saveFixtureRows('organisation_roles', 'organisation_roles');
		$this->saveFixtureRows('notes', 'notes');
		
		$this->setDefaultLogin('archie//tactile');
		
		$_POST = array(
			'mass_action'	=> 'merge',
			'master_id'		=> 100,
			'ids'			=> array('100', '200', '300', '400')
		);
		$this->setURL('/organisations/mass_action');
		$this->app->go();
		
		$f = Flash::Instance();
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($f->hasErrors());
		$this->assertTrue($r->willRedirect());
		
		$note_org_ids = $db->getCol("SELECT organisation_id FROM notes order by title");
		$this->assertEqual($note_org_ids, array(100, 200, 300, 400));
	}
	
}
