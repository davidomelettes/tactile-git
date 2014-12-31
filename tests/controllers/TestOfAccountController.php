<?php

require_once 'Zend/XmlRpc/Client.php';
require_once 'Zend/Http/Client/Adapter/Test.php';
require_once 'Zend/Http/Client.php';


AutoLoader::Instance()->addPath(FILE_ROOT.'omelette/lib/payment/');

Mock::Generate('Zend_XmlRpc_Client');

class TestOfAccountController extends ControllerTest {

	/**
	 * 
	 */
	function setup() {
		parent::setUp();
		$this->setDefaultLogin();
		$this->loadFixtures('accounts');
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		
	}
	
	function tearDown() {
		parent::tearDown();
		$db = DB::Instance();
		$query = 'UPDATE tactile_accounts set current_plan_id = 1';
		$db->Execute($query) or die($db->ErrorMsg());
		Omelette::setAccountPlan(1);
		
		$query = 'DELETE FROM payment_records';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM user_company_access WHERE username<>\'greg//tactile\'';
		$db->Execute($query) or die($db->ErrorMsg());
		
	}
	
	function testAccessingChangePlanFormWithoutHTTPS() {
		$this->setURL('account/change_plan');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testChangePlanForm() {
		$_SERVER['HTTP_X_FARM'] = 'HTTPS';
		$this->setURL('account/change_plan');
		$this->app->go();
		$this->genericPageTest();
		
		$plan = $this->view->get('current_plan');
		$this->assertIsA($plan,'AccountPlan');
		$this->assertEqual($plan->id, 1);
		
		//check that there are some 'text' inputs:
		$this->assertPattern('#<input.+type="text"#i', $this->view->output);
	}
	
	function testChangeFromFreeToPaid() {
		
		$client = new MockZend_XmlRpc_Client();
		$response = new Zend_XmlRpc_Response();
		$response->setReturnValue('?valid=true&trans_id=Tactile20080205101445&code=A&auth_code=9999&message=TEST AUTH&amount=6.0&test_status=true');
		$client->setReturnValue('getLastResponse', $response);
		
		SecPayRequest_Abstract::setDefaultClient($client);
		
		$this->setURL('account/process_plan_change');
		
		$_POST = $this->getFixture('free_to_paid');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$account = DataObject::Construct('TactileAccount');
		/* @var $account TactileAccount */
		$account = $account->load(1);

		$this->assertIsA($account, 'TactileAccount');
		
		$this->assertEqual($account->current_plan_id, 2);
		
		$plan = DataObject::Construct('AccountPlan');
		$plan = $plan->load(2);
		
		$payment = $account->getLatestPayment();
		$this->assertIsA($payment, 'PaymentRecord');
		$this->assertEqual($payment->amount, $plan->cost_per_month);
	}
	
	function testChangeWithEmptyForm() {
		$client = new MockZend_XmlRpc_Client();
		SecPayRequest_Abstract::setDefaultClient($client);
		
		$client->expectNever('call');
		
		$this->setURL('account/process_plan_change');
		$_POST = $this->getFixture('empty_form');
		
		$this->app->go();
		$this->checkUnsuccessfulSave();
		
		$account = DataObject::Construct('TactileAccount');
		/* @var $account TactileAccount */
		$account = $account->load(1);

		$this->assertIsA($account, 'TactileAccount');
		
		$this->assertEqual($account->current_plan_id, 1);
		
		$payment = $account->getLatestPayment();
		$this->assertFalse($payment);
	}
	
	function testSavingWithSamePlan() {
		$client = new MockZend_XmlRpc_Client();
		SecPayRequest_Abstract::setDefaultClient($client);
		
		$client->expectNever('call');
		
		$this->setURL('account/process_plan_change');
		$_POST = $this->getFixture('same_plan');
		
		$this->app->go();
		$this->checkUnsuccessfulSave();
		
		$account = DataObject::Construct('TactileAccount');
		/* @var $account TactileAccount */
		$account = $account->load(1);

		$this->assertIsA($account, 'TactileAccount');
		
		$this->assertEqual($account->current_plan_id, 1);
		
		$payment = $account->getLatestPayment();
		$this->assertFalse($payment);
	}
	
	function testSavingWithInvalidCardNumber() {
		$client = new MockZend_XmlRpc_Client();
		SecPayRequest_Abstract::setDefaultClient($client);
		
		$client->expectNever('call');
		
		$this->setURL('account/process_plan_change');
		$_POST = $this->getFixture('invalid_card');
		
		$this->app->go();
		$this->checkUnsuccessfulSave();
		
		$account = DataObject::Construct('TactileAccount');
		/* @var $account TactileAccount */
		$account = $account->load(1);

		$this->assertIsA($account, 'TactileAccount');
		
		$this->assertEqual($account->current_plan_id, 1);
		
		$payment = $account->getLatestPayment();
		$this->assertFalse($payment);
	}

	function testGoingFromPaidToFree() {
		$client = new MockZend_XmlRpc_Client();
		SecPayRequest_Abstract::setDefaultClient($client);
		
		$client->expectNever('call');
		
		$this->saveFixtureRows('existing_payment_record', 'payment_records');
		$account = DataObject::Construct('TactileAccount');
		/* @var $account TactileAccount */
		$account = $account->load(1);
		$account->current_plan_id = 2;
		$account->save();
		Omelette::setAccountPlan(2);
		
		$_POST = $this->getFixture('paid_to_free');
		Flash::Instance()->clear();
		$this->setURL('acccount/process_plan_change');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$account = DataObject::Construct('TactileAccount');
		/* @var $account TactileAccount */
		$account = $account->load(1);

		$this->assertIsA($account, 'TactileAccount');
		
		$this->assertEqual($account->current_plan_id, 1);
		
		$this->assertTrue($account->getPlan()->is_free());
	}
	
	function testPerUserFreeToPerUserPaid() {
		$client = new MockZend_XmlRpc_Client();
		$response = new Zend_XmlRpc_Response();
		$response->setReturnValue('?valid=true&trans_id=Tactile20080205101445&code=A&auth_code=9999&message=TEST AUTH&amount=6.0&test_status=true');
		$client->setReturnValue('getLastResponse', $response);
		
		SecPayRequest_Abstract::setDefaultClient($client);
		
		$account = DataObject::Construct('TactileAccount');
		/* @var $account TactileAccount */
		$account = $account->load(1);
		$account->current_plan_id = 3;
		$account->save();
		Omelette::setAccountPlan(3);
		
		$_POST = $this->getFixture('free_peruser_to_paid_peruser');
		Flash::Instance()->clear();
		$this->setURL('acccount/process_plan_change');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$account = DataObject::Construct('TactileAccount');
		/* @var $account TactileAccount */
		$account = $account->load(1);

		$this->assertIsA($account, 'TactileAccount');
		
		$this->assertEqual($account->current_plan_id, 4);
		
		$this->assertFalse($account->getPlan()->is_free());
	}
	
	function testAccessingAsTheAccountOwner() {
		$this->setURL('accounts');
		
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());

		$this->genericPageTest();
		
		$this->assertFalse(Flash::Instance()->hasErrors());
	}
	
	function testAccessingAsNotTheAccountOwner() {
		$this->saveMultiFixture('alternate_user');
		
		$this->_auth->getStorage()->write('other_user//tactile');
		
		$this->setURL('accounts');
		
		$this->app->go();
		
		$rp = RouteParser::Instance();
		$user = CurrentlyLoggedInUser::Instance();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());

		$this->assertTrue(Flash::Instance()->hasErrors());
		
	}
	
}

?>
