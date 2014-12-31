<?php
class TestOfTagActions extends ControllerTest {
	
	public function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$this->loadFixtures('tagging');
	}
	
	function testRenamingToNewTag() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setURL('tags/dorename');
		$_POST = array(
			'old_tag' => 'bar',
			'new_tag' => 'foo'
		);
		
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM tag_map tm join tags t on t.id=tm.tag_id where t.name=\'foo\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 1);
		
		$query = 'SELECT count(*) FROM tag_map tm join tags t on t.id=tm.tag_id where t.name=\'bar\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 0);
		
		$query = 'SELECT count(*) FROM tags t where t.name=\'bar\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 0);
	}
	
	function testRenamingToExistingTag() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->saveFixtureRows('default_activities', 'tactile_activities');
		$this->saveFixtureRows('extra_tags', 'tags');
		$this->saveFixtureRows('extra_tag_map', 'tag_map');
		
		
		$this->setURL('tags/dorename');
		$_POST = array(
			'old_tag' => 'bar',
			'new_tag' => 'foo',
			'confirm_merge' => 1
		);		
		
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM tag_map tm join tags t on t.id=tm.tag_id where t.name=\'foo\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 2);
		
		$query = 'SELECT count(*) FROM tag_map tm join tags t on t.id=tm.tag_id where t.name=\'bar\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 0);
		
		$query = 'SELECT count(*) FROM tags t where t.name=\'bar\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 0);
	}
	
	function testRenamingToExistingTagWithItemAlreadyTagged() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->saveFixtureRows('default_activities', 'tactile_activities');
		$this->saveFixtureRows('extra_tags', 'tags');
		$this->saveFixtureRows('extra_tag_map', 'tag_map');
		
		
		$this->setURL('tags/dorename');
		$_POST = array(
			'old_tag' => 'foo',
			'new_tag' => 'baz',
			'confirm_merge' => 1
		);		
		
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM tag_map tm join tags t on t.id=tm.tag_id where t.name=\'baz\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 1);
		
		$query = 'SELECT count(*) FROM tag_map tm join tags t on t.id=tm.tag_id where t.name=\'foo\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 0);
		
		$query = 'SELECT count(*) FROM tags t where t.name=\'foo\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 0);
	}
	
	function testMergingWithoutConfirmDoesntWork() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->saveFixtureRows('default_activities', 'tactile_activities');
		$this->saveFixtureRows('extra_tags', 'tags');
		$this->saveFixtureRows('extra_tag_map', 'tag_map');
		
		
		$this->setURL('tags/dorename');
		$_POST = array(
			'old_tag' => 'bar',
			'new_tag' => 'foo'
		);		
		
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		$this->assertEqual($r->getLocation(), 'tags/merge/?old_tag=bar&new_tag=foo');
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM tag_map tm join tags t on t.id=tm.tag_id where t.name=\'foo\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 1);
		
		$query = 'SELECT count(*) FROM tag_map tm join tags t on t.id=tm.tag_id where t.name=\'bar\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 1);
		
		$query = 'SELECT count(*) FROM tags t where t.name=\'bar\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 1);
	}
	
	function testDeletingTag() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setURL('tags/delete');
		$_POST = array(
			'tag' => 'bar'
		);
		$f = Flash::Instance();
		$foo = $f->messages;
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		$db = DB::Instance();
			
		$query = 'SELECT count(*) FROM tags t where t.name=\'bar\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 0);
	}
	
	function testRenamingWithMissingOldTag() {
		$this->setURL('tags/dorename');
		$_POST = array(
			'new_tag' => 'foo'
		);
		
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$this->assertEqual(1, count($f->errors));
	}
	
	function testRenamingWithMissingNewTag() {
		$this->setURL('tags/dorename');
		$_POST = array(
			'old_tag' => 'foo'
		);
		
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$this->assertEqual(1, count($f->errors));
	}
	
	function testRenamingWithInvalidOldTag() {
		$this->setURL('tags/dorename');
		$_POST = array(
			'old_tag' => 'boo',
			'new_tag' => 'moo'
		);
		
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$this->assertEqual(1, count($f->errors));
	}
	
	function testRenamingTagToItself() {
		$this->saveFixtureRows('default_tags', 'tags');
		$this->saveFixtureRows('default_tag_map', 'tag_map');
		
		$this->setURL('tags/dorename');
		$_POST = array(
			'old_tag' => 'bar',
			'new_tag' => 'bar'
		);
		
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$this->assertEqual(1, count($f->errors));
		
		$db = DB::Instance();
		$query = 'SELECT count(*) FROM tag_map tm join tags t ON t.id=tm.tag_id where t.name=\'bar\'';
		$count = $db->GetOne($query);
		$this->assertEqual($count, 1);
	}
	
	function testDeleteByTagCreateDelayedTask() {
		// Test to see the job file for deletion by tag is written correctly
		$this->saveFixtureRows('delayed_task_tags', 'tags');
		
		// Create the companies that we'll be deleting
		$db = DB::Instance();
		$id = 700;
		while ($id < 800) {
			$name = "Delete $id";
			$query = "INSERT INTO organisations (id, name, usercompanyid, owner) VALUES ('$id', '$name', '1', 'greg//tactile')";
			$db->execute($query);
			$query = "INSERT INTO tag_map (tag_id, organisation_id, hash) VALUES ('7', '$id', 'c$id')";
			$db->execute($query);
			$query = "INSERT INTO organisation_roles (roleid, organisation_id, read, write) VALUES ('1', '$id', 'true', 'true')";
			$db->execute($query);
			$id++;
		}
		
		$_POST['tag'][] = 'delayed';
		$_POST['for'] = 'organisations';
		$_POST['confirm'] = 'yes';
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->setURL('tags/process_delete_items');
		$this->app->go();
		
		$f = Flash::Instance();
		$r = $this->injector->instantiate('Redirection');
		$loc = $r->getLocation();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(0, count($f->messages));
		$this->assertTrue($r->willRedirect());
		$this->assertEqual('tags/deletion_requested/?tag%5B0%5D=delayed&for=organisations', $loc);
		
		$expected = array(
			'for'			=> 'organisations',
			'taggable'		=> 'Organisation',
			'tag'			=> array('delayed'),
			'task_type'		=> 'DelayedTaggedItemDeletion',
			'iteration'		=> '1',
			'EGS_USERNAME'	=> 'greg//tactile',
			'EGS_COMPANY_ID'=> '1',
			'USER_SPACE'	=> 'tactile'
		);
		
		$task_data = $task_storage->read(0, false);
		$this->assertEqual($task_data, $expected);
	}
	
	function testDeleteByTagPerformDelayedTask() {
		$this->saveFixtureRows('delayed_task_tags', 'tags');
		
		// Create the companies that we'll be deleting
		$db = DB::Instance();
		$query = 'DELETE FROM organisations WHERE id>1';
		$db->Execute($query);
		$query = 'DELETE FROM tag_map WHERE tag_id>1';
		$db->Execute($query);
		$query = 'DELETE FROM organisation_roles WHERE organisation_id>1';
		$db->Execute($query);
		$id = 700;
		while ($id < 800) {
			$name = "Delete $id";
			$query = "INSERT INTO organisations (id, name, usercompanyid, owner) VALUES ('$id', '$name', '1', 'greg//tactile')";
			$db->execute($query);
			$query = "INSERT INTO tag_map (tag_id, organisation_id, hash) VALUES ('7', '$id', 'c$id')";
			$db->execute($query);
			$query = "INSERT INTO organisation_roles (roleid, organisation_id, read, write) VALUES ('1', '$id', 'true', 'true')";
			$db->execute($query);
			$id++;
		}
		
		$org = new Tactile_Organisation();
		$result = $org->load(700);
		$this->assertTrue($result);
		
		$task_data = array(
			'for'			=> 'organisations',
			'taggable'		=> 'Organisation',
			'tag'			=> array('delayed'),
			'task_type'		=> 'DelayedTaggedItemDeletion',
			'iteration'		=> '1',
			'EGS_USERNAME'	=> 'greg//tactile',
			'EGS_COMPANY_ID'=> '1',
			'USER_SPACE'	=> 'tactile'
		);
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		$task_storage->write($task_data);
		$mail = new Zend_Mail();
		$task = new DelayedTaggedItemDeletion();
		$task->setMail($mail);
		
		$this->transport->expectOnce('send');
		$task->load(0);
		$task->execute();
		
		$org = new Tactile_Organisation();
		$result = $org->load(700);
		$this->assertFalse($result);
		
		$body = $mail->getBodyText(true);
		$expected = "The deletion you requested has now been completed.=0A=0ADuring the opera=\ntion, 100 organisations tagged 'delayed' were deleted.=0A=0AThanks for y=\nour patience whilst the deletion was run.=0A=0AThe Tactile Robot";
		$this->assertEqual($body, $expected);
	}
	
	function teardown() {
		$db = DB::Instance();
		$query = 'DELETE FROM tags';
		$db->Execute($query) or die($db->ErrorMsg());
		parent::teardown();
	}
	
}
