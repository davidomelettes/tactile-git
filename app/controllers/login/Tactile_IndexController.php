<?php

require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Auth.php';

require_once 'Auth/Adapter/Db.php';

class Tactile_IndexController extends Controller {

	function __construct($module,$view) {
		parent::__construct($module,$view);
		if(!$this->view->is_json) {
			$this->view->set('layout','loginpage');
		}
		$this->setTemplateName('index');
	}

	/**
	 * This is the login page, no logic yet - but logo dependent on hostname?
	 */
	public function index() {
		$this->setTemplateName('index');
		
		$site_address = Omelette::getUserSpace();
		$db = DB::Instance();
		$google_domain = $db->getOne("SELECT google_apps_domain FROM tactile_accounts WHERE site_address = ". $db->qstr($site_address) . " LIMIT 1");
		$this->view->set('google_domain', $google_domain);
		if (!empty($google_domain)) {
			$this->view->set('wrapper_class', 'wide');
		}
		$this->view->set('logo_url', $this->_getLogoUrl());
	}
	
	protected function _getLogoUrl() {
		$site_address = Omelette::getUserSpace();
		$db = DB::Instance();
		$logo = $db->getArray("SELECT s3.id, filename FROM s3_files s3 JOIN tactile_accounts ta ON ta.id = s3.account_id JOIN account_plans ap ON ta.current_plan_id = ap.id WHERE (ap.cost_per_month > 0 OR ta.created <= (now() - interval '14 days')) AND ta.site_address = " . $db->qstr($site_address));
		if (!empty($logo)) {
			$logo = $logo[0];
			$co_id = $db->getOne("SELECT organisation_id FROM tactile_accounts WHERE site_address = " . $db->qstr($site_address));
			$protocol = (empty($_SERVER['HTTP_X_FARM']) || $_SERVER['HTTP_X_FARM'] != 'HTTPS') ? 'http' : 'https';
			$logo_url = $protocol . '://s3.amazonaws.com/tactile_public/' .
				$co_id . '/' . $logo['id'] . '/' . $logo['filename'];
			return $logo_url;
		} else {
			return false;
		}
	}
	
	/**
	 * Perform a login attempt
	 * - implementation is injected, Tactile uses username/hostname/password combinations
	 * - updates last_login
	 * - if box is ticked, does the remembered-user stuff
	 * - retains the URL that led to the login screen, and puts the user back there (when appropriate)
	 */
	public function login() {
		$db = DB::Instance();
		$injector=$this->_injector;
		//$authentication=$injector->Instantiate('LoginHandler');

		$username = $this->_data['username'];
		$password = $this->_data['password'];
		$userspace = Omelette::getUserSpace();
		$authAdapter = new Auth_Adapter_Db($username, $password, $userspace);
		
		$auth = Zend_Auth::getInstance();
		
		$result = $auth->authenticate($authAdapter);
		if ($result->isValid()) {
			$db->execute("INSERT INTO user_logins (entered_username, site_address, was_successful) VALUES (".$db->qstr($username).", ".$db->qstr($userspace).", 'true')");
			$identity = $result->getIdentity();
			$user = new Tactile_User();
			
			if(false === $user->load($identity)) {
				
			}
			
			$controller = (!empty($_POST['controller'])) ? $_POST['controller'] : '';
			$module = (!empty($_POST['module'])) ? $_POST['module'] : '';

			if($controller == '' && 
				(empty($_POST['redirect']) || $_POST['redirect'] == '/') && 
				!$user->hasLoggedInBefore()) {
					
				if(isset($_POST['redirect']) 
					&& $_POST['redirect'] == '/') {
						$_POST['redirect'] = 'welcome/';
				}
			}
			
			$user->update($identity, 'last_login', date('Y-m-d H:i:s'));
			
			if(!empty($_POST['submodule'])) {
				$module=array($module,$_POST['submodule']);
			}
			$action = (!empty($_POST['action']) && $_POST['action'] <> 'login') ? $_POST['action'] : '';
			
			if (isset($this->_data['rememberUser'])) {
				RememberedUser::rememberMe($identity);
			}
			unset($_POST['controller']);
			unset($_POST['module']);
			unset($_POST['action']);
			unset($_POST['username']);
			unset($_POST['password']);
			
			sendTo($controller,$action,$module,$_POST);
		}
		else {
			$log = new Zend_Log(new Zend_Log_Writer_Stream(DATA_ROOT.'/application.log'));
			$log->info('Failed Login Attempt: host="'.SERVER_ROOT.'", username="'.$_POST['username'].'", password="'.$_POST['password'].'"');
			$db->execute("INSERT INTO user_logins (entered_username, site_address, was_successful) VALUES (".$db->qstr($username).", ".$db->qstr($userspace).", 'false')");
			$flash=Flash::Instance();
			$flash->addError('Incorrect username/password combination, please try again'); 
			sendTo();
		}
	}
	
