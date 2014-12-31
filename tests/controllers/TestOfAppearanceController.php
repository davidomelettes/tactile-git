<?php

class TestOfAppearanceController extends ControllerTest {

	function setup() {
		parent::setUp();
		$this->setDefaultLogin();
		$sql = "UPDATE tactile_accounts SET created=now() - interval '1 day' WHERE id = 1";
		DB::Instance()->execute($sql) or die($sql);
		Omelette::setAccountPlan(1);
		Omelette::setAccount(1);
	}
	
	function tearDown() {
		$sql = "DELETE FROM tactile_accounts_magic";
		DB::Instance()->execute($sql) or die($sql);
		$sql = "UPDATE tactile_accounts SET current_plan_id = '1'";
		DB::Instance()->execute($sql) or die($sql);
		Omelette::setAccountPlan(1);
		$sql = "UPDATE tactile_accounts SET created=now() - interval '1 day'";
		DB::Instance()->execute($sql) or die($sql);
		parent::tearDown();
	}
	
	function testThemesWork() {
		$this->setURL('');
		
		$this->app->go();
		$this->assertPattern('/\/themes\/green\.css" \/>/', $this->view->output);
		
	}
	
	function testChangingThemesOnTrialPeriod() {
		$this->setURL('appearance/save_theme/?theme=purple');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$sql = "SELECT value FROM tactile_accounts_magic WHERE usercompanyid = '1' AND key = 'theme'";
		$theme = DB::Instance()->getOne($sql);
		$this->assertEqual($theme, 'purple');
	}

	function testChangingThemesOnFreePlanInsideTrial() {
		$sql = "UPDATE tactile_accounts SET created = now() WHERE id = 1";
		DB::Instance()->execute($sql) or die($sql);
		Omelette::setAccount(1);
		
		$this->setURL('appearance/save_theme/?theme=purple');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$sql = "SELECT value FROM tactile_accounts_magic WHERE usercompanyid = '1' AND key = 'theme'";
		$theme = DB::Instance()->getOne($sql);
		
		// You can now do this inside your free trial period
		$this->assertEqual($theme, 'purple');
	}
	
	function testChangingThemesOnFreePlanOutsideTrial() {
		$sql = "UPDATE tactile_accounts SET created = now() - interval '31 days' WHERE id = 1";
		DB::Instance()->execute($sql) or die($sql);
		Omelette::setAccount(1);
		
		$this->setURL('appearance/save_theme/?theme=purple');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$sql = "SELECT value FROM tactile_accounts_magic WHERE usercompanyid = '1' AND key = 'theme'";
		$theme = DB::Instance()->getOne($sql);
		
		// You can not do this outside your free trial period
		$this->assertNotEqual($theme, 'purple');
	}
	
	function testChangingThemesOnNonFreePlan() {
		$sql = "UPDATE tactile_accounts SET current_plan_id = '2'";
		DB::Instance()->execute($sql) or die($sql);
		Omelette::setAccountPlan(2);
		
		$this->setURL('appearance/save_theme/?theme=purple');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$sql = "SELECT value FROM tactile_accounts_magic WHERE usercompanyid = '1' AND key = 'theme'";
		$theme = DB::Instance()->getOne($sql);
		$this->assertEqual($theme, 'purple');
	}
}
