<?php
require_once LIB_ROOT.'spyc/spyc.php';

class DelayedTaskYAMLStorage implements DelayedTaskStorage {
	
	/**
	 * The folder that job-files should be saved to
	 *
	 * @var String
	 */
	public static $task_folder;
	
	/**
	 * For YAML storage, this is the filename
	 *
	 * @var String
	 */
	protected $key;
	
	/**
	 * The filename of the 'locked' file
	 *
	 * @var String
	 */
	protected $locked_key;
	
	/**
	 * Contents of task file
	 *
	 * @var Array
	 */
	protected $data;
	
	/**
	 * Read the contents of the file that was passed in as the key.
	 * Setting 'lock' moves the file to a hidden file, doesn't actually lock it...
	 * 
	 * @param String $key The filename to load
	 * @param Boolean optional $lock Defaults to true
	 * @return Array
	 */
	public function read($key, $lock = true) {
		$filename = $key;
		$this->key = $key;
		$data = Spyc::YAMLLoad($filename);
		
		if($lock) {
			$tmp_name = str_replace(basename($filename),'.'.basename($filename),$filename);
			rename($filename,$tmp_name);
			$this->locked_key = $tmp_name;
		}
		
		$this->data = $data;
		return $this->data;
	}
	
	protected function _escapeStrings($data) {
		foreach ($data as $index => $datum) {
			if (is_array($datum)) {
				$data[$index] = $this->_escapeStrings($datum);
			} elseif (is_string($datum) && !is_numeric($datum) && !in_array($datum, array('true','false','TRUE','FALSE'), true)) {
				// This is a string, and not an int
				if (strpos($datum, ':') === FALSE && strpos($datum, '- ') === FALSE) {
					$data[$index] = '"' . $datum . '"';
				} else {
					// Is going to be expressed as a Literal Block, so don't escape
					$data[$index] = $datum;
				}
			}
		}
		return $data;
	}
	
	/**
	 * Writes the $data array as YAML to a uniquely named folder in the folder specified in self::$task_folder
	 *
	 * @param Array $data
	 */
	public function write($data) {
		$data = $this->_escapeStrings($data);
		$yaml = Spyc::YAMLDump($data, false, 0);
		$this->key = tempnam(self::$task_folder,'job');
		//need to make sure whatever user the process runs as can do things with the file!
		chmod($this->key,0666);
		$fp = fopen($this->key,'w+');
		fwrite($fp,$yaml);
		fclose($fp);	
	}
	
	/**
	 * Increments the iteration count, to see how many times this job has been run
	 */
	public function update_iteration() {
		$data = $this->data;
		if (isset($data['iteration'])) {
			$data['iteration'] += 1;
			$yaml = Spyc::YAMLDump($data);
			$fp = fopen($this->locked_key,'w+');
			fwrite($fp,$yaml);
			fclose($fp);
		}
	}
	
	public function getKey() {
		return $this->key;
	}
	
	/**
	 * Renames the .file
	 *
	 */
	public function unlock() {
		if (isset($this->data['iteration']) && $this->data['iteration'] >= 3) {
			// Don't unlock if we've tried three times already
			return;
		}
		if(file_exists($this->locked_key) && !file_exists($this->key)) {
			$this->update_iteration();
			rename($this->locked_key,$this->key);
		}		
	}
	
	/**
	 * Removes both/either of the 'locked' and/or not-locked job files
	 *
	 */
	public function remove() {
		if(file_exists($this->key)) {
			unlink($this->key);
		}
		if(file_exists($this->locked_key)) {
			unlink($this->locked_key);
		}
	}
	
	
}
?>