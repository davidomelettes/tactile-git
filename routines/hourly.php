<?php
/**
 * Iterates through all files in the 'hourly' folder and executes 'go()' on them- they're expected to be
 * EGSCLIApplications
 * @author gj 
 */


$_SERVER['HTTP_HOST'] = 'localhost';
error_reporting(E_ALL);
define('FILE_ROOT',dirname(__FILE__).'/../');
require FILE_ROOT.'app/setup.php';

$injector=new Phemto();
$hourly_config = array();
require_once LIB_ROOT.'spyc/spyc.php';
$config = Spyc::YAMLLoad(FILE_ROOT.'conf/tasks_config.yml');
$hourly_config = $config['hourly_config'];


$hourly_dir = FILE_ROOT.'routines/hourly/';
$al->addPath($hourly_dir);
//grab all files in the 'hourly' dir
$files = new DirectoryIterator($hourly_dir);
$files = new DotFilter($files);

foreach($files as $file) {
	//the classname is the same as the filename
	$classname = array_shift(explode('.',$file->getFilename()));
	$task = new $classname($injector,$hourly_config[$classname]);
	$task->go();
}


?>