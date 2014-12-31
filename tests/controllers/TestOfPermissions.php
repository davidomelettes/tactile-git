<?php

class TestOfPermissions extends ControllerTest {
	
	function setup() {
		parent::setup();
		$this->loadFixtures('permissions');
		$this->saveFixtureRows('user_person_set', 'people');
		$this->saveFixtureRows('user_set', 'users');
		$this->saveFixtureRows('user_company_access_set', 'user_company_access');
		$this->saveFixtureRows('role_set', 'roles');
		$this->saveFixtureRows('hasrole_set', 'hasrole');
		
		$this->saveFixtureRows('organisation_set', 'organisations');
		$this->saveFixtureRows('organisation_role_set', 'organisation_roles');
		$this->saveFixtureRows('person_set', 'people');
		
		$this->saveFixtureRows('notes_set', 'notes');
	}
	
	function tearDown() {
		parent::tearDown();
		
		$query = 'DELETE FROM tactile_accounts_magic';
		DB::Instance()->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM tactile_magic';
		DB::Instance()->Execute($query) or die($db->ErrorMsg());
		
		Omelette_Magic::clearAll();
	}
	
	function testAdminCanSeePrivatePersonInOverview() {
		$this->setDefaultLogin();
		$this->setURL('people/alphabetical');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertTrue(in_array('Greg Jones', $names));
		$this->assertTrue(in_array('Charlie Chaplain', $names));
	}
	
	function testNonAdminCannotSeePrivatePersonInOverview() {
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('people/alphabetical');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertTrue(in_array('Greg Jones', $names));
		$this->assertFalse(in_array('Charlie Chaplain', $names));
	}
	
	function testAdminCanSeePrivatePersonInView() {
		$this->setDefaultLogin();
		$this->setURL('people/view/30');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('/Charlie Chaplain/', $this->view->output);
	}
	
	function testNonAdminCannotSeePrivatePersonInView() {
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('people/view/30');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		$this->assertNoPattern('/Charlie Chaplain/',$this->view->output);
	}
	
	function testAdminCanSeePrivatePersonInPublicOrganisation() {
		$this->setDefaultLogin();
		$this->setURL('people/alphabetical');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertTrue(in_array('Daniel Dangerous', $names));
	}
	
	function testAdminCanSeePrivatePersonInPublicOrganisationInView() {
		$this->setDefaultLogin();
		$this->setURL('people/view/40');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('/Daniel Dangerous/', $this->view->output);
	}
	
	function testNonAdminCanSeePrivatePersonInPublicOrganisationInView() {
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('people/view/40');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$this->assertPattern('/Daniel Dangerous/', $this->view->output);
	}
	
	function testNonAdminCanSeePrivatePersonInPublicOrganisation() {
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('people/alphabetical');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertTrue(in_array('Daniel Dangerous', $names));
	}
	
	function testAdminCanSeePersonInRestrictedOrganisation() {
		$this->setDefaultLogin();
		$this->setURL('people/alphabetical');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertTrue(in_array('Edgar Edwards', $names));
	}
	
	function testNonAdminCannotSeePersonInRestrictedOrganisation() {
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('people/alphabetical');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertFalse(in_array('Edgar Edwards', $names));
	}
	
	function testGroupedNonAdminCanSeePersonInRestrictedOrganisation() {
		$this->setDefaultLogin('denver//tactile');
		$this->setURL('people/alphabetical');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertTrue(in_array('Edgar Edwards', $names));
	}
	
	function testAdminCanSeePrivatePersonInTagSearch() {
		$this->saveFixtureRows('tag_set', 'tags');
		$this->saveFixtureRows('tag_map_set', 'tag_map');
		$this->setDefaultLogin();
		$this->setURL('people/by_tag/?tag[]=private');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertEqual(count($names), 1);
		$this->assertTrue(in_array('Charlie Chaplain', $names));
	}
	
	function testNonAdminCannotSeePrivatePersonInTagSearch() {
		$this->saveFixtureRows('tag_set', 'tags');
		$this->saveFixtureRows('tag_map_set', 'tag_map');
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('people/by_tag/?tag[]=private');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertEqual(count($names), 0);
	}
	
	function testGroupedNonAdminCanSeePublicPersonInTagSearch() {
		$this->saveFixtureRows('tag_set', 'tags');
		$this->saveFixtureRows('tag_map_set', 'tag_map');
		$this->setDefaultLogin('denver//tactile');
		$this->setURL('people/by_tag/?tag[]=public');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertEqual(count($names), 1);
		$this->assertTrue(in_array('Alfred Anderson', $names));
	}
	
	function testGroupedNonAdminCannotSeePrivatePersonInTagSearch() {
		$this->saveFixtureRows('tag_set', 'tags');
		$this->saveFixtureRows('tag_map_set', 'tag_map');
		$this->setDefaultLogin('denver//tactile');
		$this->setURL('people/by_tag/?tag[]=private');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertEqual(count($names), 0);
	}
	
