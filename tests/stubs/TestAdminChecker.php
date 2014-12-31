<?php
/**
 * @author de
 */
class TestAdminChecker implements ModuleAdminChecking {
	
	private static $cache = array(); 
	
	public static $return_value = FALSE;
	
	/**
	 * Omelette doesn't care about different modules, there is just the 'is_admin' flag
	 * @param String $username
	 * @param String $module
	 * @return Boolean
	 */
	public function isModuleAdmin($username,$module) {
		return self::$return_value;
	}
	
}
