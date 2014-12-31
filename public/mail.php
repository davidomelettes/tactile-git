<?php

define('DROPBOX_MAIL_SCRIPT_KEY', 'archie is a lovely woofy dog');

require_once 'Zend/Controller/Response/Http.php';

// Bootstrap application
$_SERVER['HTTP_HOST'] = 'localhost';
error_reporting(E_ALL^E_NOTICE);
define('FILE_ROOT',dirname(__FILE__).'/../');
require FILE_ROOT.'app/setup.php';

class ParseMail extends EGSCLIApplication {
	public function go() {
		require_once "Zend/Mail/Message.php";
		$mail = new Zend_Mail_Message(array('raw' => $_POST['mail']));
		
		$parser = new NewEmailParser($this->logger);
		$parser->apply($mail);
	}
}

// Do we let them in?
$response = new Zend_Controller_Response_Http();
if (empty($_POST['key']) || $_POST['key'] !== DROPBOX_MAIL_SCRIPT_KEY) {
	// Nope
	$response->setHttpResponseCode(401);
	$response->setBody('Access Denied');

} else {
	// Task setup
	$ondemand_config = array();
	require_once LIB_ROOT.'spyc/spyc.php';
	$config = Spyc::YAMLLoad(FILE_ROOT.'conf/tasks_config.yml');
	$ondemand_config = $config['ondemand_config'];
	
	// Class loading
	$injector = new Phemto();
	$injector->register('OmeletteModelLoader');
	AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'mail/');

	// Execute the task
	try {
		$task = new ParseMail($injector,$ondemand_config['ParseMail']);
		$task->go();
		$response->setBody("Finished Successfully");
	} catch(Exception $e) {
		$response->setHttpResponseCode(500);
		$response->setBody($e->getMessage() . $e->getTraceAsString());
		echo $e->getMessage();
		echo $e->getTraceAsString();
	}
}
$response->sendResponse();
