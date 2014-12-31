<?php

/**
 * Responsible for user-interaction with various settings they can change.
 *
 * @author gj
 */
class PreferencesController extends Controller {
	
	public function password() {
		$this->index();
		$this->view->set('pref_view', 'password');
	}
	
	public function dashboard() {
		$this->index();
		$this->view->set('pref_view', 'dashboard');
	}
	
	public function email() {
		$this->index();
		$this->view->set('pref_view', 'email');
	}
	
	public function date_time() {
		$this->index();
		$this->view->set('pref_view', 'date_time');
	}
	
	public function keys() {
		$this->index();
		$this->view->set('pref_view', 'keys');
	}
	
	/**
	 * Shows the preference form(s) to the user
	 * - change password
	 * - dropbox key change/display
	 * 
	 * @return void
	 */
	function index() {
		$this->setTemplateName('index');
		$user = CurrentlyLoggedInUser::Instance();
		$user_model = $user->getModel();
		$account = $user->getAccount();
		
		// Welcome message
		$hidden = Omelette_Magic::getAsBoolean(
			'hide_welcome_message',
			$user->getRawUsername()
		);
		$this->view->set('display_welcome_message_box',$hidden);
		
		// Password
		$this->view->set('pref_view', 'password');
		$google_domain = $account->google_apps_domain;
		if (!empty($google_domain)) {
			$this->view->set('google_domain', $google_domain);
			$openid = $user_model->openid;
			if (!empty($openid)) {
				$google_email = $user_model->google_apps_email;
				$this->view->set('google_email', $google_email);
				$this->view->set('openid', $openid);
			}
		}
		
		// Dashboard
		$dashboard_prefs = TimelinePreference::getAll(EGS::getUsername());
		$this->view->set('dashboard_prefs', $dashboard_prefs);
		
		// Email
		$send_missing_contact_email = Omelette_Magic::getAsBoolean(
			'send_missing_contact_email',
			$user->getRawUsername()
		);
		$this->view->set('send_missing_contact_email',$send_missing_contact_email);
		$prefs = EmailPreference::getAll(EGS::getUsername());
		$this->view->set('email_prefs', $prefs);
		
		$this->view->set('tactilemail_user_addresses', Tactile_AccountMagic::getAsBoolean('tactilemail_user_addresses', 't', 't'));
		
		$user_role = $user->getUserRole();
		$emails = new TactileEmailAddressCollection();
		$sh = new SearchHandler($emails, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('role_id', '=', $user_role->id));
		$emails->load($sh);
		$this->view->set('emails', $emails);
		
		// Date/Time
		$date_format = strtolower(str_replace('/','',$user_model->date_format));
		$this->view->set('date_format', $date_format);
		$this->view->set('time_format', Omelette_Magic::getValue('time_format', CurrentlyLoggedInUser::Instance()->getRawUsername(), '24h'));
		
		require_once LIB_ROOT.'spyc/spyc.php';
		$file = FILE_ROOT.'conf/timezones.yml';
		$zones = Spyc::YAMLLoad($file);
		//timezones grouped into "common european", "north american" and "all"
		$this->view->set('europe_timezones', $zones['europe_main']);
		$this->view->set('america_timezones', $zones['america_main']);
		$this->view->set('all_timezones', $zones['all']);
		
		//doing this so if one of the common TZs is current, then the first instance will be selected,
		//rather than the repeat in the full list
		if(isset($zones['europe_main'][$user_model->timezone])) {
			$this->view->set('europe_selected', $user_model->timezone);
		}
		elseif(isset($zones['america_main'][$user_model->timezone])) {
			$this->view->set('america_selected', $user_model->timezone);
		}
		else {
			$this->view->set('all_selected', $user_model->timezone);
		}
		
		// Keychain
		$this->view->set('dropboxkey', $user_model->dropboxkey);
		$this->view->set('webkey', $user_model->webkey);
		$this->view->set('api_token', $user_model->api_token);
		$this->view->set('api_enabled', $account->isApiEnabled());
		
		// Entanet
		if($account->isEntanetEnabled()) {
			$this->view->set('show_entanet', true);
			$extension = $user_model->getEntanetExtension();
			$this->view->set('entanet_extension', $extension);
		}
	}
	
