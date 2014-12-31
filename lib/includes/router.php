<?php

//authentication setup
$login_type='HTMLForm'; //because this is the web front-end:
if(!isset($injector)) {
	require_once LIB_ROOT.'phemto/phemto.php';
	$injector=new Phemto();
}
$injector->register($auth_type.'Authenticator');
$injector->register($login_type.'LoginHandler');

if(defined('EGS_COMPANY_ID') && file_exists(USER_ROOT.EGS_COMPANY_ID.'/dependencies.yml')) {
	$deps = Spyc::YAMLLoad(USER_ROOT.EGS_COMPANY_ID.'/dependencies.yml');
	foreach ($deps as $interface=>$implementation) {
		$injector->register($implementation,$interface);
	}
}

/* URL Specific bits *************************************************************/
// Initialise routing (TODO: Make this site specific?)
$Router = RouteParser::Instance();
include FILE_ROOT . '/conf/routes.php';

$Router->ParseRoute(
    isset($_GET['url']) ? $_GET['url'] : ''
);
$view = new View();
//module
if(!isset($login_required))
	$login_required=true;
$modules=ModuleFactory::Factory(null,$login_required);
$module = $modules[0];
$view->set('module',strtolower($module));
$view->set('modules',$modules);
$al=&AutoLoader::Instance();
$al->addPath(file_path_concat(FILE_ROOT.'egs/controllers/',$modules));
$al->addPath(file_path_concat(CONTROLLER_ROOT,$modules));
//$scan_dirs=array_merge(array(file_path_concat(CONTROLLER_ROOT,$modules)),$scan_dirs);
//controller
$controllername=ControllerFactory::Factory($login_required);
//action
if($Router->Dispatch('action') !== null) {
	$view->assign('action',strtolower($Router->Dispatch('action')));
}
showtime("---");
$c=new $controllername($module,$view);

showtime("---");
$action=ActionFactory::Factory($c);
if ($module == 'login') {
	$actions = array('index','password','requestpassword','login','logout');
	if (!in_array(strtolower($action),$actions))
		$action = 'index';
}
showtime('pre-controller-new');
$controller = $c;
$controller->setInjector($injector);
$controller->setData($Router->Dispatch());
$controller->setData($_GET);
$controller->setData($_POST);
$controller->setTemplateName($action);





?>
