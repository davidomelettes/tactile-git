<?php
/**
 *Handles the determination of which Controller to use, based on the current URL and any customised routing
 */
class ControllerFactory {

	/**
	 * Uses global information ($_GET probably) to determine the Controller to instantiate and return
	 * @return string	A (probably) extended form of the Controller class
	 **/
	public static function Factory($requireLogin=true) {
		$router = RouteParser::Instance();
		if($router->Dispatch('controller') !== null) {
			$classname=ucfirst(strtolower($router->Dispatch('controller'))).'Controller';
			if(class_exists($classname)) {
				$controller=$classname;
				return $controller;
			}
		}

		$controller = 'IndexController';
		return $controller;
	}
}


?>
