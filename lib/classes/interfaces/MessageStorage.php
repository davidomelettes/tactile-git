<?php
/**
 * Classes implementing this will be responsible for storing errors and messages for some later use
 */
interface MessageStorage {
	
	/**
	 * Saves the state of the object, however the implementation needs to do so
	 */
	public function save();
	
	/**
	 * Accessor for messages
	 * @return Array
	 */
	public function getMessages();
	
	/**
	 * Accessor for messages, returns JSON
	 * @return String
	 */
	public function getMessagesAsJSON();
	
	/**
	 * Accessor for Errors, returns JSON
	 * @return String
	 */
	public function getErrorsAsJSON();
	
			
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
	public function addError($error,$fieldname=null,$prefix='');
	
	/**
	 * Add the supplied message to the flash 
	 * 
	 * @param String $message
	 * @return void
	 */
	public function addMessage($message);
	
	/**
	 * Add a series of errors to the store
	 * 
	 * Assumes the keys of the array are fieldnames (them not being won't really matter though)
	 * @param Array $errors
	 * @param String $prefix
	 * @see SessionFlash::addError()
	 */
	public function addErrors($errors,$prefix='');
	
	/**
	 * Removes all errors and messages (from the object and from any storage)
	 * @return void
	 */
	public function clear();
		
	/**
	 * Clears the messages array (object only)
	 * 
	 * @return void
	 */
	public function clearMessages();

	/**
	 * Returns true iff there are any stored errors
	 * 
	 * @return Boolean
	 */
	public function hasErrors();
}
?>