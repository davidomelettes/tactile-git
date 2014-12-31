<?php
/**
 *Handles the determination of which Action to call, based on the current URL and any customised routing
 */
class ActionFactory {

	/**
	 * Uses global information ($_GET probably) to determine the action to return
	 * 
	 * @return string	the name of the action
	 **/
	
	/**
	 * Use environment information to determine the Controller to be used for execution
	 */
	public static function Factory($controller) {
		$router = RouteParser::Instance();
		$_action=$router->Dispatch('action');
		if (!empty($_action)&&$_action !== null) {
			if ($_action == 'new') {
				$action = '_new';
			} else {
				$action=ucfirst($_action);
			}
			return $action;
		}
		return 'index';
	}
}


?>
