<?php
class TestOfEmails extends ControllerTest {
	
	function setup() {
		parent::setup();
		$this->loadFixtures('emails');
		$this->saveFixture('some_company', 'organisations');
		$this->saveFixture('some_opportunity', 'opportunities');
		$this->saveFixture('some_person', 'people');
		$this->saveFixture('some_other_person', 'people');
		$this->saveFixtureRows('some_other_user', 'users');
		$this->saveFixtureRows('some_other_access', 'user_company_access');
		$this->saveFixture('greg_contact_method', 'person_contact_methods');
	}
	
	function tearDown() {
		DB::Instance()->execute("DELETE FROM emails");
		parent::tearDown();
	}
	
	function testOfIndexPage() {
		$this->setDefaultLogin();
		$this->setURL('emails');
		$this->app->go();
		$this->genericPageTest();
		
		$this->assertEqual($this->app->getControllerName(),'emails');
		$this->assertEqual($this->view->get('templateName'),$this->makeTemplatePath('tactile/emails/index'));
		
		$emails = $this->view->get('emails');
		$this->assertFalse($emails==false);
		$this->assertIsA($emails, 'EmailCollection');
		$this->assertEqual(count($emails),0);
	}
	
	function testViewingOwnEmail() {
		$this->saveFixture('unknown_to', 'emails');
		
		$this->setDefaultLogin();
		$this->setURL('emails/assign/300');
		$this->app->go();
		$this->genericPageTest();
		
		$model = $this->view->get('email');
		$this->assertIsA($model, 'Email');
		$this->assertEqual($model->id,300);
		$this->assertEqual($model->body, 'Test Body');
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}
	
