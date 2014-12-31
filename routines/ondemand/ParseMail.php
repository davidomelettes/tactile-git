#!/usr/bin/php -q
<?php

$_SERVER['HTTP_HOST'] = 'localhost';
error_reporting(E_ALL);
define('FILE_ROOT',dirname(__FILE__).'/../../');
require FILE_ROOT.'app/setup.php';

$ondemand_config = array();
require_once LIB_ROOT.'spyc/spyc.php';
$config = Spyc::YAMLLoad(FILE_ROOT.'conf/tasks_config.yml');
$ondemand_config = $config['ondemand_config'];

$injector=new Phemto();

AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'mail/');
class ParseMail extends EGSCLIApplication {
	
	public function go() {
		require_once "Zend/Mail/Message.php";
		$mail = new Zend_Mail_Message(array('raw' => $_POST['mail']));
		$action = new EmailParser($this->logger);
		$action->apply(null, $mail);
	}
	
}

if($_POST['key'] !== '1234567890') {
	print("EXIT(1): Error - key didn't match!\n");
}
else {
	// Do the script
	$task = new ParseMail($injector,$ondemand_config['ParseMail']);
	$task->go();
	print("EXIT(0): Finished successfully\n");
}

?>