	function testGroupedNonAdminCannotSeePrivatePersonInTagSearchWithPublicPeopleToo() {
		$this->saveFixtureRows('tag_set', 'tags');
		$this->saveFixtureRows('tag_map_set', 'tag_map');
		$this->setDefaultLogin('denver//tactile');
		$this->setURL('people/by_tag/?tag[]=mixed');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertEqual(count($names), 1);
		$this->assertTrue(in_array('Bob Bachelor', $names));
	}
	
	function testGroupedNonAdminCanSeePrivatePersonInOrganisationInTagSearch() {
		$this->saveFixtureRows('tag_set', 'tags');
		$this->saveFixtureRows('tag_map_set', 'tag_map');
		$this->setDefaultLogin('denver//tactile');
		$this->setURL('people/by_tag/?tag[]=orgmixed');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertEqual(count($names), 3);
		$this->assertTrue(in_array('Daniel Dangerous', $names));
	}
	
	function testNonAdminOwnerCanSeePrivatePersonInOrganisationInTagSearch() {
		$this->saveFixtureRows('tag_set', 'tags');
		$this->saveFixtureRows('tag_map_set', 'tag_map');
		$this->setDefaultLogin('david//tactile');
		$this->setURL('people/by_tag/?tag[]=orgmixed');
		$this->app->go();
		
		$people = $this->view->get('persons');
		$names = $people->pluck('fullname');
		$this->assertEqual(count($names), 3);
		$this->assertTrue(in_array('Daniel Dangerous', $names));
	}
	
	function testAdminCanSeeTagOnPrivatePerson() {
		$this->saveFixtureRows('tag_set', 'tags');
		$this->saveFixtureRows('tag_map_set', 'tag_map');
		$this->setDefaultLogin();
		$this->setURL('people/alphabetical');
		$this->app->go();
		
		$tags = $this->view->get('all_tags');
		$this->assertTrue(in_array('private', $tags));
	}
	
	function testNonAdminCannotSeeTagOnPrivatePerson() {
		$this->saveFixtureRows('tag_set', 'tags');
		$this->saveFixtureRows('tag_map_set', 'tag_map');
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('people/alphabetical');
		$this->app->go();
		
		$tags = $this->view->get('all_tags');
		$this->assertFalse(in_array('private', $tags));
	}
	
	function testAdminCanSeePrivatePersonInQuickSearch() {
		$this->setDefaultLogin();
		$this->setURL('search/?name=ch');
		$this->setAjaxRequest();
		$this->app->go();
		
		$this->assertPattern('/Charlie Chaplain/', $this->view->output);
	}
	
	function testNonAdminCannotSeePrivatePersonInQuickSearch() {
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('search/?name=ch');
		$this->setAjaxRequest();
		$this->app->go();
		
		$this->assertNoPattern('/Charlie Chaplain/', $this->view->output);
	}
	
	function testAdminCanSeePrivateNoteAgainstOrganisation() {
		$this->setDefaultLogin();
		$this->setURL('organisations/view/10');
		$this->app->go();
		
		$timeline = $this->view->get('activity_timeline');
		$titles = $timeline->pluck('title');
		$this->assertEqual(count($timeline), 1);
		$this->assertEqual($titles, array('A Private Note'));
	}
	
	function testNonAdminCannotSeePrivateNoteAgainstOrganisation() {
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('organisations/view/10');
		$this->app->go();
		
		$timeline = $this->view->get('activity_timeline');
		$titles = $timeline->pluck('title');
		$this->assertEqual(count($timeline), 0);
		$this->assertEqual($titles, array());
	}
	
	function testNonAdminInMultipleGroupsDoesNotSeeDuplicateItems() {
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('organisations/view/30');
		$this->app->go();
		
		$timeline = $this->view->get('activity_timeline');
		$titles = $timeline->pluck('title');
		$this->assertEqual(count($timeline), 1);
		$this->assertEqual($titles, array('Should not be duplicated'));
	}
	
	function testNonAdminCannotReadPersonWithNoOrgPermissions() {
		$this->setDefaultLogin('david//tactile');
		$this->setURL('people/view/60');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
	}
	
	function testNonAdminCreatorCanReadPersonWithNoOrgPermissions() {
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('people/view/60');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}
	
	function testNonAdminCannotAccessDisabledImport() {
		$query = "INSERT INTO tactile_accounts_magic (usercompanyid, key, value) VALUES ('1','permissions_import_enabled','f')";
		DB::Instance()->Execute($query) or die($db->ErrorMsg());
		
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('import');
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertFalse($r->willRedirect());
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
	}
	
	function testDefaultPermissionsPage() {
		$this->setDefaultLogin();
		$this->setURL('permissions/default_permissions');
		$this->app->go();
		$this->genericPageTest();
		$this->assertPattern('/User Permissions/', $this->view->output);
	}
	
