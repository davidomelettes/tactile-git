<?php
class Hash implements YAMLable, Iterator {
	protected $hash=array();
	private $pointer=0;
	function set($key,$val) {
		$this->hash[$key]=$val;
	}
	
	function add($val) {
		$this->hash[]=$val;
	}
	
	function toArray() {
		return $this->hash;
	}

	function current() {
		return current($this->hash);
	}
	function next() {
	$this->pointer++;
		return next($this->hash);
	}
	function rewind() {
	$this->pointer=0;
		reset($this->hash);
	}
	function key() {
		return key($this->hash);
	}
	function valid() {
		return ($this->pointer < count($this->hash));
	}
}
?>