<?php
/**
 *
 * @author gj
 */
class OmeletteModuleAdminChecker implements ModuleAdminChecking {
	
	private static $cache = array(); 
	
	/**
	 * Omelette doesn't care about different modules, there is just the 'is_admin' flag
	 * @param String $username
	 * @param String $module
	 * @return Boolean
	 */
	public function isModuleAdmin($username,$module) {
		if(!isset(self::$cache[$username])) {
			$user = DataObject::Construct('User');
			$user = $user->load($username);
			self::$cache[$username] = $user!==false && $user->is_admin == 't';
		}
		return self::$cache[$username];
	}
}
?>