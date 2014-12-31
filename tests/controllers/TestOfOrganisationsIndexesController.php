<?php

class TestOfOrganisationsIndexesController extends ControllerTest {

	function setup() {
		parent::setup();
		
		$this->setDefaultLogin();
		$db = DB::Instance();
		$query = 'DELETE FROM organisations WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		$query = 'DELETE FROM users WHERE person_id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		$query = 'DELETE FROM people WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());		
		$this->loadFixtures('clients');
	}

	function testIndexAlphabetical() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_clients', 'organisations');

		$this->setURL('organisations');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$collection = $this->view->get('organisations');
		/* @var $collection Omelette_OrganisationCollection */
		$this->assertIsA($collection, 'Omelette_OrganisationCollection');
		
		$page_names = $collection->pluck('name');
		
		$expected_names = array('Alpha Company', 'Bravo Company', 'Charlie Company', 'Default Company', 'Echo Company');
		
		$this->assertEqual($page_names, $expected_names);
	}
	
	function testIndexRecentlyAdded() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		
		$this->setURL('organisations/recent/');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$collection = $this->view->get('organisations');
		/* @var $collection Omelette_OrganisationCollection */
		$this->assertIsA($collection, 'Omelette_OrganisationCollection');
		
		$page_names = $collection->pluck('name');
		
		$expected_names = array('Bravo Company', 'Charlie Company', 'Echo Company','Alpha Company',  'Default Company');
		
		$this->assertEqual($page_names, $expected_names);

		//BCEAD
	}
	
	function testIndexAssignedToMe() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		
		$this->saveFixtureRows('index_test_clients', 'organisations');
		
		$this->setURL('organisations/mine/');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$collection = $this->view->get('organisations');
		/* @var $collection Omelette_OrganisationCollection */
		$this->assertIsA($collection, 'Omelette_OrganisationCollection');
		
		$page_names = $collection->pluck('name');
		
		$expected_names = array('Alpha Company','Charlie Company', 'Default Company');
		
		$this->assertEqual($page_names, $expected_names);
	}
	
	function testIndexRecentlyViewed() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		
		$this->saveFixtureRows('index_test_clients', 'organisations');
		$this->saveFixtureRows('client_views', 'recently_viewed');
		$this->saveFixtureRows('client_index_access', 'organisation_roles');
		
		$this->setURL('organisations/recently_viewed');

		$this->app->go();

		$this->genericPageTest();
		
		$collection = $this->view->get('organisations');
		/* @var $collection ViewedItemCollection */
		$this->assertIsA($collection, 'ViewedItemCollection');
		
		$page_names = $collection->pluck('name');
		
		$expected_names = array('Default Company', 'Bravo Company','Charlie Company','Alpha Company' );
		
		$this->assertEqual($page_names, $expected_names);
	}
	
	function testFilterByTown() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		
		$this->saveFixtureRows('test_client_addresses', 'organisation_addresses');
		
		$this->setURL('organisations/by_town/?q=Testtown');
		$this->app->go();
		$this->genericPageTest();
		
		$collection = $this->view->get('organisations');
		/* @var $collection Omelette_OrganisationCollection */
		$this->assertIsA($collection, 'Omelette_OrganisationCollection');
		
		$page_names = $collection->pluck('name');
		
		$expected_names = array('Alpha Company', 'Echo Company');
		
		$this->assertEqual($page_names, $expected_names);
	}

	function testFilterByTownSameUserCompany() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		
		$this->saveFixtureRows('test_client_addresses', 'organisation_addresses');

		// XX Company is in the same town as Alpha and Echo Company
		$this->saveFixtureRows('cross_company', 'organisations');
		$this->saveFixtureRows('cross_company_clients', 'organisations');
		$this->saveFixtureRows('cross_company_client_addresses', 'organisation_addresses');
		
		$this->setURL('organisations/by_town/?q=Testtown');
		$this->app->go();
		
		$collection = $this->view->get('organisations');
		/* @var $collection Omelette_OrganisationCollection */
		$this->assertIsA($collection, 'Omelette_OrganisationCollection');
		
		$page_names = $collection->pluck('name');
		
		// XX Company belongs to another usercompany
		$expected_names = array('Alpha Company', 'Echo Company');
		
		$this->assertEqual($page_names, $expected_names);
	}
	
	function testFilterByTownCaseSensitive() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		
		$this->saveFixtureRows('test_client_addresses', 'organisation_addresses');
		
		$this->setURL('organisations/by_town/?q=testtown');
		$this->app->go();
		$this->genericPageTest();
		
		$collection = $this->view->get('organisations');
		/* @var $collection Omelette_OrganisationCollection */
		$this->assertIsA($collection, 'Omelette_OrganisationCollection');
		
		$page_names = $collection->pluck('name');
		
		$expected_names = array('Alpha Company', 'Echo Company');
		
		$this->assertEqual($page_names, $expected_names);
	}
	
	function testFilterByTownNonTown() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		
		$this->saveFixtureRows('test_client_addresses', 'organisation_addresses');
		
		$this->setURL('organisations/by_town/?q=X');
		$this->app->go();
		$this->genericPageTest();
		
		$collection = $this->view->get('organisations');
		/* @var $collection Omelette_OrganisationCollection */
		$this->assertIsA($collection, 'Omelette_OrganisationCollection');
		
		$this->assertEqual(count($collection), 0);
	}
	
	function testFilterByTownEmptyQuery() {
		$this->setURL('organisations/by_town/?q=');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		
		/* @var $r DummyRedirectHandler */
		$this->assertTrue($r->willRedirect());
	}
	
	function testFilterByCounty() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		
		$this->saveFixtureRows('test_client_addresses', 'organisation_addresses');
		
		$this->setURL('organisations/by_county/?q=Testshire');
		$this->app->go();
		$this->genericPageTest();
		
		$collection = $this->view->get('organisations');
		/* @var $collection Omelette_OrganisationCollection */
		$this->assertIsA($collection, 'Omelette_OrganisationCollection');
		
		$page_names = $collection->pluck('name');
		
		$expected_names = array('Alpha Company', 'Charlie Company', 'Echo Company');
		
		$this->assertEqual($page_names, $expected_names);
	}
	
	function testFilterByCountyNonCounty() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		
		$this->saveFixtureRows('test_client_addresses', 'organisation_addresses');
		
		$this->setURL('organisations/by_county/?q=X');
		$this->app->go();
		$this->genericPageTest();
		
		$collection = $this->view->get('organisations');
		/* @var $collection Omelette_OrganisationCollection */
		$this->assertIsA($collection, 'Omelette_OrganisationCollection');
		
		$this->assertEqual(count($collection), 0);
	}
	
	function testFilterByCountyEmptyQuery() {
		$this->setURL('organisations/by_county/?q=');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		
		/* @var $r DummyRedirectHandler */
		$this->assertTrue($r->willRedirect());
	}
	
	function testTagPaging() {
		
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		$this->saveFixtureRows('client_index_access', 'organisation_roles');
		
		
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		
		$this->setURL('organisations/by_tag/?tag[]=Foo');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$this->assertFalse(Flash::Instance()->hasErrors());
		$collection = $this->view->get('organisations');
		$this->assertIsA($collection, 'TaggedItemCollection');
		
		$actual = $collection->pluck('name');
		$expected = array('Alpha Company', 'Bravo Company', 'Charlie Company', 'Echo Company');
		$this->assertEqual($actual, $expected);
		
		$this->assertEqual($this->view->get('cur_page'), 1);
		$this->assertEqual($this->view->get('num_pages'), 1);
	}
	
	function testCombinationTagPaging() {
		
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		$this->saveFixtureRows('client_index_access', 'organisation_roles');
		
		
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		
		$this->setURL('organisations/by_tag/?tag[]=Foo&tag[]=Bar');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$this->assertFalse(Flash::Instance()->hasErrors());
		$collection = $this->view->get('organisations');
		$this->assertIsA($collection, 'TaggedItemCollection');
		
		$actual = $collection->pluck('name');
		$expected = array('Charlie Company', 'Echo Company');
		$this->assertEqual($actual, $expected);
		
		$this->assertEqual($this->view->get('cur_page'), 1);
		$this->assertEqual($this->view->get('num_pages'), 1);
	}
	
	function testCombinationTagPagingWhenOneTagHasMoreClientsThanWillFitOnOnePage() {
		
		SearchHandler::$perpage_default = 5;
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		$this->saveFixtureRows('client_index_access', 'organisation_roles');
		
		
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		
		$this->saveFixtureRows('more_clients', 'organisations');
		$this->saveFixtureRows('more_client_access', 'organisation_roles');
		$this->saveFixtureRows('more_client_tag_maps', 'tag_map');
		
		$this->setURL('organisations/by_tag/?tag[]=Foo&tag[]=Bar');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$this->assertFalse(Flash::Instance()->hasErrors());
		$collection = $this->view->get('organisations');
		$this->assertIsA($collection, 'TaggedItemCollection');
		
		$actual = $collection->pluck('name');
		
		$expected = array('Charlie Company', 'Echo Company');
		$this->assertEqual($actual, $expected);
		
		$this->assertEqual($this->view->get('cur_page'), 1);
		$this->assertEqual($this->view->get('num_pages'), 1);
	}
		
	function testTagListOnIndex() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('alternative_user_company_access', 'user_company_access');
		$this->saveFixtureRows('alternative_user_role', 'roles');
		$this->saveFixtureRows('alternative_user_hasrole', 'hasrole');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		$this->saveFixtureRows('client_index_access', 'organisation_roles');
		$this->saveFixtureRows('alternative_user_organisation_access', 'organisation_roles');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		
		$this->saveFixtureRows('orgs_only_visible_by_greg', 'organisations');
		$this->saveFixtureRows('org_roles_only_visible_by_greg', 'organisation_roles');
		$this->saveFixtureRows('tags_only_visible_by_greg', 'tags');
		$this->saveFixtureRows('tag_map_only_visible_by_greg', 'tag_map');
		
		$this->setDefaultLogin();
		$this->setURL('organisations/alphabetical');
		$this->app->go();
		
		$names = $this->view->get('organisations')->pluck('name');
		$this->assertEqual(array('Alpha Company', 'Bravo Company', 'Charlie Company', 'Default Company', 'Echo Company', 'Lucky Seven'), $names);
		$this->assertEqual(array('Bar', 'Baz', 'Foo', 'Super Secret'), $this->view->get('all_tags'));
	}
	
	function testTagListShowsCorrectTagsForNonAdmins() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('alternative_user_company_access', 'user_company_access');
		$this->saveFixtureRows('alternative_user_role', 'roles');
		$this->saveFixtureRows('alternative_user_hasrole', 'hasrole');
		$this->saveFixtureRows('index_test_clients', 'organisations');
		$this->saveFixtureRows('client_index_access', 'organisation_roles');
		$this->saveFixtureRows('alternative_user_organisation_access', 'organisation_roles');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		
		$this->saveFixtureRows('orgs_only_visible_by_greg', 'organisations');
		$this->saveFixtureRows('org_roles_only_visible_by_greg', 'organisation_roles');
		$this->saveFixtureRows('tags_only_visible_by_greg', 'tags');
		$this->saveFixtureRows('tag_map_only_visible_by_greg', 'tag_map');
		
		$this->setDefaultLogin('user2//tactile');
		$this->setURL('organisations/alphabetical');
		$this->app->go();
		
		$names = $this->view->get('organisations')->pluck('name');
		$this->assertEqual(array('Alpha Company', 'Bravo Company', 'Echo Company'), $names);
		$this->assertEqual(array('Bar', 'Foo'), $this->view->get('all_tags'));
	}
		
}
