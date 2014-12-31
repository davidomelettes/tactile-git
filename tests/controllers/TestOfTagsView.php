<?php

class TestOfTagsView extends ControllerTest {

	function setup() {
		parent::setup();
		
		$this->setDefaultLogin();
		EGS::setCompanyId(1);
		EGS::setUsername('greg//tactile');
		
		$this->loadFixtures('tagging');
	}
	
	function teardown() {
		$db = DB::Instance();
		$query = 'DELETE FROM tags';
		$db->Execute($query) or die($db->ErrorMsg());
		parent::teardown();
	}
	
	function testTagIndex() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setURL('tags/');
		$this->app->go();
		
		$this->genericPageTest();
		$this->assertEqual($this->app->getControllerName(), 'tags');
		$this->assertEqual($this->view->get('templateName'), $this->makeTemplatePath('tactile/tags/index'));
		
		$tags = $this->view->get('tags');
		$this->assertFalse($tags == false);
		$this->assertTrue(in_array('bar', array_keys($tags)));
		$this->assertFalse(in_array('xyzzy', array_keys($tags)));
	}
	
	function testByTag() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setURL('tags/by_tag?tag=bar');
		$this->app->go();
		
		$this->genericPageTest();
		$types = $this->view->get('types_with_results');
		$this->assertEqual(array('organisations'), $types);
		
		$items = $this->view->get('items');
		$orgs = $items['organisations'];
		$this->assertIsA($orgs, 'TaggedItemCollection');
		
		$org_names = $orgs->pluck('name');
		$this->assertEqual(array('Default Company'), $org_names);
	}
	
	function testByTagBadTag() {
		// Tag is garbage
		// Show no items here, or flash and redirect?
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setURL('tags/by_tag?tag=xyzzy');
		$this->app->go();
		
		$this->assertPattern('/No items at present/', $this->view->output);
		$types = $this->view->get('types_with_results');
		$this->assertEqual(array(), $types);
	}
	
	function testByTagMultipleItemTypes() {
		// Results contain multiple item types
		$this->saveFixtureRows('default_activities', 'tactile_activities');
		$this->saveFixtureRows('default_opportunities', 'opportunities');
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('extra_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		$this->saveFixtureRows('multiple_types_tag_map', 'tag_map');
		
		$this->setURL('tags/by_tag?tag=bar');
		$this->app->go();
		
		$this->genericPageTest();
		
		$types = $this->view->get('types_with_results');
		$this->assertEqual(array('organisations', 'people', 'opportunities', 'activities'), $types);
		
		$items = $this->view->get('items');
		$orgs = $items['organisations'];
		$people = $items['people'];
		$opps = $items['opportunities'];
		$acts = $items['activities'];
		
		$this->assertIsA($orgs, 'TaggedItemCollection');
		$this->assertIsA($people, 'TaggedItemCollection');
		$this->assertIsA($opps, 'TaggedItemCollection');
		$this->assertIsA($acts, 'TaggedItemCollection');
		
		$org_names = $orgs->pluck('name');
		$people_names = $people->pluck('name');
		$opp_names = $opps->pluck('name');
		$act_names = $acts->pluck('name');
		$this->assertEqual(array('Default Company'), $org_names);
		$this->assertEqual(array('Greg Jones'), $people_names);
		$this->assertEqual(array('Test Opportunity'), $opp_names);
		$this->assertEqual(array('Default Activity'), $act_names);
	}
	
	function testByTagMultipleTags() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('extra_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		$this->saveFixtureRows('multiple_tags_tag_map', 'tag_map');
		
		$this->setURL('tags/by_tag?tag[]=bar&tag[]=foo');
		$this->app->go();
		
		$types = $this->view->get('types_with_results');
		$this->assertEqual(array('organisations'), $types);
		
		$items = $this->view->get('items');
		$orgs = $items['organisations'];
		$this->assertIsA($orgs, 'TaggedItemCollection');
		
		$org_names = $orgs->pluck('name');
		$this->assertEqual(array('Default Company'), $org_names);
		
		$filter_by = $this->view->get('filter_by');
		$this->assertFalse($filter_by == false);
		$this->assertEqual(array(array('name'=>'bar', 'count'=>1), array('name'=>'foo', 'count'=>1)), $filter_by);
	}
	
	function testByTagMultipleTagsMatchAndNonMatch() {
		// All these tags exist, and some match, but the whole set matches nothing
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('extra_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		$this->saveFixtureRows('multiple_tags_tag_map', 'tag_map');
		
		$this->setURL('tags/by_tag?tag[]=bar&tag[]=foo&tag[]=baz');
		$this->app->go();
		
		$this->assertPattern('/No items at present/', $this->view->output);
		$types = $this->view->get('types_with_results');
		$this->assertEqual(array(), $types);
	}
	
	function testByTagMultipleTagsGoodAndBadTags() {
		// One tag exists, the other is garbage
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('extra_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		$this->saveFixtureRows('multiple_tags_tag_map', 'tag_map');
		
		$this->setURL('tags/by_tag?tag[]=bar&tag[]=xyzzy');
		$this->app->go();
		
		$this->assertPattern('/No items at present/', $this->view->output);
		$types = $this->view->get('types_with_results');
		$this->assertEqual(array(), $types);
	}
	
	function testByTagNoPermissionToSeeItems() {
		// The company matching this tag is marked as read: false for the default user role
		/**
		 * This test is now obsolete (by the permissions test suite)
		 * Admins can see everything now anyway
		 * @de
		 */
		/*$this->saveFixtureRows('private_person', 'people');
		$this->saveFixtureRows('private_user', 'users');
		$this->saveFixtureRows('private_company', 'organisations');
		$this->saveFixtureRows('private_companyroles', 'organisation_roles');
		$this->saveFixtureRows('private_tags', 'tags');
		$this->saveFixtureRows('private_tag_map', 'tag_map');
		
		$this->setURL('tags/by_tag?tag[]=private');
		$this->app->go();
		
		$this->assertPattern('/No items at present/', $this->view->output);
		$types = $this->view->get('types_with_results');
		$this->assertEqual(array(), $types);*/
	}
	
	function testByTagCrossCompany() {
		// Load two companies, with overlapping tags
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		$this->saveFixtureRows('other_company', 'organisations');
		$this->saveFixtureRows('other_company_tags', 'tags');
		$this->saveFixtureRows('other_company_tag_map', 'tag_map');
		
		$this->setURL('tags/by_tag?tag[]=bar');
		$this->app->go();
		
		$types = $this->view->get('types_with_results');
		$this->assertEqual(array('organisations'), $types);
		
		$items = $this->view->get('items');
		$orgs = $items['organisations'];
		$this->assertIsA($orgs, 'TaggedItemCollection');
		
		$org_names = $orgs->pluck('name');
		$this->assertEqual(array('Default Company'), $org_names);
	}
	
	function testRenameTag() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setURL('tags/rename?old_tag=bar');
		$this->app->go();
		
		$this->genericPageTest();
		$this->assertPattern('/Do you want to rename/', $this->view->output);
	}
	
	function testRenameTagBadTag() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setURL('tags/rename?old_tag=xyzzy');
		$this->app->go();
		
		$f = Flash::Instance();
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($f->hasErrors());
		$this->assertTrue($r->willRedirect());
	}
	
	function testDeleteByTag() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		$this->saveFixtureRows('default_activities', 'tactile_activities');
		$this->saveFixtureRows('extra_tags', 'tags');
		$this->saveFixtureRows('extra_tag_map', 'tag_map');
		
		$this->setURL('tags/delete_items?tag[]=foo&for=activities');
		$this->app->go();
		
		$this->genericPageTest();
		
		$tags = $this->view->get('selected_tags');
		$this->assertEqual(array('foo'), $tags);
		
		$for = $this->view->get('for');
		$this->assertEqual('activities', $for);
		
		$count = $this->view->get('count');
		$this->assertEqual(1, $count);
	}
	
	function testDeleteByTagBadTag() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setURL('tags/delete_items?tag[]=xyzzy&for=people');
		$this->app->go();
		
		$count = $this->view->get('count');
		$this->assertEqual(0, $count);
	}
	
	function testDeleteByTagIncludingAccount() {
		// Try to delete the account by tag
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setURL('tags/delete_items?tag[]=bar&for=organisations');
		$this->app->go();
		
		$tags = $this->view->get('selected_tags');
		$this->assertEqual(array('bar'), $tags);
		
		$for = $this->view->get('for');
		$this->assertEqual('organisations', $for);
		
		$count = $this->view->get('count');
		$this->assertEqual(1, $count);
		
		// Predict cascade results
		$wad_compare = array(
			'people' => array('count'=>1)
		);
		$wad = $this->view->get('will_also_delete');
		$this->assertFalse($wad == false);
		$this->assertEqual($wad_compare, $wad);
		
		$account_orgs = $this->view->get('account_orgs');
		$this->assertFalse($account_orgs == false);
		$this->assertEqual(array('T'=>'Default Company'), $account_orgs);
		
		$user_people = $this->view->get('user_people');
		$this->assertFalse($user_people == false);
		$this->assertEqual(array('Greg Jones'=>'Greg Jones'), $user_people);
	}
	
}
