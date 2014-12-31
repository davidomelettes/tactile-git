<?php

class TestOfApiAccess extends ControllerTest {
	
	function setup() {
		parent::setup();
	}
	
	function teardown() {
		$db = DB::Instance();
		$sql = "UPDATE tactile_accounts SET tactile_api_enabled = 'FALSE'";
		$db->execute($sql) or die('Failed to revert tactile_api_enabled');
		$sql = "UPDATE users SET api_token = NULL";
		$db->execute($sql) or die('Failed to revert api_tokens');
		
		Omelette::setIsApi(false);
		
		parent::teardown();
	}
	
	function testAccessToOrganisationsIndexWhileNotLoggedInWithApiKeyWithoutApiEnabled() {
		// Error
		// Should output 'failed to login' error as JSON
		$_GET['api_token'] = 'foo';
		
		$db = DB::Instance();
		$sql = "UPDATE users SET api_token = 'foo'";
		$db->execute($sql) or die('failed to set api_token');
		
		$this->setURL('organisations');
		$this->app->go();
		
		$output = $this->view->output;
		$this->assertIsJsonObject($output);
		$this->assertPattern('/^{"status":"error", "messages": {"403":"Failed to login"}}$/',$output);
	}
	
	function testAccessToOrganisationsIndexWhileNotLoggedInWithApiKey() {
		// Pass
		// Should respond with JSON
		$_GET['api_token'] = 'foo';
		
		$db = DB::Instance();
		$sql = "UPDATE users SET api_token = 'foo'";
		$db->execute($sql) or die('failed to set api_token');
		$sql = "UPDATE tactile_accounts SET tactile_api_enabled = 'TRUE'";
		$db->execute($sql) or die('failed to set tactile_api_enabled');
		
		$this->setURL('organisations');
		$this->app->go();
		
		$output = $this->view->output;
		$this->assertIsJsonObject($output);
		$this->assertPattern('/^{"status":"success", "organisations": \[{"name":"Default Company"/',$output);
		$this->assertPattern('/"cur_page":1/', $output);
		$this->assertPattern('/"num_pages":1/', $output);
		$this->assertPattern('/"per_page":30/', $output);
		$this->assertPattern('/"total":1/', $output);
	}
	
	function testAccessToOrganisationsIndexWhileNotLoggedInWithWrongApiKey() {
		// Error
		// Should output HTML login page
		$_GET['api_token'] = 'bar';
		
		$db = DB::Instance();
		$sql = "UPDATE users SET api_token = 'foo'";
		$db->execute($sql) or die('failed to set api_token');
		$sql = "UPDATE tactile_accounts SET tactile_api_enabled = 'TRUE'";
		$db->execute($sql) or die('failed to set tactile_api_enabled');
		
		$this->setURL('organisations');
		$this->app->go();
		
		$output = $this->view->output;
		$this->assertIsJsonObject($output);
		$this->assertPattern('/^{"status":"error", "messages": {"403":"Failed to login"}}$/',$output);
	}
	
	function testAccessToOrganisationsIndexWhileLoggedInWithApiKey() {
		// Pass
		// Should respond with HTML
		$this->setDefaultLogin();
		$_GET['api_token'] = 'foo';
		
		$db = DB::Instance();
		$sql = "UPDATE users SET api_token = 'foo'";
		$db->execute($sql) or die('failed to set api_token');
		$sql = "UPDATE tactile_accounts SET tactile_api_enabled = 'TRUE'";
		$db->execute($sql) or die('failed to set tactile_api_enabled');
		
		$this->setURL('organisations');
		$this->app->go();
		$this->genericPageTest();
		
		$output = $this->view->output;
		$this->assertPattern('/Organisations/',$output);
	}
	
	function testAccessToNonWhitelistedAddressWhileNotLoggedInWithApiKey() {
		// Error
		// Ouput login page as HTML
		$_GET['api_token'] = 'foo';
		
		$db = DB::Instance();
		$sql = "UPDATE users SET api_token = 'foo'";
		$db->execute($sql) or die('failed to set api_token');
		$sql = "UPDATE tactile_accounts SET tactile_api_enabled = 'TRUE'";
		$db->execute($sql) or die('failed to set tactile_api_enabled');
		
		$this->setURL('terms');
		$this->app->go();
		$this->genericPageTest();
		
		$output = $this->view->output;
		$this->assertPattern('/Log in to Tactile CRM/',$output);
	}
	
	function testAccessToNonWhitelistedAddressWhileLoggedInWithApiKey() {
		// Pass
		// Output the page as normal
		$this->setDefaultLogin();
		$_GET['api_token'] = 'foo';
		
		$db = DB::Instance();
		$sql = "UPDATE users SET api_token = 'foo'";
		$db->execute($sql) or die('failed to set api_token');
		$sql = "UPDATE tactile_accounts SET tactile_api_enabled = 'TRUE'";
		$db->execute($sql) or die('failed to set tactile_api_enabled');
		
		$this->setURL('terms');
		$this->app->go();
		$this->genericPageTest();
		
		$output = $this->view->output;
		$this->assertPattern('/Terms and Conditions of Use/',$output);
	}
	
	function testAccessToNonExistantAddressWithApiKey() {
		// Error
		// Should give us the HTML login page
		$_GET['api_token'] = 'foo';
		
		$db = DB::Instance();
		$sql = "UPDATE users SET api_token = 'foo'";
		$db->execute($sql) or die('failed to set api_token');
		$sql = "UPDATE tactile_accounts SET tactile_api_enabled = 'TRUE'";
		$db->execute($sql) or die('failed to set tactile_api_enabled');
		
		$this->setURL('xxx');
		$this->app->go();
		$this->genericPageTest();
		
		$output = $this->view->output;
		$this->assertPattern('/Log in to Tactile CRM/',$output);
	}
	
}
