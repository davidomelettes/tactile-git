<?php
interface DelayedTaskStorage {
	
	public function read($key, $lock = true);
	
	public function write($data);

	public function unlock();
	
	public function remove();
	
}
?>