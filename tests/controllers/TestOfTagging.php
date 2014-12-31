<?php
class TestOfTagging extends ControllerTest {

	function setup() {
		parent::setup();
		
		$db = DB::Instance();
		
		$query = 'DELETE FROM opportunities';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM tags';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->setDefaultLogin();
		$this->loadFixtures('tagging');
		
	}
	
	function testTaggingClient() {
		$this->setJSONRequest();
		$this->setURL('organisations/add_tag/?id=1');
		
		$_POST['tag'] = 'foo';
		
		$this->app->go();
		
		$this->standardTagAssertions();
		
		$tagged_item = $this->view->get('tagged_item');
		$this->assertIsA($tagged_item, 'TaggedItem');
		$this->assertTrue(in_array('foo',$tagged_item->getTags()));
	}
	
	function testTaggingPerson() {
		$this->setJSONRequest();
		$this->setURL('people/add_tag/?id=1');
		
		$_POST['tag'] = 'foo';
		
		$this->app->go();
		
		$this->standardTagAssertions();
		
		$tagged_item = $this->view->get('tagged_item');
		$this->assertIsA($tagged_item, 'TaggedItem');
		$this->assertTrue(in_array('foo',$tagged_item->getTags()));	
	}
	
	function testTaggingOpportunity() {
		
		$this->saveFixtureRows('default_opportunities', 'opportunities');
		
		$this->setJSONRequest();
		$this->setURL('opportunities/add_tag/?id=1');
		
		$_POST['tag'] = 'foo';
		
		$this->app->go();
		
		$this->standardTagAssertions();
		
		$tagged_item = $this->view->get('tagged_item');
		$this->assertIsA($tagged_item, 'TaggedItem');
		$this->assertTrue(in_array('foo',$tagged_item->getTags()));	
	}
	
	function testTaggingActivity() {
		$this->saveFixtureRows('default_activities', 'tactile_activities');
		
		$this->setJSONRequest();
		$this->setURL('activities/add_tag/?id=1');
		
		$_POST['tag'] = 'foo';
		
		$this->app->go();
		
		$this->standardTagAssertions();
		
		$tagged_item = $this->view->get('tagged_item');
		$this->assertIsA($tagged_item, 'TaggedItem');
		$this->assertTrue(in_array('foo',$tagged_item->getTags()));	
	}
	
	function testTaggingInvalidCompanyID() {
		$this->setJSONRequest();
		$this->setURL('organisations/add_tag/?id=999');
		
		$_POST['tag'] = 'foo';
		
		$this->app->go();
		
		$this->assertTrue(json_decode($this->view->output)!==false);
		$this->assertTrue(Flash::Instance()->hasErrors());
	}
	
	function testTaggingInvalidPersonID() {
		$this->setJSONRequest();
		$this->setURL('people/add_tag/?id=999');
		
		$_POST['tag'] = 'foo';
		
		$this->app->go();
		
		$this->assertTrue(json_decode($this->view->output)!==false);
		$this->assertTrue(Flash::Instance()->hasErrors());
	}
	
	function testTaggingInvalidOppID() {
		$this->setJSONRequest();
		$this->setURL('opportunities/add_tag/?id=999');
		
		$_POST['tag'] = 'foo';
		
		$this->app->go();
		
		$this->assertTrue(json_decode($this->view->output)!==false);
		$this->assertTrue(Flash::Instance()->hasErrors());
	}
	
	function testTaggingInvalidActID() {
		$this->setJSONRequest();
		$this->setURL('activities/add_tag/?id=999');
		
		$_POST['tag'] = 'foo';
		
		$this->app->go();
		
		$this->assertTrue(json_decode($this->view->output)!==false);
		$this->assertTrue(Flash::Instance()->hasErrors());
	}
	
	function testDuplicateClientTags() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setJSONRequest();
		$this->setURL('organisations/add_tag/?id=1');
		
		$_POST['tag'] = 'bar';
		
		$this->app->go();
		
		$this->assertTrue(json_decode($this->view->output)!==false);
		$this->assertTrue(Flash::Instance()->hasErrors());
	}
	
	function testAddingExistingTagToPerson() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setJSONRequest();
		$this->setURL('people/add_tag/?id=1');
		
		$_POST['tag'] = 'bar';
		
		$this->app->go();
		
		$this->standardTagAssertions();
		
		$tagged_item = $this->view->get('tagged_item');
		$this->assertIsA($tagged_item, 'TaggedItem');
		$this->assertTrue(in_array('bar',$tagged_item->getTags()));	
	}
	
	function testRemovingClientTag() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setJSONRequest();
		
		$this->setURL('organisations/remove_tag/?tag=bar&id=1');
		
		$this->app->go();

		$this->standardTagAssertions();
		$client = DataObject::Construct('Organisation');
		$client->load(1);
		$tagged_item = new TaggedItem($client);
		$tags = $tagged_item->getTags();
		$this->assertFalse(in_array('bar', $tags));
	}
	
	function testRemovingNonExistantTag() {
		$this->setJSONRequest();
		
		$this->setURL('organisations/remove_tag/?tag=no&id=1');
		
		$this->app->go();
		$hash = json_decode($this->view->output, true);
		$this->assertTrue($hash!==null);
		$this->assertTrue(isset($hash['errors']) && count($hash['errors']) > 0);
		$this->assertTrue(Flash::Instance()->hasErrors());
	}
	
	function testRemovingTagNotAttached() {
		$this->saveFixtureRows('default_tags', 'tags');
		
		$this->setJSONRequest();
		
		$this->setURL('organisations/remove_tag/?tag=bar&id=1');
		
		$this->app->go();
		
		$hash = json_decode($this->view->output, true);
		$this->assertTrue($hash!==null);
		$this->assertTrue(isset($hash['errors']) && count($hash['errors']) > 0);
		$this->assertTrue(Flash::Instance()->hasErrors());
	}
	
	function testAttachingToBadID() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setJSONRequest();
		
		$this->setURL('organisations/remove_tag/?tag=bar&id=999');
		
		$this->app->go();
		
		$hash = json_decode($this->view->output, true);
		$this->assertTrue($hash!==null);
		$this->assertTrue(isset($hash['errors']) && count($hash['errors']) > 0);
		$this->assertTrue(Flash::Instance()->hasErrors());
	}
	
	function testControllerHandlesEmptyTagList() {
		$this->setURL('people/by_tag/');
		$this->app->go();
		
		$this->genericPageTest();
	}

	function standardTagAssertions() {
		$this->assertTrue(json_decode($this->view->output)!==null);
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
	}
	
}

?>
