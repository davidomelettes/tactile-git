<?php

require_once('Charts/Google/Bar.php');

class Charts_Google_Bar_Vertical extends Charts_Google_Bar {
	
	public function __construct() {
		$this->_type = 'bvs';
	}
	
}
