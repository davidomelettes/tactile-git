<?php
/**
 * Some methods common to the Flash implementations so far used
 */
abstract class FlashBase implements MessageStorage {
	
	/**
	 * Accessor for messages
	 * @return Array
	 */
	public function getMessages() {
		return $this->messages;
	}
	
	/**
	 * Accessor for messages, returns JSON
	 * @return String
	 */
	public function getMessagesAsJSON() {
		return json_encode($this->getMessages());
	}
	
	/**
	 * Accessor for the first (and normally only) success message
	 *
	 * @return String
	 */
	public function getMessageAsJSON() {
		return json_encode(current($this->getMessages()));		
	}
	
	/**
	 * Accessor for Errors, returns JSON
	 * @return String
	 */
	public function getErrorsAsJSON() {
		return json_encode($this->errors);
	}
	
	/**
	 * Add a series of errors to the store
	 * 
	 * Assumes the keys of the array are fieldnames (them not being won't really matter though)
	 * @param Array $errors
	 * @param String $prefix
	 * @see SessionFlash::addError()
	 */
	public function addErrors($errors,$prefix='') {
		foreach($errors as $fieldname=>$error) {
			$this->addError($error,$fieldname,$prefix);
		}
	}
}
?>