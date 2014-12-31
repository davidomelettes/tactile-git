<?php
class ModuleFactory {

	public static function Factory($default_page=null,$requireLogin=true) {
		if($default_page==null)
			$default_page='dashboard';
		$router = RouteParser::Instance();
		$modules = array();
		if(!$requireLogin||isLoggedIn()) {
			if($router->Dispatch('module') !== null) {
				$modules[0]=$router->Dispatch('module');
				if($router->Dispatch('submodule') !== null) {
					$modules[1]=$router->Dispatch('submodule');
				}
			}
			else {
				$modules[0]=$default_page;
			}
		
		}
		else {
			$modules[0]='login';
		}
		$al=&AutoLoader::Instance();
		foreach($modules as $module) {
			$al->addPath(CONTROLLER_ROOT.$module.'/');
		}
		$al->addPath(CONTROLLER_ROOT.'shared/');
		return $modules;

	}


}
?>
