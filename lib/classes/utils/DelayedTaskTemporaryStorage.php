<?php
class DelayedTaskTemporaryStorage implements DelayedTaskStorage {

	protected $store = array();
	
	protected $key;
	
	protected $locked_key;
	
	public function read($key, $lock = true) {
		if(isset($this->store[$key])) {
			$return =  $this->store[$key];
			$this->key = $key;
			if($lock) {
				$this->locked_key = '.'.$this->key;
				$this->store[$this->locked_key] = $this->store[$this->key];
				unset($this->store[$this->key]);
			}
			return $return;			
		}
		return false;
	}
	
	public function write($data) {
		$this->store[] = $data;
	}
	
	public function unlock() {
		if(isset($this->store[$this->locked_key])) {
			$this->store[$this->key] = $this->store[$this->locked_key];
			unset($this->store[$this->locked_key]);
		}
	}
	
	public function remove() {
		if(isset($this->store[$this->key])) {
			unset($this->store[$this->key]);
		}
		if(isset($this->locked_key) && isset($this->store[$this->locked_key])) {
			unset($this->store[$this->locked_key]);
		}
	}
	
}
?>