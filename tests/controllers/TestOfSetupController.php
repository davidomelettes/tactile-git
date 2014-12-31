<?php

class TestOfSetupController extends ControllerTest {
	
	function setup() {
		parent::setUp();
		$this->setDefaultLogin();
		$this->loadFixtures('setup');
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		
	}
	
	function teardown() {
		parent::tearDown();
		$db = DB::Instance();
		
		$query = 'DELETE FROM opportunitystatus';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM opportunities';
		$db->Execute($query) or die($db->ErrorMsg());
	}
	
	function testIntermediateOppStatusDeletionPage() {
		$this->saveFixtureRows('opp_statuses', 'opportunitystatus');
		$this->saveFixtureRows('opps_with_statuses', 'opportunities');
		
		$this->setUrl('/setup/delete?group=opportunities&option=status&id=200');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->genericPageTest();
		$this->assertPattern('/Select new Opportunity status/', $this->view->output);
	}
	
	function testDeletingOpportunityStatus() {
		$this->saveFixtureRows('opp_statuses', 'opportunitystatus');
		$this->saveFixtureRows('opps_with_statuses', 'opportunities');
		
		$_POST = array('group'=>'opportunities', 'option'=>'status', 'id'=>'200', 'new_option'=>'100');
		$this->setUrl('/setup/process_delete');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$opp = new Tactile_Opportunity();
		$this->assertTrue(FALSE !== $opp->load(200));
		$this->assertEqual($opp->status_id, 100);
		
		//$db = DB::Instance();
	}
	
}
