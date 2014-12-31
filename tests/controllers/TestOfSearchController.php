<?php

class TestOfSearchController extends ControllerTest {

	function setup() {
		parent::setUp();
		$this->setDefaultLogin();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		
		$this->loadFixtures('search');
		$this->saveFixtureRows('search_organisations', 'organisations');
		$this->saveFixtureRows('search_people', 'people');
		$this->saveFixtureRows('search_opportunities', 'opportunities');
		$this->saveFixtureRows('search_activity_types', 'activitytype');
		$this->saveFixtureRows('search_activities', 'tactile_activities');
		$this->saveFixtureRows('search_custom_fields', 'custom_fields');
		$this->saveFixtureRows('search_custom_field_options', 'custom_field_options');
		$this->saveFixtureRows('search_custom_field_map', 'custom_field_map');
		$this->saveFixtureRows('search_advanced_searches', 'advanced_searches');
	}
	
	function teardown() {
		parent::tearDown();
		$db = DB::Instance();
		
		$queries = array(
			'DELETE FROM organisations WHERE id > 1',
			'DELETE FROM people WHERE id > 1',
			'DELETE FROM opportunities',
			'DELETE FROM tactile_activities',
			'DELETE FROM custom_fields',
			'DELETE FROM activitytype',
			'DELETE FROM advanced_searches',
		);
		
		foreach ($queries as $query) {
			$db->Execute($query) or die($db->ErrorMsg());
		}
	}
	
	function testAdvancedSearchPageLoads() {
		$this->setUrl('/search/advanced');
		$this->app->go();
		$this->genericPageTest();
		$qb_fields = $this->view->get('qb_fields');
		$this->assertEqual($qb_fields['gen_name'], array('label'=>'Name','operators'=>array('IS','IS NOT','CONTAINS','DOES NOT CONTAIN', 'BEGINS WITH'),'type'=>'db_column','column'=>'name','accept'=>'text'));
		$this->assertTrue(!empty($qb_fields['gen_description']));
		$this->assertTrue(!empty($qb_fields['org_status']));
		$this->assertTrue(!empty($qb_fields['per_can_email']));
		$this->assertTrue(!empty($qb_fields['opp_status']));
		$this->assertTrue(!empty($qb_fields['act_type']));
		$this->assertTrue(!empty($qb_fields['per_900']));
	}
	
	function testOrgSearchByName() {
		$this->setUrl('/search/advanced?r=org&q[gen_name][op]=CONTAINS&q[gen_name][value]=crm');
		$this->app->go();
		$this->assertPattern('/id="qb_gen_name"/', $this->view->output);
		$collection = $this->view->get('collection');
		$this->assertIsA($collection, 'Omelette_OrganisationCollection');
		$names = $collection->pluck('name');
		$this->assertEqual($names, array('Tactile CRM', 'Zanzibar CRM'));
	}
	
	// test search organisations by DOES NOT CONTAIN
	function testOrgSearchByNameDoesNotContain() {
		$this->setUrl('/search/advanced?r=org&q[gen_name][op]=DOES+NOT+CONTAIN&q[gen_name][value]=crm');
		$this->app->go();
		$this->assertPattern('/id="qb_gen_name"/', $this->view->output);
		$collection = $this->view->get('collection');
		$this->assertIsA($collection, 'Omelette_OrganisationCollection');
		$names = $collection->pluck('name');
		$this->assertEqual($names, array('Default Company', 'Tictacle DRM', 'XXX Co'));
	}
	
	// test search opportunities by GREATER THAN
	function testOppSearchByCostGreaterThan() {
		$this->setUrl('/search/advanced?r=opp&q[opp_cost][op]=GREATER+THAN&q[opp_cost][value]=500');
		$this->app->go();
		$this->assertPattern('/id="qb_opp_cost"/', $this->view->output);
		$collection = $this->view->get('collection');
		$this->assertIsA($collection, 'Tactile_OpportunityCollection');
		$names = $collection->pluck('name');
		$this->assertEqual($names, array('Build a website'));
	}
	
	// test search people by boolean
	function testPersonSearchByBoolean() {
		$this->setUrl('/search/advanced?r=per&q[per_can_call][value]=FALSE');
		$this->app->go();
		$this->assertPattern('/id="qb_per_can_call"/', $this->view->output);
		$this->assertPattern('/<th>Can Call<\/th>/', $this->view->output);
		$this->assertPattern('/<td><img src="\/graphics\/tactile\/false.png" alt="f" \/><\/td>/', $this->view->output);
		$collection = $this->view->get('collection');
		$this->assertIsA($collection, 'Omelette_PersonCollection');
		$names = $collection->pluck('name');
		$this->assertEqual($names, array('Greg Jones', 'Simon Kamina'));
	}
	
