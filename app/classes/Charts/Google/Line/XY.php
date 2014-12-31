<?php

require_once('Charts/Google/Line.php');

class Charts_Google_Line_XY extends Charts_Google_Line {
	
	public function __construct($data, $dataType='t') {
		parent::__construct($data, $dataType);
		$this->_type = 'lxy';
	}
	
	public function addLineStyle($options) {
		if (!is_array($options)) {
			throw new Exception('Options must be an array');
		}
		$this->_lineStyles[] = $options; 
		
		return $this;
	}
	
}
