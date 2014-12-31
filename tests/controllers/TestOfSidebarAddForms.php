<?php
class TestOfSidebarAddForms extends ControllerTest {
	
	function setup() {
		parent::setup();
		$this->loadFixtures('sidebar');
		$this->setDefaultLogin();
	}
	
	function teardown() {
		parent::teardown();
	}
	
	function testLoadingAddPersonToCompanyForm() {
		$this->setAjaxRequest();
		$this->setUrl('organisations/new_person');
		$_GET['id'] = 1;
		
		$this->app->go();
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('#^<form action="/people/save/"#', $this->view->output);
		$this->assertPattern('#name="Person\[firstname]"#', $this->view->output);
		$this->assertPattern('#name="Person\[surname]"#', $this->view->output);
		$this->assertPattern('#name="Person\[phone]\[contact]"#', $this->view->output);
		$this->assertPattern('#name="Person\[email]\[contact]"#', $this->view->output);
	}
	
	function testLoadingAddOpportunityToCompanyForm() {
		$this->setAjaxRequest();
		$this->setUrl('organisations/new_opportunity');
		$_GET['id'] = 1;
		
		$this->app->go();
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('#^<form action="/opportunities/save/"#', $this->view->output);
		$this->assertPattern('#name="Opportunity\[name]"#', $this->view->output);
		$this->assertPattern('#name="Opportunity\[person_id]"#', $this->view->output);
		$this->assertPattern('#name="Opportunity\[organisation_id]"#', $this->view->output);
		$this->assertPattern('#name="Opportunity\[cost]"#', $this->view->output);
	}

	function testLoadingAddActivityToCompanyForm() {
		$this->setAjaxRequest();
		$this->setUrl('organisations/new_activity');
		$_GET['id'] = 1;
		
		$this->app->go();
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('#^<form action="/activities/save/"#', $this->view->output);
		$this->assertPattern('#name="Activity\[name]"#', $this->view->output);
		$this->assertPattern('#name="Activity\[person_id]"#', $this->view->output);
		$this->assertPattern('#name="Activity\[organisation_id]"#', $this->view->output);
		$this->assertPattern('#name="Activity\[class]"#', $this->view->output);
	}
	
	function testLoadingAddOpportunityToPersonForm() {
		$this->setAjaxRequest();
		$this->setUrl('people/new_opportunity');
		$_GET['id'] = 1;
		
		$this->app->go();
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('#^<form action="/opportunities/save/"#', $this->view->output);
		$this->assertPattern('#name="Opportunity\[name]"#', $this->view->output);
		$this->assertPattern('#name="Opportunity\[person_id]"#', $this->view->output);
		$this->assertPattern('#name="Opportunity\[organisation_id]"#', $this->view->output);
		$this->assertPattern('#name="Opportunity\[cost]"#', $this->view->output);
	}

	function testLoadingAddActivityToPersonForm() {
		$this->setAjaxRequest();
		$this->setUrl('people/new_activity');
		$_GET['id'] = 1;
		
		$this->app->go();
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('#^<form action="/activities/save/"#', $this->view->output);
		$this->assertPattern('#name="Activity\[name]"#', $this->view->output);
		$this->assertPattern('#name="Activity\[person_id]"#', $this->view->output);
		$this->assertPattern('#name="Activity\[organisation_id]"#', $this->view->output);
		$this->assertPattern('#name="Activity\[class]"#', $this->view->output);
	}
	
	function testLoadingAddActivityToOpportunityForm() {
		$this->setAjaxRequest();
		$this->saveFixtureRows('default_opportunity', 'opportunities');
		$this->setUrl('opportunities/new_activity');
		$_GET['id'] = 1;
		
		$this->app->go();
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('#^<form action="/activities/save/"#', $this->view->output);
		$this->assertPattern('#name="Activity\[name]"#', $this->view->output);
		$this->assertPattern('#name="Activity\[person_id]"#', $this->view->output);
		$this->assertPattern('#name="Activity\[opportunity_id]"#', $this->view->output);
		$this->assertPattern('#name="Activity\[class]"#', $this->view->output);
	}
	
}
