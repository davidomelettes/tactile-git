<?php

class TimelinePreference {
	
	protected static $_defaults = array(
		'activities'	=> array(
			'new'		=> 'none',
			'completed'	=> 'none',
			'overdue'	=> 'none'
		),
		'opportunities'	=> array(
			'new'		=> 'none'
		),
		'notes'			=> array(
			'new'		=> 'all'
		),
		'emails'		=> array(
			'new'		=> 'all'
		),
		'files'			=> array(
			'new'		=> 'none'
		)
	);
	
	protected static $_values = array('none', 'mine', 'all');
	
	public static function getAll($username) {
		$prefs = self::$_defaults;
		foreach (self::$_defaults as $item => $types) {
			foreach ($types as $type => $default) {
				$key = "timeline_{$item}_{$type}";
				if (FALSE !== ($magic = Omelette_Magic::getValue($key, $username, FALSE))) {
					if (in_array($magic, self::$_values)) {
						$prefs[$item][$type] = $magic;
					}
				}
			}
		}
		return $prefs;
	}
	
	public static function setAll($prefs, $username) {
		$success = true;
		foreach ($prefs as $item => $types) {
			foreach ($types as $type => $value) {
				if (!empty(self::$_defaults[$item][$type]) &&
					!empty($prefs[$item][$type]) && in_array($prefs[$item][$type], self::$_values)) {
					$key = "timeline_{$item}_{$type}";
					if (TRUE !== Omelette_Magic::saveChoice($key, $prefs[$item][$type], $username)) {
						$success = false;
					}
				}
			}
		}
		return $success;
	}
	
}