	function testSaveDefaultPermissions() {
		$this->setDefaultLogin();
		$this->setURL('permissions/save_defaults');
		$_POST = array(
			'username'	=> 'archie',
			'Sharing'	=> array(
				'read'	=> 'private',
				'write'	=> 'private'
			)
		);
		$this->app->go();
		
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$db = DB::Instance();
		$result = $db->getOne('SELECT value FROM tactile_magic WHERE username = ' . $db->qstr('archie//tactile') . ' AND key = ' . $db->qstr('read_permissions'));
		$this->assertIdentical('private', $result);
	}
	
	function testFixedDefaultPermissionsCannotBeOverriddenByNonAdminUsers() {
		$db = DB::Instance();
		$db->execute("INSERT INTO tactile_magic (username, key, value) VALUES ('archie//tactile', 'permissions_fixed', 't')");
		$db->execute("INSERT INTO tactile_magic (username, key, value) VALUES ('archie//tactile', 'read_permissions', 'private')");
		$db->execute("INSERT INTO tactile_magic (username, key, value) VALUES ('archie//tactile', 'write_permissions', 'private')");
		
		$this->setDefaultLogin('archie//tactile');
		$this->setURL('organisations/save');
		$_POST = array(
			'Organisation' => array(
				'name'		=> 'Test Org',
				'Sharing'	=> array(
					'read'	=> 'everyone',
					'write'	=> 'everyone'
				)
			)
		);
		
		$this->app->go();
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$id = $db->getOne("SELECT id FROM organisations WHERE usercompanyid = '1' AND name LIKE 'Test%' ORDER BY created DESC LIMIT 1");
		$org = new Tactile_Organisation();
		$org->load($id);
		$this->assertEqual('Test Org', $org->name);
		$this->assertEqual('only by you', $org->getReadString());
		$this->assertEqual('only by you', $org->getWriteString());
	}
	
	function testFixedDefaultPermissionsCannotBeOverriddenByAdminUsers() {
		$db = DB::Instance();
		$db->execute("INSERT INTO tactile_magic (username, key, value) VALUES ('greg//tactile', 'permissions_fixed', 't')");
		$db->execute("INSERT INTO tactile_magic (username, key, value) VALUES ('greg//tactile', 'read_permissions', 'private')");
		$db->execute("INSERT INTO tactile_magic (username, key, value) VALUES ('greg//tactile', 'write_permissions', 'private')");
		
		$this->setDefaultLogin();
		$this->setURL('organisations/save');
		$_POST = array(
			'Organisation' => array(
				'name'		=> 'Test Org',
				'Sharing'	=> array(
					'read'	=> 'everyone',
					'write'	=> 'everyone'
				)
			)
		);
		
		$this->app->go();
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$id = $db->getOne("SELECT id FROM organisations WHERE usercompanyid = '1' AND name LIKE 'Test%' ORDER BY created DESC LIMIT 1");
		$org = new Tactile_Organisation();
		$org->load($id);
		$this->assertEqual('Test Org', $org->name);
		$this->assertEqual('only by you', $org->getReadString());
		$this->assertEqual('only by you', $org->getWriteString());
	}
	
	function testDefaultPermissionsCanBeOverridenWhenCreatingNewOrganisation() {
		$db = DB::Instance();
		$db->execute("INSERT INTO tactile_magic (username, key, value) VALUES ('greg//tactile', 'read_permissions', 'private')");
		$db->execute("INSERT INTO tactile_magic (username, key, value) VALUES ('greg//tactile', 'write_permissions', 'private')");
		
		$this->setDefaultLogin();
		$this->setURL('organisations/save');
		$_POST = array(
			'Organisation' => array(
				'name'		=> 'Test Org',
				'Sharing'	=> array(
					'read'	=> 'everyone',
					'write'	=> 'everyone'
				)
			)
		);
		
		$this->app->go();
		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$id = $db->getOne("SELECT id FROM organisations WHERE usercompanyid = '1' AND name LIKE 'Test%' ORDER BY created DESC LIMIT 1");
		$org = new Tactile_Organisation();
		$org->load($id);
		$this->assertEqual('Test Org', $org->name);
		$this->assertEqual('by everyone', $org->getReadString());
		$this->assertEqual('by everyone', $org->getWriteString());
	}
	
	function testFixedPermissionsAppearance() {
		$db = DB::Instance();
		$db->execute("INSERT INTO tactile_magic (username, key, value) VALUES ('greg//tactile', 'permissions_fixed', 't')");
		$db->execute("INSERT INTO tactile_magic (username, key, value) VALUES ('greg//tactile', 'read_permissions', 'private')");
		$db->execute("INSERT INTO tactile_magic (username, key, value) VALUES ('greg//tactile', 'write_permissions', 'private')");
		
		$this->setDefaultLogin();
		$this->setURL('organisations/new');
		$this->app->go();
		
		$this->assertPattern('/This Organisation will be Viewable <span class="permission">only by you<\/span> and Editable <span class="permission">only by you<\/span>/', $this->view->output);
	}
}
