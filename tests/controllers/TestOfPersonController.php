<?php

class TestOfPersonController extends ControllerTest {

	
	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$db = DB::Instance();
		
		$query = 'DELETE FROM notes';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM users WHERE person_id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM people WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());

		
		$this->loadFixtures('people');
		
		
	}
	
	function testOfIndexRecentlyViewedWithNoPeople() {
		$this->setURL('people/recently_viewed');
		$this->app->go();
		$this->genericPageTest();
		
		$this->assertEqual($this->app->getControllerName(),'persons');
		$this->assertEqual($this->view->get('templateName'),$this->makeTemplatePath('contacts/tactile_persons/index'));
		
		$people = $this->view->get('persons');
		$this->assertFalse($people==false);
		$this->assertIsA($people, 'ViewedItemCollection');
		$this->assertEqual(count($people),0);
	}
	
	function testOfIndexPageOnceSomethingIsViewed() {
		ViewedPage::createOrUpdate(ViewedPage::TYPE_PERSON, 1, 'greg//tactile', 'Greg Jones');

		$this->setURL('people');
		$this->app->go();
		$this->genericPageTest();
		
		$this->assertEqual($this->app->getControllerName(),'persons');
		$this->assertEqual($this->view->get('templateName'),$this->makeTemplatePath('contacts/tactile_persons/index'));
		
		$people = $this->view->get('persons');
		$this->assertFalse($people==false);
		$this->assertIsA($people, 'ViewedItemCollection');
		$this->assertEqual(count($people),1);
	}
	
	function testNewClientPage() {
		$this->setURL('people/new');
		$this->app->go();
		$this->genericPageTest();
		
		$this->assertEqual($this->app->getControllerName(),'persons');
		$this->assertEqual($this->view->get('templateName'),$this->makeTemplatePath('contacts/tactile_persons/new'));
		
		$this->assertIsA($this->view->get('Person'), 'Person');
	}
	
	function testSavePersonBasic() {		
		$_POST['Person'] = $this->getFixture('basic');
		
		$this->setURL('people/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$person = DataObject::Construct('Person');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM people"), 2);
		
		$person = $person->loadBy('surname',$_POST['Person']['surname']);
		$this->assertIsA($person,'Person');
		
		$this->assertFixture($person, 'basic');
		
		$this->assertEqual($person->assigned_to, EGS::getUsername());
		
		$this->assertNow($person->created);
	}
	
	function testWithNoFirstname() {
		$_POST['Person'] = $this->getFixture('no_firstname');
		
		$this->setURL('people/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$person = DataObject::Construct('Person');
		$person = $person->loadBy('surname',$_POST['Person']['surname']);
		$this->assertFalse($person);
	}
	
	function testWithNoSurname() {
		$_POST['Person'] = $this->getFixture('no_surname');
		
		$this->setURL('people/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$person = DataObject::Construct('Person');
		$person = $person->loadBy('firstname',$_POST['Person']['firstname']);
		$this->assertFalse($person);
	}
	
	function testSaveWithNoPost() {
		$this->setURL('people/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$person = DataObject::Construct('Person');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM people"), 1);
	}
	
	function testCreatedIsIgnored() {
		$this->setURL('people/save');
		$_POST['Person'] = $this->getFixture('with_created_set');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		$person = DataObject::Construct('Person');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM people"), 2);
		
		$person = $person->loadBy('surname',$_POST['Person']['surname']);
		$this->assertIsA($person,'Person');
		$this->assertEqual($person->firstname, $_POST['Person']['firstname']);
		$this->assertEqual($person->surname, $_POST['Person']['surname']);

		$this->assertNow($person->created);
	}
	
	function testWithQuotes() {
		$_POST['Person'] = $this->getFixture('with_quotes');
		
		$this->setURL('people/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$person = DataObject::Construct('Person');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM people"), 2);
		
		$person = $person->loadBy('surname',$_POST['Person']['surname']);
		$this->assertIsA($person,'Person');
		
		$this->assertFixture($person, 'with_quotes');
		
		$this->assertFalse(strpos($person->surname,'\\'));
		
		$this->assertEqual($person->assigned_to, EGS::getUsername());
		
		$this->assertNow($person->created);
	}
	
	function testWithAllMainFields() {
		$this->setURL('people/save');
		$_POST['Person'] = $this->getFixture('all_main_fields');
		
		$this->app->go();
		$this->checkSuccessfulSave();
		
		$person = DataObject::Construct('Person');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM people"), 2);
		
		$person = $person->loadBy('surname',$_POST['Person']['surname']);
		$this->assertIsA($person,'Person');
		
		$this->assertFixture($person, 'all_main_fields');
		
		$this->assertEqual($person->organisation, 'Default Company');
	}
	
	function testSavingWithPhoneNumber() {
		$this->setURL('people/save');
		
		$_POST['Person'] = $this->getFixture('basic_with_phone');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$person = DataObject::Construct('Person');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM people"), 2);
		
		$person = $person->loadBy('surname',$_POST['Person']['surname']);
		$this->assertIsA($person,'Person');
		
		$this->assertFixture($person, 'basic_with_phone');
		$this->assertFixture($person->phone, 'basic_with_phone', 'phone');
	}
	
	function testSavingWithPhoneMobileAndEmail() {
		$this->setURL('people/save');
		
		$_POST['Person'] = $this->getFixture('basic_with_phone_mobile_email');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$person = DataObject::Construct('Person');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM people"), 2);
		
		$person = $person->loadBy('surname',$_POST['Person']['surname']);
		$this->assertIsA($person,'Person');
		
		$this->assertFixture($person, 'basic_with_phone_mobile_email');
		
		$this->assertFixture($person->phone, 'basic_with_phone_mobile_email', 'phone');
		$this->assertFixture($person->mobile, 'basic_with_phone_mobile_email', 'mobile');
		$this->assertFixture($person->email, 'basic_with_phone_mobile_email', 'email');
	}
	
	function testViewingClient() {
		$this->setURL('people/view/1');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Person');
		/* @var $model Tactile_Person */
		$this->assertIsA($model, 'Tactile_Person');

		$this->assertEqual($model->id,1);
		
		$this->assertEqual($model->fullname, 'Greg Jones');
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertIsA($model->notes,'NoteCollection');
		
		$this->assertIsA($model->getPhoneNumbers(),'PersoncontactmethodCollection');
		$this->assertIsA($model->getMobileNumbers(),'PersoncontactmethodCollection');
		$this->assertIsA($model->getEmailAddresses(),'PersoncontactmethodCollection');
	}
	
	function testViewingInvalidId() {
		$this->setURL('people/view/999');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testViewingPersonWithNotes() {
		$this->saveFixtureRows('person_defaults', 'people');
		$this->saveFixtureRows('notes', 'notes');
		
		$id = $this->fixtures['person_defaults'][0]['id'];
		
		$this->setURL('people/view/'.$id);
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Person');
		/* @var $model Tactile_Person */
		
		$this->assertIsA($model, 'Tactile_Person');
		
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertIsA($model->notes,'NoteCollection');
		
		$this->assertEqual(count($model->notes), 1);
	}
	
	function testViewingPersonWithOwnPrivateNotes() {
		$this->saveFixtureRows('person_defaults', 'people');
		$this->saveFixtureRows('private_notes', 'notes');
		
		$id = $this->fixtures['person_defaults'][0]['id'];
		
		$this->setURL('people/view/'.$id);
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Person');
		/* @var $model Tactile_Person */
		
		$this->assertIsA($model, 'Tactile_Person');
		
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertIsA($model->notes,'NoteCollection');
		
		$this->assertEqual(count($model->notes), 1);
	}
	
	function testViewingPersonWithOtherPrivateNotes() {
		$this->saveFixtureRows('person_defaults', 'people');
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('hidden_private_notes', 'notes');
		$id = $this->fixtures['person_defaults'][0]['id'];
		
		$this->setURL('people/view/'.$id);
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Person');
		/* @var $model Tactile_Person */
		
		$this->assertIsA($model, 'Tactile_Person');		
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertIsA($model->notes,'NoteCollection');
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM notes WHERE person_id='.$db->qstr($id);
		$count = $db->GetOne($query);
		
		// We are an admin, so we should be able to see a private note
		$this->assertTrue($count == count($model->notes));
	}
	
	function testAddingNote() {
		$this->setJSONRequest();
		$this->setUrl('people/save_note/?person_id=1');
		$_POST = $this->getFixture('basic_note');
		$this->app->go();
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$note = $this->view->get('note');
		$this->assertIsA($note,'Note');
		
		$this->assertFixture($note, 'basic_note');
		
		$this->assertEqual($note->person_id,1);
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM notes';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 1);
	}
	
	function testAddingPhoneNumber() {
		$this->setJSONRequest();
		$this->setURL('people/save_contact/?type=phone&person_id=1');
		$_POST = $this->getFixture('adding_phone_number');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$contact = $this->view->get('contact');
		$this->assertIsA($contact, 'Personcontactmethod');
		$this->assertFixture($contact, 'adding_phone_number');
		$this->assertEqual($contact->person_id, 1);
	}
	
	function testAddingEmailAddress() {
		$this->setJSONRequest();
		$this->setURL('people/save_contact/?type=email&person_id=1');
		$_POST = $this->getFixture('adding_email');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$contact = $this->view->get('contact');
		$this->assertIsA($contact, 'Personcontactmethod');
		$this->assertFixture($contact, 'adding_email');
		$this->assertEqual($contact->person_id, 1);
	}
	
	function testBasicEditing() {
		$this->setURL('people/edit/1');
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Person');
		
		$this->assertIsA($model, 'Person');		
		$this->assertEqual($model->id, 1);		
		$this->assertTrue($model->canEdit());
	}
	
	function testEditingWithInvalidID() {
		$this->setURL('people/edit/999');
		$this->app->go();
		
		$f = Flash::Instance();
		$r = $this->injector->instantiate('Redirection');
		
		$this->assertTrue($f->hasErrors());
		$this->assertTrue($r->willRedirect());		
	}
	
	function testSaveOfExistingClient() {
		$this->setURL('people/save');
		$this->saveFixtureRows('person_defaults', 'people');
		
		$fixture = $this->fixtures['person_defaults'][0];
		
		$id = $fixture['id'];
		
		$_POST['Person'] = $fixture;
		$_POST['Person']['id'] = $id;
		$_POST['Person']['surname'] = 'Smith';
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$model = DataObject::Construct('Person');
		$model = $model->load($id);
		$this->assertIsA($model, 'Person');
		
		$this->assertEqual($model->surname, 'Smith');
		$this->assertEqual($model->firstname, $fixture['firstname']);
	}
	
	function testAttachingToNonExistantCompany() {
		$_POST['Person'] = $this->getFixture('basic');
		$_POST['Person']['organisation_id'] = 999;
		$this->setURL('people/save');
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$person = DataObject::Construct('Person');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM people"), 1);
	}
	
	function testWithDOBWithDMYDateFormat() {
		$db = DB::Instance();
		$query = 'UPDATE users SET date_format = \'m/d/Y\' WHERE username=\'greg//tactile\'';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$_POST['Person'] = $this->getFixture('basic_with_mdy_date');
		
		$this->setURL('people/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$person = DataObject::Construct('Person');
		$this->assertEqual(DB::Instance()->getOne("SELECT count(*) FROM people"), 2);
		
		$person = $person->loadBy('surname',$_POST['Person']['surname']);
		$this->assertIsA($person,'Person');
		
		$this->assertFixture($person, 'basic_with_mdy_date');
		
		$this->assertEqual($person->assigned_to, EGS::getUsername());
		
		$this->assertEqual($person->dob, '1984-07-31');
		
		$this->assertNow($person->created);
	}
	
	function testIndexAlphabetical() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_people', 'people');

		$this->setURL('people/alphabetical');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$collection = $this->view->get('persons');
		/* @var $collection Omelette_PersonCollection */
		$this->assertIsA($collection, 'Omelette_PersonCollection');
		
		$page_names = $collection->pluck('fullname');
		
		$expected_names = array('Bertie Aardvark', 'Antony Bumblebee', 'Charlie Catfish', 'Donald Dog', 'Greg Jones', 'Fred Smith');
		
		$this->assertEqual($page_names, $expected_names);		
	}
	
	function testIndexAssignedToMe() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_people', 'people');

		$this->setURL('people/mine/');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$collection = $this->view->get('persons');
		/* @var $collection Omelette_PersonCollection */
		$this->assertIsA($collection, 'Omelette_PersonCollection');
		
		$page_names = $collection->pluck('fullname');		
		$expected_names = array('Bertie Aardvark', 'Donald Dog', 'Greg Jones');
		
		$this->assertEqual($page_names, $expected_names);	
	}
	
	function testIndexRecentlyAdded() {
		//BCAD		
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_people', 'people');

		$this->setURL('people/recent/');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$collection = $this->view->get('persons');
		/* @var $collection Omelette_PersonCollection */
		$this->assertIsA($collection, 'Omelette_PersonCollection');
		
		$page_names = $collection->pluck('fullname');
		
		$expected_names = array('Fred Smith','Donald Dog','Bertie Aardvark','Charlie Catfish', 'Antony Bumblebee','Greg Jones');
		
		$this->assertEqual($page_names, $expected_names);	
	}
	
	function testIndexRecentlyViewed() {
		//DAG
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		
		$this->saveFixtureRows('index_test_people', 'people');
		$this->saveFixtureRows('person_views', 'recently_viewed');
		
		$this->setURL('people/recently_viewed');

		$this->app->go();

		$this->genericPageTest();
		
		$collection = $this->view->get('persons');
		/* @var $collection ViewedItemCollection */
		$this->assertIsA($collection, 'ViewedItemCollection');
		
		$page_names = $collection->pluck('fullname');
		
		$expected_names = array('Donald Dog', 'Antony Bumblebee','Greg Jones');
				
		$this->assertEqual($page_names, $expected_names);
	}
	
	function testFilterByJobTitle() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_people', 'people');
		
		$this->setURL('people/by_jobtitle/?q=IT+Manager');
		$this->app->go();
		$this->genericPageTest();
		
		$collection = $this->view->get('persons');
		/* @var $collection Omelette_PersonCollection */
		$this->assertIsA($collection, 'Omelette_PersonCollection');
		
		$page_names = $collection->pluck('fullname');
		
		$expected_names = array('Bertie Aardvark', 'Charlie Catfish');
		
		$this->assertEqual($page_names, $expected_names);
	}

	function testFilterByJobTitleSameUserCompany() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_people', 'people');
		
		// Xx Xx has the same job title as Bertie and Charlie
		$this->saveFixtureRows('cross_company', 'organisations');
		$this->saveFixtureRows('cross_company_people', 'people');
		
		$this->setURL('people/by_jobtitle/?q=IT+Manager');
		$this->app->go();
		$this->genericPageTest();
		
		$collection = $this->view->get('persons');
		/* @var $collection Omelette_PersonCollection */
		$this->assertIsA($collection, 'Omelette_PersonCollection');
		
		$page_names = $collection->pluck('fullname');
		
		$expected_names = array('Bertie Aardvark', 'Charlie Catfish');
		
		$this->assertEqual($page_names, $expected_names);
	}
	
	function testFilterByJobTitleNonTitle() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('index_test_people', 'people');
		
		$this->setURL('people/by_jobtitle/?q=X');
		$this->app->go();
		
		$collection = $this->view->get('persons');
		/* @var $collection Omelette_PersonCollection */
		$this->assertIsA($collection, 'Omelette_PersonCollection');
		
		$this->assertEqual(count($collection), 0);
	}
	
	function testFilterByTitleEmptyQuery() {
		$this->setURL('people/by_jobtitle/?q=');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		
		/* @var $r DummyRedirectHandler */
		$this->assertTrue($r->willRedirect());
	}
}

?>
