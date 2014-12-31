<?php

require_once('Charts/Google/Pie.php');

class Charts_Google_Pie_3D extends Charts_Google_Pie {
	
	public function __construct($data, $dataType='t') {
		parent::__construct($data, $dataType);
		$this->_type = 'p3';
	}
	
}
