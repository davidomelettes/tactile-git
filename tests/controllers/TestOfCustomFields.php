<?php
/**
 * 
 * @author paulbain
 *
 */
class TestOfCustomFields extends ControllerTest {

	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$db = DB::Instance();
		$db->Execute("delete from custom_fields") or die("Couldn't delete from custom_fields.");
		$db->Execute("delete from custom_field_options") or die("Couldn't delete from custom_field_options.");
		$db->Execute("delete from custom_field_map") or die("Couldn't delete from custom_field_map.");
		DB::Instance()->Execute('UPDATE tactile_accounts SET current_plan_id = 4') or die('Error changing plan!');
		Omelette::setAccountPlan(4);
		$this->loadFixtures('customfields');
	}
	
	function teardown() {
		$db = DB::Instance();
		parent::tearDown();
	}

	function testCustomFieldIndexOnFreePlanOutsideTrial() {
		Omelette::setAccountPlan(3);
		Omelette::getAccount()->created = '2007-10-29 16:35:12.485317';
		$this->setURL('customfields');
		$this->app->go();
		
		$f = Flash::Instance();
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($f->hasErrors());
		$this->assertTrue($r->willRedirect());
	}
	
	function testCustomFieldIndexOnFreePlanInsideTrial() {
		DB::Instance()->Execute('UPDATE tactile_accounts SET current_plan_id = 3') or die('Error changing plan!');
		Omelette::setAccountPlan(3);
		Omelette::getAccount()->created = date('Y-m-d');
		$this->setURL('customfields');
		$this->app->go();
		
		$f = Flash::Instance();
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($f->hasErrors());
		$this->assertFalse($r->willRedirect());
		Omelette::getAccount()->created = '2007-10-29 16:35:12.485317';
	}
	
	
	
	function testCustomFieldIndexOnPaidPlan() {
		$db = DB::Instance();
		$this->setURL('customfields');
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->genericPageTest();
	}
	
	function testSaveNewCustomField(){
		$this->setURL('customfields/save/');
		
		$db = DB::Instance();
		$count = $db->getOne("select count(*) from custom_fields");
		$this->assertEqual($count,0);
		
		$_POST = array(
			'custom'=>array(
				'x123'=>array(
					'type'=>'t',
					'name'=>'Test Field',
					'organisations'=>1,
					'activities'=>1,	
					'opportunities'=>1,
					'people'=>1
				)
			)
		);
		
		$this->app->go();		
		$this->checkSuccessfulSave();
		
		$count = $db->getOne("select count(*) from custom_fields");
		$this->assertEqual($count,1);
	}

	function testSaveNewCustomFieldWithSelect(){
		$this->setURL('customfields/save/');
		
		$db = DB::Instance();

		$count = $db->getOne("select count(*) from custom_field_options");
		$this->assertEqual($count,0);
		
		$_POST = array(
			'custom'=>array(
				'x123'=>array(
					'type'=>array(
						'option'=>array(
							'x1'=> 	'One',
							'x2'=>	'Two',
							'x3'=>	'Three'
						)
					),
					'name'=>'Test Field',
					'organisations'=>1,
					'activities'=>1,	
					'opportunities'=>1,
					'people'=>1
				)
			)
		);
		
		$this->app->go();		
		$this->checkSuccessfulSave();
		
		$count = $db->getOne("select count(*) from custom_field_options");
		$this->assertEqual($count,3);
	}
	
	function testSaveNewCustomFieldWithCheckbox(){
		$this->setURL('customfields/save/');
		
		$db = DB::Instance();
		
		$_POST = array(
			'custom'=>array(
				'x123'=>array(
					'type'=>'c',
					'name'=>'Test Field',
					'organisations'=>1,
					'activities'=>1,	
					'opportunities'=>1,
					'people'=>1
				)
			)
		);
		
		$this->app->go();		
		$this->checkSuccessfulSave();
	}	
	
	function testSaveNewCustomFieldWithouNameFails(){
		$this->setURL('customfields/save/');
		
		$db = DB::Instance();
		$count = $db->getOne("select count(*) from custom_fields");
		$this->assertEqual($count,0);
		
		$_POST = array(
			'custom'=>array(
				'x123'=>array(
					'type'=>'t',
					'name'=>"",
					'organisations'=>1,
					'activities'=>1,	
					'opportunities'=>1,
					'people'=>1
				)
			)
		);

		$this->app->go();
		$this->checkUnsuccessfulSave();
	}	
	
	function testSaveNewCustomTextFieldMap(){
		$this->saveFixtureRows('basic_company','organisations');
		$this->saveFixtureRows('text_custom_field','custom_fields');
		$this->setURL('organisations/save_custom_multi/');
		$db = DB::Instance();
		
		$_POST = array(
			'custom_field'=>array(
				'x100'=>array(
					'field_id' => 100,
					'value'=>'Whakka Whakka Woo'
				)
			),
			'organisation_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,1);
	}
	
	function testSaveNewCustomCheckboxFieldMapWithEnabled(){
		$this->saveFixtureRows('basic_company','organisations');
		$this->saveFixtureRows('checkbox_custom_field','custom_fields');
		$this->setURL('organisations/save_custom_multi/');
		$db = DB::Instance();
		
		$_POST = array(
			'custom_field'=>array(
				'x100'=>array(
					'field_id' => 100,
					'enabled'=>'on'
				)
			),
			'organisation_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,1);
		
		$enabled = $db->getOne("select enabled from custom_field_map");
		$this->assertEqual($enabled,'t');
	}	
	
	function testSaveNewCustomCheckboxFieldMapWithoutEnabled(){
		$this->saveFixtureRows('basic_company','organisations');
		$this->saveFixtureRows('checkbox_custom_field','custom_fields');
		$this->setURL('organisations/save_custom_multi/');
		$db = DB::Instance();
		
		$_POST = array(
			'custom_field'=>array(
				'x100'=>array(
					'field_id' => 100
				)
			),
			'organisation_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,1);
		
		$enabled = $db->getOne("select enabled from custom_field_map");
		$this->assertEqual($enabled,'f');
	}		
	
	function testFailedSaveNewCustomFieldMapDisabledForOrganisations(){
		$this->saveFixtureRows('basic_company','organisations');
		$this->saveFixtureRows('custom_field_disabled','custom_fields');
		$this->setURL('organisations/save_custom_multi/');
		$db = DB::Instance();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,0);
		
		$_POST = array(
			'custom_field'=>array(
				'x100'=>array(
					'field_id' => 100,
					'value'=>'Whakka Whakka Woo'
				)
			),
			'organisation_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,0);
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
	}	
	
	function testSaveNewCustomTextFieldMapForPeople(){
		$this->saveFixtureRows('basic_person','people');
		$this->saveFixtureRows('text_custom_field','custom_fields');
		$this->setURL('organisations/save_custom_multi/');
		$db = DB::Instance();
		
		$_POST = array(
			'custom_field'=>array(
				'x100'=>array(
					'field_id' => 100,
					'value'=>'Whakka Whakka Woo'
				)
			),
			'person_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,1);
	}
	
	function testFailedSaveNewCustomFieldMapDisabledForPeople(){
		$this->saveFixtureRows('basic_person','people');
		$this->saveFixtureRows('custom_field_disabled','custom_fields');
		$this->setURL('people/save_custom_multi/');
		$db = DB::Instance();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,0);
		
		$_POST = array(
			'custom_field'=>array(
				'x100'=>array(
					'field_id' => 100,
					'value'=>'Whakka Whakka Woo'
				)
			),
			'person_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,0);
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
	}	
	
	function testUpdateCustomTextFieldMap(){
		$this->saveFixtureRows('basic_company','organisations');
		$this->saveFixtureRows('text_custom_field','custom_fields');
		$this->saveFixtureRows('text_custom_field_map','custom_field_map');
		$this->setURL('organisations/save_custom_multi/');
		$db = DB::Instance();
		
		$custom_field = new CustomfieldMap();
		$custom_field->load(100);
		
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,1);
		$this->assertEqual($custom_field->value,"whakka whakka woo");
		
		$_POST = array(
			'custom_field'=>array(
				'100'=>array(
					'field_id' => 100,
					'value'=>'Whakka Whakka Woo Woo'
				)
			),
			'organisation_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,1);
		
		$custom_field = new CustomfieldMap();
		$custom_field->load(100);
		$this->assertEqual($custom_field->value,"Whakka Whakka Woo Woo");
		
	}	
	
	function testUpdateCustomCheckboxFieldMapFromEnabledToDisabled(){
		$this->saveFixtureRows('basic_company','organisations');
		$this->saveFixtureRows('checkbox_custom_field','custom_fields');
		$this->saveFixtureRows('checkbox_custom_field_map_enabled','custom_field_map');
		$this->setURL('organisations/save_custom_multi/');
		$db = DB::Instance();
		
		$custom_field = new CustomfieldMap();
		$custom_field->load(100);
		
		$this->assertEqual($custom_field->enabled,"t");
		
		$_POST = array(
			'custom_field'=>array(
				'100'=>array(
					'field_id' => 100
				)
			),
			'organisation_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,1);
		
		$custom_field = new CustomfieldMap();
		$custom_field->load(100);
		$this->assertEqual($custom_field->enabled,"f");
	}	

	function testUpdateCustomCheckboxFieldMapFromDisabledToEnabled(){
		$this->saveFixtureRows('basic_company','organisations');
		$this->saveFixtureRows('checkbox_custom_field','custom_fields');
		$this->saveFixtureRows('checkbox_custom_field_map_disabled','custom_field_map');
		$this->setURL('organisations/save_custom_multi/');
		$db = DB::Instance();
		
		$custom_field = new CustomfieldMap();
		$custom_field->load(100);
		
		$this->assertEqual($custom_field->enabled,"f");
		
		$_POST = array(
			'custom_field'=>array(
				'100'=>array(
					'field_id' => 100,
					'enabled'=>'on'
				)
			),
			'organisation_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,1);
		
		$custom_field = new CustomfieldMap();
		$custom_field->load(100);
		$this->assertEqual($custom_field->enabled,"t");
	}		
	
	function testSaveNewCustomTextFieldMapForOpportunities(){
		$this->saveFixtureRows('basic_opportunity','opportunities');
		$this->saveFixtureRows('text_custom_field','custom_fields');
		$this->setURL('opportunities/save_custom_multi/');
		$db = DB::Instance();
		
		$_POST = array(
			'custom_field'=>array(
				'x100'=>array(
					'field_id' => 100,
					'value'=>'Whakka Whakka Woo'
				)
			),
			'opportunity_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,1);
	}	
	
	function testSaveNewDisabledCustomTextFieldMapForOpportunitiesFails(){
		$this->saveFixtureRows('basic_opportunity','opportunities');
		$this->saveFixtureRows('custom_field_disabled','custom_fields');
		$this->setURL('opportunities/save_custom_multi/');
		$db = DB::Instance();
		
		$_POST = array(
			'custom_field'=>array(
				'x100'=>array(
					'field_id' => 100,
					'value'=>'Whakka Whakka Woo'
				)
			),
			'opportunity_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,0);
	}	
	
	function testSaveNewCustomTextFieldMapForActivities(){
		$this->saveFixtureRows('basic_activity','tactile_activities');
		$this->saveFixtureRows('text_custom_field','custom_fields');
		$this->setURL('activities/save_custom_multi/');
		$db = DB::Instance();
		
		$_POST = array(
			'custom_field'=>array(
				'x100'=>array(
					'field_id' => 100,
					'value'=>'Whakka Whakka Woo'
				)
			),
			'activity_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,1);
	}	

	function testSaveNewDisabledCustomTextFieldMapForActivitiesFails(){
		$this->saveFixtureRows('basic_activity','tactile_activities');
		$this->saveFixtureRows('custom_field_disabled','custom_fields');
		$this->setURL('activities/save_custom_multi/');
		$db = DB::Instance();
		
		$_POST = array(
			'custom_field'=>array(
				'x100'=>array(
					'field_id' => 100,
					'value'=>'Whakka Whakka Woo'
				)
			),
			'activity_id'=>100
		);
		$this->setJSONRequest();
		$this->app->go();
		$count = $db->getOne("select count(*) from custom_field_map");
		$this->assertEqual($count,0);
	}		
	
}
