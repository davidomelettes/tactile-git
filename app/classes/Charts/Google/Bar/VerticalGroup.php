<?php

require_once('Charts/Google/Bar/Vertical.php');

class Charts_Google_Bar_Vertical_Group extends Charts_Google_Bar_Vertical {
	
	public function __construct() {
		$this->_type = 'bvg';
	}
	
}
