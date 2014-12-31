<?php
class NonSessionFlash extends FlashBase implements MessageStorage {
	protected $errors=array();
	protected $messages=array();
	
	/**
	 * Saves the state of the object, however the implementation needs to do so
	 */
	public function save() {
		
	}
	
	/**
	 * Add an error message to the store
	 * 
	 * Allows it to be attached to a particular fieldname, with an optional prefix
	 * 
	 * @param String $error
	 * @param String [$fieldname]
	 * @param String [$prefix]
	 * 
	 * @return void
	 */
	public function addError($error,$fieldname=null,$prefix=null) {
		if(is_null($fieldname) && is_null($prefix)) {
			$this->errors[] = $error;
		}
		else {
			$this->errors[$prefix.$fieldname] = $error; 
		}
		
	}
	
	/**
	 * Add the supplied message to the flash messages array
	 * 
	 * @param String $message
	 * @return void
	 */
	public function addMessage($message) {
		if(!in_array($message, $this->messages)) {
			$this->messages[]=$message;
		}
	}
	
	/**
	 * Removes all errors and messages (from the object and from any storage)
	 * @return void
	 */
	public function clear() {
		$this->errors=array();
		$this->messages=array();
	}
		
	/**
	 * Clears the messages array (object only)
	 * 
	 * @return void
	 */
	public function clearMessages() {
		$this->messages = array();
	}

	/**
	 * Returns true iff there are any stored errors
	 * 
	 * @return Boolean
	 */
	public function hasErrors() {
		return count($this->errors) > 0;
	}
	
/**
	 * magic-get allows read-access to errors and messages
	 * 
	 * this is where $noclear being set has an effect
	 * @param String $var
	 * @return Array
	 */
	public function __get($var) {
		if($var=='errors'||$var=='messages') {
			return $this->$var;
		}
	}
}
?>