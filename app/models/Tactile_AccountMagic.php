<?php
/**
 * Responsible for representing user-choices
 */
class Tactile_AccountMagic extends DataObject {

	protected static $cache = array();
	
	protected static $cache_loaded = false; 
	
	/**
	 * 
	 * @param $tablename	string	The name of a table in the database 
	 */
	public function __construct() {
		parent::__construct('tactile_accounts_magic');
	}

	public function loadByKey($key) {
		if (!isset(self::$cache[$key])) {
			if (!self::$cache_loaded) {
				$cc = new ConstraintChain();
				$cc->add(new Constraint('key', '=', $key));
				$cc->add(new Constraint('usercompanyid', '=', EGS::getCompanyId()));
				self::$cache[$key] = $this->loadBy($cc);
			}
			else {
				return false;
			}
		}
		return self::$cache[$key];
	}

	/**
	 * Save a key => value choice
	 *
	 * @param String $key
	 * @param String $value
	 */
	public static function saveChoice($key, $value) {
		$tm = new Tactile_AccountMagic();
		$exists = $tm->loadByKey($key);
		if ($exists !== false) {
			$tm = $exists;
		}
		$tm->usercompanyid = EGS::getCompanyId();
		$tm->key = $key;
		if ($value === true) {
			$value = 't';
		}
		if ($value === false) {
			$value = 'f';
		}
		$tm->value = $value;
		$success = $tm->save();
		if ($success) {
			self::$cache[$key] = $tm;
		}
		return $success;
	}

	public function clearChoice($key) {
		$tm = new Tactile_AccountMagic();
		$exists = $tm->loadByKey($key);
		if (FALSE !== $exists) {
			if (FALSE !== $exists->delete()) {
				unset(self::$cache[$key]);
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	
	public function toggleChoice($key, $default = 't', $values = array('t', 'f')) {
		$current = self::getValue($key, $default);
		$toggled = $values[(array_search($current, $values) + 1) % count($values)];
		return self::saveChoice($key, $toggled);
	}
	
	/**
	 * Convenience method for returning the value of an entry as compared to a supplied value
	 * 
	 * @param String $key
	 * @param String optional $match - the string to compare the value to, defaults to 't' (true)
	 * @param String optional $default - the string to compare to if there is no value
	 * @return Boolean
	 */
	public static function getAsBoolean($key, $match = 't', $default = '') {
		$value = self::getValue($key, $default);
		return $value == $match;
	}

	/**
	 * Get the value for a given key
	 *
	 * @param String $key
	 * @param String optional $default The string that is returned if there is no value
	 * @return String
	 */
	public function getValue($key, $default = '') {
		$tm = new Tactile_AccountMagic();
		$tm = $tm->loadByKey($key);
		if ($tm === false) {
			return $default;
		}
		return $tm->value;
	}
	
	public static function loadAll() {
		$db = DB::Instance();
		$query = 'SELECT * FROM tactile_accounts_magic WHERE usercompanyid = ' . $db->qstr(EGS::getCompanyId());
		$rows = $db->GetArray($query);
		foreach ($rows as $row) {
			$m = new Tactile_AccountMagic();
			$m->_data = $row;
			$m->load($row['id']);
			self::$cache[$m->key] = $m;
		}
		self::$cache_loaded = true;
	}

}
