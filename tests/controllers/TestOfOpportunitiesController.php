<?php

class TestOfOpportunitiesController extends ControllerTest {

	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$db = DB::Instance();
		
		$this->loadFixtures('opportunities');
		$this->saveFixtureRows('default_status', 'opportunitystatus');
	}
	
	public function teardown() {
		$db = DB::Instance();
		$query = 'DELETE FROM opportunitystatus';
		$db->execute($query) or die($db->ErrorMsg());
		
		parent::teardown();
	}
	
	function testOfIndexPage() {
		$this->setURL('opportunities');
		$this->app->go();
		$this->genericPageTest();
		
		$this->assertEqual($this->app->getControllerName(),'opportunitys');
		$this->assertEqual($this->view->get('templateName'),$this->makeTemplatePath('crm/tactile_opportunitys/index'));
		
		$opportunities = $this->view->get('opportunitys');
		$this->assertFalse($opportunities==false);
		$this->assertIsA($opportunities, 'Tactile_OpportunityCollection');
		$this->assertEqual(count($opportunities),0);
	}
	
	function testNewOpportunityPage() {
		$this->setURL('opportunities/new');
		$this->app->go();
		$this->genericPageTest();
		
		$this->assertEqual($this->app->getControllerName(),'opportunitys');
		$this->assertEqual($this->view->get('templateName'),$this->makeTemplatePath('crm/tactile_opportunitys/new'));
		
		$this->assertIsA($this->view->get('Opportunity'), 'Opportunity');
	}
	
	function testSaveOpportunityBasic() {	
		$_POST['Opportunity'] = $this->getFixture('basic');
		
		$this->setURL('opportunities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$opportunity = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opportunity->getAll()), 1);
		
		$opportunity = $opportunity->loadBy('name',$_POST['Opportunity']['name']);
		$this->assertIsA($opportunity,'Opportunity');
		
		$this->assertFixture($opportunity, 'basic');
		
		$this->assertEqual($opportunity->assigned_to, EGS::getUsername());
		
		$this->assertNow($opportunity->created);
	}
	
	function testWithNoName() {
		$_POST['Opportunity'] = $this->getFixture('basic');
		unset($_POST['Opportunity']['name']);
		$this->setURL('opportunities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
	}
	
	function testWithNoEndDate() {
		$_POST['Opportunity'] = $this->getFixture('basic');
		unset($_POST['Opportunity']['enddate']);
		$this->setURL('opportunities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 0);
	}
	
	function testWithNoPost() {
		$this->setURL('opportunities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 0);
	}
	
	function testCreatedIsIgnored() {
		$this->setURL('opportunities/save');
		$_POST['Opportunity'] = $this->getFixture('with_created_set');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 1);
		
		$opp = $opp->loadBy('name',$_POST['Opportunity']['name']);
		$this->assertIsA($opp,'Opportunity');
		$this->assertEqual($opp->name, $_POST['Opportunity']['name']);
		$this->assertEqual($opp->enddate, fix_date($_POST['Opportunity']['enddate']));

		$this->assertNow($opp->created);
	}
	
	function testWithQuotes() {
		$_POST['Opportunity'] = $this->getFixture('with_quotes');
		
		$this->setURL('opportunities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 1);
		
		$opp = $opp->loadBy('name',$_POST['Opportunity']['name']);
		$this->assertIsA($opp,'Opportunity');
		
		$this->assertFixture($opp, 'with_quotes');
		
		$this->assertFalse(strpos($opp->name,'\\'));
		
		$this->assertEqual($opp->assigned_to, EGS::getUsername());
		
		$this->assertNow($opp->created);
	}
	
	function testWithAllMainFields() {
		$_POST['Opportunity'] = $this->getFixture('all_main_fields');
		
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		
		$this->setURL('opportunities/save');
		$this->app->go();
				
		$this->checkSuccessfulSave();
		
		$opportunity = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opportunity->getAll()), 1);
		
		$opportunity = $opportunity->loadBy('name',$_POST['Opportunity']['name']);
		$this->assertIsA($opportunity,'Opportunity');
		
		$this->assertFixture($opportunity, 'all_main_fields');
		
		$this->assertNow($opportunity->created);
	}
	
	function testInvalidDate() {
		$_POST['Opportunity'] = $this->getFixture('invalid_enddate');
		
		$this->setURL('opportunities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 0);
	}
	
	function testProbabilityNotNumeric() {
		$_POST['Opportunity'] = $this->getFixture('non_numeric_probability');
		
		$this->setURL('opportunities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 0);
	}
	
	function testProbabilityTooHigh() {
		$_POST['Opportunity'] = $this->getFixture('prob_over_100');
		
		$this->setURL('opportunities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 0);
	}
	
	function testProbabilityNegative() {
		$_POST['Opportunity'] = $this->getFixture('prob_negative');
		
		$this->setURL('opportunities/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 0);
	}
	
	function testSavingWithLookups() {
		$this->saveMultiFixture('custom_defaults');
		$this->setURL('opportunities/save');
		
		$_POST['Opportunity'] = $this->getFixture('basic_with_lookups');
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 1);
		
		$opp = $opp->loadBy('name', $_POST['Opportunity']['name']);
		$this->assertIsA($opp, 'Tactile_Opportunity');
		$this->assertFixture($opp, 'basic_with_lookups');
		
		$this->assertEqual($opp->status, 'Test Status');
		$this->assertEqual($opp->source, 'Test Source');
		$this->assertEqual($opp->type, 'Test Type');
	}
	
	function testAttachingToCompany() {
		$this->setURL('opportunities/save');
		$_POST['Opportunity'] = $this->getFixture('basic');
		$_POST['Opportunity']['organisation_id'] = 1;
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 1);
		
		$opp = $opp->loadBy('name', $_POST['Opportunity']['name']);
		$this->assertIsA($opp, 'Tactile_Opportunity');
		$this->assertFixture($opp, 'basic');
	}
	
	function testAttachingToNonexistantCompany() {
		$this->setURL('opportunities/save');
		$_POST['Opportunity'] = $this->getFixture('basic');
		$_POST['Opportunity']['organisation_id'] = 999;
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 0);
	}
	
	function testAttachingToSomeoneElsesCompany() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		$this->saveFixtureRows('alternative_companies', 'organisations');
		
		$this->setURL('opportunities/save');
		$_POST['Opportunity'] = $this->getFixture('basic');
		$_POST['Opportunity']['organisation_id'] = 10;
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 0);
	}
	
	function testWithInvalidLookups() {
		$this->setURL('opportunities/save');
		
		$_POST['Opportunity'] = $this->getFixture('basic_with_invalid_lookups');
		
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$opp = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opp->getAll()), 0);
	}
	
	function testViewingOpportunity() {
		$this->saveFixtureRows('default_opps', 'opportunities');
		
		$this->setURL('opportunities/view/100');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Opportunity');
		/* @var $model Tactile_Person */
		$this->assertIsA($model, 'Tactile_Opportunity');

		$this->assertEqual($model->id, 100);
		
		$this->assertEqual($model->name, 'Default Opp');
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$this->assertIsA($model->notes,'NoteCollection');
		
	}
	
	function testViewingInvalidId() {
		$this->setURL('opportunities/view/999');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
	}
	
	function testAddingNote() {
		
		$this->saveFixtureRows('default_opps', 'opportunities');
		$this->setJSONRequest();
		$this->setUrl('opportunities/save_note/?opportunity_id=100');
		$_POST = $this->getFixture('basic_note');
		
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());

		$note = $this->view->get('note');
		$this->assertIsA($note,'Note');		
		$this->assertFixture($note, 'basic_note');		
		$this->assertEqual($note->opportunity_id,100);
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM notes';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 1);
	}
	
	function testBasicEditing() {
		$this->saveFixtureRows('default_opps', 'opportunities');
		$this->setURL('opportunities/edit/100');
		$this->app->go();
		
		$this->genericPageTest();
		
		$model = $this->view->get('Opportunity');
		
		$this->assertIsA($model, 'Opportunity');		
		$this->assertEqual($model->id, 100);		
		$this->assertTrue($model->canEdit());
	}
	
	function testEditingWithInvalidID() {
		$this->setURL('opportunities/edit/999');
		$this->app->go();
		
		$f = Flash::Instance();
		$r = $this->injector->instantiate('Redirection');
		
		$this->assertTrue($f->hasErrors());
		$this->assertTrue($r->willRedirect());		
	}
	
	function testSaveOfExistingOpportunity() {
		$this->setURL('opportunities/save');
		$this->saveFixtureRows('default_opps', 'opportunities');
		
		$fixture = $this->fixtures['default_opps'][0];
		
		$id = $fixture['id'];
		
		$_POST['Opportunity'] = $fixture;
		$_POST['Opportunity']['id'] = $id;
		$_POST['Opportunity']['enddate'] = '12/03/2009';
		
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$model = DataObject::Construct('Opportunity');
		$model = $model->load($id);
		$this->assertIsA($model, 'Opportunity');
		
		$this->assertEqual($model->enddate, fix_date('12/03/2009'));
		$this->assertEqual($model->name, $fixture['name']);
	}
	
	function testSavingOpportunityWithMDYDateFormat() {
		$db = DB::Instance();
		$query = 'UPDATE users SET date_format = \'m/d/Y\' WHERE username=\'greg//tactile\'';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$_POST['Opportunity'] = $this->getFixture('basic_with_mdy_date');
		
		$this->setURL('opportunities/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$opportunity = DataObject::Construct('Opportunity');
		$this->assertEqual(count($opportunity->getAll()), 1);
		
		$opportunity = $opportunity->loadBy('name',$_POST['Opportunity']['name']);
		$this->assertIsA($opportunity,'Opportunity');
		
		$this->assertEqual($opportunity->enddate, '2008-12-20');
		
		$this->assertFixture($opportunity, 'basic_with_mdy_date');
		
		$this->assertEqual($opportunity->assigned_to, EGS::getUsername());
		
		$this->assertNow($opportunity->created);
	}
	
	function testCompanyIDInURLGetsUsed() {
		$this->setURL('opportunities/new/?organisation_id=1');
		
		$this->app->go();
		
		$this->genericPageTest();
		$db = DB::Instance();
		$query = 'SELECT * from organisations';
		$this->assertPattern('#id="opportunity_organisation" value="Default Company"#i', $this->view->output);
		$this->assertPattern('#id="opportunity_organisation_id" value="1"#i', $this->view->output);
		
	}
	
	function testPersonIDInURLGetsUsed() {
		$this->setURL('opportunities/new/?person_id=1');
		
		$this->app->go();
		
		$this->genericPageTest();
		
		$this->assertPattern('#id="opportunity_person" value="Greg Jones"#i', $this->view->output);
		$this->assertPattern('#id="opportunity_person_id" value="1"#i', $this->view->output);
	}
}
