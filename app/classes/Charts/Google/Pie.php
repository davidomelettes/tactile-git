<?php

require_once('Charts/Google.php');

class Charts_Google_Pie extends Charts_Google {
	
	protected $_pieLabels = array();
	
	public function __construct() {
		$this->_type = 'p';
	}
	
	public function setPieLabels($labels) {
		$this->_setPipeOptions('chl', $labels);
		
		return $this;
	}
	
}