	// test search activities by select, IS NOT as operator (should show results with a non-equal value AND results with a NULL value)
	function testActivitySearchBySelect() {
		$this->setUrl('/search/advanced?r=act&q[act_type][op]=IS+NOT&q[act_type][value]=100');
		$this->app->go();
		$this->assertFalse(Flash::Instance()->hasErrors());
		$this->assertPattern('/id="qb_act_type"/', $this->view->output);
		$this->assertPattern('/<th>Activity Type<\/th>/', $this->view->output);
		$this->assertPattern('/<td>Recreational<\/td>/', $this->view->output);
		$collection = $this->view->get('collection');
		$this->assertIsA($collection, 'Tactile_ActivityCollection');
		$names = $collection->pluck('name');
		$this->assertEqual($names, array('Eat a sandwich', 'Throw a party'));
	}
	
	// test search organisations by combination of filters
	function testMultipleFilters() {
		$this->setUrl('/search/advanced?r=org&q[gen_name][op]=CONTAINS&q[gen_name][value]=crm&q[gen_created][op]=AFTER&q[gen_created][value]=1%2F2%2F2011');
		$this->app->go();
		$this->assertFalse(Flash::Instance()->hasErrors());
		$this->assertPattern('/id="qb_gen_created"/', $this->view->output);
		$this->assertPattern('/<th>Creation Time<\/th>/', $this->view->output);
		$collection = $this->view->get('collection');
		$names = $collection->pluck('name');
		$this->assertEqual($names, array('Zanzibar CRM'));
	}
	
	// test search by people custom field
	function testSearchByCustomField() {
		$this->setUrl('/search/advanced?r=per&q[per_900][op]=IS&q[per_900][value]=200');
		$this->app->go();
		$this->assertFalse(Flash::Instance()->hasErrors());
		$this->assertPattern('/id="qb_per_900"/', $this->view->output);
		$this->assertPattern('/<th>Favourite Colour<\/th>/', $this->view->output);
		$this->assertPattern('/<td>Green<\/td>/', $this->view->output);
		$collection = $this->view->get('collection');
		$names = $collection->pluck('name');
		$this->assertEqual($names, array('David Edwards'));
	}
	
	// test search people by custom field with negation
	function testSearchByCustomFieldWithNegation() {
		$this->setUrl('/search/advanced?r=per&q[per_900][op]=IS+NOT&q[per_900][value]=100');
		$this->app->go();
		$this->assertFalse(Flash::Instance()->hasErrors());
		$this->assertPattern('/id="qb_per_900"/', $this->view->output);
		$this->assertPattern('/<th>Favourite Colour<\/th>/', $this->view->output);
		$this->assertPattern('/<td>Blue<\/td>/', $this->view->output);
		$collection = $this->view->get('collection');
		$names = $collection->pluck('name');
		$this->assertEqual($names, array('David Edwards', 'Greg Jones', 'Bob Smith'));
	}
	
	// test BEGINS WITH
	function function_name() {
		$this->setUrl('/search/advanced?r=org&q[gen_name][op]=BEGINS+WITH&q[gen_name][value]=ta');
		$this->app->go();
		$collection = $this->view->get('collection');
		$names = $collection->pluck('name');
		$this->assertEqual($names, array('Tactile CRM'));
	}
	
	// test save search
	function testSavingAdvancedSearch() {
		$_POST = array('name'=>'Test Search', 'form'=> 'r=per&q%5Bgen_name%5D%5Bop%5D=CONTAINS&q%5Bgen_name%5D%5Bvalue%5D=crm');
		$this->setUrl('/search/save');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$searches = DB::Instance()->getAssoc("SELECT * FROM advanced_searches");
		$search = array_pop($searches);
		$this->assertEqual($search['name'], 'Test Search');
		$this->assertEqual($search['record_type'], 'per');
		$this->assertEqual($search['query'], 'q[gen_name][op]=CONTAINS&q[gen_name][value]=crm');
	}
	
	// test delete search
	function testDeletingAdvancedSearch() {
		$this->setUrl('/search/delete/100');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$searches = DB::Instance()->getAssoc("SELECT * FROM advanced_searches");
		$this->assertTrue(empty($searches));
	}
	
	// test recall search
	/*
	 * Uses header() to redirect instead of sendTo(), (can't test?)
	function testRecallingAdvancedSearch() {
		$this->setUrl('/search/recall/100');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	*/
	
}