	function change_datetime() {
		$user = CurrentlyLoggedInUser::Instance()->getModel();
		
		if(!empty($this->_data['date_format'])) {
			$date_format = $this->_data['date_format'];
			
			if($date_format == 'mdy') {
				$user->date_format = 'm/d/Y';
			}
			else {
				$user->date_format = 'd/m/Y';
			}
		}
		
		$time_format = (!empty($this->_data['time_format']) && $this->_data['time_format'] == '12h') ? '12h' : '24h';
		Omelette_Magic::saveChoice('time_format', $time_format, CurrentlyLoggedInUser::Instance()->getRawUsername());
		
		if(!empty($this->_data['timezone'])) {
			$timezone = $this->_data['timezone'];
			$tz = @timezone_open($timezone);	//can't find any other way to validate timezone...
			if($tz===false) {
				Flash::Instance()->addError("Invalid timezone selected");
				sendTo('preferences', 'date_time');
				return;
			}
			$user->timezone = $timezone;
		}
		$success = $user->save();
		if($success===false) {
			Flash::Instance()->addError("There was a problem saving your preferences, please try again");
		}
		else {
			Flash::Instance()->addMessage("Preferences changed successfully");
		}
		sendTo('preferences', 'date_time');
		return;
	}
	
	/**
	 * Carries out a password-change for the user
	 * - checks current password is entered and is correct
	 * - checks 2 new passwords match
	 * - destroys any remembered-user entries
	 * - sends back to preference page
	 * 
	 * @return void
	 */
	public function change_password() {
		$flash = Flash::Instance();
		if(!$this->is_post) {
			sendTo();
		}
		$changer = new PasswordChanger();
		$success = $changer->changePassword(CurrentlyLoggedInUser::Instance(), $this->_data);
		sendTo('preferences','index','tactile');
	}
	
	public function email_preferences() {		
		if(!isset($this->_data['email_prefs'])) {
			$this->_data['email_prefs'] = array();
		}
		EmailPreference::setAll($this->_data['email_prefs'], EGS::getUsername());
		Flash::Instance()->addMessage('Mail preferences altered, changes are reflected below');
		sendTo('preferences', 'email');
	}
	
	/**
	 * Generates a new key for the user
	 * 
	 * @return void
	 */
	public function gen_key() {
		$user = CurrentlyLoggedInUser::Instance()->getModel();
		$action = $this->_data['submit_key'];
		switch ($action) {
			case 'New Dropbox Address':
				$user->dropboxkey = Omelette_User::generateDropBoxKey();
				if($user->save()!==false) {
					Flash::Instance()->addMessage('Dropbox Address changed successfully');
				}
				break;
			case 'New Subscription Key':
				$user->webkey = Omelette_User::generateWebKey();
				if($user->save()!==false) {
					Flash::Instance()->addMessage('Calendar URL changed successfully');
				}
				break;
			case 'New API Token':
				$user->api_token = Omelette_User::generateApiToken();
				if (FALSE !== $user->save()) {
					Flash::Instance()->addMessage('New API Token generated successfully');
				}
				break;
		}
		sendTo('preferences', 'keys');
	}
	
	/**
	 * Lets users choose whether or not the 'welcome message' is shown on the dashboard 
	 * (In case they hide it, and want it back again...)
	 *
	 * @return void
	 */
	public function toggle_welcome_message() {
		Omelette_Magic::toggleChoice(
			'hide_welcome_message',
			CurrentlyLoggedInUser::Instance()->getRawUsername()
		);
		sendTo('preferences');
	}
	
	/**
	 * Lets users choose whether or not missing contact emails are sent 
	 * (In case they stop them, and want them back again...)
	 *
	 * @return void
	 */
	public function toggle_send_missing_contact_email() {
		Omelette_Magic::toggleChoice(
			'send_missing_contact_email',
			CurrentlyLoggedInUser::Instance()->getRawUsername()
		);
		sendTo('preferences');
	}
	
	/**
	 * Sets the dashboard timeline options
	 */
	public function filter_dashboard() {
		if (!isset($this->_data['dashboard'])) {
			$this->_data['dashboard'] = array();
		}
		$prefs = $this->_data['dashboard'];
		TimelinePreference::setAll($prefs, EGS::getUsername());
		Omelette_Magic::saveChoice('dashboard_timeline_restriction', 'custom', EGS::getUsername());
		Flash::Instance()->addMessage('Dashboard preferences altered, changes are reflected below');
		sendTo();
	}
	
	function options() {
		if (!$this->view->is_json) {
			sendTo('preferences');
			return;
		}
		$user = new Omelette_User();
		$this->view->set('users', $user->getAll());
		$group = new Omelette_Role();
		$this->view->set('groups', $group->getAll());
		
		require_once LIB_ROOT.'spyc/spyc.php';
		$file = FILE_ROOT.'conf/timezones.yml';
		$zones = Spyc::YAMLLoad($file);
		//timezones grouped into "common european", "north american" and "all"
		$this->view->set('timezones', $zones['all']);
	}
	
