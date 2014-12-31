<?php
class Registry {
	private $store;
	private function __construct() {
		$this->store=array();
	}
	
	public static function Instance() {
		static $registry;
		if($registry==null) {
			$registry=new Registry();
		}
		return $registry;
	}
	
	public function get($key) {
		if(isset($this->store[$key])) {
			return $this->store[$key];
		}	
		throw new Exception('Key not found in Registry: '.$key);
	}
	
	public function set($key,$val) {
		$this->store[$key]=$val;
	}
	
}
?>