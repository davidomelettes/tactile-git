<?php

require_once('Charts/Google.php');

class Charts_Google_Line extends Charts_Google {
	
	public function __construct() {
		$this->_type = 'lc';
	}

	public function addLineStyle($style) {
		$this->_addCommaPipeOptions('chls', $style);
		
		return $this;
	}
	
}