	public function save_email_address() {
		$this->setTemplateName('save');
		$errors = array();
		if (FALSE === Tactile_AccountMagic::getAsBoolean('tactilemail_user_addresses', 't', 't')) {
			 $errors[] = 'User-specified Email Addresses are not enabled on this account. Please contact your account administrator.';
			 Flash::Instance()->addErrors($errors);
			 return;
		}
		
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		
		$email_data = isset($this->_data['TactileEmailAddress']) ? $this->_data['TactileEmailAddress'] : array();
		$email_data['role_id'] = $user->getUserRole()->id; 
		
		// Does this address already exist? (May be resubmitting the validation form)
		$emails = new TactileEmailAddressCollection();
		$sh = new SearchHandler($emails, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('email_address', '=', $email_data['email_address']));
		$sh->addConstraint(new Constraint('role_id', '=', $email_data['role_id']));
		$emails->load($sh);
		if (count($emails) > 0) {
			$email_id = $emails->current()->id;
			$email_data['id'] = $email_id;
		}
		
		if (isset($email_data['verify_code'])) {
			unset($email_data['verify_code']);
		}
		if (isset($email_data['verified_at'])) {
			unset($email_data['verified_at']);
		}
		$saver = new ModelSaver();
		
		// Wrap the save inside a try...catch block because we have a multi-column unique constraint on the table that DO can't handle
		try {
			$email = $saver->save($email_data, 'TactileEmailAddress', $errors);
		} catch (Exception $e) {
			$email = FALSE;
			$errors[] = 'Failed to save Email Address';
		}
		if (FALSE !== $email) {
			// Send verification email
			$email->sendVerificationEmail();
		} else {
			Flash::Instance()->addErrors($errors);
		}
	}
	
	public function delete_email_address() {
		ModelDeleter::delete(new TactileEmailAddress(),'Address',array('preferences', 'email'));
	}
	
	public function send_validation_email() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		
		$id = !empty($this->_data['id']) ? $this->_data['id'] : '';
		$address = new TactileEmailAddress();
		if (FALSE === $address->load($id) || !$address->canEdit()) {
			Flash::Instance()->addError("You don't have permission to do that.");
			sendTo('preferences/email');
			return;
		}
		if ($address->sendVerificationEmail()) {
			Flash::Instance()->addMessage("Verification email sent.");
		} else {
			Flash::Instance()->addError("Error sending re-validation email. Please try again.");
		}
		sendTo('preferences/email');
	}
	
	public function save_google_login() {
		// Perform some basic sanity checks
		if (empty($this->_data['google_login'])) {
			Flash::Instance()->addError("Email address cannot be blank");
			sendTo('preferences/password');
			return;
		}
		$email_address = trim($this->_data['google_login']);
		if (!preg_match('/@(.+)/', $email_address, $matches)) {
			Flash::Instance()->addError("Please enter a valid email address");
			sendTo('preferences/password');
			return;
		}
		$domain = $matches[1];
		
		// Domain must match account's domain
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		if ($domain !== $account->google_apps_domain) {
			Flash::Instance()->addError("Please enter a " . $account->google_apps_domain . ' email address');
			sendTo('preferences/password');
			return;
		} 
		
		// Save the value they entered, because we can't retrieve it later
		// We also have to trust the user enters their email address truthfully, as only the domain part is used in authentication 
		$user_model = $user->getModel();
		$user_model->google_apps_email = $email_address;
		$user_model->save();

		// Start the authentication process
		// We are using Google's magic discovery extensions with the JanRain OpenID library
		require_once 'Auth/OpenID/FileStore.php';
		$store = new Auth_OpenID_FileStore(OPENID_FILESTORE_DIRECTORY);
		require_once 'Auth/OpenID/Consumer.php';
		$consumer = new Auth_OpenID_Consumer($store);
		require_once 'GApps/OpenID/Discovery.php';
		$helper = new GApps_OpenID_Discovery($consumer);
		
		// Prepare the auth request
		$auth_request = $consumer->begin($domain);
		if (empty($auth_request)) {
			Flash::Instance()->addError('Authentication Error: Invalid OpenID. Please check your email address and try again.');
			sendTo('preferences/password');
			return;
		}
		
		// Where are we going to send the user to on return from the IDP?
		$realm = 'http' . (Omelette::isHttps() ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . '/';
		$redirect_to = $realm . 'openid_verify.php';
		
		//$realm = 'http://localhost/~dave/';
		//$redirect_to = 'http://localhost/~dave/finish_auth.php';
		
		// Generate a magic URL...
		$redirect_url = $auth_request->redirectURL($realm, $redirect_to);
		if (Auth_OpenID::isFailure($redirect_url)) {
			Flash::Instance()->addError('Failed to redirect to authentication server: ' . $redirect_url->message);
			sendTo('preferences/password');
			return;
		} else {
			// ...and send the user there
			header('Location: '.$redirect_url);
		}
	}
	
	public function unlink_google_login() {
		$user = CurrentlyLoggedInUser::Instance();
		$model = $user->getModel();
		$model->openid = '';
		$model->save();
		
		Flash::Instance()->addMessage('Accounts unlinked');
		sendTo('preferences/password');
	}
	
}
