<?php

/**
 * This should never be run unless you know what you're doing!
 * @author de
 */

$_SERVER['HTTP_HOST'] = 'localhost';
error_reporting(E_ALL);
define('FILE_ROOT',dirname(__FILE__).'/../');
require FILE_ROOT.'app/setup.php';

$injector=new Phemto();
$hourly_config = array();
require_once LIB_ROOT . 'spyc/spyc.php';
$config = Spyc::YAMLLoad(FILE_ROOT.'conf/tasks_config.yml');
$never_config = $config['never_config'];


$never_dir = FILE_ROOT.'routines/never/';
$al->addPath($never_dir);
//grab all files in the 'hourly' dir
$files = new DirectoryIterator($never_dir);
$files = new DotFilter($files);

foreach($files as $file) {
	//the classname is the same as the filename
	$classname = array_shift(explode('.' , $file->getFilename()));
	$task = new $classname($injector, $never_config[$classname]);
	$task->go();
}
