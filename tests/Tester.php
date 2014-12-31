<?php
define('TEST_MODE', true);

function exception_handler(Exception $e) {
	if (ob_get_level() > 0) {
		ob_flush();
	}
	echo "Uncaught exception! " . $e->getMessage() . "\n";
	echo $e->getTraceAsString() . "\n";
}

set_exception_handler('exception_handler');

if (isset($argv[1])) {
	define('DB_NAME', $argv[1]);
}


$_SERVER['HTTP_HOST'] = 'localhost';
error_reporting(E_ALL^E_NOTICE);
define('FILE_ROOT',dirname(__FILE__).'/../');
require FILE_ROOT.'app/setup.php';
if(!defined('TACTILE_GDATA_CONTACTS_PROCESSOR_URL')) {
	define('TACTILE_GDATA_CONTACTS_PROCESSOR_URL', 'https://google.tactilecrm.com/');
}
//$injector=new Phemto();



define('TEST_ROOT',FILE_ROOT.'tests/');

require_once(TEST_ROOT . 'simpletest/unit_tester.php');
require_once(TEST_ROOT . 'simpletest/reporter.php');
require_once(TEST_ROOT . 'simpletest/mock_objects.php');

AutoLoader::Instance()->addPath(TEST_ROOT);
AutoLoader::Instance()->addPath(TEST_ROOT.'stubs/');
$test_dirs = array(
	TEST_ROOT.'controllers/',
	TEST_ROOT.'routines/',
	TEST_ROOT.'validators/',
	TEST_ROOT.'formatters/',
	TEST_ROOT.'misc/'
);

$test = new GroupTest('All Tests');

$testFiles = array();
foreach($test_dirs as $dir) {
	AutoLoader::Instance()->addPath($dir);
	
	$files = new DirectoryIterator($dir);
	$files = new DotFilter($files);

	foreach($files as $file) {
		$prefix = 'TestOf';
		if (isset($argv[2])) {
			preg_replace('/^TestOf/', '' ,$argv[2]);
			$prefix .= $argv[2];
		}
		if(substr($file,0,strlen($prefix))==$prefix) {
			$testFiles[] = $file->getPathname();
		}
	}
}
asort($testFiles);
foreach ($testFiles as $file) {
	$test->addTestFile($file);
}
$test->run(new TextReporter());
