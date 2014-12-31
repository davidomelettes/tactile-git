<?php

// @de
// This file exists because OpenID doesn't play nicely with mod_rewrite, I think

// Basic bootstrapping
error_reporting(0);
define('FILE_ROOT',dirname(__FILE__).'/../');
require FILE_ROOT.'app/setup.php';
$injector = new Phemto();
$tactile = new Tactile($injector);
$redirector = $injector->instantiate('Redirection');
$user = CurrentlyLoggedInUser::Instance();


$force = true;


// Receive an OpenID response 
require_once 'Auth/OpenID/FileStore.php';
$store = new Auth_OpenID_FileStore(OPENID_FILESTORE_DIRECTORY);
require_once 'Auth/OpenID/Consumer.php';
$consumer = new Auth_OpenID_Consumer($store);
require_once 'GApps/OpenID/Discovery.php';
$helper = new GApps_OpenID_Discovery($consumer);

$realm = 'http' . (Omelette::isHttps() ? 's' : '') . '://' . preg_replace('/$www/', '', $_SERVER['SERVER_NAME']) . '/';
$redirect_to = $realm . 'openid_login.php';
$realm = str_replace('://www.', '://*.', $realm);

$auth_response = $consumer->complete($redirect_to);
switch ($auth_response->status) {
	case Auth_OpenID_CANCEL:
		Flash::Instance()->addError('Authentication process cancelled');
		break;
	case Auth_OpenID_FAILURE:
		if (!$force) {
			Flash::Instance()->addError('Authentication failed: ' . $auth_response->message . '. Please check your email address and try again');
			break;
		}
	case Auth_OpenID_SUCCESS:
		// Search for a user with this URL
		require_once 'Zend/Auth.php';
		$auth =  Zend_Auth::getInstance();
		
		$openid = $force ? $_GET['openid_identity'] : $auth_response->getDisplayIdentifier();
		require_once 'Auth/Adapter/OpenId.php';
		$adapter = new Auth_Adapter_OpenId($openid);
		$result = $auth->authenticate($adapter);
		
		if ($result->isValid()) {
			Flash::Instance()->addMessage('Successful login');
			$identity = $result->getIdentity();
			// Keep them logged in
			RememberedUser::rememberMe($identity);
		} else {
			// Might be a first time login, is there a user matching this email address without an openid value?
			$account = new TactileAccount();
			$cc = new ConstraintChain();
			$cc->add(new Constraint('site_address','=',Omelette::getUserspace()));
			$account = $account->loadBy($cc);
			$expected_google_domain = $account->google_apps_domain;
			// Confirm we're accepting on the right domain
			if (!preg_match('/'.preg_quote($expected_google_domain).'/', $openid)) {
				Flash::Instance()->addError('Failed to login via OpenID. Incorrect domain.');
			} elseif (!empty($_SESSION['GOOGLE_APPS_EMAIL'])) {
				$db = DB::Instance();
				$found_user = $db->getOne("SELECT username FROM users WHERE username like " . $db->qstr('%//'.Omelette::getUserspace()) . " AND openid IS NULL AND google_apps_email = " . $db->qstr($_SESSION['GOOGLE_APPS_EMAIL']));
				$_SESSION['GOOGLE_APPS_EMAIL'] = '';
				if (!empty($found_user)) {
					// Make sure this openid is not already in use
					$found_openid = $db->getOne("SELECT username FROM users WHERE openid = " . $db->qstr($openid));
					if (empty($found_openid)) {
						// Safe to accept this openid for this user
						Flash::Instance()->addMessage('Successful login');
						$user = new Tactile_User();
						$user->update($found_user, 'openid', $openid);
						RememberedUser::rememberMe($found_user);
					} else {
						Flash::Instance()->addError('Failed to login via OpenID');
					}
				} else {
					Flash::Instance()->addError('Failed to login via OpenID');
				}
			} else {
				Flash::Instance()->addError('Failed to login via OpenID');
			}
		}
		break;
	default:
		Flash::Instance()->addError('An unexpected error occurred. Please check your email address and try again');
		break;
}
sendTo();

// Assuming redirection, this is how we mimic $tactile->go()
Flash::Instance()->save();
$redirector->go();
