<?php
/**
 * Responsible for representing user-choices
 */
class Omelette_Magic extends DataObject {

	protected static $cache = array();
	
	protected static $cache_loaded = false; 
	
	/**
	 * 
	 * @param $tablename	string	The name of a table in the database 
	 */
	public function __construct() {
		parent::__construct('tactile_magic');
	}

	public function loadByUsernameKey($username, $key) {
		if(!isset(self::$cache[$username][$key])) {
			if(!self::$cache_loaded) {
				$cc = new ConstraintChain();
				$cc->add(new Constraint('key', '=', $key));
				$cc->add(new Constraint('username', '=', $username));
				self::$cache[$username][$key] = $this->loadBy($cc);
			}
			else {
				if ($username !== CurrentlyLoggedInUser::Instance()->getRawUsername() && preg_match('/'.preg_quote(Omelette::getUserspace()).'/', $username)) {
					$cc = new ConstraintChain();
					$cc->add(new Constraint('key', '=', $key));
					$cc->add(new Constraint('username', '=', $username));
					self::$cache[$username][$key] = $this->loadBy($cc);
				} else {
					return false;
				}
			}
		}
		return self::$cache[$username][$key];
	}

	/**
	 * Save a key=>value choice for the given username
	 *
	 * @param String $key
	 * @param String $value
	 * @param String $username
	 */
	public static function saveChoice($key, $value, $username=null) {
		$username = (empty($username) ? EGS::getUsername() : $username);
		$tm = new Omelette_Magic();
		$exists = $tm->loadByUsernameKey($username, $key);
		if($exists!==false) {
			$tm = $exists;
		}
		$tm->key = $key;
		if($value===true) {
			$value = 't';
		}
		if($value===false) {
			$value = 'f';
		}
		$tm->value = $value;
		$tm->username = $username;
		$success = $tm->save();
		if ($success) {
			self::$cache[$username][$key] = $tm;
		}
		return $success;
	}

	public function toggleChoice($key, $username, $default = 't', $values = array('t', 'f')) {
		$current = self::getValue($key,$username,$default);
		$toggled = $values[(array_search($current,$values)+1)%count($values)];
		return self::saveChoice($key,$toggled,$username);
	}
	
	/**
	 * Convenience method for returning the value of an entry as compared to a supplied value
	 * 
	 * @param String $key
	 * @param String $username
	 * @param String optional $match - the string to compare the value to, defaults to 't' (true)
	 * @param String optional $default - the string to compare to if there is no value
	 * @return Boolean
	 */
	public static function getAsBoolean($key, $username, $match = 't', $default = '') {
		$value = self::getValue($key, $username, $default);
		return $value == $match;
	}

	/**
	 * Get the value for a given key
	 *
	 * @param String $key
	 * @param String $username
	 * @param String optional $default The string that is returned if there is no value
	 * @return String
	 */
	public function getValue($key, $username=null, $default = '') {
		$username = (empty($username) ? EGS::getUsername() : $username);
		$tm = new Omelette_Magic();
		$tm = $tm->loadByUsernameKey($username, $key);
		if($tm === false) {
			return $default;
		}
		return $tm->value;
	}
	
	public static function loadAll($username) {
		$db = DB::Instance();
		$query = 'SELECT * FROM tactile_magic WHERE username='.$db->qstr($username);
		$rows = $db->GetArray($query);
		foreach($rows as $row) {
			$m = new Omelette_Magic();
			$m->_data = $row;
			$m->load($row['id']);
			self::$cache[$username][$m->key] = $m;
		}
		self::$cache_loaded = true;
	}
	
	/*
	 * @de The only purpose of this function is to allow the unit tests to clear the contents of the cache between tests
	 */
	public static function clearAll() {
		self::$cache = array();
		self::$cache_loaded = false;
	}

}
?>