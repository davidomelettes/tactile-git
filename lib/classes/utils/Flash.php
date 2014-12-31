<?php
class Flash {
	/**
	 * Returns the singleton Flash instance
	 * 
	 * Attempts to instantiate a registered implementation of MessageStorage, but will fallback to
	 * SessionFlash for backwards compatibility
	 * @return MessageStorage
	 */
	public static function &Instance($noclear=false) {
		static $Flash;
		if(empty($Flash)) {
			global $injector;
			try {
				$Flash = $injector->instantiate('MessageStorage',array($noclear));
			}
			catch(PhemtoException $e) {
				$Flash = new SessionFlash($noclear);
			}
		}
		return $Flash;
	}	
	
}
?>
