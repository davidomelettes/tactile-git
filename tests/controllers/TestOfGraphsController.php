<?php

class TestOfGraphsController extends ControllerTest {

	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		
		$db = DB::Instance();
		$query = "UPDATE tactile_accounts set current_plan_id = '2'"; // Set to a non-free plan
		$db->Execute($query) or die($db->ErrorMsg());
		Omelette::setAccountPlan(2);
		
		$query = "DELETE FROM tactile_magic WHERE key = 'dashboard_graph'";
		$db->Execute($query) or die($db->ErrorMsg());
	}
	
	function teardown() {
		$db = DB::Instance();
		$query = "UPDATE tactile_accounts set current_plan_id = '1'"; // Set back to free plan
		$db->Execute($query) or die($db->ErrorMsg());
		Omelette::setAccountPlan(1);
		parent::tearDown();
	}
	
	function testSalesHistory() {
		$this->setURL('graphs/sales_history?date=01%2F01%2F2007');
		//$this->setURL('graphs/sales_history?date[Month]=01&date[Year]=2007');
		$this->app->go();
		$this->genericPageTest();
		
		$data = $this->view->get('sales_history_data');
		$expected = array(
			'2006-02-01' => '0',
			'2006-03-01' => '0',
			'2006-04-01' => '0',
			'2006-05-01' => '0',
			'2006-06-01' => '0',
			'2006-07-01' => '0',
			'2006-08-01' => '0',
			'2006-09-01' => '0',
			'2006-10-01' => '0',
			'2006-11-01' => '0',
			'2006-12-01' => '0',
			'2007-01-01' => '0'
		);
		$this->assertEqual($data, $expected);
		
		$this->assertPattern('/February 2006/', $this->view->output);
		$this->assertPattern('/January 2007/', $this->view->output);
	}
	
	function testSalesHistoryWithBadDate() {
		$this->setURL('graphs/sales_history?date=x');
		$this->app->go();
		$this->genericPageTest();
		
		$fy = date('F Y');
		$this->assertPattern("/$fy/", $this->view->output);
	}
	
	function testPipeline() {
		$this->setURL('graphs/pipeline');
		$this->app->go();
		$this->genericPageTest();
	}
	
	function testOppSourceByQtyWithExpiredTrial() {
		$db = DB::Instance();
		$query = "UPDATE tactile_accounts set current_plan_id = '1'";
		$db->Execute($query) or die($db->ErrorMsg());
		Omelette::setAccountPlan(1);
		$query = "UPDATE tactile_accounts set created = now() - interval '21 days'";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->setURL('graphs/opps_by_source_qty');
		$this->app->go();
		
		$f = Flash::Instance();
		//$this->assertTrue($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		//$this->assertTrue($r->willRedirect());
		
		$query = "UPDATE tactile_accounts set current_plan_id = '2'";
		$db->Execute($query) or die($db->ErrorMsg());
		Omelette::setAccountPlan(2);
	}
	
	function testOppsBySourceQty() {
		$this->setURL('graphs/opps_by_source_qty');
		$this->app->go();
		$this->genericPageTest();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}
	
	function testOppsBySourceCost() {
		$this->setURL('graphs/opps_by_source_cost');
		$this->app->go();
		$this->genericPageTest();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertPattern('/There are currently no opportunities that match your criteria - try another user/', $this->view->output);
	}
	
	function testOppsByTypeQty() {
		$this->setURL('graphs/opps_by_type_qty');
		$this->app->go();
		$this->genericPageTest();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}
	
	function testOppsByTypeCost() {
		$this->setURL('graphs/opps_by_type_cost');
		$this->app->go();
		$this->genericPageTest();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}
	
	function testOppsByStatusQty() {
		$this->setURL('graphs/opps_by_status_qty');
		$this->app->go();
		$this->genericPageTest();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}
	
	// Graph is now loaded via ajax
	/*function testDefaultGraphOnDashboard() {
		$this->setURL('/');
		$this->app->go();
		$this->genericPageTest();
		$this->assertEqual('My Pipeline', $this->view->get('graph_title'));
	}*/
	
	function testGraphPinning() {
		$_POST['chart_method'] = 'salesHistory';
		$this->setURL('graphs/pin_to_dashboard');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$expected = array('Graph preference saved');
		$received = $f->getMessages();
		// No message needed now due to ajax pinning method
		//$this->assertEqual($expected, $received);
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		Omelette_Magic::loadAll('greg//tactile');
		$pinned = Omelette_Magic::getValue('dashboard_graph', 'greg//tactile', 'pipeline');
		$this->assertEqual($pinned, 'salesHistory');
	}
	
	/*
	function testPinnedGraphOnDashboard() {
		Omelette_Magic::saveChoice('dashboard_graph', 'salesHistory', 'greg//tactile');
		
		$this->setURL('/');
		$this->app->go();
		$this->assertEqual('My Sales History', $this->view->get('graph_title'));
	}
	*/
	
}
