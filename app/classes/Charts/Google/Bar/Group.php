<?php

require_once('Charts/Google/Bar.php');

class Charts_Google_Bar_Group extends Charts_Google_Bar {
	
	public function __construct() {
		$this->_type = 'bhg';
	}
	
}
