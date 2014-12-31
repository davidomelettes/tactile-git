<?php

class TestOfUsersController extends ControllerTest {

	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$db = DB::Instance();
		
		$query = 'DELETE FROM user_company_access WHERE username IN (SELECT username FROM users WHERE person_id > 1)';
		$db->Execute($query) or die($db->ErrorMsg());
		
		
		$query = 'DELETE FROM users WHERE person_id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM people WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM organisations WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM roles WHERE id>2';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->loadFixtures('users');
	}
	
	function testOfIndexPage() {
		$this->setURL('users');
		$this->app->go();
		$this->genericPageTest();
		
		$this->assertEqual($this->app->getControllerName(),'users');
		$this->assertEqual($this->view->get('templateName'),$this->makeTemplatePath('admin/tactile_users/index'));
		
		$users = $this->view->get('users');
		$this->assertFalse($users==false);
		$this->assertIsA($users, 'Omelette_UserCollection');
		$this->assertEqual(count($users), 1);
	}
	
	function testNewUserPage() {
		
		$this->setURL('users/new');
		$this->app->go();
		$this->genericPageTest();
		
		$this->assertEqual($this->app->getControllerName(),'users');
		$this->assertEqual($this->view->get('templateName'),$this->makeTemplatePath('admin/tactile_users/new'));
		
		$this->assertIsA($this->view->get('User'), 'Omelette_User');
	}
	
	function testSaveUserBasic() {
		$_POST = $this->getFixture('basic');
		$this->transport->expectOnce('send');
		
		$this->setURL('users/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$user = DataObject::Construct('User');
		$this->assertEqual(count($user->getAll()), 2);
		
		/* @var $user Omelette_User */
		$user = $user->load($_POST['User']['username']);
		$this->assertIsA($user,'Omelette_User');
		
		$person = DataObject::Construct('Person');
		$person = $person->loadBy('surname', $_POST['Person']['surname']);
		$this->assertIsA($person, 'Tactile_Person');
		
		$this->assertFixture($user, 'basic', 'User');
		$this->assertFixture($person, 'basic', 'Person');
		
		$this->assertFalse($user->is_admin());
		$this->assertTrue($user->is_enabled());
	}
	
	function testMissingFirstname() {
		$_POST = $this->getFixture('missing_firstname');
		$this->transport->expectNever('send');
		
		$this->setURL('users/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$user = DataObject::Construct('User');
		$this->assertEqual(count($user->getAll()), 1);
		
		$db = DB::Instance();
		$person_count = $db->getOne("SELECT count(*) FROM people");
		$this->assertEqual($person_count, 1);
	}
	
	function testMissingSurname() {
		$_POST = $this->getFixture('missing_surname');
		$this->transport->expectNever('send');
		
		$this->setURL('users/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$user = DataObject::Construct('User');
		$this->assertEqual(count($user->getAll()), 1);
		
		$db = DB::Instance();
		$person_count = $db->getOne("SELECT count(*) FROM people");
		$this->assertEqual($person_count, 1);
	}
	
	function testMissingEmail() {
		$_POST = $this->getFixture('missing_firstname');
		$this->transport->expectNever('send');
		
		$this->setURL('users/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$user = DataObject::Construct('User');
		$this->assertEqual(count($user->getAll()), 1);
		
		$db = DB::Instance();
		$person_count = $db->getOne("SELECT count(*) FROM people");
		$this->assertEqual($person_count, 1);
	}
	
	function testMissingUser() {
		$_POST = $this->getFixture('missing_user');
		$this->transport->expectNever('send');
		
		$this->setURL('users/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$user = DataObject::Construct('User');
		$this->assertEqual(count($user->getAll()), 1);
		
		$db = DB::Instance();
		$person_count = $db->getOne("SELECT count(*) FROM people");
		$this->assertEqual($person_count, 1);
	}
	
	function testMissingPerson() {
		$_POST = $this->getFixture('missing_person');
		$this->transport->expectNever('send');
		
		$this->setURL('users/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();
		
		$user = DataObject::Construct('User');
		$this->assertEqual(count($user->getAll()), 1);
		
		$db = DB::Instance();
		$person_count = $db->getOne("SELECT count(*) FROM people");
		$this->assertEqual($person_count, 1);
	}
	
	function testExistingUsername() {
		$_POST = $this->getFixture('existing_username');
		$this->transport->expectNever('send');
		
		$this->setURL('users/save');
		$this->app->go();
		
		$this->checkUnsuccessfulSave();

		$user = DataObject::Construct('User');
		$this->assertEqual(count($user->getAll()), 1);
		
		$db = DB::Instance();
		$person_count = $db->getOne("SELECT count(*) FROM people");
		$this->assertEqual($person_count, 1);
	}
	
	function testWithRole() {
		$this->saveFixtureRows('default_roles', 'roles');
		$_POST = $this->getFixture('with_role');
		$this->transport->expectOnce('send');
		
		$this->setURL('users/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$user = DataObject::Construct('User');
		$this->assertEqual(count($user->getAll()), 2);
		
		/* @var $user Omelette_User */
		$user = $user->load($_POST['User']['username']);
		$this->assertIsA($user,'Omelette_User');
		
		$person = DataObject::Construct('Person');
		$person = $person->loadBy('surname', $_POST['Person']['surname']);
		$this->assertIsA($person, 'Tactile_Person');
		
		$this->assertFixture($user, 'basic', 'User');
		$this->assertFixture($person, 'basic', 'Person');
		
		$this->assertEqual($user->getRawUsername(), $_POST['User']['username'].'//'.Omelette::getUserSpace());
		
		$this->assertFalse($user->is_admin());
		$this->assertTrue($user->is_enabled());
		
		$db = DB::Instance();
		$query = 'SELECT roleid FROM hasrole WHERE username='.$db->qstr($user->getRawUsername());
		$ids = $db->GetCol($query);
		$this->assertEqual(count($ids), 3);
		$this->assertTrue(in_array(100, $ids));
	}
	
	function testWithTwoRoles() {
		$this->saveFixtureRows('default_roles', 'roles');

		$_POST = $this->getFixture('with_two_roles');
		$this->transport->expectOnce('send');
		
		$this->setURL('users/save');
		$this->app->go();
		
		$this->checkSuccessfulSave();
		
		$user = DataObject::Construct('User');
		$this->assertEqual(count($user->getAll()), 2);
		
		/* @var $user Omelette_User */
		$user = $user->load($_POST['User']['username']);
		$this->assertIsA($user,'Omelette_User');
		
		$person = DataObject::Construct('Person');
		$person = $person->loadBy('surname', $_POST['Person']['surname']);
		$this->assertIsA($person, 'Tactile_Person');
		
		$this->assertFixture($user, 'basic', 'User');
		$this->assertFixture($person, 'basic', 'Person');
		
		$this->assertEqual($user->getRawUsername(), $_POST['User']['username'].'//'.Omelette::getUserSpace());
		
		$this->assertFalse($user->is_admin());
		$this->assertTrue($user->is_enabled());
		
		$db = DB::Instance();
		$query = 'SELECT roleid FROM hasrole WHERE username='.$db->qstr($user->getRawUsername());
		$ids = $db->GetCol($query);
		$this->assertEqual(count($ids), 4);
		$this->assertTrue(in_array(100, $ids));
		$this->assertTrue(in_array(101, $ids));
	}
	
	function testWithInvalidRoles() {
		$_POST = $this->getFixture('basic');
		$_POST['role_ids'] = array('999');
		
		$this->transport->expectNever('send');
		
		$this->setURL('users/save');
		$this->app->go();

		$this->checkUnsuccessfulSave();		
		
		$user = DataObject::Construct('User');
		$this->assertEqual(count($user->getAll()), 1);
		
		$db = DB::Instance();
		$person_count = $db->getOne("SELECT count(*) FROM people");
		$this->assertEqual($person_count, 1);
	}
	
	
	function testEditingUser() {
		$db = DB::Instance();
		$this->saveFixtureRows('existing_email', 'person_contact_methods');
		$_POST = array(
			'User'=>array(
				'username'=>'greg',
				'enabled'=>'on',
				'_checkbox_exists_enabled'=>'true',
				'is_admin'=>'on',
				'_checkbox_exists_is_admin'=>'true',
				'person_id'=>'1'
			),
			'Person'=>array(
				'id'=>'1',
				'firstname'=>'Fred',
				'surname'=>'Jones',
				'email'=> array(
					'contact' => 'greg.jones@senokian.com',
					'id' => 200
				)
			)
		);
		$this->setURL('users/save');
		$this->app->go();
		$this->checkSuccessfulSave();
		
		
		
		$user = DataObject::Construct('User');
		$user = $user->load('greg');
		/* @var $user Omelette_User */
		$this->assertIsA($user, 'Omelette_User');
		$this->assertTrue($user->is_admin());
		
		$person = DataObject::Construct('Person');
		$person = $person->load(1);
		$this->assertEqual($person->firstname, $_POST['Person']['firstname']);
		$this->assertEqual($person->email, $_POST['Person']['email']['contact']);		
		
		$query = 'SELECT * FROM person_contact_methods WHERE person_id=1';
		$rows = $db->GetArray($query);
		$this->assertEqual(count($rows), 1);
		
		//cleanup
		$query = 'UPDATE people SET firstname=\'Greg\' WHERE id=1';
		$db->Execute($query) or die($db->ErrorMsg());
	}
	
}

?>
