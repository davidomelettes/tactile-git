<?php
class UserPreferences {
	private $username;
	private $preferences=array();
	private $prefs=array();
	private function __construct($username) {
		$this->username=$username;
		$this->initialise();
	}
	
	
	public static function &instance($username = EGS_USERNAME) {
		static $instance;
		if ($instance==null) {
			if  (empty($username)) {
				throw new Exception('UserPreferences::instance() requires username');				
			}
			$instance = new UserPreferences($username);
		}
		
		return $instance;
	}
	
	protected function initialise() {
		$db = DB::Instance();
		$query = 'SELECT module,settings FROM userpreferences WHERE username='.$db->qstr($this->username);
		$this->prefs = $db->GetAssoc($query);
	}
	
	
	public function userHasPreferences() {
		foreach($this->prefs as $module=>$prefs) {
			if(substr($module,0,1)!=='_'&&!empty($prefs)) {
				return true;
			}
		}
		return false;
	}

	public function userCanSetPreferences() {
		$accessObject = &AccessObject::Instance(EGS_USERNAME);
		$modules = $accessObject->tree;
		foreach ($modules as $module) {
			if ($module['name'] == 'egs') {
				// EGS isn't really a module - don't show this
				continue;
			}
			
			// FIXME: Only show module if preferences file exists
			if (!file_exists(FILE_ROOT . '/app/controllers/' . strtolower($module['name']) . '/Preferences.php')) {
				continue;
			}
			
			if ($accessObject->hasPermission(array($module['name']))) {
				return true;
			}
		}
		return false;
	}

	public function getPreferenceValue($name, $module='home') {
		/*if nothing in the module, try for a default*/
		if(!isset($this->prefs[$module])) {
			return $this->getDefault($name,$module);
		}
		
		/*the preferences are serialised and base64'd in the database, so decode*/
		$encoded = $this->prefs[$module];
		$decoded = unserialize(base64_decode($encoded));
		/*fall back to default if nothing set*/
		if(!isset($decoded[$name])) {
			return $this->getDefault($name,$module);
		}
		return $decoded[$name];
	}

	function getDefault($name,$module) {
		$al = AutoLoader::Instance();
		$al->addPath(CONTROLLER_ROOT.strtolower($module).'/');
		$classname = ucwords($module) . 'Preferences.php';
		if(class_exists($classname)) {
			$preferences = new $classname(false);
			return $preferences->getPreferenceDefault($preferencename);
		} else {
			return null;
		}
	}

	function setPreferenceValue($name,$module,$value) {
		if(!isset($this->prefs[$module])) {
			$this->prefs[$module]=array();
		}
		$encoded = $this->prefs[$module];
		if(!is_string($encoded)) {
			$encoded = '';
		}
		$decoded = unserialize(base64_decode($encoded));
		
		$decoded[$name] = $value;
		
		$encoded = base64_encode(serialize($decoded));
		$db = DB::Instance();
		$data=array(
			'username'=>$this->username,
			'module'=>$module,
			'settings'=>$encoded
		);
		$db->Replace('userpreferences',$data,array('username','module'),true);
		$this->initialise();
	}
}
?>
