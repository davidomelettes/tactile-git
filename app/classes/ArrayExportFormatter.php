<?php

class ArrayExportFormatter extends ExportFormatter {
	
	function __construct() {
		$this->_stream = array();
	}
	
	public function addHeadings() {
		return true;
	}
	
	public function output($rows) {
		foreach ($rows as $row) {
			$this->_stream[] = $row;
		}
	}
	
}
