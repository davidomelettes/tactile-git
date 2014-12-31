<?php

error_reporting(E_ALL);
define('FILE_ROOT',dirname(__FILE__).'/../');
define('APP_SETUP_FILE', FILE_ROOT.'app/setup.php');
define('SVN_VERSION_FILE', FILE_ROOT.'app/svn.php');
if (!file_exists(SVN_VERSION_FILE)) {
	die('Failed to locate SVN version file. Please run the build script.');
}
require_once SVN_VERSION_FILE;
require_once APP_SETUP_FILE;
$injector = new Phemto();
$tactile = new Tactile($injector);
try {
	$tactile->go();
} catch (Exception $e) {
	trigger_error('Uncaught Exception: ' . $e->getMessage(), E_USER_ERROR);
	throw $e;
}
