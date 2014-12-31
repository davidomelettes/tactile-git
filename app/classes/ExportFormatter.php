<?php

abstract class ExportFormatter {
	
	/**
	 * Export results storage
	 * @var mixed
	 */
	protected $_stream = null;
	
	/**
	 * The fieldnames in the order they want to be outputted
	 * @var Array
	 */
	protected $_ordering;
	
	public function setStream($stream) {
		$this->_stream = $stream;
	}
	
	public function getStream() {
		if ($this->_stream === null) {
			throw new Exception('No stream to fetch!');
		}
		if (!is_array($this->_stream)) {
			rewind($this->_stream);
		}
		return $this->_stream;
	}
	
	public function setOrder($fields) {
		$this->_ordering = $fields;
	}
	
	abstract public function addHeadings();
	
}
