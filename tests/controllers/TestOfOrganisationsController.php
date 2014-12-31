<?php


class TestOfOrganisationsController extends ControllerTest {

	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$db = DB::Instance();
		
		$query = 'DELETE FROM organisations WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		$query = 'DELETE FROM users WHERE person_id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		$query = 'DELETE FROM people WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());		
		$query = 'DELETE FROM notes';
		$db->Execute($query) or die($db->ErrorMsg());
		$this->loadFixtures('clients');
	}
	
	function testNewClientPage() {
		$this->setURL('organisations/new');
		$this->app->go();
		$this->genericPageTest();
		
		$this->assertEqual($this->app->getControllerName(),'organisations');
		$this->assertEqual($this->view->get('templateName'),$this->makeTemplatePath('contacts/organisations/new'));
		
		$this->assertIsA($this->view->get('Organisation'), 'Organisation');
	}
	
	function testSaveClientBasic() {
		$_POST['Organisation'] = $this->getFixture('basic');
		
		$this->setURL('organisations/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);

		$client = DataObject::Construct('Organisation');
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		$this->assertIsA($client,'Organisation');
		
		$this->assertFixture($client, 'basic');
		
		$this->assertEqual($client->assigned_to, EGS::getUsername());
		$this->assertNow($client->created);
	}
	
	function testSaveWithNoName() {
		$_POST['Organisation'] = $this->getFixture('no_name');
		
		$this->setURL('organisations/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 1);
	}
	
	function testWithNoAccountNumber() {
		$_POST['Organisation'] = $this->getFixture('no_accountnumber');
		
		$this->setURL('organisations/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		$this->assertIsA($client,'Organisation');
		$this->assertFixture($client, 'no_accountnumber');
		$this->assertEqual($client->accountnumber,'TECL01');
	}
	
	function testSaveWithNoPost() {
		$this->setURL('organisations/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 1);
	}
	
	function testCreatedIsIgnored() {
		$this->setURL('organisations/save');
		$_POST['Organisation'] = $this->getFixture('with_created_set');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		$this->assertIsA($client,'Organisation');
		$this->assertEqual($client->accountnumber, $_POST['Organisation']['accountnumber']);

		$this->assertNow($client->created);
	}
	
	function testWithAllMainFields() {
		$this->setURL('organisations/save');
		$_POST['Organisation'] = $this->getFixture('all_main_fields');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		$this->assertIsA($client,'Organisation');
		
		$this->assertFixture($client, 'all_main_fields');
	}
	
	function testEmployeesRejectsNonNumeric() {
		$this->setURL('organisations/save');
		
		$_POST['Organisation'] = $this->getFixture('non_numeric_employees');
		
		$this->app->go();
		
		$this->checkSuccessfulSave(); // employees no longer a field
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
	}
	
	function testCreditlimitRejectsNonNumeric() {
		$this->setURL('organisations/save');
		
		$_POST['Organisation'] = $this->getFixture('non_numeric_creditlimit');
		
		$this->app->go();
		
		$this->checkSuccessfulSave(); // creditlimit no longer a field
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
	}
	
	function testSavingWithPhoneNumber() {
		$this->setURL('organisations/save');
		
		$_POST['Organisation'] = $this->getFixture('basic_with_phone');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		$this->assertIsA($client,'Organisation');
		
		$this->assertFixture($client, 'basic_with_phone');
		$this->assertFixture($client->phone, 'basic_with_phone', 'phone');
	}
	
	function testSavingWithPhoneFaxAndEmail() {
		$this->setURL('organisations/save');
		
		$_POST['Organisation'] = $this->getFixture('basic_with_phone_fax_email');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		$this->assertIsA($client,'Organisation');
		
		$this->assertFixture($client, 'basic_with_phone_fax_email');
		
		$this->assertFixture($client->phone, 'basic_with_phone_fax_email', 'phone');
		$this->assertFixture($client->fax, 'basic_with_phone_fax_email', 'fax');
		$this->assertFixture($client->email, 'basic_with_phone_fax_email', 'email');
	}
	
	function testSavingWithAddress() {
		$this->setURL('organisations/save');
		
		$_POST['Organisation'] = $this->getFixture('basic_with_address');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		$this->assertIsA($client,'Organisation');
		
		$this->assertFixture($client, 'basic_with_address');
	}
	
	/**
	 * This should now save, as all address fields are now non-mandatory 
	 */
	function testWithAddressBitsMissing() {
		$this->setURL('organisations/save');
		
		$_POST['Organisation'] = $this->getFixture('basic_with_incomplete_address');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
	}
	
	function testSettingEveryoneAccess() {
		$this->setURL('organisations/save');
		
		$_POST['Organisation'] = $this->getFixture('basic');
		$_POST['Sharing'] = $this->getFixture('access_for_everyone');		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		/* @var $client Tactile_Organisation */
		$this->assertIsA($client,'Organisation');
		
		$this->assertFixture($client, 'basic');
		
		$this->assertEqual($client->getAccess('read'), 'everyone');
		$this->assertEqual($client->getAccess('write'), 'everyone');
	}
	
	function testSettingPrivateAccess() {
		$this->setURL('organisations/save');
		
		$_POST['Organisation'] = $this->getFixture('basic');
		$_POST['Sharing'] = $this->getFixture('private_access');		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		/* @var $client Tactile_Organisation */
		$this->assertIsA($client,'Organisation');
		
		$this->assertFixture($client, 'basic');
		
		$this->assertEqual($client->getAccess('read'), 'private');
		$this->assertEqual($client->getAccess('write'), 'private');
	}
	
	function testSettingMixedAccess() {
		$this->setURL('organisations/save');
		
		$_POST['Organisation'] = $this->getFixture('basic');
		$_POST['Sharing'] = $this->getFixture('everyone_read_private_write');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		
		/* @var $client Tactile_Organisation */
		$this->assertIsA($client,'Organisation');
		
		$this->assertFixture($client, 'basic');
		
		$this->assertEqual($client->getAccess('read'), 'everyone');
		$this->assertEqual($client->getAccess('write'), 'private');
	}
	
	function testSettingWithRoles() {
		$this->setURL('organisations/save');
		
		$db = DB::Instance();
		$query = "INSERT INTO roles (name,usercompanyid) VALUES ('Test Role',".EGS::getCompanyId().")";
		$db->Execute($query) or die("Insert role failed: ".$db->ErrorMsg());
		$role_id = $db->get_last_insert_id();
		
		$_POST['Organisation'] = $this->getFixture('basic');
		$_POST['Sharing'] = array('read'=>array($role_id), 'write'=>array($role_id));
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		
		/* @var $client Tactile_Organisation */
		$this->assertIsA($client,'Organisation');
		
		$this->assertFixture($client, 'basic');
		
		$this->assertEqual($client->getAccess('read'), 'multi');
		$this->assertEqual($client->getAccess('write'), 'multi');
		
		$roles_with_read = $client->getRoles('read');
		$roles_with_write = $client->getRoles('write');
		
		$this->assertTrue(isset($roles_with_read[$role_id]));
		$this->assertTrue(isset($roles_with_write[$role_id]));
		
		$query = 'DELETE FROM roles WHERE id='.$role_id;
		$db->Execute($query) or die("Deleting role failed: ".$db->ErrorMsg());
	}
	
	function testSettingSalesInfo() {
		$this->setURL('organisations/save');
		
		$this->saveMultiFixture('crm_defaults');
		
		$_POST['Organisation'] = $this->getFixture('with_crm_fields');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		$this->assertIsA($client,'Organisation');
		
		$this->assertFixture($client, 'with_crm_fields');
		
		$this->assertEqual($client->company_status, 'Test Status');
		$this->assertEqual($client->company_source, 'Test Source');
		$this->assertEqual($client->company_classification, 'Test Classification');
		$this->assertEqual($client->company_rating, 'Test Rating');
		$this->assertEqual($client->company_industry, 'Test Industry');
		$this->assertEqual($client->company_type, 'Test Type');
	}
	
	function testViewingClient() {
		$this->setURL('organisations/view/1');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Organisation');
		/* @var $model Tactile_Organisation */
		$this->assertIsA($model, 'Tactile_Organisation');
		
		$this->assertEqual($model->id,1);
		$this->assertEqual($model->getFormatted('name'), 'Default Company');
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertIsA($model->notes,'NoteCollection');
		
		$this->assertIsA($model->getPhoneNumbers(),'OrganisationcontactmethodCollection');
		$this->assertIsA($model->getFaxNumbers(),'OrganisationcontactmethodCollection');
		$this->assertIsA($model->getEmailAddresses(),'OrganisationcontactmethodCollection');
		
		$db = DB::Instance();
		$query = 'SELECT link_id, type, label FROM recently_viewed';
		$rows = $db->GetArray($query);
		
		$this->assertEqual(count($rows), 1);		
		$this->assertEqual($rows[0]['link_id'], 1);
		$this->assertEqual($rows[0]['type'], ViewedPage::TYPE_ORGANISATION);
		$this->assertEqual($rows[0]['label'], $model->getFormatted('name'));
	}
	
	function testViewingPrivateClientAsAdmin() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('basic_private_company', 'organisations');
	
		$this->setURL('organisations/view/109');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}

	function testViewingPrivateClientAsNonAdmin() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('basic_private_company', 'organisations');
		
		// Replace admin checker with a foo that always returns false
		// (admins bypass permissions check)
		$this->injector->register('TestAdminChecker');
	
		$this->setURL('organisations/view/109');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
	}
	
	function testViewingClientWithMoreDetails() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('contact_methods', 'organisation_contact_methods');
		$this->saveFixtureRows('addresses', 'organisation_addresses');
		
		$id = $this->fixtures['client_defaults'][0]['id'];
		
		$this->setURL('organisations/view/'.$id);
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Organisation');
		/* @var $model Tactile_Organisation */
		
		$this->assertIsA($model, 'Tactile_Organisation');
		
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertIsA($model->getPhoneNumbers(),'OrganisationcontactmethodCollection');
		$this->assertIsA($model->getFaxNumbers(),'OrganisationcontactmethodCollection');
		$this->assertIsA($model->getEmailAddresses(),'OrganisationcontactmethodCollection');
		
		$this->assertEqual(count($model->getPhoneNumbers()), 1);
		$this->assertEqual((string)$model->phone, $this->fixtures['contact_methods'][1]['contact']);
		
		$this->assertEqual(count($model->getEmailAddresses()), 1);
		$this->assertEqual((string)$model->email, $this->fixtures['contact_methods'][0]['contact']);
		
		$this->assertEqual($model->address->street1, $this->fixtures['addresses'][0]['street1']);
		$this->assertEqual($model->address->postcode, $this->fixtures['addresses'][0]['postcode']);
		$this->assertEqual($model->address->country, 'United Kingdom');
	}
	
	function testViewingInvalidId() {
		$this->setURL('organisations/view/999');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testViewingClientWithNotes() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('notes', 'notes');
		
		$id = $this->fixtures['client_defaults'][0]['id'];
		
		$this->setURL('organisations/view/'.$id);
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Organisation');
		/* @var $model Tactile_Organisation */
		
		$this->assertIsA($model, 'Tactile_Organisation');
		
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertIsA($model->notes,'NoteCollection');
		
		$this->assertEqual(count($model->notes), 1);
	}
	
	function testViewingClientWithOwnPrivateNotes() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('private_notes', 'notes');
		
		$id = $this->fixtures['client_defaults'][0]['id'];
		
		$this->setURL('organisations/view/'.$id);
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Organisation');
		/* @var $model Tactile_Organisation */
		
		$this->assertIsA($model, 'Tactile_Organisation');
		
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertIsA($model->notes,'NoteCollection');
		
		$this->assertEqual(count($model->notes), 1);
	}
	
	function testViewingClientWithOtherPrivateNotes() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('hidden_private_notes', 'notes');
		$id = $this->fixtures['client_defaults'][0]['id'];
		
		$this->setURL('organisations/view/'.$id);
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Organisation');
		/* @var $model Tactile_Organisation */
		
		$this->assertIsA($model, 'Tactile_Organisation');		
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertIsA($model->notes,'NoteCollection');
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM notes WHERE organisation_id='.$db->qstr($id);
		$count = $db->GetOne($query);
		
		// We are an admin, so we can see everything
		$this->assertTrue($count == count($model->notes));
	}
	
	function testAddingNote() {
		$this->setJSONRequest();
		$this->setUrl('organisations/save_note/?organisation_id=1');
		$_POST = $this->getFixture('basic_note');
		$this->app->go();
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$note = $this->view->get('note');
		$this->assertIsA($note,'Note');
		
		$this->assertFixture($note, 'basic_note');
		
		$this->assertEqual($note->organisation_id,1);
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM notes';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 1);
	}
	
	function testAddingPhoneNumber() {
		$this->setJSONRequest();
		$this->setURL('organisations/save_contact/?type=phone&organisation_id=1');
		$_POST = $this->getFixture('adding_phone_number');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$contact = $this->view->get('contact');
		$this->assertIsA($contact, 'Organisationcontactmethod');
		$this->assertFixture($contact, 'adding_phone_number');
		$this->assertEqual($contact->organisation_id, 1);
	}
	
	function testAddingEmailAddress() {
		$this->setJSONRequest();
		$this->setURL('organisations/save_contact/?type=email&organisation_id=1');
		$_POST = $this->getFixture('adding_email');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$contact = $this->view->get('contact');
		$this->assertIsA($contact, 'Organisationcontactmethod');
		$this->assertFixture($contact, 'adding_email');
		$this->assertEqual($contact->organisation_id, 1);
	}
	
	function testAddingAddress() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$_SERVER['HTTP_ACCEPT'] = 'application/json';
		$this->setURL('organisations/save_address/');
		$_POST = $this->getFixture('adding_address');
		$this->app->go();
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$addresses = $this->view->get('addresses');
		$this->assertIsA($addresses, 'Tactile_OrganisationaddressCollection');
		$this->assertEqual(2, count($addresses));
		$streets = $addresses->pluck('street1');
		$this->assertEqual(array('45 Acacia Avenue', '12 Some Road'), $streets);
	}
	
	function testListingPeopleForCompany() {
		$this->setURL('organisations/people/?id=1');
		$this->app->go();
		$this->assertEqual('<',substr($this->view->output,0,1));
		$people = $this->view->get('people');
		$this->assertIsA($people, 'Omelette_PersonCollection');
		$this->assertEqual(count($people), 1);
	}
	
	function testBasicEditing() {
		$this->setURL('organisations/edit/1');
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Organisation');
		
		$this->assertIsA($model, 'Organisation');		
		$this->assertEqual($model->id, 1);		
		$this->assertTrue($model->canEdit());
	}
	
	function testEditingPrivateClientAsAdmin() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('basic_private_company', 'organisations');
	
		$this->setURL('organisations/edit/109');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}

	function testEditingPrivateClientAsNonAdmin() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('basic_private_company', 'organisations');
		
		// Replace admin checker with a foo that always returns false
		// (admins bypass permissions check)
		$this->injector->register('TestAdminChecker');
	
		$this->setURL('organisations/edit/109');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
	}
	
	
	function testEditingWithInvalidID() {
		$this->setURL('organisations/edit/999');
		$this->app->go();
		
		$f = Flash::Instance();
		$r = $this->injector->instantiate('Redirection');
		
		$this->assertTrue($f->hasErrors());
		$this->assertTrue($r->willRedirect());		
	}
	
	function testSaveOfExistingClient() {
		$this->setURL('organisations/save');
		$this->saveFixtureRows('client_defaults', 'organisations');
		
		$fixture = $this->fixtures['client_defaults'][0];
		
		$id = $fixture['id'];
		
		$_POST['Organisation'] = $fixture;
		$_POST['Organisation']['id'] = $id;
		$_POST['Organisation']['accountnumber'] = 'NEW01';
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$model = DataObject::Construct('Organisation');
		$model = $model->load($id);
		$this->assertIsA($model, 'Organisation');
		
		$this->assertEqual($model->accountnumber, 'NEW01');
		$this->assertEqual($model->name, $fixture['name']);
	}
	
	function testSaveOfExistingPrivateClientAsAdmin() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('basic_private_company', 'organisations');
	
		$this->setURL('organisations/save');
		
		$fixture = $this->fixtures['basic_private_company'][0];
		
		$id = $fixture['id'];
		
		$_POST['Organisation'] = $fixture;
		$_POST['Organisation']['id'] = $id;
		$_POST['Organisation']['accountnumber'] = 'NEW01';

		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$model = DataObject::Construct('Organisation');
		$model = $model->load($id);
		$this->assertIsA($model, 'Organisation');
		
		$this->assertEqual($model->accountnumber, 'NEW01');
		$this->assertEqual($model->name, $fixture['name']);
	}
	
	function testSaveOfExistingPrivateClientAsNonAdmin() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('basic_private_company', 'organisations');
		
		// Replace admin checker with a foo that always returns false
		// (admins bypass permissions check)
		$this->injector->register('TestAdminChecker');
	
		$this->setURL('organisations/save');
		
		$fixture = $this->fixtures['basic_private_company'][0];
		
		$id = $fixture['id'];
		
		$_POST['Organisation'] = $fixture;
		$_POST['Organisation']['id'] = $id;
		$_POST['Organisation']['accountnumber'] = 'NEW01';
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
	}
		
	function testSaveWhenAccNumExists() {
		$this->setURL('organisations/save');
		$this->saveFixtureRows('client_defaults', 'organisations');
		
		$existing = $this->fixtures['client_defaults'][0];
		
		$new = $this->fixtures['basic'];
		$new['accountnumber'] = $existing['accountnumber'];
		$_POST['Organisation'] = $new;
		
		$this->app->go();		
		$this->checkUnsuccessfulSave();		
	}
	
	function testWithBackslashes() {
		$name = 'test \ with \\\ backslashes';	//this means 2 slashes

		$_POST['Organisation'] = array(
			'name' => $name,
			'accountnumber' => 'TWB01'
		);
		
		$this->setURL('organisations/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$client = DataObject::Construct('Organisation');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM organisations"), 2);
		
		$client = $client->loadBy('name',$_POST['Organisation']['name']);
		$this->assertIsA($client,'Organisation');
		
		$this->assertEqual($client->name, $name);
		$this->assertEqual($client->accountnumber, 'TWB01');
		
		$this->assertEqual($client->assigned_to, EGS::getUsername());
		
		$this->assertNow($client->created);		
	}

	function testDeletingPrivateClientAsAdmin() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('basic_private_company', 'organisations');
		
		$this->setURL('organisations/delete/109');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}
	
	function testDeletingPrivateClientAsNonAdmin() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('basic_private_company', 'organisations');
		
		// Replace admin checker with a foo that always returns false
		// (admins bypass permissions check)
		$this->injector->register('TestAdminChecker');
	
		$this->setURL('organisations/delete/109');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
	}
}
