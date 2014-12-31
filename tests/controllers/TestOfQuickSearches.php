<?php
class TestOfQuickSearches extends ControllerTest {

	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$this->loadFixtures('quick_search');
	}
	
	function testOrganisationQuickSearch() {
		$this->setAjaxRequest();
		$this->setUrl('organisations/filtered_list');
		$_POST['name'] = 'Def';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('/<li/', $this->view->output);
		
		$items = $this->view->get('items');
		$this->assertEqual(count($items), 1);
	}
	
	function testOrganisationQuickSearchWithNoMatches() {
		$this->setAjaxRequest();
		$this->setUrl('organisations/filtered_list');
		$_POST['name'] = 'Boo';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		//$this->assertPattern('/^\s*$/', $this->view->output);
		
		$items = $this->view->get('items');
		$this->assertEqual(count($items), 0);
	}
	
	function testResultBeingRecentRemovesItFromTheRest() {
		$this->saveFixtureRows('default', 'recently_viewed');
		$this->setAjaxRequest();
		$this->setUrl('organisations/filtered_list');
		$_POST['name'] = 'Def';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('/<li/', $this->view->output);
		
		$items = $this->view->get('items');
		$this->assertEqual(count($items), 0);
		
		$recent = $this->view->get('recent');
		$this->assertEqual(count($recent), 1);
	}
	
	function testWithRecentlyViewedAlongsideNonViewed() {
		$this->saveFixtureRows('default', 'recently_viewed');
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->setAjaxRequest();
		$this->setUrl('organisations/filtered_list');
		$_POST['name'] = 'Def';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('/<li/', $this->view->output);
		
		$items = $this->view->get('items');
		$this->assertEqual(count($items), 1);
		
		$recent = $this->view->get('recent');
		$this->assertEqual(count($recent), 1);
	}
	
	function testPeopleQuickSearch() {
		$this->setAjaxRequest();
		$this->setUrl('people/filtered_list');
		$_POST['name'] = 'Gre';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('/<li/', $this->view->output);
		
		$items = $this->view->get('items');
		$items->rewind();
		$this->assertEqual('Greg', $items->current()->firstname);
		$this->assertEqual(count($items), 1);
	}
	
	function testPersonQuickSearchWithNoMatches() {
		$this->setAjaxRequest();
		$this->setUrl('people/filtered_list');
		$_POST['name'] = 'Boo';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		//$this->assertPattern('/^\s*$/', $this->view->output);
		
		$items = $this->view->get('items');
		$this->assertEqual(count($items), 0);
	}
	
	function testPersonBeingRecentRemovesItFromTheRest() {
		$this->saveFixtureRows('default', 'recently_viewed');
		$this->setAjaxRequest();
		$this->setUrl('people/filtered_list');
		$_POST['name'] = 'Gre';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('/<li/', $this->view->output);
		
		$items = $this->view->get('items');
		$this->assertEqual(count($items), 0);
		
		$recent = $this->view->get('recent');
		$this->assertEqual(count($recent), 1);
	}
	
	function testWithRecentlyViewedPersonAlongsideNonViewed() {
		$this->saveFixtureRows('default', 'recently_viewed');
		$this->saveFixtureRows('person_defaults', 'people');
		$this->setAjaxRequest();
		$this->setUrl('people/filtered_list');
		$_POST['name'] = 'Gre';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('/<li/', $this->view->output);
		
		$items = $this->view->get('items');
		$this->assertEqual(count($items), 1);
		$items->rewind();
		$this->assertEqual($items->current()->firstname, 'Greta');
		
		$recent = $this->view->get('recent');
		$this->assertEqual(count($recent), 1);
		$recent->rewind();
		$this->assertEqual($recent->current()->firstname, 'Greg');
	}
	
	function testActivityQuickSearch() {
		$this->saveFixtureRows('default_activities', 'tactile_activities');
		$this->setAjaxRequest();
		$this->setUrl('activities/filtered_list');
		$_POST['name'] = 'Tes';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('/<li/', $this->view->output);
		
		$items = $this->view->get('items');
		$items->rewind();
		$this->assertEqual('Test Activity', $items->current()->name);
		$this->assertEqual(count($items), 1);
	}
	
	function testActivityQuickSearchWithNoMatches() {
		$this->setAjaxRequest();
		$this->setUrl('activities/filtered_list');
		$_POST['name'] = 'Tes';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		//$this->assertPattern('/^\s*$/', $this->view->output);
		
		$items = $this->view->get('items');
		$items->rewind();
		$this->assertEqual(count($items), 0);
	}
	
	function testOpportunityQuickSearch() {
		$this->saveFixtureRows('default_opportunities', 'opportunities');
		$this->setAjaxRequest();
		$this->setUrl('opportunities/filtered_list');
		$_POST['name'] = 'Tes';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('/<li/', $this->view->output);
		
		$items = $this->view->get('items');
		$items->rewind();
		$this->assertEqual('Test Opportunity', $items->current()->name);
		$this->assertEqual(count($items), 1);
	}
	
	function testOpportunityQuickSearchWithNoMatches() {
		$this->setAjaxRequest();
		$this->setUrl('opportunities/filtered_list');
		$_POST['name'] = 'Tes';
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		//$this->assertPattern('/^\s*$/', $this->view->output);
		
		$items = $this->view->get('items');
		$items->rewind();
		$this->assertEqual(count($items), 0);
	}
	
	function teardown() {
		$db = DB::Instance();
		$query = 'DELETE FROM recently_viewed';
		$db->Execute($query) or die($db->ErrorMsg());
		parent::teardown();
	}
	
}
