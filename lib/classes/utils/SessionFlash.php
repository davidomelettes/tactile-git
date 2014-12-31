<?php
/**
 * The standard implementation that keeps track of messages and errors in the session
 * 
 * Once the messages/errors are pulled (for display) they are removed
 */
class SessionFlash extends FlashBase implements MessageStorage {
	protected $_errors_store=array();
	protected $_errors_show=array();
	protected $_messages_show=array();
	protected $_messages_store=array();

	/**
	 * passing 'true' prevents the display-and-delete behaviour, useful for debugging
	 * @param Boolean $noclear
	 */
	public function __construct($noclear=false) {
		$this->noclear=$noclear;
		$this->restore();
	}
	
	/**
	 * puts the flash object into the session, having stuck _store into _show
	 * @return void
	 */
	public function save() {
		$this->_messages_show=$this->_messages_store;
		$this->_errors_show=$this->_errors_store;
		$_SESSION['flash']=$this;
	}
	
	/**
	 * Pulls saved messages out of the session into the object
	 * @return void
	 */
	private function restore() {
		if(empty($_SESSION['flash'])) {
			$_SESSION['flash']=array();
		}
		$temp=&$_SESSION['flash'];
		foreach($temp as $key=>$var) {
			$this->$key=$var;
		}		
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
			$return=$this->{'_'.$var.'_show'};
			if($this->noclear!==true) {
				$this->{'_'.$var.'_store'}=array();
				$this->save();
			}	
			return $return;
		}
	}
	
	
	
	/**
	 * Add an error message to the store
	 * 
	 * Allows it to be attached to a particular fieldname, with an optional prefix
	 * @param String $error
	 * @param String [$fieldname]
	 * @param String [$prefix]
	 * @return void
	 */
	public function addError($error,$fieldname=null,$prefix='') {
		$this->clearMessages();
		
		if(!in_array($error,$this->_errors_store)) {
			if(!empty($fieldname))
				$this->_errors_store[$prefix.$fieldname]=$error;
			else
				$this->_errors_store['_'.count($this->_errors_store)]=$error;
		}
	}
	
	/**
	 * Add the supplied message to the flash messages array
	 * 
	 * @param String $message
	 * @return void
	 */
	public function addMessage($message) {
		if(!in_array($message, $this->_messages_store)) {
			$this->_messages_store[]=$message;
		}
	}
	
	/**
	 * Removes all errors and messages from the object and from the session
	 * @return void
	 */
	public function clear() {
		$this->_errors_store=array();
		$this->_messages_store=array();
		unset($_SESSION['flash']);
	}	
		
	/**
	 * Clears the messages array
	 * 
	 * @return void
	 */
	public function clearMessages() {
		$this->_messages_store=array();		
	}

	/**
	 * Returns true if there are any stored errors
	 * @return Boolean
	 */
	public function hasErrors() {
		return count($this->_errors_store) != 0;
	}
}
?>