	function testViewingInvalidId() {
		$this->setDefaultLogin();
		$this->setURL('emails/assign/999');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testViewingOthersEmail() {
		$this->saveFixture('unknown_to', 'emails');
		
		$this->setOtherUserLogin();
		$this->setURL('emails/assign/100');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testAssignEmail() {
		$this->saveFixture('unknown_to', 'emails');
		$_POST['Email'] = $this->getFixture('basic');
		$_POST['email_assign'] = 'false';
		$_POST['email_direction'] = 'incoming';
		
		$this->setDefaultLogin();
		$this->setURL('emails/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$email = DataObject::Construct('Email');
		$email = $email->load(300);
		$this->assertEqual($email->person_id, 1000);
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testAssignEmailToOpportunity() {
		$this->saveFixture('unknown_to', 'emails');
		$_POST['Email'] = $this->getFixture('assign_opportunity');
		$_POST['email_assign'] = 'false';
		$_POST['email_direction'] = 'incoming';
		
		$this->setDefaultLogin();
		$this->setURL('emails/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$email = DataObject::Construct('Email');
		$email = $email->load(300);
		$this->assertEqual($email->opportunity_id, 100);
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	/*
	function testAssignEmailChangeTo() {
		$this->saveFixture('unknown_to', 'emails');
		$_POST['Email'] = $this->getFixture('change_from');
		$_POST['email_assign'] = 'false';
		$_POST['email_direction'] = 'incoming';
		
		$this->setDefaultLogin();
		$this->setURL('emails/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$email = DataObject::Construct('Email');
		$email = $email->load(300);
		$this->assertEqual($email->person_id, 1000);
		$this->assertEqual($email->email_from, 'different@example.com');
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testAssignEmailChangeFrom() {
		$this->saveFixture('unknown_from', 'emails');
		$_POST['Email'] = $this->getFixture('change_to');
		$_POST['email_assign'] = 'false';
		$_POST['email_direction'] = 'outgoing';
		
		$this->setDefaultLogin();
		$this->setURL('emails/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$email = DataObject::Construct('Email');
		$email = $email->load(400);
		$this->assertEqual($email->person_id, 1000);
		$this->assertEqual($email->email_to, 'different@example.com');
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testAssignEmailChangeToAndAssignEmail() {
		$this->saveFixture('unknown_to', 'emails');
		$_POST['Email'] = $this->getFixture('change_from');
		$_POST['email_assign'] = 'on';
		$_POST['email_direction'] = 'incoming';
		
		$this->setDefaultLogin();
		$this->setURL('emails/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$email = DataObject::Construct('Email');
		$email = $email->load(300);
		$this->assertEqual($email->person_id, 1000);
		$this->assertEqual($email->email_from, 'different@example.com');
		
		$email_address = DataObject::Construct('Personcontactmethod');
		$email_address = $email_address->loadBy('contact','different@example.com');
		$this->assertEqual($email_address->person_id, 1000);
		$this->assertEqual($email_address->type, 'E');
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testAssignEmailChangeFromAndAssignEmail() {
		$this->saveFixture('unknown_from', 'emails');
		$_POST['Email'] = $this->getFixture('change_to');
		$_POST['email_assign'] = 'on';
		$_POST['email_direction'] = 'outgoing';
		
		$this->setDefaultLogin();
		$this->setURL('emails/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$email = DataObject::Construct('Email');
		$email = $email->load(400);
		$this->assertEqual($email->person_id, 1000);
		$this->assertEqual($email->email_to, 'different@example.com');
		
		$email_address = DataObject::Construct('Personcontactmethod');
		$email_address = $email_address->loadBy('contact','different@example.com');
		$this->assertEqual($email_address->person_id, 1000);
		$this->assertEqual($email_address->type, 'E');
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	*/
	function testCanDeleteOwnEmail() {
		$this->setDefaultLogin();
		$this->saveFixture('unknown_to', 'emails');
		
		$this->setUrl('emails/delete/300');
		$email = DataObject::Construct('Email');
		$email = $email->load(300);
		$this->assertIsA($email, 'Email');
		
		$this->app->go();
		
		$this->assertFalse(Flash::Instance()->hasErrors());
		$this->assertEqual(count(Flash::Instance()->errors), 0);
		$email = DataObject::Construct('Email');
		$email = $email->load(300);
		$this->assertFalse($email);
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testCantDeleteOthersEmail() {
		$this->setOtherUserLogin();
		$this->saveFixture('unknown_to', 'emails');
		
		$this->setUrl('emails/delete/300');
		$email = DataObject::Construct('Email');
		$email = $email->load(300);
		$this->assertIsA($email, 'Email');
		
		$this->app->go();
				
		$this->assertTrue(Flash::Instance()->hasErrors());
		$this->assertEqual(count(Flash::Instance()->errors), 1);
		$email = DataObject::Construct('Email');
		$email = $email->load(300);
		$this->assertIsA($email, 'Email');
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function setOtherUserLogin() {
		$this->_auth->getStorage()->write('otheruser//tactile');
	}
	
	function testAssignEmailCreatesNewContacts() {
		$this->saveFixture('unknown_to', 'emails');
		$this->saveFixture('default_status', 'opportunitystatus');
		$this->saveFixtureRows('default_contact_method', 'person_contact_methods');
		$_POST['Email'] = $this->getFixture('create_contacts');
		$_POST['email_assign'] = 'on';
		$_POST['email_direction'] = 'incoming';
		
		$this->setDefaultLogin();
		$this->setURL('emails/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$email = DataObject::Construct('Email');
		$email = $email->load($_POST['Email']['id']);
		
		$person = new Tactile_Person();
		$person->load($email->person_id);
		$this->assertEqual($person->name, 'Robert Smith');
		$this->assertEqual($person->email->contact, 'notauser@example.com');
		
		$org = new Tactile_Organisation();
		$org->load($email->organisation_id);
		$this->assertEqual($org->name, 'The Cure');
		
		$opp = new Tactile_Opportunity();
		$opp->load($email->opportunity_id);
		$this->assertEqual($opp->name, "Friday I'm in Love");
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
}