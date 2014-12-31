<?php
if(!defined('FILE_ROOT')) {
	define('FILE_ROOT','./');
}
require_once FILE_ROOT.'lib/setup.php';
require_once FILE_ROOT.'lib/phemto/phemto.php';
$p=new Phemto();

$al = AutoLoader::Instance();
$al->addPath(FILE_ROOT.'omelette/lib/');
$al->addPath(FILE_ROOT.'omelette/lib/mixins/');
$al->addPath(FILE_ROOT.'omelette/lib/formatters/');
$al->addPath(FILE_ROOT.'omelette/lib/utils/');
$al->addPath(FILE_ROOT.'omelette/lib/validators/');
$al->addPath(FILE_ROOT.'omelette/lib/interfaces/');
$al->addPath(FILE_ROOT.'omelette/lib/implementations/');
$al->addPath(FILE_ROOT.'omelette/models/');
$al->addPath(FILE_ROOT.'omelette/lib/exceptions/');
$al->addPath(FILE_ROOT.'omelette/lib/autohandlers/');
$al->addPath(FILE_ROOT.'omelette/lib/usage/');
$al->addPath(FILE_ROOT.'omelette/lib/s3/');

if(!defined('USER_SPACE')) {
	list($space,) = explode('.',str_replace('http://','',SERVER_ROOT));
	Omelette::setUserSpace($space);
}

SearchHandler::$perpage_default = 30;
