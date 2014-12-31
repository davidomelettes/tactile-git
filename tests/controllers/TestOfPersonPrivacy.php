<?php

class TestOfPersonPrivacy extends ControllerTest {

	
	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$db = DB::Instance();
		
		$query = 'DELETE FROM notes';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM users WHERE person_id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM people WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());

		
		$this->loadFixtures('people');
		
		
	}
	
	function testPrivatePeopleAreShownInListForAdmins() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('private_company', 'organisations');
		$this->saveFixtureRows('private_people', 'people');
		
		$this->setURL('people/alphabetical');
		$this->app->go();
		
		$collection = $this->view->get('persons');
		$this->assertIsA($collection, 'Omelette_PersonCollection');
		
		$page_names = $collection->pluck('fullname');
		$expected_names = array('Aa Aa', 'Bb Bb', 'Cc Cc', 'Dd Dd', 'Greg Jones', 'Pp Pp', 'Fred Smith');
		
		$this->assertEqual($page_names, $expected_names);
	}
	
	function testPrivatePeopleAreNotShownInListForNonAdmins() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('private_company', 'organisations');
		$this->saveFixtureRows('private_people', 'people');
		
		// Replace admin checker with a foo that always returns false
		// (admins bypass permissions check)
		$this->injector->register('TestAdminChecker');
		
		$this->setURL('people/alphabetical');
		$this->app->go();
		
		$collection = $this->view->get('persons');
		$this->assertIsA($collection, 'Omelette_PersonCollection');
		
		$page_names = $collection->pluck('fullname');
		
		$expected_names = array('Aa Aa', 'Bb Bb', 'Dd Dd', 'Greg Jones', 'Pp Pp', 'Fred Smith');
		
		$this->assertEqual($page_names, $expected_names);
	}
	
	function testPrivatePeopleCanBeViewedByAdmins() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('private_company', 'organisations');
		$this->saveFixtureRows('private_people', 'people');

		// 610 does not belong to a company, is private, and belongs to alternative_user
		$this->setURL('people/view/610');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}
	
	function testPrivatePeopleCantBeViewedByNonAdmins() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('private_company', 'organisations');
		$this->saveFixtureRows('private_people', 'people');

		// Replace admin checker with a foo that always returns false
		// (admins bypass permissions check)
		$this->injector->register('TestAdminChecker');
		
		// 610 does not belong to a company, is private, and belongs to alternative_user
		$this->setURL('people/view/610');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		/* @var $r DummyRedirectHandler */
		$this->assertTrue($r->willRedirect());
	}
		
	function testPrivatePeopleCanBeEditedByAdmins() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('private_company', 'organisations');
		$this->saveFixtureRows('private_people', 'people');

		// 610 does not belong to a company, is private, and belongs to alternative_user
		$this->setURL('people/edit/610');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		/* @var $r DummyRedirectHandler */
		$this->assertFalse($r->willRedirect());
	}
	
	function testPrivatePeopleCantBeEditedByNonAdmins() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('private_company', 'organisations');
		$this->saveFixtureRows('private_people', 'people');
		
		// Replace admin checker with a foo that always returns false
		// (admins bypass permissions check)
		$this->injector->register('TestAdminChecker');

		// 610 does not belong to a company, is private, and belongs to alternative_user
		$this->setURL('people/edit/610');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		/* @var $r DummyRedirectHandler */
		$this->assertTrue($r->willRedirect());
	}
	
	function testOfPrivatePersonListByTagsWithAdmin() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('private_company', 'organisations');
		$this->saveFixtureRows('private_people', 'people');
		$this->saveFixtureRows('private_tags', 'tags');
		$this->saveFixtureRows('private_tag_map', 'tag_map');

		$this->setURL('people/by_tag/?tag=private');
		$this->app->go();
		
		$collection = $this->view->get('persons');
		$this->assertIsA($collection, 'TaggedItemCollection');
		
		$page_names = $collection->pluck('fullname');
		
		$expected_names = array('Cc Cc');
		
		$this->assertEqual($page_names, $expected_names);
	}	
	
	function testOfPrivatePersonListByTagsWithoutAdmin() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('private_company', 'organisations');
		$this->saveFixtureRows('private_people', 'people');
		$this->saveFixtureRows('private_tags', 'tags');
		$this->saveFixtureRows('private_tag_map', 'tag_map');

		// Replace admin checker with a foo that always returns false
		// (admins bypass permissions check)
		$this->injector->register('TestAdminChecker');
		
		$this->setURL('people/by_tag/?tag=private');
		$this->app->go();
		
		$collection = $this->view->get('persons');
		$this->assertIsA($collection, 'TaggedItemCollection');
		
		$page_names = $collection->pluck('fullname');
		
		// Expect an empty list
		$expected_names = array();
		
		$this->assertEqual($page_names, $expected_names);
	}
	
	function testPrivatePeopleCantBeDeletedByNonAdmins() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('private_company', 'organisations');
		$this->saveFixtureRows('private_people', 'people');
		
		// Replace admin checker with a foo that always returns false
		// (admins bypass permissions check)
		$this->injector->register('TestAdminChecker');

		// 610 does not belong to a company, is private, and belongs to alternative_user
		$this->setURL('people/delete/610');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
	}
	
	function testPrivatePeopleCanBeDeletedByAdmins() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('private_company', 'organisations');
		$this->saveFixtureRows('private_people', 'people');

		// 610 does not belong to a company, is private, and belongs to alternative_user
		$this->setURL('people/delete/610');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}
	
	
}
