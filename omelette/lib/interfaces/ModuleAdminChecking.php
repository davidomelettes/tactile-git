<?php
/**
 * Implementations will be asked whether or not a username should be treated as a module-admin
 * @author gj
 */
interface ModuleAdminChecking {
	
	/**
	 * Return true iff the supplied user is a module admin for the supplied module
	 * @param String $username
	 * @param String $module
	 */
	public function isModuleAdmin($username,$module);
	
}
?>