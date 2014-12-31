<?php

/**
 * Abstract class for vCalendar items
 */
class VCalObject {
	protected $_name;
	protected $_description;
	protected $_location;
	
	public function __construct($name, $description=null, $location=null) {
		if (!isset($name)) {
			throw new Exception('Missing item name!');
		}
		$this->setName($name);
		
		$this->setDescription($description);
		
		$this->setLocation($location);
	}
	
	public function setName($name) {
		$this->_name = $name;
	}
	
	public function setDescription($desc) {
		$this->_description = $desc;
	}
	
	public function setLocation($location) {
		$this->_location = $location;
	}

	public function escapeString($string) {
		// Consolidate newlines
		$subs = array(
			'/\r\n/'		=> "\n",
			'/\r/'			=> "\n"
		);
		$string = preg_replace(array_keys($subs), array_values($subs), $string);
		$string = preg_replace('/\n/', "\\n", $string);
		
		// "fold" long strings
		$string = implode("\r\n ", str_split($string, 73));
		
		return $string;
	}
	
}