	/**
	 * Performs a logout
	 * - destroy the session
	 * - remove any remembered-ness
	 * - send back to index
	 */
	function logout() {
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		if(RememberedUser::is_remembered()){
			RememberedUser::destroyMemory();
		}
		sendTo();
		return;
	}
	
	/**
	 * This is the "I've forgotten my password" page, no logic
	 */
	function password_form() {
		$this->view->set('wrapper_class', 'wide');
		$this->view->set('site_address', Omelette::getUserSpace());
		$this->view->set('logo_url', $this->_getLogoUrl());
	}
	
	/**
	 * Performs a password-reset & sends the new password to the user
	 */
	function resetByUsername() {
		if(empty($this->_data['username'])) {
			Flash::Instance()->addError('Please enter your username');
			sendTo('password');
			return;
		}
		//$user = Omelette_User::loadByEmail($this->_data['email']);
		$user = DataObject::Construct('User');
		/* @var $user Omelette_User */
		$user = $user->load($this->_data['username']);
		if ($user===false) {
			Flash::Instance()->addError('Couldn\'t find a user with that username');
			sendTo('password');
			return;
		} elseif (!$user->is_enabled()) {
			Flash::Instance()->addError('That user is disabled');
			sendTo('password');
			return;
		}
		$password = $user->setPassword();
		$mail = new Omelette_Mail('password_reset');
		
		$mail->getView()->set('User',$user);
		$mail->getView()->set('password',$password);
		$mail->getView()->set('login_url', 'http://' . Omelette::getUserSpace() . '.tactilecrm.com');
		
		$mail->getMail()
			->addTo($user->getEmail())
			->setFrom(TACTILE_EMAIL_FROM,TACTILE_EMAIL_NAME)
			->setSubject('Tactile CRM: Password Reset');
		
		$mail->send();
		Flash::Instance()->addMessage('An email has been sent to '.$user->getEmail().' with your new password');
		sendTo();
		return;
	}
	
	/**
	 * Emails the user their username if a single user can be found using the supplied email address
	 */
	public function remindByEmail() {
		if(empty($this->_data['email'])) {
			Flash::Instance()->addError('Please enter your email address');
			sendTo('password');
			return;
		}
		
		$mail = new Omelette_Mail('username_reminder');
		
		$user = DataObject::Construct('User');
		/* @var $user Omelette_User */
		$user = $user->loadByEmail($this->_data['email']);
		if ($user===false) {
			$db = DB::Instance();
			$users = $db->getCol("SELECT username FROM users u LEFT JOIN people p ON p.id = u.person_id LEFT JOIN person_contact_methods pcm ON p.id = pcm.person_id AND pcm.main AND pcm.type = 'E' WHERE u.enabled AND pcm.contact = " . $db->qstr($this->_data['email']) . " AND username LIKE " . $db->qstr('%//'.Omelette::getUserspace()));
			if (!empty($users) && is_array($users)) {
				$user_models = array();
				foreach ($users as $username) {
					$user_model = new Omelette_User();
					if ($user_model->load($username)) {
						$user_models[] = $user_model;
					}
				}
				$mail->getView()->set('users', $user_models);
				$mail->getView()->set('multiple_users', true);
			} else {
				Flash::Instance()->addError('Couldn\'t find a user with that email address, or more than one user shares that email address');
				sendTo('password');
				return;
			}
		} else {
			$mail->getView()->set('User',$user);
		}
		
		$mail->getView()->set('login_url', 'http://' . Omelette::getUserSpace() . '.tactilecrm.com');
		
		$mail->getMail()
			->addTo($this->_data['email'])
			->setFrom(TACTILE_EMAIL_FROM,TACTILE_EMAIL_NAME)
			->setSubject('Tactile CRM: Username Reminder');
		
		$mail->send();
		Flash::Instance()->addMessage('An email has been sent to '.$this->_data['email'].' with your username');
		sendTo();
		return;
	}
	
	/**
	 * These are all here so that it doesn't pass up to Controller and give nasty errors
	 */
	
	public function _new() {
		$this->index();
	}
	
	public function edit() {
		$this->index();
	}
	
	public function view() {
		$this->index();
	}
	
	public function delete() {
		$this->index();
	}
	
	public function save() {
		$this->index();
	}
	
	function __call($method,$args) {
		$this->index();
	}

}

