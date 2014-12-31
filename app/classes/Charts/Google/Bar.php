<?php

require_once('Charts/Google.php');

class Charts_Google_Bar extends Charts_Google {
	
	protected $_barStyle = array();
	protected $_autoSizeColumns = false;
	
	public function __construct() {
		$this->_type = 'bhs';
	}
	
	public function setBarStyle($style) {
		$this->_setCommaOptions('chbh', $style);
		
		return $this;
	}
	
	public function addLineStyle($style) {
		$this->_addCommaPipeOptions('chls', $style);
		
		return $this;
	}
	
	public function setAutoSizeColumns($bool=true) {
		$this->_autoSizeColumns = $bool;
		
		return $this;
	}
	
	public function outputSrc($sizeX, $sizeY) {
		if ($this->_autoSizeColumns) {
			// Fudge the width
			$bars = $this->_countData();
			if ($bars > 0) {
				$horizontal_axes = 0;
				foreach ($this->_axes as $axis => $axes) {
					if ($axis == 'x' || $axis == 't') {
						$horizontal_axes += count($axes);
					}
				}
				$width = round(($sizeY - ($bars * 5) - ($horizontal_axes * 16)) / $bars);
				
				$style = array($width);
				$this->setBarStyle($style);
			}
		}
		$output = parent::outputSrc($sizeX, $sizeY);
		
		return $output;
	}
	
}
