<?php

// @de
// This file exists because OpenID doesn't play nicely with mod_rewrite 

// Basic bootstrapping
error_reporting(0);
define('FILE_ROOT',dirname(__FILE__).'/../');
require FILE_ROOT.'app/setup.php';
$injector = new Phemto();
$tactile = new Tactile($injector);
$redirector = $injector->instantiate('Redirection');
$user = CurrentlyLoggedInUser::Instance();
$realm = 'http' . (Omelette::isHttps() ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . '/';
if (empty($user)) {
	header('Location: ' . $realm);
	exit(0);
}
EGS::setUsername($user->getRawUsername());
EGS::setCompanyId($user->getUserCompanyId());


$force = true;


// Expect an OpenID response and verify it 
require_once 'Auth/OpenID/FileStore.php';
$store = new Auth_OpenID_FileStore(OPENID_FILESTORE_DIRECTORY);
require_once 'Auth/OpenID/Consumer.php';
$consumer = new Auth_OpenID_Consumer($store);
require_once 'GApps/OpenID/Discovery.php';
$helper = new GApps_OpenID_Discovery($consumer);

$redirect_to = $realm . 'openid_verify.php';
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
		Flash::Instance()->addMessage('Accounts linked');
		$openid = $force ? $_GET['openid_identity'] : $auth_response->getDisplayIdentifier();
		$model = $user->getModel();
		$model->openid = $openid;
		$model->save();
		break;
	default:
		Flash::Instance()->addError('An unexpected error occurred. Please check your email address and try again');
		break;
}
sendTo('preferences/password');

// Assuming redirection, this is how we mimic $tactile->go()
Flash::Instance()->save();
$redirector->go();
