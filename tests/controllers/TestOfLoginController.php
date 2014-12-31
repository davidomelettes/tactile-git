<?php

class TestOfLoginController extends ControllerTest {

	
	
	function testLoginScreen() {
		$this->app->go();
		$this->genericPageTest();
		$this->assertEqual($this->app->getControllerName(),'index');
		$this->assertEqual($this->view->get('layout'), 'loginpage');		
		$this->assertFalse($this->view->get('flash')->hasErrors());
		$this->assertEqual($this->view->get('templateName'), $this->makeTemplatePath('login/index'));
	}
	
	function testURLWhenNotLoggedIn() {
		$this->setURL('clients');
		$this->testLoginScreen();
	}
	
	function testLogin() {
		$this->setURL('login');
		$_POST = array('username'=>'greg','password'=>'password');
		$this->app->go();
		
		$this->assertEqual($this->app->getControllerName(),'index');
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		
		$redirector = $this->injector->instantiate('Redirection');
		$this->assertTrue($redirector->willRedirect());
		
		$this->assertTrue($this->app->isLoggedIn());
	}
	
	function testLoginFail() {
		$this->setURL('login');
		
		$_POST = array('username'=>'greg','password'=>'notpassword');
		$this->app->go();
		
		$this->assertEqual($this->app->getControllerName(),'index');
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		
		$redirector = $this->injector->instantiate('Redirection');
		$this->assertTrue($redirector->willRedirect());
		
		$this->assertFalse($this->app->isLoggedIn());
	}
	
	function testForgottenPasswordScreen() {
		$this->setURL('password');
		$this->app->go();
		$this->genericPageTest();
		$this->assertEqual($this->view->get('templateName'), $this->makeTemplatePath('login/password_form'));
	}
	
	function testForgottenPassword() {
		$this->transport->expectOnce('send');
		
		$model_loader = $this->injector->instantiate('ModelLoading');
		$model_loader->useTest('User');
		
		$this->setURL('password/resetbyusername');
		$username = 'greg//tactile';
		$_POST = array('username' => $username);
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(md5('password'), Test_User::$password_generated);
		$redirector = $this->injector->instantiate('Redirection');
		$this->assertTrue($redirector->willRedirect());
	}
	
	function testForgottenPasswordWithInvalidUsername() {
		$this->transport->expectNever('send');
		
		$this->setURL('password/resetbyusername');
		$username = 'wrong//tactile';
		$_POST = array('username' => $username);
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		
		$redirector = $this->injector->instantiate('Redirection');
		$this->assertTrue($redirector->willRedirect());
	}
	
	function testForgottenPasswordWithValidUsernameInDifferentCase() {
		$this->transport->expectNever('send');
		
		$this->injector->register(new Singleton('TestDB'));
		$db = $this->injector->instantiate('DB');
		
		$this->setURL('password/resetbyusername');
		$username = 'GREG//tactile';
		$_POST['username'] = $username;
		
		$this->app->go();
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		// Returns you to the form
		$redirector = $this->injector->instantiate('Redirection');
		$this->assertTrue($redirector->willRedirect());
	}
	
	function testRemindUsernameByEmail() {
		$this->transport->expectOnce('send');
		
		$model_loader = $this->injector->instantiate('ModelLoading');
		$model_loader->useTest('User');
		
		$this->setURL('username/remindbyemail');
		$valid_email = 'foo@bar.com';
		Test_User::$valid_email = $valid_email;
		$_POST = array('email' => $valid_email);
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$redirector = $this->injector->instantiate('Redirection');
		$this->assertTrue($redirector->willRedirect());
	}
	
	function testRemindUsernameWithInvalidEmail() {
		$this->transport->expectNever('send');
		
		$model_loader = $this->injector->instantiate('ModelLoading');
		$model_loader->useTest('User');
		
		$this->setURL('username/remindbyemail');
		$valid_email = 'foo@bar.com';
		$invalid_email = 'foo@baz.com';
		Test_User::$valid_email = $valid_email;
		$_POST = array('email' => $invalid_email);
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertTrue($f->hasErrors());
		$redirector = $this->injector->instantiate('Redirection');
		$this->assertTrue($redirector->willRedirect());
	}
	
	function testRemindUsernameByEmailInDifferentCase() {
		$this->transport->expectOnce('send');
		
		$model_loader = $this->injector->instantiate('ModelLoading');
		$model_loader->useTest('User');
		
		$this->setURL('username/remindbyemail');
		$valid_email = 'FOO@bAr.coM';
		Test_User::$valid_email = $valid_email;
		$_POST = array('email' => $valid_email);
		$this->app->go();
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$redirector = $this->injector->instantiate('Redirection');
		$this->assertTrue($redirector->willRedirect());
	}
}

?>
